<?php
require_once __DIR__ . '/../boot.php';

session_start();
if (!isset($_SESSION['swift_auth'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$pdo = \Swift\Core\Database::getInstance();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed. Please check swift/config.php']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
    
    // We will handle timezone conversion in PHP to be robust against MySQL configuration issues
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN classification != 'normal' THEN 1 ELSE 0 END) as threats,
            AVG(risk_score) as avg_risk,
            COUNT(DISTINCT CASE WHEN classification != 'normal' THEN ip ELSE NULL END) as unique_ips
        FROM swift_logs 
        WHERE timestamp > ?
    ");
    $stmt->execute([$yesterday]);
    $stats = $stmt->fetch();

    $total = $stats['total'] ?? 0;
    $threats = $stats['threats'] ?? 0;
    $avgRisk = round($stats['avg_risk'] ?? 0, 1);
    $uniqueIps = $stats['unique_ips'] ?? 0;

    // 24h Breakdown
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN classification = 'malicious' THEN 1 ELSE 0 END) as malicious_24h,
            SUM(CASE WHEN classification = 'suspicious' THEN 1 ELSE 0 END) as suspicious_24h
        FROM swift_logs 
        WHERE timestamp > ?
    ");
    $stmt->execute([$yesterday]);
    $breakdown24h = $stmt->fetch();

    $malicious24h = $breakdown24h['malicious_24h'] ?? 0;
    $suspicious24h = $breakdown24h['suspicious_24h'] ?? 0;

    // Lifetime Stats
    $lifetimeStats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN classification != 'normal' THEN 1 ELSE 0 END) as threats,
            SUM(CASE WHEN classification = 'malicious' THEN 1 ELSE 0 END) as malicious,
            SUM(CASE WHEN classification = 'suspicious' THEN 1 ELSE 0 END) as suspicious
        FROM swift_logs
    ")->fetch();

    $totalLifetime = $lifetimeStats['total'] ?? 0;
    $threatsLifetime = $lifetimeStats['threats'] ?? 0;
    $maliciousLifetime = $lifetimeStats['malicious'] ?? 0;
    $suspiciousLifetime = $lifetimeStats['suspicious'] ?? 0;

    // Lifetime Unique Attackers
    $uniqueLifetime = $pdo->query("
        SELECT COUNT(DISTINCT ip) 
        FROM swift_logs 
        WHERE classification != 'normal'
    ")->fetchColumn();

    $filter = $_GET['filter'] ?? 'all';
    $whereClause = "WHERE 1=1";
    if ($filter === 'threats') {
        $whereClause = "WHERE classification != 'normal'";
    } elseif ($filter === 'safe') {
        $whereClause = "WHERE classification = 'normal'";
    }

    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM swift_logs $whereClause");
    $totalLogs = $countStmt->fetch()['total'];
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    if ($limit < 1) $limit = 25;
    if ($limit > 100) $limit = 100;
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;
    
    $logQuery = "SELECT * FROM swift_logs $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $recentLogs = $pdo->query($logQuery)->fetchAll();
    

    // We need to know the SYSTEM timezone to interpret the DB string correctly.
    // robust method: get format +07:00 directly from MySQL if possible, or easily parsable string
    $sysOffsetStr = $pdo->query("SELECT TIME_FORMAT(TIMEDIFF(NOW(), UTC_TIMESTAMP), '%H:%i')")->fetchColumn();
    // Prepend + if not negative
    if ($sysOffsetStr && $sysOffsetStr[0] !== '-' && $sysOffsetStr[0] !== '+') {
        $sysOffsetStr = '+' . $sysOffsetStr;
    }

    $targetTz = new DateTimeZone(date_default_timezone_get());
    $sysTz = null;
    try {
        if ($sysOffsetStr) $sysTz = new DateTimeZone($sysOffsetStr);
    } catch (Exception $e) { /* ignore */ }

    // Convert timestamps in PHP
    
    foreach ($recentLogs as &$log) {
        if (!$log['timestamp']) continue; // Skip if empty
        
        try {
            // If we successfully determined system TZ, use it. Otherwise assume server time = DB time (no conversion)
            if ($sysTz) {
                $logTime = new DateTime($log['timestamp'], $sysTz); 
                $logTime->setTimezone($targetTz);
                $log['timestamp'] = $logTime->format('Y-m-d H:i:s');
            }
        } catch (Exception $e) {
            // Keep original timestamp if conversion fails
        }
    }

    $totalPages = ceil($totalLogs / $limit);

    // Top Attackers (Lifetime View to reflect database volume)
    $stmt = $pdo->query("
        SELECT ip, COUNT(*) as attack_count, SUM(risk_score) as total_risk 
        FROM swift_logs 
        WHERE classification != 'normal'
        GROUP BY ip 
        ORDER BY total_risk DESC 
        LIMIT 15
    ");
    $topAttackers = $stmt->fetchAll();

    // For traffic chart, fetch raw data and group in PHP
    $stmt = $pdo->prepare("
        SELECT timestamp 
        FROM swift_logs 
        WHERE timestamp > ? 
        ORDER BY timestamp ASC
    ");
    $stmt->execute([$yesterday]);
    $rawTraffic = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Group by hour in User Timezone
    $trafficDataMap = [];
    $sysOffsetStr = $pdo->query("SELECT TIME_FORMAT(TIMEDIFF(NOW(), UTC_TIMESTAMP), '%H:%i')")->fetchColumn();
    if ($sysOffsetStr && $sysOffsetStr[0] !== '-' && $sysOffsetStr[0] !== '+') {
        $sysOffsetStr = '+' . $sysOffsetStr;
    }

    $sysTz = null;
    try {
        if ($sysOffsetStr) $sysTz = new DateTimeZone($sysOffsetStr);
    } catch (Exception $e) {}
    
    $userTz = new DateTimeZone(date_default_timezone_get());

    foreach ($rawTraffic as $ts) {
        try {
            if ($sysTz) {
                $dt = new DateTime($ts, $sysTz);
                $dt->setTimezone($userTz);
                $hour = $dt->format('H:00');
            } else {
                 $hour = date('H:00', strtotime($ts));
            }
            if (!isset($trafficDataMap[$hour])) $trafficDataMap[$hour] = 0;
            $trafficDataMap[$hour]++;
        } catch (Exception $e) {}
    }

    $trafficData = [];
    foreach ($trafficDataMap as $h => $c) {
        $trafficData[] = ['hour' => $h, 'count' => $c];
    }

    $stmt = $pdo->prepare("
        SELECT classification, COUNT(*) as count 
        FROM swift_logs 
        WHERE timestamp > ? 
        GROUP BY classification
    ");
    $stmt->execute([$yesterday]);
    $threatData = $stmt->fetchAll();

    // Lifetime Threat Data (Pie Chart)
    $threatDataLifetime = $pdo->query("
        SELECT classification, COUNT(*) as count 
        FROM swift_logs 
        GROUP BY classification
    ")->fetchAll();

    $stmt = $pdo->prepare("
         SELECT detection_tags, COUNT(*) as count 
         FROM swift_logs 
         WHERE classification != 'normal' AND timestamp > ? 
         GROUP BY detection_tags
         ORDER BY count DESC
         LIMIT 10
    ");
    $stmt->execute([$yesterday]);
    $attackVectors = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT uri, COUNT(*) as count, SUM(risk_score) as risk 
        FROM swift_logs 
        WHERE classification != 'normal' AND timestamp > ? 
        GROUP BY uri 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $stmt->execute([$yesterday]);
    $topEndpoints = $stmt->fetchAll();

    echo json_encode([
        'total_24h' => $total,
        'threats_24h' => $threats,
        'malicious_24h' => $malicious24h,
        'suspicious_24h' => $suspicious24h,
        'total_lifetime' => $totalLifetime,
        'threats_lifetime' => $threatsLifetime,
        'malicious_lifetime' => $maliciousLifetime,
        'suspicious_lifetime' => $suspiciousLifetime,
        'unique_ips_lifetime' => $uniqueLifetime,
        'avg_risk' => $avgRisk,
        'unique_ips' => $uniqueIps,
        'recent_logs' => $recentLogs,
        'top_attackers' => $topAttackers,
        'attack_vectors' => $attackVectors,
        'top_endpoints' => $topEndpoints,
        'chart_traffic' => $trafficData,
        'chart_threats' => $threatData,
        'chart_threats_lifetime' => $threatDataLifetime,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_logs' => $totalLogs,
            'limit' => $limit
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}

if ($action === 'export') {
    $stmt = $pdo->query("SELECT * FROM swift_logs ORDER BY id DESC LIMIT 1000"); 
    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="swift_logs_export_' . date('Y-m-d_H-i') . '.csv"');

    $output = fopen('php://output', 'w');
    if (count($logs) > 0) {
        fputcsv($output, array_keys($logs[0])); 
        foreach ($logs as $log) {
            fputcsv($output, $log);
        }
    }
    fclose($output);
    exit;
}

if ($action === 'ai_summary') {
    $pdo = \Swift\Core\Database::getInstance();
    
    $stmt = $pdo->query("SELECT method, uri, classification, risk_score, detection_tags, headers, payload FROM swift_logs ORDER BY id DESC LIMIT 15");
    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($logs)) {
        echo json_encode(['summary' => "<b>Security Briefing:</b><br>Insufficient telemetry data collected for AI analysis at this time."]);
        exit;
    }

    foreach ($logs as &$log) {
        if (isset($log['headers'])) {
            $log['headers'] = substr($log['headers'], 0, 200) . (strlen($log['headers']) > 200 ? '...' : '');
        }
        if (isset($log['payload'])) {
            $log['payload'] = substr($log['payload'], 0, 300) . (strlen($log['payload']) > 300 ? '...' : '');
        }
    }

    $groq = new \Swift\Core\GroqService();
    $analysis = $groq->analyzeLogs($logs);
    
    $html = $analysis;
    $html = preg_replace('/^### (.*$)/m', '<h5 class="text-accent font-bold mt-3 mb-1">$1</h5>', $html);
    $html = preg_replace('/^## (.*$)/m', '<h4 class="text-accent font-bold mt-4 mb-2">$1</h4>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h3 class="text-accent font-bold mt-4 mb-2">$1</h3>', $html);
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/^\- (.*$)/m', '<li class="ml-4">$1</li>', $html);
    $html = preg_replace('/`(.*?)`/', '<code class="bg-surface-light px-1 rounded font-mono text-xs">$1</code>', $html);
    $html = nl2br($html);
    
    echo json_encode(['summary' => "<b>Deep AI Analysis:</b><br><div class='mt-2'>" . $html . "</div>"]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);

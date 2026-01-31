<?php
require_once __DIR__ . '/../boot.php';

session_start();
if (!isset($_SESSION['swift_auth'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$pdo = \Swift\Core\Database::getInstance();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed. Please check swift/config.php']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM swift_logs WHERE timestamp > NOW() - INTERVAL 1 DAY");
    $total = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as threats FROM swift_logs WHERE classification != 'normal' AND timestamp > NOW() - INTERVAL 1 DAY");
    $threats = $stmt->fetch()['threats'];

    $stmt = $pdo->query("SELECT AVG(risk_score) as avg_risk FROM swift_logs WHERE timestamp > NOW() - INTERVAL 1 DAY");
    $avgRisk = round($stmt->fetch()['avg_risk'] ?? 0, 1);

    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip) as unique_ips FROM swift_logs WHERE classification != 'normal' AND timestamp > NOW() - INTERVAL 1 DAY");
    $uniqueIps = $stmt->fetch()['unique_ips'];

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
    
    $stmt = $pdo->query($logQuery);
    $recentLogs = $stmt->fetchAll();

    $totalPages = ceil($totalLogs / $limit);

    $stmt = $pdo->query("
        SELECT ip, COUNT(*) as attack_count, SUM(risk_score) as total_risk 
        FROM swift_logs 
        WHERE classification != 'normal' AND timestamp > NOW() - INTERVAL 1 DAY 
        GROUP BY ip 
        ORDER BY total_risk DESC 
        LIMIT 5
    ");
    $topAttackers = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT DATE_FORMAT(timestamp, '%H:00') as hour, COUNT(*) as count 
        FROM swift_logs 
        WHERE timestamp > NOW() - INTERVAL 1 DAY 
        GROUP BY hour 
        ORDER BY timestamp ASC
    ");
    $trafficData = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT classification, COUNT(*) as count 
        FROM swift_logs 
        WHERE timestamp > NOW() - INTERVAL 1 DAY 
        GROUP BY classification
    ");
    $threatData = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT detection_tags, COUNT(*) as count 
        FROM swift_logs 
        WHERE classification != 'normal' AND timestamp > NOW() - INTERVAL 1 DAY 
        GROUP BY detection_tags
        ORDER BY count DESC
        LIMIT 10
    ");
    $attackVectors = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT uri, COUNT(*) as count, SUM(risk_score) as risk 
        FROM swift_logs 
        WHERE classification != 'normal' AND timestamp > NOW() - INTERVAL 1 DAY 
        GROUP BY uri 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $topEndpoints = $stmt->fetchAll();

    echo json_encode([
        'total_24h' => $total,
        'threats_24h' => $threats,
        'avg_risk' => $avgRisk,
        'unique_ips' => $uniqueIps,
        'recent_logs' => $recentLogs,
        'top_attackers' => $topAttackers,
        'attack_vectors' => $attackVectors,
        'top_endpoints' => $topEndpoints,
        'chart_traffic' => $trafficData,
        'chart_threats' => $threatData,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_logs' => $totalLogs,
            'limit' => $limit
        ]
    ]);
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

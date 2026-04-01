<?php
if (defined('SWIFT_AGENT_RUNNING')) return;
define('SWIFT_AGENT_RUNNING', true);

// SWIFT Security Agent
// Include this at the very top of your application

if (file_exists(__DIR__ . '/swift/boot.php')) {
    require_once __DIR__ . '/swift/boot.php';

    try {
        $pdo = \Swift\Core\Database::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'];

        $telemetry = \Swift\Core\Telemetry::capture();
        
        $analyzer = new \Swift\Core\Analyzer();
        $analysis = $analyzer->analyze($telemetry);
        
        $logId = \Swift\Core\Logger::log($telemetry, $analysis);
        
        // 2. Block Malicious Requests (WAF Mode)
        // Only block the specific request containing the payload. Do not ban the IP.
        if ($analysis['classification'] === 'malicious') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $token = bin2hex(random_bytes(16));
            $_SESSION['swift_blocked_id'] = $logId;
            $_SESSION['swift_blocked_token'] = $token;

            // Redirect to 403 immediately with token
            header("Location: /swift2/swift/dashboard/403.php?token=" . $token);
            exit;
        }
        
    } catch (Exception $e) {
        // Fail silently to not disrupt the app
        // error_log("SWIFT Agent Error: " . $e->getMessage());
    }
}

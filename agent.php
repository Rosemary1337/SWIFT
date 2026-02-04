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
        
        \Swift\Core\Logger::log($telemetry, $analysis);

        // 2. Block Malicious Requests (WAF Mode)
        // Only block the specific request containing the payload. Do not ban the IP.
        if ($analysis['classification'] === 'malicious') {
            $reason = "WAF Block: Malicious Payload Detected (" . $analysis['detection_tags'] . ")";
            
            // Redirect to 403 immediately
            header("Location: /swift2/swift/dashboard/403.php?reason=" . urlencode($reason));
            exit;
        }
        
    } catch (Exception $e) {
        // Fail silently to not disrupt the app
        // error_log("SWIFT Agent Error: " . $e->getMessage());
    }
}

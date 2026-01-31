<?php
if (defined('SWIFT_AGENT_RUNNING')) return;
define('SWIFT_AGENT_RUNNING', true);

// SWIFT Security Agent
// Include this at the very top of your application

if (file_exists(__DIR__ . '/swift/boot.php')) {
    require_once __DIR__ . '/swift/boot.php';

    try {
        $telemetry = \Swift\Core\Telemetry::capture();
        
        $analyzer = new \Swift\Core\Analyzer();
        $analysis = $analyzer->analyze($telemetry);
        
        \Swift\Core\Logger::log($telemetry, $analysis);
        
    } catch (Exception $e) {
        // Fail silently to not disrupt the app
        // error_log("SWIFT Agent Error: " . $e->getMessage());
    }
}

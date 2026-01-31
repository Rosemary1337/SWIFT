<?php

namespace Swift\Core;

use Swift\Core\Database;

class Logger {
    public static function log($telemetry, $analysis) {
        $pdo = Database::getInstance();
        if (!$pdo) return;

        $stmt = $pdo->prepare("INSERT INTO swift_logs (ip, method, uri, payload_hash, headers, payload, risk_score, classification, detection_tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $payloadHash = hash('sha256', $telemetry['payload']);

        $stmt->execute([
            $telemetry['ip'],
            $telemetry['method'],
            $telemetry['uri'],
            $payloadHash,
            $telemetry['headers'],
            $telemetry['payload'],
            $analysis['risk_score'],
            $analysis['classification'],
            $analysis['detection_tags']
        ]);
    }
}

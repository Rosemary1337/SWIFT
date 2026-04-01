<?php

namespace Swift\Core;

use PDO;
use Exception;

class SecurityAgent {
    private $pdo;
    private $quarantineDir;

    public function __construct() {
        $this->pdo = Database::getInstance();
        $this->quarantineDir = __DIR__ . '/../../storage/quarantine/';
        if (!is_dir($this->quarantineDir)) {
            mkdir($this->quarantineDir, 0755, true);
        }
    }

    /**
     * Inspect an uploaded file for malicious content.
     * 
     * @param array $file The $_FILES element
     * @return array [is_malicious => bool, type => string, risk_score => int, details => string]
     */
    public function inspectUpload($file) {
        $filename = $file['name'];
        $tmpPath = $file['tmp_name'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $riskScore = 0;
        $detectionTags = [];
        $isMalicious = false;

        // 1. Extension Check
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'inc', 'asp', 'aspx', 'jsp', 'exe', 'sh', 'py', 'pl'];
        if (in_array($extension, $dangerousExtensions)) {
            $riskScore += 50;
            $detectionTags[] = "dangerous_extension";
        }

        // 2. Content Scan (Webshell Patterns & Obfuscation)
        if (file_exists($tmpPath)) {
            $content = file_get_contents($tmpPath);
            
            // Basic Signature Patterns
            $patterns = [
                'eval\s*\(' => 40,
                'base64_decode\s*\(' => 30,
                'shell_exec\s*\(' => 50,
                'system\s*\(' => 50,
                'passthru\s*\(' => 50,
                'popen\s*\(' => 40,
                'exec\s*\(' => 40,
                'assert\s*\(' => 40,
                'preg_replace\s*\(.*\/e' => 60,
                'str_rot13\s*\(' => 20,
                'gzuncompress\s*\(' => 20,
                'gzinflate\s*\(' => 20,
                'hex2bin\s*\(' => 20,
                '\$_POST\[.*\]\s*\(\s*\$_POST' => 80, // Dynamic code execution
                'WSO_VERSION' => 100, // WSO Webshell
                'FilesMan' => 100, // FilesMan Webshell
                'c99shell' => 100, // c99 Webshell
            ];

            foreach ($patterns as $pattern => $score) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $riskScore += $score;
                    $detectionTags[] = "pattern_" . str_replace(['\s*', '\(', '\[', '\]', '\$'], '', $pattern);
                }
            }

            // Advanced Obfuscation & Heuristics
            $heuristics = [
                'Execution_of_Encoded_Data' => [
                    'pattern' => '/(eval|assert|system|passthru|shell_exec|exec|preg_replace)\s*\(\s*(base64_decode|str_rot13|gzuncompress|gzinflate|hex2bin|urldecode)\s*\(/i',
                    'score' => 80
                ],
                'Variable_Function_Call' => [
                    'pattern' => '/\$[_a-zA-Z0-9]+\s*\(/',
                    'score' => 45
                ],
                'String_Concatenation_Obfuscation' => [
                    'pattern' => '/([\'"][^\'"]*[\'"]\s*\.\s*){3,}/',
                    'score' => 40
                ],
                'Variable_Variables' => [
                    'pattern' => '/(\$\{\$[_a-zA-Z0-9]+\}|\$\$[_a-zA-Z0-9]+)/',
                    'score' => 30
                ],
                'Backtick_Execution' => [
                    'pattern' => '/`[^`]*`/',
                    'score' => 50
                ],
                'Short_Tag_Execution' => [
                    'pattern' => '/\<\?\=\s*(shell_exec|system|exec|passthru|`)/i',
                    'score' => 80
                ],
                'High_Entropy_String' => [
                    // Matches abnormally long base64/hex strings often representing packed payloads
                    'pattern' => '/([A-Za-z0-9+\/]{200,}={0,2})/',
                    'score' => 60
                ]
            ];

            foreach ($heuristics as $name => $rule) {
                if (preg_match($rule['pattern'], $content)) {
                    $riskScore += $rule['score'];
                    $detectionTags[] = $name;
                }
            }
        }


        if ($riskScore >= 70) {
            $isMalicious = true;
        }

        return [
            'is_malicious' => $isMalicious,
            'risk_score' => min($riskScore, 100),
            'detection_tags' => implode(', ', array_unique($detectionTags)),
            'details' => $isMalicious ? "Malicious patterns detected in file: " . $filename : "File appears safe."
        ];
    }

    /**
     * Log and quarantine a malicious file.
     */
    public function quarantine($file, $detectionResult) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $uri = $_SERVER['REQUEST_URI'] ?? '/upload';
        
        // Log to swift_logs
        $stmt = $this->pdo->prepare("INSERT INTO swift_logs (ip, method, uri, risk_score, classification, detection_tags) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ip,
            'POST',
            $uri,
            $detectionResult['risk_score'],
            'malicious',
            $detectionResult['detection_tags']
        ]);
        $logId = $this->pdo->lastInsertId();

        // Save to quarantine
        $originalName = $file['name'];
        $safeName = $logId . '_' . bin2hex(random_bytes(8)) . '.quarantine';
        $quarantinePath = $this->quarantineDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $quarantinePath)) {
            $stmt = $this->pdo->prepare("INSERT INTO swift_quarantine (log_id, original_name, quarantine_path, detection_details) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $logId,
                $originalName,
                $quarantinePath,
                $detectionResult['details']
            ]);
            return $logId;
        }

        return false;
    }
}

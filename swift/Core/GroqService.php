<?php

namespace Swift\Core;

class GroqService {
    private $apiKey;
    private $apiUrl = 'https://api.groq.com/openai/v1/chat/completions'; 

    public function __construct() {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("SELECT svalue FROM swift_settings WHERE skey = 'groq_api_key' LIMIT 1");
            $stmt->execute();
            $dbKey = $stmt->fetchColumn();
            
            if ($dbKey) {
                $this->apiKey = $dbKey;
                return;
            }
        } catch (\Exception $e) {
        }

        $this->apiKey = SWIFT_CONFIG['groq']['api_key'];
    }

    public function analyzeLogs($logs) {
        if (empty($this->apiKey) || strpos($this->apiKey, 'gsk_') === false) {
            return "Groq API Key not configured.";
        }

        $logContext = json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        
        $prompt = "As a security analyst, analyze these web logs and provide a concise summary of results, patterns, and risks:\n" . $logContext;

        $data = [
            'model' => 'llama-3.3-70b-versatile', 
            'messages' => [
                ['role' => 'system', 'content' => 'You are a security expert.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "Connection error: " . $error_msg;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);
        
        if ($http_code !== 200) {
            $api_error = $json['error']['message'] ?? 'Unknown API error';
            return "Groq API Error ($http_code): " . $api_error;
        }
        
        return $json['choices'][0]['message']['content'] ?? "Analysis failed: Unexpected API response format.";
    }
}

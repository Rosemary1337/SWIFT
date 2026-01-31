<?php

namespace Swift\Core;

class Telemetry {
    public static function capture() {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'headers' => json_encode(self::getHeaders()),
            'payload' => self::getPayload(),
        ];
    }

    private static function getHeaders() {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    private static function getPayload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return file_get_contents('php://input') ?: json_encode($_POST);
        }
        return json_encode($_GET);
    }
}

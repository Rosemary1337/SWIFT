<?php

namespace Swift\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = SWIFT_CONFIG['db'];
        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            error_log("SWIFT DB Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            $instance = new self();
            if (!$instance->pdo) {
                return null;
            }
            self::$instance = $instance;
        }
        return self::$instance->pdo;
    }
}

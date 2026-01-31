<?php

spl_autoload_register(function ($class) {
    $prefix = 'Swift\\';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$config = require __DIR__ . '/config.php';
define('SWIFT_CONFIG', $config);

try {
    $pdo = \Swift\Core\Database::getInstance();
    if ($pdo) {
        $stmt = $pdo->query("SELECT skey, svalue FROM swift_settings WHERE skey IN ('last_cleanup', 'log_retention_days')");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $last_cleanup = $settings['last_cleanup'] ?? 0;
        $retention_days = $settings['log_retention_days'] ?? 3;
        
        if (time() - $last_cleanup > 3600) {
            $pdo->prepare("DELETE FROM swift_logs WHERE timestamp < NOW() - INTERVAL ? DAY")->execute([$retention_days]);
            
            $pdo->prepare("INSERT INTO swift_settings (skey, svalue) VALUES ('last_cleanup', ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)")
                ->execute([time()]);
        }
    }
} catch (\Exception $e) {
}

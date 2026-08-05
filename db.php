<?php
// Configuración de la base de datos MySQL - Detección rápida y auto-sanación sin bloqueo
if (!defined('DB_PASS')) {
    define('DB_PASS', '94010521240Aa@');
}

$db_hosts = ['localhost', '127.0.0.1'];

// Priorizar combinaciones locales más comunes de XAMPP y cPanel
$db_configs = [
    ['user' => 'root', 'pass' => '', 'db' => 'xcolnet_db'],
    ['user' => 'root', 'pass' => '', 'db' => 'y21a91r3ufvk_xcolnet_db'],
    ['user' => 'root', 'pass' => DB_PASS, 'db' => 'xcolnet_db'],
    ['user' => 'y21a91r3ufvk_ANDRES', 'pass' => DB_PASS, 'db' => 'y21a91r3ufvk_xcolnet_db'],
    ['user' => 'y21a91r3ufvk_andres', 'pass' => DB_PASS, 'db' => 'y21a91r3ufvk_xcolnet_db'],
    ['user' => 'andres', 'pass' => '', 'db' => 'xcolnet_db'],
];

$connected = false;
$pdo = null;
$last_error = "";

foreach ($db_hosts as $host) {
    foreach ($db_configs as $config) {
        try {
            $pdo = new PDO(
                "mysql:host=" . $host . ";dbname=" . $config['db'] . ";charset=utf8mb4",
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 1, // Timeout ultrarrápido de 1s por intento para evitar cuelgues
                ]
            );
            if (!defined('DB_HOST')) define('DB_HOST', $host);
            if (!defined('DB_NAME')) define('DB_NAME', $config['db']);
            if (!defined('DB_USER')) define('DB_USER', $config['user']);
            $connected = true;
            break 2;
        } catch (PDOException $e) {
            $last_error = $e->getMessage() . " (Host: $host, DB: {$config['db']})";
        }
    }
}
?>

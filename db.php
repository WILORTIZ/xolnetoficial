<?php
// Configuración de la base de datos MySQL - Detección rápida y auto-sanación sin bloqueo
if (!defined('DB_PASS')) {
    define('DB_PASS', '94010521240Aa@');
}

// Priorizar variables de entorno del servidor para producción si existen
$env_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (defined('DB_PASS') ? DB_PASS : '94010521240Aa@');
$env_user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$env_name = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'xcolnet_db';

$db_hosts = [getenv('DB_HOST') ?: 'localhost', '127.0.0.1'];

// Priorizar combinaciones locales más comunes de XAMPP y cPanel
$db_configs = [
    ['user' => $env_user, 'pass' => $env_pass, 'db' => $env_name],
    ['user' => 'y21a91r3ufvk_ANDRES', 'pass' => '94010521240Aa@', 'db' => 'y21a91r3ufvk_xcolnet_db'],
    ['user' => 'y21a91r3ufvk_ANDRES', 'pass' => '94010521240Aa@', 'db' => 'xcolnet_db'],
    ['user' => 'y21a91r3ufvk_admin', 'pass' => '94010521240Aa@', 'db' => 'y21a91r3ufvk_xcolnet_db'],
    ['user' => 'y21a91r3ufvk_xcolnet', 'pass' => '94010521240Aa@', 'db' => 'y21a91r3ufvk_xcolnet_db'],
    ['user' => $env_user, 'pass' => '', 'db' => $env_name],
    ['user' => 'root', 'pass' => '', 'db' => 'xcolnet_db'],
    ['user' => 'root', 'pass' => '94010521240Aa@', 'db' => 'xcolnet_db'],
    ['user' => 'root', 'pass' => '', 'db' => 'y21a91r3ufvk_xcolnet_db'],
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
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );
            if (!defined('DB_HOST')) define('DB_HOST', $host);
            if (!defined('DB_NAME')) define('DB_NAME', $config['db']);
            if (!defined('DB_USER')) define('DB_USER', $config['user']);
            $connected = true;
            break 2;
        } catch (PDOException $e) {
            error_log("Fallo intento conexión BD (" . $config['user'] . "@" . $host . "): " . $e->getMessage());
            $last_error = "Error al conectar con la base de datos.";
        }
    }
}
?>

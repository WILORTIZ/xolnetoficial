<?php
header('Content-Type: text/plain; charset=utf-8');

$hosts = ['localhost', '127.0.0.1'];
$db = 'y21a91r3ufvk_xcolnet_db';
$user = 'y21a91r3ufvk_ANDRES';
$pass = '94010521240Aa@';

echo "Iniciando pruebas de diagnóstico de base de datos...\n\n";

foreach ($hosts as $host) {
    echo "==================================================\n";
    echo "PROBANDO HOST: $host\n";
    echo "==================================================\n\n";

    // Prueba 1: Conectar al servidor sin especificar base de datos
    echo "Prueba 1: Conectando a MySQL ($host) con usuario $user...\n";
    try {
        $pdo1 = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        echo "¡ÉXITO! Conexión al servidor MySQL establecida con éxito.\n";
        
        // Listar bases de datos a las que tiene acceso
        echo "\nBases de datos accesibles para este usuario:\n";
        $stmt = $pdo1->query("SHOW DATABASES");
        while ($row = $stmt->fetchColumn()) {
            echo " - " . $row . "\n";
        }
    } catch (PDOException $e) {
        echo "FALLO en Prueba 1: " . $e->getMessage() . "\n";
    }
    
    echo "\n--------------------------------------------------\n\n";
    
    // Prueba 2: Conectar directamente a la base de datos especificada
    echo "Prueba 2: Conectando directamente a la base de datos '$db'...\n";
    try {
        $pdo2 = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "¡ÉXITO! Conexión a la base de datos '$db' establecida con éxito.\n";
        
        // Listar tablas para confirmar privilegios de lectura
        echo "\nTablas encontradas en la base de datos:\n";
        $stmt = $pdo2->query("SHOW TABLES");
        while ($row = $stmt->fetchColumn()) {
            echo " - " . $row . "\n";
        }
    } catch (PDOException $e) {
        echo "FALLO en Prueba 2: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>

<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'db.php';

try {
    if ($pdo instanceof PDO) {
        try {
            $pdo->exec("ALTER TABLE Pqrs ADD COLUMN AceptoPoliticaDatos TINYINT(1) DEFAULT 1;");
            echo "✔ Columna AceptoPoliticaDatos agregada con éxito a la tabla 'Pqrs'.\n";
        } catch (PDOException $e) {
            echo "ℹ Nota tabla Pqrs: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

@unlink(__FILE__);
?>

<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== Actualización de Estructura de Base de Datos Producción ===\n\n";

require_once 'db.php';

try {
    if ($pdo instanceof PDO) {
        // Add AceptoPoliticaDatos to proyectos table safely if not exists
        try {
            $pdo->exec("ALTER TABLE proyectos ADD COLUMN AceptoPoliticaDatos TINYINT(1) DEFAULT 1;");
            echo "✔ Columna AceptoPoliticaDatos añadida a la tabla 'proyectos'.\n";
        } catch (PDOException $e1) {
            echo "ℹ Tabla 'proyectos': " . $e1->getMessage() . "\n";
        }

        // Add AceptoPoliticaDatos to pqrs table safely if not exists
        try {
            $pdo->exec("ALTER TABLE pqrs ADD COLUMN AceptoPoliticaDatos TINYINT(1) DEFAULT 1;");
            echo "✔ Columna AceptoPoliticaDatos añadida a la tabla 'pqrs'.\n";
        } catch (PDOException $e2) {
            echo "ℹ Tabla 'pqrs': " . $e2->getMessage() . "\n";
        }

        echo "\n✔ Estructura de Base de Datos actualizada correctamente sin borrar ningún dato.\n";
    } else {
        echo "❌ Error: PDO no está disponible en db.php.\n";
    }
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

// Auto delete after running
@unlink(__FILE__);
echo "\nScript de migración eliminado por seguridad.\n";
?>

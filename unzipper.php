<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== Iniciando Corrección de Permisos y Extracción ===\n\n";

// 1. Corregir permisos de carpetas principales antes de extraer
echo "Corrigiendo permisos de carpetas principales a 0755...\n";
if (file_exists('lib')) {
    chmod('lib', 0755);
    echo " - Permisos de 'lib' corregidos.\n";
}
if (file_exists('images')) {
    chmod('images', 0755);
    echo " - Permisos de 'images' corregidos.\n";
}
if (file_exists('css')) {
    chmod('css', 0755);
    echo " - Permisos de 'css' corregidos.\n";
}
if (file_exists('js')) {
    chmod('js', 0755);
    echo " - Permisos de 'js' corregidos.\n";
}

$zipFile = 'xcolnet_release.zip';
$backupFile = 'db.php.bak';
$dbFile = 'db.php';

// 2. Respaldar db.php si existe
if (file_exists($dbFile)) {
    if (copy($dbFile, $backupFile)) {
        echo "\nBackup de db.php creado con éxito.\n";
    } else {
        echo "\nERROR: No se pudo crear el backup de db.php.\n";
        exit;
    }
} else {
    echo "\nAdvertencia: db.php no existía previamente.\n";
}

// 3. Extraer el zip
if (file_exists($zipFile)) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        if ($zip->extractTo(__DIR__)) {
            echo "Archivos extraídos correctamente del zip.\n";
        } else {
            echo "ERROR: Falló la extracción de los archivos del zip.\n";
            $zip->close();
            exit;
        }
        $zip->close();
    } else {
        echo "ERROR: No se pudo abrir el archivo zip.\n";
        exit;
    }
} else {
    echo "ERROR: El archivo $zipFile no existe en el servidor.\n";
    exit;
}

// 4. Restaurar db.php desde el backup si el backup existe
if (file_exists($backupFile)) {
    if (copy($backupFile, $dbFile)) {
        echo "db.php original del servidor restaurado correctamente.\n";
        unlink($backupFile); // Eliminar el backup temporal
    } else {
        echo "ERROR: No se pudo restaurar el db.php original del servidor.\n";
    }
}

// 5. Corregir permisos de forma recursiva para todo lo extraído
echo "\nAplicando permisos correctos de forma recursiva (Directorios: 0755, Archivos: 0644)...\n";
function fix_permissions_recursive($dir) {
    if (!is_dir($dir)) return;
    @chmod($dir, 0755);
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            fix_permissions_recursive($path);
        } else {
            @chmod($path, 0644);
        }
    }
}

// Aplicar a carpetas clave
fix_permissions_recursive('lib');
fix_permissions_recursive('images');
fix_permissions_recursive('css');
fix_permissions_recursive('js');

// 6. Limpieza final de archivos temporales
echo "\nLimpiando archivos temporales...\n";
@unlink($zipFile);
@unlink('uploader.php');
@unlink('debug_assets.php');
echo "Proceso terminado con éxito.\n";

// Auto-eliminarse
@unlink(__FILE__);
?>

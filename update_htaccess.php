<?php
header('Content-Type: text/plain; charset=utf-8');
echo "Actualizando .htaccess en el servidor...\n";

$htaccess_content = <<<EOD
# Desactivar el puente de Node/Python anterior para habilitar PHP Nativo directamente
PassengerEnabled off

# Forzar la conexión segura HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Configuración básica de reescritura para mayor seguridad y enlaces limpios
DirectoryIndex index.php
Options -Indexes
EOD;

if (file_put_contents('.htaccess', $htaccess_content) !== false) {
    echo "¡ÉXITO! .htaccess actualizado con las reglas de redirección HTTPS.\n";
} else {
    echo "ERROR: No se pudo escribir en el archivo .htaccess. Por favor, revisa los permisos de escritura.\n";
}

// Auto-eliminar
@unlink(__FILE__);
echo "Script temporario de actualización eliminado.\n";
?>

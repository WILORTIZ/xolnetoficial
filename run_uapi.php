<?php
header('Content-Type: text/plain; charset=utf-8');

$domain = 'xcolnet.com';

// Read the certificate from the CRT file
$crt = file_get_contents('xcolnet.com-certificate.crt');
if (!$crt) {
    die("Error: No se pudo leer el archivo xcolnet.com-certificate.crt\n");
}

// Read the CA bundle (optional, but good practice)
$cabundle = file_get_contents('xcolnet.com-intermediate.pem');

// We will run the uapi command.
// We must escape the arguments properly.
// The format is: uapi SSL install_ssl domain=DOMAIN crt=CRT cabundle=CABUNDLE
$cmd = 'uapi SSL install_ssl domain=' . escapeshellarg($domain) . ' crt=' . escapeshellarg($crt);
if ($cabundle) {
    $cmd .= ' cabundle=' . escapeshellarg($cabundle);
}

echo "Ejecutando comando: $cmd\n\n";

$output = shell_exec($cmd);

echo "Resultado de UAPI:\n";
echo $output;

// Auto-delete
@unlink(__FILE__);
echo "\nScript temporario de UAPI eliminado.\n";
?>

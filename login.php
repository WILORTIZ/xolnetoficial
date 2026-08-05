<?php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si ya está autenticado
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errorMessage = "";

// Función para verificar el hash de contraseña de ASP.NET Core Identity V3 en PHP
function verify_aspnet_hash($password, $hashedPassword) {
    $bytes = base64_decode($hashedPassword);
    if (!$bytes || strlen($bytes) < 13) {
        return false;
    }
    
    // El byte 0 indica el formato (0x01 para V3)
    $format = ord($bytes[0]);
    if ($format !== 0x01) {
        // Si no es V3, verificar usando bcrypt estándar de PHP
        return password_verify($password, $hashedPassword);
    }
    
    // Leer enteros de 4 bytes en formato Big-Endian
    $prf = unpack('N', substr($bytes, 1, 4))[1];
    $iterations = unpack('N', substr($bytes, 5, 4))[1];
    $saltSize = unpack('N', substr($bytes, 9, 4))[1];
    
    if (strlen($bytes) < 13 + $saltSize) {
        return false;
    }
    
    $salt = substr($bytes, 13, $saltSize);
    $subkey = substr($bytes, 13 + $saltSize);
    $subkeySize = strlen($subkey);
    
    $algo = 'sha256';
    if ($prf === 2) {
        $algo = 'sha512';
    } elseif ($prf === 0) {
        $algo = 'sha1';
    }
    
    $derived = hash_pbkdf2($algo, $password, $salt, $iterations, $subkeySize, true);
    return hash_equals($subkey, $derived);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['usernameOrEmail'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usernameOrEmail) || empty($password)) {
        $errorMessage = "El usuario/correo y la contraseña son obligatorios.";
    } else {
        try {
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare("
                    SELECT u.*, r.Nombre as RolNombre 
                    FROM Usuarios u 
                    JOIN Roles r ON u.RolId = r.Id 
                    WHERE u.Username = ? OR u.Email = ?
                ");
                $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $user_password_hash = $user['PasswordHash'] ?? $user['passwordhash'] ?? '';
                    $user_id = $user['Id'] ?? $user['id'] ?? 0;
                    $user_username = $user['Username'] ?? $user['username'] ?? '';
                    $user_email = $user['Email'] ?? $user['email'] ?? '';
                    $user_rol_nombre = $user['RolNombre'] ?? $user['rolnombre'] ?? '';

                    if (verify_aspnet_hash($password, $user_password_hash)) {
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $user_username;
                        $_SESSION['email'] = $user_email;
                        $_SESSION['role'] = $user_rol_nombre;
                        
                        header("Location: index.php");
                        exit;
                    } else {
                        $errorMessage = "Credenciales incorrectas.";
                    }
                } else {
                    $errorMessage = "Credenciales incorrectas.";
                }
            } else {
                $errorMessage = "No se pudo conectar a la base de datos MySQL.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error en el servidor al intentar iniciar sesión: " . $e->getMessage();
        }
    }
}

$pageTitle = "Acceso";
include 'header.php';
?>

<section class="min-h-[80vh] flex items-center justify-center py-20 bg-surface">
    <div class="w-full max-w-md px-4">
        <div class="glass rounded-xl p-8 border border-outline-variant/40 shadow-2xl relative overflow-hidden">
            <div class="text-center mb-8">
                <a class="font-headline-md text-headline-md font-bold tracking-tight text-primary flex items-center justify-center gap-2.5 no-underline group mb-3" href="index.php">
                    <span class="brand-icon-wrapper text-primary transition-transform duration-500 ease-in-out group-hover:rotate-[180deg] group-hover:scale-110 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="2.5" fill="currentColor" />
                            <line x1="12" y1="9.5" x2="12" y2="5" />
                            <circle cx="12" cy="3.5" r="1.8" fill="currentColor" />
                            <line x1="14.1" y1="10.5" x2="18.3" y2="7.5" />
                            <circle cx="19.8" cy="6.4" r="1.8" fill="currentColor" />
                            <line x1="14.1" y1="13.5" x2="18.3" y2="16.5" />
                            <circle cx="19.8" cy="17.6" r="1.8" fill="currentColor" />
                            <line x1="9.9" y1="13.5" x2="5.7" y2="16.5" />
                            <circle cx="4.2" cy="17.6" r="1.8" fill="currentColor" />
                            <line x1="9.9" y1="10.5" x2="5.7" y2="7.5" />
                            <circle cx="4.2" cy="6.4" r="1.8" fill="currentColor" />
                        </svg>
                    </span>
                    <span class="text-primary font-bold text-2xl tracking-tight">xcolnet</span>
                </a>
                <h2 class="font-display text-2xl font-bold text-on-surface mb-1">Iniciar Sesión</h2>
                <p class="font-body-md text-on-surface-variant/80 text-sm">Acceso administrativo a la plataforma.</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="mb-6 p-4 bg-error/10 border border-error/30 text-error rounded-lg text-sm text-center flex items-center justify-center gap-2 font-medium">
                    <span class="material-symbols-outlined text-[18px]">warning</span>
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post" class="space-y-5">
                <div class="space-y-1.5 text-left">
                    <label for="usernameOrEmail" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Usuario o Correo Electrónico</label>
                    <input type="text" id="usernameOrEmail" name="usernameOrEmail" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ingresa tu usuario o email" required value="<?php echo isset($_POST['usernameOrEmail']) ? htmlspecialchars($_POST['usernameOrEmail']) : ''; ?>" />
                </div>

                <div class="space-y-1.5 text-left">
                    <label for="password" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Contraseña</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ingresa tu contraseña" required />
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-primary text-white font-label-md font-semibold text-base rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/25">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-6 pt-4 border-t border-outline-variant/20">
                <a href="index.php" class="font-label-md text-sm text-on-surface-variant hover:text-primary transition-colors no-underline font-medium inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span> Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

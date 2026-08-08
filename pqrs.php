<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (empty($postedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errorMessage = "Error de validación de seguridad (CSRF). Por favor recargue e intente nuevamente.";
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $sector = trim($_POST['sector'] ?? 'General');
        $tipo = trim($_POST['tipo'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');
        $politicaDatos = isset($_POST['politicaDatos']);

        if (empty($nombre) || empty($email) || empty($tipo) || empty($mensaje)) {
            $errorMessage = "Por favor, completa todos los campos obligatorios (*).";
        } elseif (!$politicaDatos) {
            $errorMessage = "Debes aceptar la Política de Tratamiento de Datos Personales para radicar tu PQRS.";
        } else {
            try {
                if ($pdo instanceof PDO) {
                    $stmt = $pdo->prepare("INSERT INTO Pqrs (Nombre, Email, Telefono, Tipo, Mensaje, Estado, FechaCreacion, AceptoPoliticaDatos) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $estado = "Pendiente";
                    $fecha = date('Y-m-d H:i:s');
                    $aceptoValor = 1;
                    $tipoFinal = "[$sector] $tipo";
                    
                    $stmt->execute([$nombre, $email, $telefono, $tipoFinal, $mensaje, $estado, $fecha, $aceptoValor]);
                    $pqrsId = $pdo->lastInsertId();
                    
                    $successMessage = "¡Tu solicitud ha sido radicada con éxito! Tu número de radicado es: <strong>#PQRS-$pqrsId</strong>. Nos pondremos en contacto contigo lo antes posible.";
                } else {
                    $errorMessage = "No hay conexión a la base de datos para radicar la PQRS.";
                }
            } catch (PDOException $e) {
                error_log("Error en pqrs.php: " . $e->getMessage());
                $errorMessage = "Error al procesar tu solicitud. Por favor intente más tarde.";
            }
        }
    }
}

$pageTitle = "Radicar PQRS";
include 'header.php';
?>

<section class="py-16 md:py-24 bg-surface">
    <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">Atención al Cliente</span>
            <h1 class="font-display text-display text-3xl md:text-5xl font-bold text-on-surface mb-4">Peticiones, Quejas, Reclamos y Sugerencias</h1>
            <p class="font-body-lg text-on-surface-variant text-base md:text-lg">
                Tu opinión y satisfacción son lo más importante para nosotros. Envíanos tus comentarios o solicitudes.
            </p>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="glass rounded-xl p-8 md:p-10 border border-outline-variant/40 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-6 mb-8">
                    <div>
                        <h2 class="font-headline-lg text-xl md:text-2xl font-bold text-on-surface mb-1">Formulario de Radicación de PQRS</h2>
                        <p class="font-body-md text-on-surface-variant/80 text-sm mb-0">Completa la información requerida para dar seguimiento a tu caso.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php 
                        $uRole = $_SESSION['role'] ?? '';
                        $isAdminPage = isset($_SESSION['user_id']) && (!empty($uRole) && (strtolower($uRole) === 'administrador' || strtolower($uRole) === 'admin' || strpos(strtolower($uRole), 'admin') !== false));
                        if ($isAdminPage): 
                        ?>
                            <a href="admin_pqrs.php" class="px-4 py-2 bg-amber-500/10 text-amber-700 font-label-md text-sm rounded-lg hover:bg-amber-500 hover:text-white transition-all no-underline font-semibold flex items-center gap-1.5 border border-amber-500/30">
                                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span> Buzón PQRS (Admin)
                            </a>
                        <?php endif; ?>
                        <a href="consultar_pqrs.php" class="px-4 py-2 bg-primary/10 text-primary font-label-md text-sm rounded-lg hover:bg-primary hover:text-white transition-all no-underline font-medium flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">search</span> Consultar Radicado
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($successMessage)): ?>
                    <div class="mb-8 p-5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 rounded-lg text-center flex items-center justify-center gap-2 font-medium">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div><?php echo $successMessage; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="mb-8 p-4 bg-error/10 border border-error/30 text-error rounded-lg text-center flex items-center justify-center gap-2 font-medium">
                        <svg class="w-5 h-5 text-error shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div><?php echo $errorMessage; ?></div>
                    </div>
                <?php endif; ?>

                <form action="pqrs.php" method="post" class="space-y-6 text-left">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="nombre" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Nombre Completo *</label>
                            <input type="text" id="nombre" name="nombre" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ingresa tu nombre completo" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" />
                        </div>
                        <div class="space-y-1.5">
                            <label for="email" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="ejemplo@empresa.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1.5">
                            <label for="telefono" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Teléfono de Contacto</label>
                            <input type="text" id="telefono" name="telefono" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ej. +57 317 000 0000" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>" />
                        </div>
                        <div class="space-y-1.5">
                            <label for="sector" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Segmento *</label>
                            <select id="sector" name="sector" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled <?php echo !isset($_POST['sector']) ? 'selected' : ''; ?>>Selecciona tipo</option>
                                <option value="Empresa" <?php echo ($_POST['sector'] ?? '') === 'Empresa' ? 'selected' : ''; ?>>Empresa / Negocio</option>
                                <option value="Hogar" <?php echo ($_POST['sector'] ?? '') === 'Hogar' ? 'selected' : ''; ?>>Hogar / Residencial</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="tipo" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Tipo de Solicitud *</label>
                            <select id="tipo" name="tipo" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled <?php echo !isset($_POST['tipo']) ? 'selected' : ''; ?>>Selecciona tipo</option>
                                <option value="Petición" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'Petición') ? 'selected' : ''; ?>>Petición</option>
                                <option value="Queja" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'Queja') ? 'selected' : ''; ?>>Queja</option>
                                <option value="Reclamo" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'Reclamo') ? 'selected' : ''; ?>>Reclamo</option>
                                <option value="Sugerencia" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'Sugerencia') ? 'selected' : ''; ?>>Sugerencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="mensaje" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Mensaje o Detalle *</label>
                        <textarea id="mensaje" name="mensaje" rows="5" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Describe detalladamente tu solicitud..." required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                    </div>

                    <!-- Checklist Tratamiento de Datos Personales con Visibilidad Claras del Chulo -->
                    <div class="flex items-start gap-3 p-4 bg-surface-container-low/90 rounded-lg border border-primary/30 text-left hover:border-primary transition-all">
                        <input type="checkbox" id="politicaDatos" name="politicaDatos" required <?php echo isset($_POST['politicaDatos']) ? 'checked' : ''; ?> class="mt-1 w-5 h-5 text-primary bg-white border-2 border-primary rounded focus:ring-2 focus:ring-primary/30 cursor-pointer shrink-0 accent-blue-600" />
                        <label for="politicaDatos" class="text-xs font-body-md text-on-surface font-medium leading-relaxed cursor-pointer select-none">
                            Acepto la <a href="#" onclick="alert('Tratamiento de Datos Personales (Ley 1581 de 2012): Sus datos personales serán recolectados y almacenados por XCOLNET con estricta confidencialidad para dar trámite a su PQRS y contacto oficial.'); return false;" class="text-primary font-semibold underline hover:text-primary-container">Política de Tratamiento de Datos Personales</a> (Ley 1581 de 2012) y autorizo a XCOLNET a dar respuesta a mi solicitud. *
                        </label>
                    </div>

                    <div class="pt-4 text-center">
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-primary text-white font-label-md font-semibold text-base rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2 mx-auto">
                            <span class="material-symbols-outlined text-[20px]">send</span> Radicar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

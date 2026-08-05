<?php
require_once 'db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (empty($nombre) || empty($email) || empty($tipo) || empty($mensaje)) {
        $errorMessage = "Por favor, completa todos los campos obligatorios (*).";
    } else {
        try {
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare("INSERT INTO Pqrs (Nombre, Email, Telefono, Tipo, Mensaje, Estado, FechaCreacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $estado = "Pendiente";
                $fecha = date('Y-m-d H:i:s');
                
                $stmt->execute([$nombre, $email, $telefono, $tipo, $mensaje, $estado, $fecha]);
                $radicadoId = $pdo->lastInsertId();
                
                $successMessage = "¡Gracias! Tu PQRS ha sido registrada con éxito con el número de radicado: <strong>#$radicadoId</strong>. Guarda este número para consultar su estado.";
            } else {
                $errorMessage = "No hay conexión a la base de datos para guardar la solicitud.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error al registrar la solicitud: " . $e->getMessage();
        }
    }
}

$pageTitle = "Registrar PQRS";
include 'header.php';
?>

<section class="py-16 md:py-24 bg-surface">
    <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">Atención al Cliente</span>
            <h1 class="font-display text-display text-3xl md:text-5xl font-bold text-on-surface mb-4">Peticiones, Quejas, Reclamos y Sugerencias</h1>
            <p class="font-body-lg text-on-surface-variant text-base md:text-lg">
                Utiliza nuestro formulario oficial para enviar tus solicitudes. Tu requerimiento será atendido por el departamento técnico.
            </p>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="glass rounded-xl p-8 md:p-10 border border-outline-variant/40 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-6 mb-8">
                    <div>
                        <h2 class="font-headline-lg text-xl md:text-2xl font-bold text-on-surface mb-1">Formulario de Registro</h2>
                        <p class="font-body-md text-on-surface-variant/80 text-sm mb-0">Completa tus datos de contacto y el detalle de tu PQRS.</p>
                    </div>
                    <a href="consultar_pqrs.php" class="px-4 py-2 bg-primary/10 text-primary font-label-md text-sm rounded-lg hover:bg-primary hover:text-white transition-all no-underline font-medium flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">search</span> Consultar Estado
                    </a>
                </div>
                
                <?php if (!empty($successMessage)): ?>
                    <div class="mb-8 p-5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 rounded-lg text-center flex items-center justify-center gap-2 font-medium">
                        <span class="material-symbols-outlined text-[22px] text-emerald-600">check_circle</span>
                        <div><?php echo $successMessage; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="mb-8 p-4 bg-error/10 border border-error/30 text-error rounded-lg text-center flex items-center justify-center gap-2 font-medium">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                        <div><?php echo $errorMessage; ?></div>
                    </div>
                <?php endif; ?>

                <form action="pqrs.php" method="post" class="space-y-6 text-left">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="nombre" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Nombre Completo *</label>
                            <input type="text" id="nombre" name="nombre" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ingresa tu nombre completo" required />
                        </div>
                        <div class="space-y-1.5">
                            <label for="email" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="ejemplo@empresa.com" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="telefono" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Teléfono de Contacto</label>
                            <input type="text" id="telefono" name="telefono" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ej. +57 317 000 0000" />
                        </div>
                        <div class="space-y-1.5">
                            <label for="tipo" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Tipo de Solicitud *</label>
                            <select id="tipo" name="tipo" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled selected>Selecciona tipo de solicitud</option>
                                <option value="Petición">Petición</option>
                                <option value="Queja">Queja</option>
                                <option value="Reclamo">Reclamo</option>
                                <option value="Sugerencia">Sugerencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="mensaje" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Mensaje o Detalle *</label>
                        <textarea id="mensaje" name="mensaje" rows="5" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Describe detalladamente tu solicitud..." required></textarea>
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

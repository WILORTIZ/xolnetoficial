<?php
require_once 'db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCliente = trim($_POST['nombreCliente'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $nombreEmpresa = trim($_POST['nombreEmpresa'] ?? '');
    $tipoProyecto = trim($_POST['tipoProyecto'] ?? '');
    $presupuestoEstimado = trim($_POST['presupuestoEstimado'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombreCliente) || empty($email) || empty($telefono) || empty($tipoProyecto) || empty($presupuestoEstimado) || empty($descripcion)) {
        $errorMessage = "Por favor, completa todos los campos obligatorios (*).";
    } else {
        try {
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare("INSERT INTO Proyectos (NombreCliente, Email, Telefono, NombreEmpresa, TipoProyecto, PresupuestoEstimado, Descripcion, Estado, FechaCreacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $estado = "Pendiente";
                $fecha = date('Y-m-d H:i:s');
                
                $stmt->execute([$nombreCliente, $email, $telefono, $nombreEmpresa, $tipoProyecto, $presupuestoEstimado, $descripcion, $estado, $fecha]);
                $proyectoId = $pdo->lastInsertId();
                
                $successMessage = "¡Excelente! Tu solicitud de proyecto ha sido registrada con éxito con el código de seguimiento: <strong>#$proyectoId</strong>. Un especialista técnico se pondrá en contacto contigo a la brevedad.";
            } else {
                $errorMessage = "No hay conexión a la base de datos para registrar el proyecto.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error al registrar la solicitud de proyecto: " . $e->getMessage();
        }
    }
}

$pageTitle = "Solicitar Cotización de Proyecto";
include 'header.php';
?>

<section class="py-16 md:py-24 bg-surface">
    <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">Transformación Digital</span>
            <h1 class="font-display text-display text-3xl md:text-5xl font-bold text-on-surface mb-4">Hablemos de tu Proyecto</h1>
            <p class="font-body-lg text-on-surface-variant text-base md:text-lg">
                Diseñamos e implementamos soluciones a medida en infraestructura de red, seguridad electrónica y conectividad.
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="glass rounded-xl p-8 md:p-10 border border-outline-variant/40 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-6 mb-8">
                    <div>
                        <h2 class="font-headline-lg text-xl md:text-2xl font-bold text-on-surface mb-1">Formulario de Requerimiento Técnico</h2>
                        <p class="font-body-md text-on-surface-variant/80 text-sm mb-0">Define el alcance y presupuesto estimado de tu iniciativa.</p>
                    </div>
                    <a href="consultar_proyecto.php" class="px-4 py-2 bg-primary/10 text-primary font-label-md text-sm rounded-lg hover:bg-primary hover:text-white transition-all no-underline font-medium flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">search</span> Consultar Código
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

                <form action="proyecto.php" method="post" class="space-y-6 text-left">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="nombreCliente" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Nombre y Apellido *</label>
                            <input type="text" id="nombreCliente" name="nombreCliente" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ingresa tu nombre completo" required />
                        </div>
                        <div class="space-y-1.5">
                            <label for="email" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="ejemplo@empresa.com" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="telefono" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Teléfono / WhatsApp *</label>
                            <input type="text" id="telefono" name="telefono" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ej. +57 317 087 7414" required />
                        </div>
                        <div class="space-y-1.5">
                            <label for="nombreEmpresa" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Nombre de la Empresa / Organización</label>
                            <input type="text" id="nombreEmpresa" name="nombreEmpresa" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ej. XCOLNET S.A.S." />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="tipoProyecto" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Tipo de Proyecto *</label>
                            <select id="tipoProyecto" name="tipoProyecto" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled selected>Selecciona el tipo de proyecto</option>
                                <option value="Cableado Estructurado">Cableado Estructurado (Cat 6/6A/7)</option>
                                <option value="Networking & Redes">Networking, Routing & Wi-Fi Corporativo</option>
                                <option value="Seguridad Electrónica">Seguridad Electrónica, CCTV IP & Biometría</option>
                                <option value="Soporte & Mantenimiento TI">Soporte TI & Mantenimiento Proactivo 24/7</option>
                                <option value="Integración de Software & IA">Integración de Software & Automatización IA</option>
                                <option value="Otro Proyecto Tecnológico">Otro Proyecto Tecnológico</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="presupuestoEstimado" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Presupuesto Estimado *</label>
                            <select id="presupuestoEstimado" name="presupuestoEstimado" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled selected>Selecciona rango de presupuesto</option>
                                <option value="Menos de $2,000,000 COP">Menos de $2,000,000 COP</option>
                                <option value="$2,000,000 - $5,000,000 COP">$2,000,000 - $5,000,000 COP</option>
                                <option value="$5,000,000 - $15,000,000 COP">$5,000,000 - $15,000,000 COP</option>
                                <option value="Más de $15,000,000 COP">Más de $15,000,000 COP</option>
                                <option value="Por definir / Requiere Asesoría">Por definir / Requiere Asesoría</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="descripcion" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Descripción del Requerimiento *</label>
                        <textarea id="descripcion" name="descripcion" rows="5" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Describe los detalles, objetivos y requerimientos de tu proyecto..." required></textarea>
                    </div>

                    <div class="pt-4 text-center">
                        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-primary text-white font-label-md font-semibold text-base rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2 mx-auto">
                            <span class="material-symbols-outlined text-[20px]">rocket_launch</span> Enviar Solicitud de Cotización
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

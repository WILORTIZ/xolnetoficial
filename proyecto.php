<?php
require_once 'db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCliente = trim($_POST['nombreCliente'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $sector = trim($_POST['sector'] ?? '');
    $nombreEmpresa = trim($_POST['nombreEmpresa'] ?? '');
    $tipoProyecto = trim($_POST['tipoProyecto'] ?? '');
    $presupuestoEstimado = trim($_POST['presupuestoEstimado'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $politicaDatos = isset($_POST['politicaDatos']);

    if ($sector === 'Hogar' && empty($nombreEmpresa)) {
        $nombreEmpresa = 'Hogar / Particular';
    }

    if (empty($nombreCliente) || empty($email) || empty($telefono) || empty($sector) || empty($tipoProyecto) || empty($presupuestoEstimado) || empty($descripcion)) {
        $errorMessage = "Por favor, completa todos los campos obligatorios (*).";
    } elseif (!$politicaDatos) {
        $errorMessage = "Debes aceptar la Política de Tratamiento de Datos Personales para enviar la solicitud.";
    } else {
        try {
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare("INSERT INTO Proyectos (NombreCliente, Email, Telefono, NombreEmpresa, TipoProyecto, PresupuestoEstimado, Descripcion, Estado, FechaCreacion, AceptoPoliticaDatos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $estado = "Pendiente";
                $fecha = date('Y-m-d H:i:s');
                $aceptoValor = 1;
                
                $empresaFinal = ($sector === 'Hogar') ? "Hogar ($nombreCliente)" : ($nombreEmpresa ?: 'Empresa N/A');
                $tipoFinal = "[$sector] $tipoProyecto";

                $stmt->execute([$nombreCliente, $email, $telefono, $empresaFinal, $tipoFinal, $presupuestoEstimado, $descripcion, $estado, $fecha, $aceptoValor]);
                $proyectoId = $pdo->lastInsertId();
                
                $successMessage = "¡Excelente! Tu solicitud de proyecto ha sido registrada con éxito con el código de seguimiento: <strong>#$proyectoId</strong>. Un especialista técnico se pondrá en contacto contigo a la brevedad.";
            } else {
                $errorMessage = "No hay conexión a la base de datos para registrar el proyecto.";
            }
        } catch (PDOException $e) {
            error_log("Error en proyecto.php: " . $e->getMessage());
            $errorMessage = "Error al registrar la solicitud de proyecto. Por favor intente más tarde.";
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

                <form action="proyecto.php" method="post" class="space-y-6 text-left">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="nombreCliente" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Nombre y Apellido *</label>
                            <input type="text" id="nombreCliente" name="nombreCliente" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ingresa tu nombre completo" required value="<?php echo isset($_POST['nombreCliente']) ? htmlspecialchars($_POST['nombreCliente']) : ''; ?>" />
                        </div>
                        <div class="space-y-1.5">
                            <label for="email" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="ejemplo@empresa.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="telefono" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Teléfono / WhatsApp *</label>
                            <input type="text" id="telefono" name="telefono" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ej. +57 317 087 7414" required value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>" />
                        </div>
                        <div class="space-y-1.5">
                            <label for="sector" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Segmento de Servicio *</label>
                            <select id="sector" name="sector" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled <?php echo !isset($_POST['sector']) ? 'selected' : ''; ?>>Selecciona tipo</option>
                                <option value="Empresa" <?php echo ($_POST['sector'] ?? '') === 'Empresa' ? 'selected' : ''; ?>>Empresa / Negocio</option>
                                <option value="Hogar" <?php echo ($_POST['sector'] ?? '') === 'Hogar' ? 'selected' : ''; ?>>Hogar / Residencial</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campo Empresa/Organización (Visible únicamente si Segmento es Empresa) -->
                    <div id="containerEmpresa" class="space-y-1.5 hidden transition-all duration-300">
                        <label for="nombreEmpresa" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Nombre de la Empresa / Organización *</label>
                        <input type="text" id="nombreEmpresa" name="nombreEmpresa" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ej. XCOLNET S.A.S." value="<?php echo isset($_POST['nombreEmpresa']) ? htmlspecialchars($_POST['nombreEmpresa']) : ''; ?>" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="tipoProyecto" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Tipo de Servicio / Proyecto *</label>
                            <select id="tipoProyecto" name="tipoProyecto" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled <?php echo !isset($_POST['tipoProyecto']) ? 'selected' : ''; ?>>Selecciona el servicio</option>
                                <option value="Mesa de Ayuda para PYMEs" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Mesa de Ayuda para PYMEs' ? 'selected' : ''; ?>>Mesa de Ayuda para Pequeñas Empresas (PYMEs)</option>
                                <option value="Diseño Web & Software a Medida" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Diseño Web & Software a Medida' ? 'selected' : ''; ?>>Diseño de Páginas Web &amp; Software a tu Medida</option>
                                <option value="Seguridad Electrónica" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Seguridad Electrónica' ? 'selected' : ''; ?>>Seguridad Electrónica, CCTV IP & Biometría</option>
                                <option value="Soporte & Mantenimiento TI" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Soporte & Mantenimiento TI' ? 'selected' : ''; ?>>Soporte TI & Mantenimiento Proactivo 24/7</option>
                                <option value="Integración de Software & IA" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Integración de Software & IA' ? 'selected' : ''; ?>>Integración de Software & Automatización IA</option>
                                <option value="Migración de Correos & Cloud" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Migración de Correos & Cloud' ? 'selected' : ''; ?>>Migración de Correos Corporativos (M365 / Google / cPanel)</option>
                                <option value="Otro Proyecto Tecnológico" <?php echo ($_POST['tipoProyecto'] ?? '') === 'Otro Proyecto Tecnológico' ? 'selected' : ''; ?>>Otro Proyecto Tecnológico</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="presupuestoEstimado" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Presupuesto Estimado *</label>
                            <select id="presupuestoEstimado" name="presupuestoEstimado" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" required>
                                <option value="" disabled <?php echo !isset($_POST['presupuestoEstimado']) ? 'selected' : ''; ?>>Selecciona rango de presupuesto</option>
                                <option value="Menos de $2,000,000 COP" <?php echo ($_POST['presupuestoEstimado'] ?? '') === 'Menos de $2,000,000 COP' ? 'selected' : ''; ?>>Menos de $2,000,000 COP</option>
                                <option value="$2,000,000 - $5,000,000 COP" <?php echo ($_POST['presupuestoEstimado'] ?? '') === '$2,000,000 - $5,000,000 COP' ? 'selected' : ''; ?>>$2,000,000 - $5,000,000 COP</option>
                                <option value="$5,000,000 - $15,000,000 COP" <?php echo ($_POST['presupuestoEstimado'] ?? '') === '$5,000,000 - $15,000,000 COP' ? 'selected' : ''; ?>>$5,000,000 - $15,000,000 COP</option>
                                <option value="Más de $15,000,000 COP" <?php echo ($_POST['presupuestoEstimado'] ?? '') === 'Más de $15,000,000 COP' ? 'selected' : ''; ?>>Más de $15,000,000 COP</option>
                                <option value="Por definir / Requiere Asesoría" <?php echo ($_POST['presupuestoEstimado'] ?? '') === 'Por definir / Requiere Asesoría' ? 'selected' : ''; ?>>Por definir / Requiere Asesoría</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="descripcion" class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline">Descripción del Requerimiento *</label>
                        <textarea id="descripcion" name="descripcion" rows="5" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Describe los detalles, objetivos y requerimientos de tu proyecto..." required><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                    </div>

                    <!-- Checklist Tratamiento de Datos Personales con Visibilidad Claras del Chulo -->
                    <div class="flex items-start gap-3 p-4 bg-surface-container-low/90 rounded-lg border border-primary/30 text-left hover:border-primary transition-all">
                        <input type="checkbox" id="politicaDatos" name="politicaDatos" required <?php echo isset($_POST['politicaDatos']) ? 'checked' : ''; ?> class="mt-1 w-5 h-5 text-primary bg-white border-2 border-primary rounded focus:ring-2 focus:ring-primary/30 cursor-pointer shrink-0 accent-blue-600" />
                        <label for="politicaDatos" class="text-xs font-body-md text-on-surface font-medium leading-relaxed cursor-pointer select-none">
                            Acepto la <a href="#" onclick="alert('Tratamiento de Datos Personales (Ley 1581 de 2012): Sus datos personales serán recolectados y almacenados por XCOLNET con estricta confidencialidad para la elaboración de cotizaciones, seguimiento a proyectos y contacto comercial.'); return false;" class="text-primary font-semibold underline hover:text-primary-container">Política de Tratamiento de Datos Personales</a> (Ley 1581 de 2012) y autorizo a XCOLNET a contactarme. *
                        </label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectorSelect = document.getElementById('sector');
    const containerEmpresa = document.getElementById('containerEmpresa');
    const inputEmpresa = document.getElementById('nombreEmpresa');

    function toggleEmpresaField() {
        if (sectorSelect.value === 'Empresa') {
            containerEmpresa.classList.remove('hidden');
            inputEmpresa.setAttribute('required', 'required');
        } else {
            containerEmpresa.classList.add('hidden');
            inputEmpresa.removeAttribute('required');
            inputEmpresa.value = '';
        }
    }

    if (sectorSelect) {
        sectorSelect.addEventListener('change', toggleEmpresaField);
        toggleEmpresaField();
    }
});
</script>

<?php include 'footer.php'; ?>

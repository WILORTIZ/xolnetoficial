<?php
require_once 'db.php';

$searchId = trim($_GET['id'] ?? '');
$proyecto = null;
$searched = false;
$errorMessage = "";

if (!empty($searchId)) {
    $searched = true;
    try {
        if ($pdo instanceof PDO) {
            $stmt = null;
            try {
                $stmt = $pdo->prepare("SELECT * FROM Proyectos WHERE Id = ?");
                $stmt->execute([$searchId]);
            } catch (PDOException $e) {
                $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE Id = ?");
                $stmt->execute([$searchId]);
            }
            $proyecto = $stmt ? $stmt->fetch() : null;
            
            if (!$proyecto) {
                $errorMessage = "No se encontró ningún proyecto registrado con el código <b>#$searchId</b>.";
            }
        } else {
            $errorMessage = "No hay conexión a la base de datos para consultar el proyecto.";
        }
    } catch (PDOException $e) {
        error_log("Error en consultar_proyecto.php: " . $e->getMessage());
        $errorMessage = "Error al consultar la información del proyecto. Por favor intente más tarde.";
    }
}

$pageTitle = "Consultar Estado de Proyecto";
include 'header.php';
?>

<section class="py-16 md:py-24 bg-surface min-h-[80vh]">
    <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">Seguimiento de Cotización</span>
            <h1 class="font-display text-display text-3xl md:text-5xl font-bold text-on-surface mb-4">Consultar Proyecto</h1>
            <p class="font-body-lg text-on-surface-variant text-base md:text-lg">
                Ingresa el código de proyecto recibido para revisar su estado y avance técnico.
            </p>
        </div>

        <div class="max-w-2xl mx-auto mb-10">
            <div class="glass rounded-xl p-6 md:p-8 border border-outline-variant/40 shadow-xl">
                <form action="consultar_proyecto.php" method="get" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 space-y-1">
                        <label for="id" class="sr-only">Código de Proyecto</label>
                        <input type="number" id="id" name="id" class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant/40 rounded-lg text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md" placeholder="Ejemplo: 1" required value="<?php echo htmlspecialchars($searchId); ?>" />
                    </div>
                    <button type="submit" class="px-8 py-3.5 bg-primary text-white font-label-md font-semibold text-base rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">search</span> Buscar
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="max-w-2xl mx-auto p-4 bg-error/10 border border-error/30 text-error rounded-lg text-center flex items-center justify-center gap-2 font-medium mb-8">
                <svg class="w-5 h-5 text-error shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <div><?php echo $errorMessage; ?></div>
            </div>
        <?php endif; ?>

        <?php if ($searched && $proyecto): ?>
            <div class="max-w-2xl mx-auto">
                <div class="glass rounded-xl p-8 border border-outline-variant/40 shadow-2xl relative text-left">
                    <div class="flex items-center justify-between border-b border-outline-variant/20 pb-6 mb-6">
                        <div>
                            <span class="font-mono text-label-md text-outline">CÓDIGO DE PROYECTO</span>
                            <h3 class="font-display text-2xl font-bold text-on-surface mb-0">#<?php echo htmlspecialchars($proyecto['Id'] ?? $proyecto['id']); ?></h3>
                        </div>
                        <?php 
                            $estado = $proyecto['Estado'] ?? $proyecto['estado'] ?? 'Pendiente';
                            $badgeClass = 'bg-amber-500/10 text-amber-600 border-amber-500/30';
                            if ($estado === 'Aprobado') $badgeClass = 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30';
                            if ($estado === 'En Evaluación') $badgeClass = 'bg-blue-500/10 text-blue-600 border-blue-500/30';
                        ?>
                        <span class="px-3.5 py-1.5 rounded-full text-label-md font-bold border <?php echo $badgeClass; ?>">
                            <?php echo htmlspecialchars($estado); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline mb-1">Cliente</p>
                            <p class="font-body-md text-on-surface font-semibold mb-0"><?php echo htmlspecialchars($proyecto['NombreCliente'] ?? $proyecto['nombrecliente']); ?></p>
                        </div>
                        <div>
                            <p class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline mb-1">Empresa</p>
                            <p class="font-body-md text-on-surface font-semibold mb-0"><?php echo htmlspecialchars($proyecto['NombreEmpresa'] ?? $proyecto['nombreempresa'] ?: 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline mb-1">Tipo de Proyecto</p>
                            <p class="font-body-md text-on-surface font-semibold mb-0"><?php echo htmlspecialchars($proyecto['TipoProyecto'] ?? $proyecto['tipoproyecto']); ?></p>
                        </div>
                        <div>
                            <p class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline mb-1">Presupuesto Estimado</p>
                            <p class="font-body-md text-on-surface font-semibold mb-0"><?php echo htmlspecialchars($proyecto['PresupuestoEstimado'] ?? $proyecto['presupuestoestimado']); ?></p>
                        </div>
                    </div>

                    <div class="border-t border-outline-variant/20 pt-6">
                        <p class="font-label-md text-xs font-semibold uppercase tracking-wider text-outline mb-2">Descripción del Requerimiento</p>
                        <div class="p-4 bg-surface-container-low rounded-lg border border-outline-variant/30 text-on-surface-variant font-body-md leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($proyecto['Descripcion'] ?? $proyecto['descripcion'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>

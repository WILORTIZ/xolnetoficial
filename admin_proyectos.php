<?php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación y rol de Administrador
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Administrador') {
    header("Location: login.php");
    exit;
}

$successMessage = "";
$errorMessage = "";

// Actualizar estado de un proyecto si se envía formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (empty($postedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errorMessage = "Error de validación de seguridad (CSRF). Solicitud rechazada.";
    } else {
        $proyectoId = trim($_POST['proyecto_id'] ?? '');
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');

        if (!empty($proyectoId) && !empty($nuevoEstado)) {
            try {
                if ($pdo instanceof PDO) {
                    $stmt = $pdo->prepare("UPDATE Proyectos SET Estado = ? WHERE Id = ?");
                    $stmt->execute([$nuevoEstado, $proyectoId]);
                    $successMessage = "El estado del proyecto <b>#" . htmlspecialchars($proyectoId) . "</b> se ha actualizado a: <b>" . htmlspecialchars($nuevoEstado) . "</b>.";
                }
            } catch (PDOException $e) {
                error_log("Error en admin_proyectos.php: " . $e->getMessage());
                $errorMessage = "Error en el servidor al intentar actualizar el estado del proyecto.";
            }
        }
    }
}

// Obtener la lista de proyectos
$proyectosList = [];
try {
    if ($pdo instanceof PDO) {
        $stmt = $pdo->query("SELECT * FROM Proyectos ORDER BY FechaCreacion DESC");
        $proyectosList = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $errorMessage = "Error al consultar la lista de proyectos: " . $e->getMessage();
}

$pageTitle = "Administración de Proyectos";
include 'header.php';
?>

<section class="py-12 md:py-20 bg-surface min-h-[85vh]">
    <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10 border-b border-outline-variant/30 pb-6">
            <div>
                <span class="font-mono text-label-md text-primary uppercase tracking-[0.2em] mb-1 block font-semibold">Gestión de Proyectos</span>
                <h1 class="font-display text-2xl md:text-4xl font-bold text-on-surface mb-0">Solicitudes de Cotización de Proyectos</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 bg-primary/10 text-primary font-mono text-xs rounded-full border border-primary/20">
                    Total: <?php echo count($proyectosList); ?> Proyectos
                </span>
            </div>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 rounded-lg text-center flex items-center justify-center gap-2 font-medium">
                <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                <div><?php echo $successMessage; ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="mb-8 p-4 bg-error/10 border border-error/30 text-error rounded-lg text-center flex items-center justify-center gap-2 font-medium">
                <svg class="w-5 h-5 text-error shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <div><?php echo $errorMessage; ?></div>
            </div>
        <?php endif; ?>

        <div class="glass rounded-xl overflow-hidden border border-outline-variant/40 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-body-md">
                    <thead>
                        <tr class="bg-surface-container-highest/60 border-b border-outline-variant/30 text-xs font-mono text-outline uppercase tracking-wider">
                            <th class="py-4 px-6">Código</th>
                            <th class="py-4 px-6">Cliente / Empresa</th>
                            <th class="py-4 px-6">Contacto</th>
                            <th class="py-4 px-6">Tipo / Presupuesto</th>
                            <th class="py-4 px-6">Descripción</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        <?php if (empty($proyectosList)): ?>
                            <tr>
                                <td colspan="7" class="py-8 px-6 text-center text-on-surface-variant font-medium">
                                    No hay solicitudes de proyectos registradas por el momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proyectosList as $item): 
                                $id = $item['Id'] ?? $item['id'];
                                $nombreCliente = $item['NombreCliente'] ?? $item['nombrecliente'];
                                $nombreEmpresa = $item['NombreEmpresa'] ?? $item['nombreempresa'];
                                $email = $item['Email'] ?? $item['email'];
                                $telefono = $item['Telefono'] ?? $item['telefono'];
                                $tipoProyecto = $item['TipoProyecto'] ?? $item['tipoproyecto'];
                                $presupuesto = $item['PresupuestoEstimado'] ?? $item['presupuestoestimado'];
                                $descripcion = $item['Descripcion'] ?? $item['descripcion'];
                                $estado = $item['Estado'] ?? $item['estado'] ?? 'Pendiente';
                                $fecha = $item['FechaCreacion'] ?? $item['fechacreacion'];
                                
                                $badgeClass = 'bg-amber-500/10 text-amber-600 border-amber-500/30';
                                if ($estado === 'Aprobado') $badgeClass = 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30';
                                if ($estado === 'En Evaluación') $badgeClass = 'bg-blue-500/10 text-blue-600 border-blue-500/30';
                            ?>
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="py-4 px-6 font-mono font-bold text-primary">#<?php echo htmlspecialchars($id); ?></td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-on-surface"><?php echo htmlspecialchars($nombreCliente); ?></div>
                                        <div class="text-xs text-on-surface-variant font-medium"><?php echo htmlspecialchars($nombreEmpresa ?: 'Particular'); ?></div>
                                        <div class="text-[11px] text-outline font-mono mt-0.5"><?php echo htmlspecialchars($fecha); ?></div>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-on-surface-variant">
                                        <div><?php echo htmlspecialchars($email); ?></div>
                                        <div><?php echo htmlspecialchars($telefono); ?></div>
                                    </td>
                                    <td class="py-4 px-6 text-xs">
                                        <div class="font-bold text-on-surface mb-0.5"><?php echo htmlspecialchars($tipoProyecto); ?></div>
                                        <div class="text-primary font-mono font-semibold"><?php echo htmlspecialchars($presupuesto); ?></div>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-on-surface-variant max-w-xs truncate" title="<?php echo htmlspecialchars($descripcion); ?>">
                                        <?php echo htmlspecialchars($descripcion); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($estado); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <form action="admin_proyectos.php" method="post" class="flex items-center gap-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="proyecto_id" value="<?php echo htmlspecialchars($id); ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-3 py-1.5 bg-surface-container-low border border-outline-variant/40 rounded text-xs text-on-surface focus:outline-none focus:border-primary">
                                                <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="En Evaluación" <?php echo $estado === 'En Evaluación' ? 'selected' : ''; ?>>En Evaluación</option>
                                                <option value="Aprobado" <?php echo $estado === 'Aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                                                <option value="Rechazado" <?php echo $estado === 'Rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

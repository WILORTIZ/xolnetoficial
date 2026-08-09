<?php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['role'] ?? '';
$isAdmin = isset($_SESSION['user_id']) && (!empty($userRole) && (strtolower($userRole) === 'administrador' || strtolower($userRole) === 'admin' || strpos(strtolower($userRole), 'admin') !== false));

if (!$isAdmin) {
    header("Location: login.php");
    exit;
}

$successMessage = "";
$errorMessage = "";

$filtro = trim($_GET['filtro'] ?? 'pendientes');

// Actualizar estado de una PQRS si se envía formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (empty($postedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errorMessage = "Error de validación de seguridad (CSRF). Solicitud rechazada.";
    } else {
        $pqrsId = trim($_POST['pqrs_id'] ?? '');
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');

        if (!empty($pqrsId) && !empty($nuevoEstado)) {
            try {
                if ($pdo instanceof PDO) {
                    $stmt = $pdo->prepare("UPDATE Pqrs SET Estado = ? WHERE Id = ?");
                    $stmt->execute([$nuevoEstado, $pqrsId]);

                    if ($nuevoEstado === 'Resuelto' || $nuevoEstado === 'Resuelta' || $nuevoEstado === 'Cerrado') {
                        $successMessage = "La PQRS <b>#$pqrsId</b> se ha marcado como <b>$nuevoEstado</b> y se movió a la lista de Resueltos.";
                    } else {
                        $successMessage = "El estado del radicado <b>#$pqrsId</b> se actualizó a: <b>$nuevoEstado</b>.";
                    }
                }
            } catch (PDOException $e) {
                $errorMessage = "Error al actualizar el estado: " . $e->getMessage();
            }
        }
    }
}

// Obtener la lista de solicitudes PQRS filtrada
$pqrsList = [];
try {
    if ($pdo instanceof PDO) {
        if ($filtro === 'resueltos') {
            $stmt = $pdo->query("SELECT * FROM Pqrs WHERE Estado IN ('Resuelto', 'Resuelta', 'Atendido', 'Cerrado') ORDER BY Id ASC");
        } elseif ($filtro === 'todos') {
            $stmt = $pdo->query("SELECT * FROM Pqrs ORDER BY Id ASC");
        } else {
            // default: pendientes (activos)
            $stmt = $pdo->query("SELECT * FROM Pqrs WHERE Estado NOT IN ('Resuelto', 'Resuelta', 'Atendido', 'Cerrado') ORDER BY Id ASC");
        }
        $pqrsList = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $errorMessage = "Error al consultar la lista de PQRS: " . $e->getMessage();
}

$pageTitle = "Buzón de PQRS - Administración";
include 'header.php';
?>

<section class="py-12 md:py-20 bg-surface min-h-[85vh]">
    <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-outline-variant/30 pb-6">
            <div>
                <span class="font-mono text-label-md text-primary uppercase tracking-[0.2em] mb-1 block font-semibold">Panel de Control</span>
                <h1 class="font-display text-2xl md:text-4xl font-bold text-on-surface mb-0">Buzón Administrativo de PQRS</h1>
            </div>
            <div class="flex items-center gap-3">
                <!-- Filtros de Estado -->
                <div class="flex items-center gap-1.5 bg-surface-container-high/40 p-1 rounded-xl border border-outline-variant/30">
                    <a href="admin_pqrs.php?filtro=pendientes" class="px-3.5 py-1.5 rounded-lg font-label-md text-xs font-semibold no-underline transition-all <?php echo $filtro === 'pendientes' ? 'bg-primary text-white shadow-md' : 'text-on-surface-variant hover:text-primary'; ?>">
                        Pendientes / En Trámite
                    </a>
                    <a href="admin_pqrs.php?filtro=resueltos" class="px-3.5 py-1.5 rounded-lg font-label-md text-xs font-semibold no-underline transition-all <?php echo $filtro === 'resueltos' ? 'bg-emerald-600 text-white shadow-md' : 'text-on-surface-variant hover:text-primary'; ?>">
                        Resueltos
                    </a>
                    <a href="admin_pqrs.php?filtro=todos" class="px-3.5 py-1.5 rounded-lg font-label-md text-xs font-semibold no-underline transition-all <?php echo $filtro === 'todos' ? 'bg-on-surface text-surface shadow-md' : 'text-on-surface-variant hover:text-primary'; ?>">
                        Todos
                    </a>
                </div>
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
                            <th class="py-4 px-6">Radicado</th>
                            <th class="py-4 px-6">Solicitante</th>
                            <th class="py-4 px-6">Contacto</th>
                            <th class="py-4 px-6">Tipo</th>
                            <th class="py-4 px-6">Mensaje</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6">Cambiar Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        <?php if (empty($pqrsList)): ?>
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center text-on-surface-variant font-medium">
                                    <span class="material-symbols-outlined text-[48px] mb-2 block text-outline/60 mx-auto">mark_email_read</span>
                                    <div class="font-semibold text-base">No hay solicitudes PQRS <?php echo $filtro === 'resueltos' ? 'resueltas' : ($filtro === 'todos' ? 'registradas' : 'pendientes'); ?> en este momento.</div>
                                    <p class="text-xs text-outline mt-1 mb-0">Todas las consultas activas han sido atendidas.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pqrsList as $item): 
                                $id = $item['Id'] ?? $item['id'];
                                $nombre = $item['Nombre'] ?? $item['nombre'];
                                $email = $item['Email'] ?? $item['email'];
                                $telefono = $item['Telefono'] ?? $item['telefono'];
                                $tipo = $item['Tipo'] ?? $item['tipo'];
                                $mensaje = $item['Mensaje'] ?? $item['mensaje'];
                                $estado = $item['Estado'] ?? $item['estado'] ?? 'Pendiente';
                                $fecha = $item['FechaCreacion'] ?? $item['fechacreacion'];
                                
                                $badgeClass = 'bg-amber-500/10 text-amber-600 border-amber-500/30';
                                if ($estado === 'Resuelto' || $estado === 'Cerrado') $badgeClass = 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30';
                                if ($estado === 'En trámite') $badgeClass = 'bg-blue-500/10 text-blue-600 border-blue-500/30';
                            ?>
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="py-4 px-6 font-mono font-bold text-primary">#<?php echo htmlspecialchars($id); ?></td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-on-surface"><?php echo htmlspecialchars($nombre); ?></div>
                                        <div class="text-xs text-outline font-mono"><?php echo htmlspecialchars($fecha); ?></div>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-on-surface-variant">
                                        <div><?php echo htmlspecialchars($email); ?></div>
                                        <div><?php echo htmlspecialchars($telefono ?: 'N/A'); ?></div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-2.5 py-1 bg-surface-container rounded text-xs font-mono font-medium text-on-surface">
                                            <?php echo htmlspecialchars($tipo); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-on-surface-variant max-w-xs truncate" title="<?php echo htmlspecialchars($mensaje); ?>">
                                        <?php echo htmlspecialchars($mensaje); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($estado); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <form action="admin_pqrs.php?filtro=<?php echo urlencode($filtro); ?>" method="post" class="flex items-center gap-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="pqrs_id" value="<?php echo htmlspecialchars($id); ?>">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="px-3 py-1.5 bg-surface-container-low border border-outline-variant/40 rounded text-xs text-on-surface focus:outline-none focus:border-primary font-medium">
                                                <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="En trámite" <?php echo $estado === 'En trámite' ? 'selected' : ''; ?>>En trámite</option>
                                                <option value="Resuelto" <?php echo ($estado === 'Resuelto' || $estado === 'Cerrado') ? 'selected' : ''; ?>>✔ Resuelto</option>
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

<?php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función helper para obtener valores sin importar mayúsculas/minúsculas en las llaves del array
function get_val($arr, $keys, $default = '') {
    if (!is_array($arr)) return $default;
    foreach ((array)$keys as $k) {
        if (array_key_exists($k, $arr) && $arr[$k] !== null && $arr[$k] !== '') {
            return $arr[$k];
        }
    }
    return $default;
}

$userRole = $_SESSION['role'] ?? $_SESSION['rol'] ?? '';
$isAdmin = isset($_SESSION['user_id']) && (!empty($userRole) && (strtolower($userRole) === 'administrador' || strtolower($userRole) === 'admin' || strpos(strtolower($userRole), 'admin') !== false));

if (!$isAdmin) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$username = $_SESSION['username'] ?? $_SESSION['usuario'] ?? 'Administrador';

$successMessage = "";
$errorMessage = "";
$tab = $_GET['tab'] ?? 'dashboard';

// Procesar acciones de PQRS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_pqrs') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (empty($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errorMessage = "Error de validación de seguridad (CSRF).";
    } else {
        $pqrsId = trim($_POST['pqrs_id'] ?? '');
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
        if (!empty($pqrsId) && !empty($nuevoEstado) && $connected && $pdo) {
            try {
                try {
                    $stmt = $pdo->prepare("UPDATE Pqrs SET Estado = ? WHERE Id = ?");
                    $stmt->execute([$nuevoEstado, $pqrsId]);
                } catch (PDOException $ex1) {
                    $stmt = $pdo->prepare("UPDATE pqrs SET Estado = ? WHERE Id = ?");
                    $stmt->execute([$nuevoEstado, $pqrsId]);
                }
                $successMessage = "PQRS #$pqrsId actualizada a: <b>$nuevoEstado</b>.";
                $tab = 'pqrs';
            } catch (PDOException $e) {
                $errorMessage = "Error al actualizar PQRS: " . $e->getMessage();
            }
        }
    }
}

// Procesar acciones de Proyectos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_proyecto') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (empty($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errorMessage = "Error de validación de seguridad (CSRF).";
    } else {
        $proyectoId = trim($_POST['proyecto_id'] ?? '');
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
        if (!empty($proyectoId) && !empty($nuevoEstado) && $connected && $pdo) {
            try {
                try {
                    $stmt = $pdo->prepare("UPDATE Proyectos SET Estado = ? WHERE Id = ?");
                    $stmt->execute([$nuevoEstado, $proyectoId]);
                } catch (PDOException $ex1) {
                    $stmt = $pdo->prepare("UPDATE proyectos SET Estado = ? WHERE Id = ?");
                    $stmt->execute([$nuevoEstado, $proyectoId]);
                }
                $successMessage = "Proyecto #$proyectoId actualizado a: <b>$nuevoEstado</b>.";
                $tab = 'proyectos';
            } catch (PDOException $e) {
                $errorMessage = "Error al actualizar proyecto: " . $e->getMessage();
            }
        }
    }
}

// Procesar eliminación de testimonios / comentarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_testimonio') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (empty($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errorMessage = "Error de validación de seguridad (CSRF).";
    } else {
        $testimonioId = trim($_POST['testimonio_id'] ?? '');
        if (!empty($testimonioId) && $connected && $pdo) {
            try {
                try {
                    $stmt = $pdo->prepare("DELETE FROM testimonios WHERE Id = ?");
                    $stmt->execute([$testimonioId]);
                } catch (PDOException $ex1) {
                    $stmt = $pdo->prepare("DELETE FROM Testimonios WHERE Id = ?");
                    $stmt->execute([$testimonioId]);
                }
                $successMessage = "El comentario <b>#$testimonioId</b> ha sido eliminado exitosamente.";
                $tab = 'comentarios';
            } catch (PDOException $e) {
                $errorMessage = "Error al eliminar el comentario: " . $e->getMessage();
            }
        }
    }
}

// Consultar datos de métricas y listas
$totalPqrsPendientes = 0;
$totalPqrsResueltas = 0;
$totalProyectosPendientes = 0;
$totalProyectosResueltos = 0;
$totalTestimonios = 0;
$pqrsList = [];
$proyectosList = [];
$testimoniosList = [];

if ($connected && $pdo) {
    // 1. Consulta PQRS (Ordenado del más antiguo al más nuevo por ID)
    try {
        try {
            $stmt = $pdo->query("SELECT * FROM Pqrs ORDER BY Id ASC");
            $pqrsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex1) {
            $stmt = $pdo->query("SELECT * FROM pqrs ORDER BY Id ASC");
            $pqrsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $pqrsList = [];
    }

    foreach ($pqrsList as $p) {
        $est = strtolower(get_val($p, ['Estado', 'estado'], ''));
        if ($est === 'resuelto' || $est === 'resuelta' || $est === 'cerrado') {
            $totalPqrsResueltas++;
        } else {
            $totalPqrsPendientes++;
        }
    }

    // 2. Consulta Proyectos (Ordenado del más antiguo al más nuevo por ID)
    try {
        try {
            $stmt = $pdo->query("SELECT * FROM Proyectos ORDER BY Id ASC");
            $proyectosList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex1) {
            $stmt = $pdo->query("SELECT * FROM proyectos ORDER BY Id ASC");
            $proyectosList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $proyectosList = [];
    }

    foreach ($proyectosList as $pr) {
        $est = strtolower(get_val($pr, ['Estado', 'estado'], ''));
        if ($est === 'resuelto' || $est === 'completado' || $est === 'rechazado') {
            $totalProyectosResueltos++;
        } else {
            $totalProyectosPendientes++;
        }
    }

    // 3. Consulta Testimonios / Comentarios
    try {
        try {
            $stmt = $pdo->query("SELECT * FROM testimonios ORDER BY Id DESC");
            $testimoniosList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex1) {
            $stmt = $pdo->query("SELECT * FROM Testimonios ORDER BY Id DESC");
            $testimoniosList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $testimoniosList = [];
    }

    $totalTestimonios = count($testimoniosList);
}

$filtroPqrs = $_GET['filtro_pqrs'] ?? 'pendientes';
$filtroProyectos = $_GET['filtro_proyectos'] ?? 'pendientes';

// Filtrar PQRS
$pqrsFiltradas = array_filter($pqrsList, function($item) use ($filtroPqrs) {
    $est = strtolower(get_val($item, ['Estado', 'estado'], ''));
    $isResuelto = ($est === 'resuelto' || $est === 'resuelta' || $est === 'cerrado');
    if ($filtroPqrs === 'pendientes') return !$isResuelto;
    if ($filtroPqrs === 'resueltos') return $isResuelto;
    return true;
});

// Filtrar Proyectos
$proyectosFiltrados = array_filter($proyectosList, function($item) use ($filtroProyectos) {
    $est = strtolower(get_val($item, ['Estado', 'estado'], ''));
    $isResuelto = ($est === 'resuelto' || $est === 'completado' || $est === 'rechazado');
    if ($filtroProyectos === 'pendientes') return !$isResuelto;
    if ($filtroProyectos === 'resueltos') return $isResuelto;
    return true;
});
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - XCOLNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-900 text-slate-100 antialiased flex overflow-hidden">

    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside id="sidebar" class="w-64 bg-slate-950 border-r border-slate-800 flex flex-col justify-between shrink-0 transition-all duration-300 z-50">
        <div>
            <!-- Header Sidebar -->
            <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800/80 bg-slate-950/50">
                <div class="w-8 h-8 rounded-lg bg-sky-500/20 border border-sky-500/30 flex items-center justify-center text-sky-400 font-bold">
                    X
                </div>
                <div>
                    <h1 class="text-base font-bold text-white tracking-tight leading-none">xcolnet</h1>
                    <span class="text-[11px] text-sky-400 font-semibold tracking-wider uppercase">Panel Admin</span>
                </div>
            </div>

            <!-- Navegación de la Barra Lateral -->
            <nav class="p-4 space-y-1.5">
                <a href="admin_dashboard.php?tab=dashboard" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo ($tab === 'dashboard') ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    <span>General</span>
                </a>

                <a href="admin_dashboard.php?tab=pqrs" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo ($tab === 'pqrs') ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <span>Buzón PQRS</span>
                    </div>
                    <?php if ($totalPqrsPendientes > 0) { ?>
                        <span class="px-2 py-0.5 text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full"><?php echo $totalPqrsPendientes; ?></span>
                    <?php } ?>
                </a>

                <a href="admin_dashboard.php?tab=proyectos" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo ($tab === 'proyectos') ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Proyectos</span>
                    </div>
                    <?php if ($totalProyectosPendientes > 0) { ?>
                        <span class="px-2 py-0.5 text-xs font-bold bg-sky-500/20 text-sky-400 border border-sky-500/30 rounded-full"><?php echo $totalProyectosPendientes; ?></span>
                    <?php } ?>
                </a>

                <a href="admin_dashboard.php?tab=comentarios" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo ($tab === 'testimonios' || $tab === 'comentarios') ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <span>Comentarios</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium bg-slate-800 text-slate-400 rounded-full"><?php echo $totalTestimonios; ?></span>
                </a>

                <div class="pt-4 mt-4 border-t border-slate-800/80 space-y-1.5">
                    <a href="index.php" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm text-slate-400 hover:text-white hover:bg-slate-900 transition-all no-underline">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        <span>Volver al Sitio Web</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Footer Sidebar (Usuario Administrador) -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/80">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sky-400 shrink-0">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-semibold text-white truncate mb-0"><?php echo htmlspecialchars($username); ?></p>
                        <p class="text-xs text-slate-400 truncate mb-0">Administrador</p>
                    </div>
                </div>
                <a href="logout.php" title="Cerrar Sesión" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- AREA PRINCIPAL DE CONTENIDO -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-900">

        <!-- Topbar Superior -->
        <header class="h-16 bg-slate-950/60 border-b border-slate-800/80 flex items-center justify-between px-6 shrink-0">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold text-white mb-0 capitalize">
                    <?php 
                        if ($tab === 'pqrs') echo "Gestión de solicitudes PQRS";
                        elseif ($tab === 'proyectos') echo "Gestión de solicitudes de proyectos";
                        elseif ($tab === 'testimonios' || $tab === 'comentarios') echo "Comentarios recibidos";
                        else echo "Resumen General del Sistema";
                    ?>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($connected) { ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> DB Conectada
                    </span>
                <?php } else { ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span> DB Desconectada
                    </span>
                <?php } ?>
            </div>
        </header>

        <!-- Contenido desplazable -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <!-- Mensajes de feedback -->
            <?php if (!empty($successMessage)) { ?>
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?php echo $successMessage; ?></span>
                    </div>
                </div>
            <?php } ?>

            <?php if (!empty($errorMessage)) { ?>
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?php echo $errorMessage; ?></span>
                    </div>
                </div>
            <?php } ?>

            <!-- PESTAÑA 1: DASHBOARD GENERAL -->
            <?php if ($tab === 'dashboard') { ?>
                <!-- Tarjetas KPI -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">PQRS Pendientes</p>
                            <h3 class="text-3xl font-extrabold text-amber-400 mb-0"><?php echo $totalPqrsPendientes; ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">PQRS Resueltas</p>
                            <h3 class="text-3xl font-extrabold text-emerald-400 mb-0"><?php echo $totalPqrsResueltas; ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Proyectos Pendientes</p>
                            <h3 class="text-3xl font-extrabold text-sky-400 mb-0"><?php echo $totalProyectosPendientes; ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Total Comentarios</p>
                            <h3 class="text-3xl font-extrabold text-purple-400 mb-0"><?php echo $totalTestimonios; ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                    </div>
                </div>

                <!-- Actividad reciente -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Últimas PQRS -->
                    <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-white mb-0">Últimas Solicitudes PQRS</h4>
                            <a href="admin_dashboard.php?tab=pqrs" class="text-xs text-sky-400 hover:underline">Ver todas →</a>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($pqrsList)) { ?>
                                <p class="text-xs text-slate-400 italic">No hay PQRS registradas.</p>
                            <?php } else { ?>
                                <?php foreach (array_slice($pqrsList, 0, 4) as $p) { 
                                    $p_id = get_val($p, ['Id', 'id', 'ID'], '0');
                                    $p_nombre = get_val($p, ['NombreRemitente', 'nombreremitente', 'Nombre'], 'Anónimo');
                                    $p_asunto = get_val($p, ['Asunto', 'asunto', 'Mensaje', 'mensaje'], '');
                                    $p_estado = get_val($p, ['Estado', 'estado'], 'Pendiente');
                                ?>
                                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 flex items-center justify-between text-xs">
                                        <div>
                                            <p class="font-bold text-slate-200 mb-0.5">#<?php echo $p_id; ?> - <?php echo htmlspecialchars($p_nombre); ?></p>
                                            <p class="text-slate-400 truncate max-w-xs mb-0"><?php echo htmlspecialchars($p_asunto); ?></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($p_estado) === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?>">
                                            <?php echo htmlspecialchars($p_estado); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Últimos Proyectos -->
                    <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-white mb-0">Últimos Proyectos Solicitados</h4>
                            <a href="admin_dashboard.php?tab=proyectos" class="text-xs text-sky-400 hover:underline">Ver todos →</a>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($proyectosList)) { ?>
                                <p class="text-xs text-slate-400 italic">No hay solicitudes de proyectos.</p>
                            <?php } else { ?>
                                <?php foreach (array_slice($proyectosList, 0, 4) as $pr) { 
                                    $pr_id = get_val($pr, ['Id', 'id', 'ID'], '0');
                                    $pr_contacto = get_val($pr, ['NombreCliente', 'nombrecliente', 'NombreContacto', 'nombrecontacto', 'Nombre', 'nombre'], 'Cliente');
                                    $pr_servicio = get_val($pr, ['TipoProyecto', 'tipoproyecto', 'TipoServicio', 'tiposervicio', 'Servicio', 'servicio'], 'Proyecto TI');
                                    $pr_estado = get_val($pr, ['Estado', 'estado'], 'Pendiente');
                                ?>
                                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 flex items-center justify-between text-xs">
                                        <div>
                                            <p class="font-bold text-slate-200 mb-0.5">#<?php echo $pr_id; ?> - <?php echo htmlspecialchars($pr_contacto); ?></p>
                                            <p class="text-slate-400 truncate max-w-xs mb-0"><?php echo htmlspecialchars($pr_servicio); ?></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($pr_estado) === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-sky-500/10 text-sky-400 border border-sky-500/20'; ?>">
                                            <?php echo htmlspecialchars($pr_estado); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- PESTAÑA 2: GESTIÓN DE PQRS -->
            <?php if ($tab === 'pqrs') { ?>
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">Buzón de Solicitudes PQRS</h3>
                            <p class="text-xs text-slate-400 mb-0">Administra las peticiones, quejas, reclamos y sugerencias ingresadas.</p>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl border border-slate-800">
                            <a href="admin_dashboard.php?tab=pqrs&filtro_pqrs=pendientes" class="px-3 py-1.5 text-xs font-semibold rounded-lg no-underline transition-all <?php echo $filtroPqrs === 'pendientes' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-white'; ?>">Pendientes (<?php echo $totalPqrsPendientes; ?>)</a>
                            <a href="admin_dashboard.php?tab=pqrs&filtro_pqrs=resueltos" class="px-3 py-1.5 text-xs font-semibold rounded-lg no-underline transition-all <?php echo $filtroPqrs === 'resueltos' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white'; ?>">Resueltos (<?php echo $totalPqrsResueltas; ?>)</a>
                            <a href="admin_dashboard.php?tab=pqrs&filtro_pqrs=todos" class="px-3 py-1.5 text-xs font-semibold rounded-lg no-underline transition-all <?php echo $filtroPqrs === 'todos' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:text-white'; ?>">Todos (<?php echo count($pqrsList); ?>)</a>
                        </div>
                    </div>

                    <!-- Buscador de PQRS (por Número #, Tipo, Fecha, Nombre) -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="searchPqrsInput" placeholder="Buscar PQRS por número (#), tipo de solicitud, fecha o nombre..." class="w-full bg-slate-900 text-slate-100 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:outline-none focus:border-amber-500 placeholder:text-slate-500">
                    </div>

                    <?php if (empty($pqrsFiltradas)) { ?>
                        <div class="p-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-sm text-slate-400 mb-0">No se encontraron solicitudes PQRS para este filtro.</p>
                        </div>
                    <?php } else { ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4">Radicado</th>
                                        <th class="py-3 px-4">Remitente / Tipo</th>
                                        <th class="py-3 px-4">Contacto</th>
                                        <th class="py-3 px-4">Asunto / Mensaje</th>
                                        <th class="py-3 px-4">Fecha</th>
                                        <th class="py-3 px-4">Estado Actual</th>
                                        <th class="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="pqrsTableBody" class="divide-y divide-slate-800/60">
                                    <?php foreach ($pqrsFiltradas as $p) { 
                                        $p_id = get_val($p, ['Id', 'id', 'ID'], '0');
                                        $p_nombre = get_val($p, ['NombreRemitente', 'nombreremitente', 'Nombre'], 'Anónimo');
                                        $p_tipo = get_val($p, ['TipoSolicitud', 'tiposolicitud', 'Tipo', 'tipo'], 'PQRS');
                                        $p_correo = get_val($p, ['CorreoRemitente', 'correoremitente', 'Email'], 'N/A');
                                        $p_telefono = get_val($p, ['TelefonoRemitente', 'telefonoremitente', 'Telefono'], '');
                                        $p_asunto = get_val($p, ['Asunto', 'asunto'], 'Sin Asunto');
                                        $p_mensaje = get_val($p, ['Mensaje', 'mensaje'], '');
                                        $p_fecha = get_val($p, ['FechaCreacion', 'fechacreacion', 'Fecha', 'fecha'], 'N/A');
                                        $p_estado = get_val($p, ['Estado', 'estado'], 'Pendiente');

                                        $pqrsSearchStr = strtolower("$p_id $p_tipo $p_fecha $p_nombre $p_asunto $p_correo");
                                    ?>
                                        <tr class="pqrs-row hover:bg-slate-900/50 transition-colors" data-search="<?php echo htmlspecialchars($pqrsSearchStr); ?>">
                                            <td class="py-4 px-4 font-bold text-amber-400">#<?php echo $p_id; ?></td>
                                            <td class="py-4 px-4 font-medium text-white">
                                                <div><?php echo htmlspecialchars($p_nombre); ?></div>
                                                <div class="text-[11px] text-amber-400 font-semibold"><?php echo htmlspecialchars($p_tipo); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300">
                                                <div><?php echo htmlspecialchars($p_correo); ?></div>
                                                <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($p_telefono); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300 max-w-xs">
                                                <p class="font-semibold text-slate-200 mb-1"><?php echo htmlspecialchars($p_asunto); ?></p>
                                                <p class="text-[11px] text-slate-400 line-clamp-2 mb-0"><?php echo htmlspecialchars($p_mensaje); ?></p>
                                            </td>
                                            <td class="py-4 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                                <?php echo htmlspecialchars($p_fecha); ?>
                                            </td>
                                            <td class="py-4 px-4">
                                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($p_estado) === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?>">
                                                    <?php echo htmlspecialchars($p_estado); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <form method="POST" action="admin_dashboard.php?tab=pqrs" class="inline-flex items-center gap-2">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="update_pqrs">
                                                    <input type="hidden" name="pqrs_id" value="<?php echo $p_id; ?>">
                                                    
                                                    <select name="nuevo_estado" class="bg-slate-900 text-slate-200 border border-slate-700 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-sky-500">
                                                        <option value="Pendiente" <?php if (strtolower($p_estado)==='pendiente') echo 'selected'; ?>>Pendiente</option>
                                                        <option value="En Proceso" <?php if (strtolower($p_estado)==='en proceso') echo 'selected'; ?>>En Proceso</option>
                                                        <option value="Resuelto" <?php if (strtolower($p_estado)==='resuelto') echo 'selected'; ?>>Resuelto</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-medium text-xs rounded-lg transition-all shadow-sm">
                                                        Guardar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- PESTAÑA 3: GESTIÓN DE PROYECTOS -->
            <?php if ($tab === 'proyectos') { ?>
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">Solicitudes de Proyectos</h3>
                            <p class="text-xs text-slate-400 mb-0">Gestiona las cotizaciones e instalaciones requeridas por clientes.</p>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl border border-slate-800">
                            <a href="admin_dashboard.php?tab=proyectos&filtro_proyectos=pendientes" class="px-3 py-1.5 text-xs font-semibold rounded-lg no-underline transition-all <?php echo $filtroProyectos === 'pendientes' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:text-white'; ?>">Pendientes (<?php echo $totalProyectosPendientes; ?>)</a>
                            <a href="admin_dashboard.php?tab=proyectos&filtro_proyectos=resueltos" class="px-3 py-1.5 text-xs font-semibold rounded-lg no-underline transition-all <?php echo $filtroProyectos === 'resueltos' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white'; ?>">Resueltos (<?php echo $totalProyectosResueltos; ?>)</a>
                            <a href="admin_dashboard.php?tab=proyectos&filtro_proyectos=todos" class="px-3 py-1.5 text-xs font-semibold rounded-lg no-underline transition-all <?php echo $filtroProyectos === 'todos' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'text-slate-400 hover:text-white'; ?>">Todos (<?php echo count($proyectosList); ?>)</a>
                        </div>
                    </div>

                    <!-- Buscador de Proyectos (por Número #, Nombre, Dirección, Empresa, Tipo de Proyecto) -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="searchProyectosInput" placeholder="Buscar proyecto por número (#), nombre, empresa, dirección o tipo de proyecto..." class="w-full bg-slate-900 text-slate-100 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:outline-none focus:border-sky-500 placeholder:text-slate-500">
                    </div>

                    <?php if (empty($proyectosFiltrados)) { ?>
                        <div class="p-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-sm text-slate-400 mb-0">No se encontraron proyectos para este filtro.</p>
                        </div>
                    <?php } else { ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4">Proyecto</th>
                                        <th class="py-3 px-4">Cliente / Empresa</th>
                                        <th class="py-3 px-4">Contacto / Dirección</th>
                                        <th class="py-3 px-4">Servicio Requerido</th>
                                        <th class="py-3 px-4">Estado</th>
                                        <th class="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="proyectosTableBody" class="divide-y divide-slate-800/60">
                                    <?php foreach ($proyectosFiltrados as $pr) { 
                                        $pr_id = get_val($pr, ['Id', 'id', 'ID'], '0');
                                        $pr_contacto = get_val($pr, ['NombreCliente', 'nombrecliente', 'NombreContacto', 'nombrecontacto', 'Nombre', 'nombre'], 'Cliente');
                                        $pr_empresa = get_val($pr, ['NombreEmpresa', 'nombreempresa', 'Empresa', 'empresa'], '');
                                        $pr_correo = get_val($pr, ['CorreoContacto', 'correocontacto', 'Correo', 'correo', 'Email', 'email'], 'N/A');
                                        $pr_telefono = get_val($pr, ['TelefonoContacto', 'telefonocontacto', 'Telefono', 'telefono'], '');
                                        $pr_direccion = get_val($pr, ['DireccionProyecto', 'direccionproyecto', 'Direccion', 'direccion'], '');
                                        $pr_ciudad = get_val($pr, ['Ciudad', 'ciudad'], '');
                                        $pr_servicio = get_val($pr, ['TipoProyecto', 'tipoproyecto', 'TipoServicio', 'tiposervicio', 'Servicio', 'servicio'], 'General');
                                        $pr_descripcion = get_val($pr, ['DescripcionProyecto', 'descripcionproyecto', 'Descripcion', 'descripcion'], '');
                                        $pr_estado = get_val($pr, ['Estado', 'estado'], 'Pendiente');

                                        $proySearchStr = strtolower("$pr_id $pr_contacto $pr_empresa $pr_direccion $pr_ciudad $pr_servicio $pr_correo");
                                    ?>
                                        <tr class="proyecto-row hover:bg-slate-900/50 transition-colors" data-search="<?php echo htmlspecialchars($proySearchStr); ?>">
                                            <td class="py-4 px-4 font-bold text-sky-400">#<?php echo $pr_id; ?></td>
                                            <td class="py-4 px-4 font-medium text-white">
                                                <div><?php echo htmlspecialchars($pr_contacto); ?></div>
                                                <div class="text-[11px] text-sky-400 font-semibold"><?php echo htmlspecialchars($pr_empresa ?: 'Empresa no especificada'); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300">
                                                <div><?php echo htmlspecialchars($pr_correo); ?></div>
                                                <?php if (!empty($pr_direccion)) { ?>
                                                    <div class="text-[11px] text-slate-400">📍 <?php echo htmlspecialchars($pr_direccion); ?> <?php echo htmlspecialchars($pr_ciudad); ?></div>
                                                <?php } ?>
                                                <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($pr_telefono); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300 max-w-xs">
                                                <p class="font-semibold text-slate-200 mb-1"><?php echo htmlspecialchars($pr_servicio); ?></p>
                                                <p class="text-[11px] text-slate-400 line-clamp-2 mb-0"><?php echo htmlspecialchars($pr_descripcion); ?></p>
                                            </td>
                                            <td class="py-4 px-4">
                                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($pr_estado) === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-sky-500/10 text-sky-400 border border-sky-500/20'; ?>">
                                                    <?php echo htmlspecialchars($pr_estado); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <form method="POST" action="admin_dashboard.php?tab=proyectos" class="inline-flex items-center gap-2">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="update_proyecto">
                                                    <input type="hidden" name="proyecto_id" value="<?php echo $pr_id; ?>">
                                                    
                                                    <select name="nuevo_estado" class="bg-slate-900 text-slate-200 border border-slate-700 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-sky-500">
                                                        <option value="Pendiente" <?php if (strtolower($pr_estado)==='pendiente') echo 'selected'; ?>>Pendiente</option>
                                                        <option value="En Proceso" <?php if (strtolower($pr_estado)==='en proceso') echo 'selected'; ?>>En Proceso</option>
                                                        <option value="Resuelto" <?php if (strtolower($pr_estado)==='resuelto') echo 'selected'; ?>>Resuelto</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-medium text-xs rounded-lg transition-all shadow-sm">
                                                        Guardar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- PESTAÑA 4: COMENTARIOS -->
            <?php if ($tab === 'testimonios' || $tab === 'comentarios') { ?>
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">Comentarios Registrados</h3>
                            <p class="text-xs text-slate-400 mb-0">Comentarios enviados por los usuarios en el sitio principal.</p>
                        </div>
                        <span class="px-3 py-1 bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs font-bold rounded-full">
                            Total: <?php echo $totalTestimonios; ?>
                        </span>
                    </div>

                    <!-- Buscador de Comentarios (por Nombre de autor) -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="searchComentariosInput" placeholder="Buscar comentario por nombre del usuario o cliente..." class="w-full bg-slate-900 text-slate-100 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs focus:outline-none focus:border-purple-500 placeholder:text-slate-500">
                    </div>

                    <?php if (empty($testimoniosList)) { ?>
                        <div class="p-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-sm text-slate-400 mb-0">No se han registrado comentarios en la base de datos.</p>
                        </div>
                    <?php } else { ?>
                        <div id="comentariosGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($testimoniosList as $t) { 
                                $t_id = get_val($t, ['Id', 'id', 'ID'], '0');
                                $t_nombre = get_val($t, ['Nombre', 'nombre'], 'Cliente');
                                $t_cargo = get_val($t, ['Cargo', 'cargo'], 'Usuario');
                                $t_texto = get_val($t, ['Texto', 'texto'], '');
                                $t_estrellas = intval(get_val($t, ['Estrellas', 'estrellas'], 5));

                                $comentSearchStr = strtolower("$t_nombre $t_cargo $t_texto");
                            ?>
                                <div class="comentario-card p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3" data-search="<?php echo htmlspecialchars($comentSearchStr); ?>">
                                    <div class="flex items-center justify-between">
                                        <div class="flex text-amber-400">
                                            <?php for ($s = 0; $s < $t_estrellas; $s++) { ?>
                                                ★
                                            <?php } ?>
                                        </div>
                                        <span class="text-[10px] font-mono text-slate-500">ID #<?php echo $t_id; ?></span>
                                    </div>
                                    <p class="text-xs text-slate-300 italic leading-relaxed mb-0">"<?php echo htmlspecialchars($t_texto); ?>"</p>
                                    <div class="pt-2.5 border-t border-slate-800/60 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-bold text-white mb-0"><?php echo htmlspecialchars($t_nombre); ?></p>
                                            <p class="text-[11px] text-slate-400 mb-0"><?php echo htmlspecialchars($t_cargo); ?></p>
                                        </div>
                                        <form method="POST" action="admin_dashboard.php?tab=comentarios" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este comentario permanentemente?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="delete_testimonio">
                                            <input type="hidden" name="testimonio_id" value="<?php echo $t_id; ?>">
                                            <button type="submit" class="px-2.5 py-1 bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white border border-rose-500/20 text-xs font-medium rounded-lg transition-all flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </main>

    <!-- SCRIPT FILTRADO EN TIEMPO REAL PARA BUSCADORES -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Buscador PQRS
            const searchPqrsInput = document.getElementById('searchPqrsInput');
            if (searchPqrsInput) {
                searchPqrsInput.addEventListener('input', function(e) {
                    const q = e.target.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('#pqrsTableBody .pqrs-row');
                    rows.forEach(row => {
                        const searchData = row.getAttribute('data-search') || '';
                        row.style.display = searchData.includes(q) ? '' : 'none';
                    });
                });
            }

            // Buscador Proyectos
            const searchProyectosInput = document.getElementById('searchProyectosInput');
            if (searchProyectosInput) {
                searchProyectosInput.addEventListener('input', function(e) {
                    const q = e.target.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('#proyectosTableBody .proyecto-row');
                    rows.forEach(row => {
                        const searchData = row.getAttribute('data-search') || '';
                        row.style.display = searchData.includes(q) ? '' : 'none';
                    });
                });
            }

            // Buscador Comentarios (Nombre)
            const searchComentariosInput = document.getElementById('searchComentariosInput');
            if (searchComentariosInput) {
                searchComentariosInput.addEventListener('input', function(e) {
                    const q = e.target.value.toLowerCase().trim();
                    const cards = document.querySelectorAll('#comentariosGrid .comentario-card');
                    cards.forEach(card => {
                        const searchData = card.getAttribute('data-search') || '';
                        card.style.display = searchData.includes(q) ? '' : 'none';
                    });
                });
            }
        });
    </script>

</body>
</html>

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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$username = $_SESSION['username'] ?? 'Administrador';

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
                $stmt = $pdo->prepare("UPDATE Pqrs SET Estado = ? WHERE Id = ?");
                $stmt->execute([$nuevoEstado, $pqrsId]);
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
                $stmt = $pdo->prepare("UPDATE Proyectos SET Estado = ? WHERE Id = ?");
                $stmt->execute([$nuevoEstado, $proyectoId]);
                $successMessage = "Proyecto #$proyectoId actualizado a: <b>$nuevoEstado</b>.";
                $tab = 'proyectos';
            } catch (PDOException $e) {
                $errorMessage = "Error al actualizar proyecto: " . $e->getMessage();
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
    try {
        // PQRS
        $stmt = $pdo->query("SELECT * FROM Pqrs ORDER BY Id DESC");
        $pqrsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($pqrsList as $p) {
            $est = strtolower($p['Estado'] ?? '');
            if ($est === 'resuelto' || $est === 'resuelta' || $est === 'cerrado') {
                $totalPqrsResueltas++;
            } else {
                $totalPqrsPendientes++;
            }
        }

        // Proyectos
        $stmt = $pdo->query("SELECT * FROM Proyectos ORDER BY Id DESC");
        $proyectosList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($proyectosList as $pr) {
            $est = strtolower($pr['Estado'] ?? '');
            if ($est === 'resuelto' || $est === 'completado' || $est === 'rechazado') {
                $totalProyectosResueltos++;
            } else {
                $totalProyectosPendientes++;
            }
        }

        // Testimonios
        $stmt = $pdo->query("SELECT * FROM testimonios ORDER BY Id DESC");
        $testimoniosList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalTestimonios = count($testimoniosList);

    } catch (PDOException $e) {
        $errorMessage = "Error al consultar la base de datos: " . $e->getMessage();
    }
}

$filtroPqrs = $_GET['filtro_pqrs'] ?? 'pendientes';
$filtroProyectos = $_GET['filtro_proyectos'] ?? 'pendientes';

// Filtrar PQRS
$pqrsFiltradas = array_filter($pqrsList, function($item) use ($filtroPqrs) {
    $est = strtolower($item['Estado'] ?? '');
    $isResuelto = ($est === 'resuelto' || $est === 'resuelta' || $est === 'cerrado');
    if ($filtroPqrs === 'pendientes') return !$isResuelto;
    if ($filtroPqrs === 'resueltos') return $isResuelto;
    return true;
});

// Filtrar Proyectos
$proyectosFiltrados = array_filter($proyectosList, function($item) use ($filtroProyectos) {
    $est = strtolower($item['Estado'] ?? '');
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
                <a href="admin_dashboard.php?tab=dashboard" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo $tab === 'dashboard' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    <span>General</span>
                </a>

                <a href="admin_dashboard.php?tab=pqrs" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo $tab === 'pqrs' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <span>Buzón PQRS</span>
                    </div>
                    <?php if ($totalPqrsPendientes > 0): ?>
                        <span class="px-2 py-0.5 text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full"><?php echo $totalPqrsPendientes; ?></span>
                    <?php endif; ?>
                </a>

                <a href="admin_dashboard.php?tab=proyectos" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo $tab === 'proyectos' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Proyectos</span>
                    </div>
                    <?php if ($totalProyectosPendientes > 0): ?>
                        <span class="px-2 py-0.5 text-xs font-bold bg-sky-500/20 text-sky-400 border border-sky-500/30 rounded-full"><?php echo $totalProyectosPendientes; ?></span>
                    <?php endif; ?>
                </a>

                <a href="admin_dashboard.php?tab=testimonios" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all no-underline <?php echo $tab === 'testimonios' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <span>Testimonios</span>
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
                        elseif ($tab === 'testimonios') echo "Testimonios y Comentarios recibidos";
                        else echo "Resumen General del Sistema";
                    ?>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($connected): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> DB Conectada
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span> DB Desconectada
                    </span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Contenido desplazable -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <!-- Mensajes de feedback -->
            <?php if (!empty($successMessage)): ?>
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?php echo $successMessage; ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span><?php echo $errorMessage; ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- PESTAÑA 1: DASHBOARD GENERAL -->
            <?php if ($tab === 'dashboard'): ?>
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
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Total Testimonios</p>
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
                            <?php if (empty($pqrsList)): ?>
                                <p class="text-xs text-slate-400 italic">No hay PQRS registradas.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($pqrsList, 0, 4) as $p): ?>
                                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 flex items-center justify-between text-xs">
                                        <div>
                                            <p class="font-bold text-slate-200 mb-0.5">#<?php echo $p['Id']; ?> - <?php echo htmlspecialchars($p['NombreRemitente'] ?? 'Anónimo'); ?></p>
                                            <p class="text-slate-400 truncate max-w-xs mb-0"><?php echo htmlspecialchars($p['Asunto'] ?? $p['Mensaje'] ?? ''); ?></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($p['Estado'] ?? '') === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?>">
                                            <?php echo htmlspecialchars($p['Estado'] ?? 'Pendiente'); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Últimos Proyectos -->
                    <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-white mb-0">Últimos Proyectos Solicitados</h4>
                            <a href="admin_dashboard.php?tab=proyectos" class="text-xs text-sky-400 hover:underline">Ver todos →</a>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($proyectosList)): ?>
                                <p class="text-xs text-slate-400 italic">No hay solicitudes de proyectos.</p>
                            <?php else: ?>
                                <?php foreach (array_slice($proyectosList, 0, 4) as $pr): ?>
                                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 flex items-center justify-between text-xs">
                                        <div>
                                            <p class="font-bold text-slate-200 mb-0.5">#<?php echo $pr['Id']; ?> - <?php echo htmlspecialchars($pr['NombreContacto'] ?? 'Cliente'); ?></p>
                                            <p class="text-slate-400 truncate max-w-xs mb-0"><?php echo htmlspecialchars($pr['TipoServicio'] ?? 'Proyecto TI'); ?></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($pr['Estado'] ?? '') === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-sky-500/10 text-sky-400 border border-sky-500/20'; ?>">
                                            <?php echo htmlspecialchars($pr['Estado'] ?? 'Pendiente'); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- PESTAÑA 2: GESTIÓN DE PQRS -->
            <?php if ($tab === 'pqrs'): ?>
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

                    <?php if (empty($pqrsFiltradas)): ?>
                        <div class="p-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-sm text-slate-400 mb-0">No se encontraron solicitudes PQRS para este filtro.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4">Radicado</th>
                                        <th class="py-3 px-4">Remitente</th>
                                        <th class="py-3 px-4">Contacto</th>
                                        <th class="py-3 px-4">Asunto / Mensaje</th>
                                        <th class="py-3 px-4">Estado Actual</th>
                                        <th class="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <?php foreach ($pqrsFiltradas as $p): ?>
                                        <tr class="hover:bg-slate-900/50 transition-colors">
                                            <td class="py-4 px-4 font-bold text-sky-400">#<?php echo $p['Id']; ?></td>
                                            <td class="py-4 px-4 font-medium text-white"><?php echo htmlspecialchars($p['NombreRemitente'] ?? 'Anónimo'); ?></td>
                                            <td class="py-4 px-4 text-slate-300">
                                                <div><?php echo htmlspecialchars($p['CorreoRemitente'] ?? 'N/A'); ?></div>
                                                <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($p['TelefonoRemitente'] ?? ''); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300 max-w-xs">
                                                <p class="font-semibold text-slate-200 mb-1"><?php echo htmlspecialchars($p['Asunto'] ?? 'Sin Asunto'); ?></p>
                                                <p class="text-[11px] text-slate-400 line-clamp-2 mb-0"><?php echo htmlspecialchars($p['Mensaje'] ?? ''); ?></p>
                                            </td>
                                            <td class="py-4 px-4">
                                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($p['Estado'] ?? '') === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?>">
                                                    <?php echo htmlspecialchars($p['Estado'] ?? 'Pendiente'); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <form method="POST" action="admin_dashboard.php?tab=pqrs" class="inline-flex items-center gap-2">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="update_pqrs">
                                                    <input type="hidden" name="pqrs_id" value="<?php echo $p['Id']; ?>">
                                                    
                                                    <select name="nuevo_estado" class="bg-slate-900 text-slate-200 border border-slate-700 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-sky-500">
                                                        <option value="Pendiente" <?php if (($p['Estado']??'')==='Pendiente') echo 'selected'; ?>>Pendiente</option>
                                                        <option value="En Proceso" <?php if (($p['Estado']??'')==='En Proceso') echo 'selected'; ?>>En Proceso</option>
                                                        <option value="Resuelto" <?php if (($p['Estado']??'')==='Resuelto') echo 'selected'; ?>>Resuelto</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-medium text-xs rounded-lg transition-all shadow-sm">
                                                        Guardar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- PESTAÑA 3: GESTIÓN DE PROYECTOS -->
            <?php if ($tab === 'proyectos'): ?>
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

                    <?php if (empty($proyectosFiltrados)): ?>
                        <div class="p-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-sm text-slate-400 mb-0">No se encontraron proyectos para este filtro.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4">Proyecto</th>
                                        <th class="py-3 px-4">Cliente / Empresa</th>
                                        <th class="py-3 px-4">Contacto</th>
                                        <th class="py-3 px-4">Servicio Requerido</th>
                                        <th class="py-3 px-4">Estado</th>
                                        <th class="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <?php foreach ($proyectosFiltrados as $pr): ?>
                                        <tr class="hover:bg-slate-900/50 transition-colors">
                                            <td class="py-4 px-4 font-bold text-sky-400">#<?php echo $pr['Id']; ?></td>
                                            <td class="py-4 px-4 font-medium text-white">
                                                <div><?php echo htmlspecialchars($pr['NombreContacto'] ?? 'Cliente'); ?></div>
                                                <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($pr['NombreEmpresa'] ?? ''); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300">
                                                <div><?php echo htmlspecialchars($pr['CorreoContacto'] ?? 'N/A'); ?></div>
                                                <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($pr['TelefonoContacto'] ?? ''); ?></div>
                                            </td>
                                            <td class="py-4 px-4 text-slate-300 max-w-xs">
                                                <p class="font-semibold text-slate-200 mb-1"><?php echo htmlspecialchars($pr['TipoServicio'] ?? 'General'); ?></p>
                                                <p class="text-[11px] text-slate-400 line-clamp-2 mb-0"><?php echo htmlspecialchars($pr['DescripcionProyecto'] ?? ''); ?></p>
                                            </td>
                                            <td class="py-4 px-4">
                                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider <?php echo (strtolower($pr['Estado'] ?? '') === 'resuelto') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-sky-500/10 text-sky-400 border border-sky-500/20'; ?>">
                                                    <?php echo htmlspecialchars($pr['Estado'] ?? 'Pendiente'); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <form method="POST" action="admin_dashboard.php?tab=proyectos" class="inline-flex items-center gap-2">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="update_proyecto">
                                                    <input type="hidden" name="proyecto_id" value="<?php echo $pr['Id']; ?>">
                                                    
                                                    <select name="nuevo_estado" class="bg-slate-900 text-slate-200 border border-slate-700 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-sky-500">
                                                        <option value="Pendiente" <?php if (($pr['Estado']??'')==='Pendiente') echo 'selected'; ?>>Pendiente</option>
                                                        <option value="En Proceso" <?php if (($pr['Estado']??'')==='En Proceso') echo 'selected'; ?>>En Proceso</option>
                                                        <option value="Resuelto" <?php if (($pr['Estado']??'')==='Resuelto') echo 'selected'; ?>>Resuelto</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-medium text-xs rounded-lg transition-all shadow-sm">
                                                        Guardar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- PESTAÑA 4: TESTIMONIOS -->
            <?php if ($tab === 'testimonios'): ?>
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">Testimonios Registrados</h3>
                            <p class="text-xs text-slate-400 mb-0">Comentarios enviados por los usuarios en el sitio principal.</p>
                        </div>
                        <span class="px-3 py-1 bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs font-bold rounded-full">
                            Total: <?php echo $totalTestimonios; ?>
                        </span>
                    </div>

                    <?php if (empty($testimoniosList)): ?>
                        <div class="p-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-sm text-slate-400 mb-0">No se han registrado testimonios en la base de datos.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($testimoniosList as $t): ?>
                                <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex text-amber-400">
                                            <?php for ($s = 0; $s < intval($t['Estrellas'] ?? 5); $s++): ?>
                                                ★
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-[10px] font-mono text-slate-500">ID #<?php echo $t['Id']; ?></span>
                                    </div>
                                    <p class="text-xs text-slate-300 italic leading-relaxed mb-0">"<?php echo htmlspecialchars($t['Texto'] ?? ''); ?>"</p>
                                    <div class="pt-2 border-t border-slate-800/60">
                                        <p class="text-xs font-bold text-white mb-0"><?php echo htmlspecialchars($t['Nombre'] ?? 'Cliente'); ?></p>
                                        <p class="text-[11px] text-slate-400 mb-0"><?php echo htmlspecialchars($t['Cargo'] ?? 'Usuario'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>

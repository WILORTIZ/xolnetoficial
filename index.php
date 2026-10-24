<?php
require_once 'db.php';

// Manejo de peticiones POST de comentarios (debe ejecutarse antes de cualquier salida HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_comment') {
    header('Content-Type: application/json');
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : '';
    $texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';
    $estrellas = isset($_POST['estrellas']) ? (int)$_POST['estrellas'] : 5;
    
    if (empty($nombre) || empty($cargo) || empty($texto)) {
        echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos obligatorios.']);
        exit;
    }
    
    if ($estrellas < 1 || $estrellas > 5) {
        $estrellas = 5;
    }
    
    if ($connected && $pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO testimonios (Nombre, Cargo, Texto, Estrellas) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $cargo, '"' . $texto . '"', $estrellas]);
            echo json_encode(['success' => true, 'comment' => [
                'nombre' => $nombre,
                'cargo' => $cargo,
                'texto' => '"' . $texto . '"',
                'estrellas' => $estrellas
            ]]);
            exit;
        } catch (PDOException $e) {
            // Auto-crear la tabla si no existe en la BD de producción y reintentar inmediatamente
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS `testimonios` (
                  `Id` int(11) NOT NULL AUTO_INCREMENT,
                  `Nombre` varchar(256) NOT NULL,
                  `Cargo` varchar(256) NOT NULL,
                  `Texto` text NOT NULL,
                  `Estrellas` int(11) NOT NULL,
                  `FechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`Id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

                $stmt = $pdo->prepare("INSERT INTO testimonios (Nombre, Cargo, Texto, Estrellas) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $cargo, '"' . $texto . '"', $estrellas]);
                echo json_encode(['success' => true, 'comment' => [
                    'nombre' => $nombre,
                    'cargo' => $cargo,
                    'texto' => '"' . $texto . '"',
                    'estrellas' => $estrellas
                ]]);
                exit;
            } catch (PDOException $e2) {
                echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos: ' . $e2->getMessage()]);
                exit;
            }
        }
    } else {
        echo json_encode(['success' => true, 'comment' => [
            'nombre' => $nombre,
            'cargo' => $cargo,
            'texto' => '"' . $texto . '"',
            'estrellas' => $estrellas
        ]]);
        exit;
    }
}

$pageTitle = "Inicio";
include 'header.php';

// Obtener empresas activas de la base de datos para la marquesina animada
$empresasMarquee = [];
if ($pdo instanceof PDO) {
    try {
        // Auto-crear tabla empresas si aún no existe en MySQL
        $pdo->exec("CREATE TABLE IF NOT EXISTS `empresas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(150) NOT NULL,
            `logo_url` VARCHAR(255) NULL,
            `estilo_css` VARCHAR(255) NULL,
            `orden` INT DEFAULT 0,
            `estado` TINYINT(1) DEFAULT 1,
            `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $stmt = $pdo->query("SELECT * FROM `empresas` WHERE `estado` = 1 ORDER BY `orden` ASC, `id` ASC");
        $empresasMarquee = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si la tabla recién creada está vacía, insertar los registros iniciales
        if (empty($empresasMarquee)) {
            $empresasIniciales = [
                ['nombre' => 'EMPRESA_A', 'estilo_css' => 'font-bold tracking-widest', 'orden' => 1],
                ['nombre' => 'TECH_CORP', 'estilo_css' => 'font-extrabold italic', 'orden' => 2],
                ['nombre' => 'GLOBAL_NET', 'estilo_css' => 'font-bold uppercase', 'orden' => 3],
                ['nombre' => 'SYSTEMS', 'estilo_css' => 'font-medium', 'orden' => 4],
                ['nombre' => 'DATA_HUB', 'estilo_css' => 'font-light tracking-tighter', 'orden' => 5],
                ['nombre' => 'INFRA_STRUC', 'estilo_css' => 'font-bold', 'orden' => 6]
            ];
            $insertStmt = $pdo->prepare("INSERT INTO `empresas` (`nombre`, `estilo_css`, `orden`, `estado`) VALUES (:nombre, :estilo_css, :orden, 1)");
            foreach ($empresasIniciales as $emp) {
                $insertStmt->execute([
                    ':nombre' => $emp['nombre'],
                    ':estilo_css' => $emp['estilo_css'],
                    ':orden' => $emp['orden']
                ]);
            }
            $stmt = $pdo->query("SELECT * FROM `empresas` WHERE `estado` = 1 ORDER BY `orden` ASC, `id` ASC");
            $empresasMarquee = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $empresasMarquee = [];
    }
}

if (empty($empresasMarquee)) {
    // Respaldos por defecto si no hay conexión a base de datos
    $empresasMarquee = [
        ['nombre' => 'EMPRESA_A', 'estilo_css' => 'font-bold tracking-widest'],
        ['nombre' => 'TECH_CORP', 'estilo_css' => 'font-extrabold italic'],
        ['nombre' => 'GLOBAL_NET', 'estilo_css' => 'font-bold uppercase'],
        ['nombre' => 'SYSTEMS', 'estilo_css' => 'font-medium'],
        ['nombre' => 'DATA_HUB', 'estilo_css' => 'font-light tracking-tighter'],
        ['nombre' => 'INFRA_STRUC', 'estilo_css' => 'font-bold']
    ];
}
?>

<main>
    <!-- Hero Section -->
    <section id="hero" class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden bg-surface">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop flex flex-col md:flex-row items-center gap-12 lg:gap-16 relative z-10">
            <div class="flex-1 text-left">
                <h1 class="font-display text-display text-3xl md:text-5xl lg:text-6xl mb-6 text-on-surface leading-tight font-bold tracking-tight">
                    Integración Tecnológica e <span class="text-primary">Infraestructura de Red</span> de Alto Nivel
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl mb-10 leading-relaxed text-base md:text-lg">
                    Soluciones integrales en conectividad, infraestructura física y seguridad electrónica para la transformación digital de su empresa.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="proyecto.php" class="px-8 py-4 bg-primary text-white font-label-md text-body-lg rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/25 no-underline font-semibold flex items-center gap-2">
                        Solicitar Presupuesto
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    <a href="#capacidades" class="px-8 py-4 bg-white border border-outline-variant/60 text-on-surface font-label-md text-body-lg rounded-lg hover:bg-surface-container-low active:scale-95 transition-all no-underline font-medium">
                        Nuestros Servicios
                    </a>
                </div>
            </div>
            <div class="flex-1 w-full relative min-h-[450px]">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-full max-w-lg glass rounded-xl p-6 md:p-8 float-anim shadow-2xl relative border border-outline-variant/40">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-error/60"></div>
                                <div class="w-3 h-3 rounded-full bg-tertiary/60"></div>
                                <div class="w-3 h-3 rounded-full bg-primary/60"></div>
                            </div>
                            <div class="font-mono text-label-md text-on-surface-variant bg-surface-container rounded px-3 py-1">red_estado: operativa</div>
                        </div>
                            <div class="space-y-6">
                                <div class="h-[0.5px] bg-outline-variant/30 w-full"></div>
                                <div class="flex justify-between items-end">
                                    <div class="space-y-2">
                                        <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Disponibilidad del Nodo</p>
                                        <p class="font-headline-lg text-headline-lg font-bold text-3xl text-on-surface">99.98%</p>
                                    </div>
                                    <div class="flex gap-1.5 h-12 items-end">
                                        <div class="w-2.5 h-8 bg-primary/40 rounded-t-sm"></div>
                                        <div class="w-2.5 h-10 bg-primary/60 rounded-t-sm"></div>
                                        <div class="w-2.5 h-12 bg-primary rounded-t-sm"></div>
                                        <div class="w-2.5 h-12 bg-primary rounded-t-sm"></div>
                                        <div class="w-2.5 h-11 bg-primary/90 rounded-t-sm"></div>
                                    </div>
                                </div>

                                <!-- Slideshow Flotante de Servicios (Cambia automáticamente cada 3 segundos) -->
                                <div class="relative w-full h-48 md:h-56 rounded-lg overflow-hidden border border-outline-variant/40 shadow-md">
                                    <div id="heroSlideshow" class="absolute inset-0 w-full h-full">
                                        <div class="slide-item absolute inset-0 opacity-100 transition-opacity duration-1000 ease-in-out">
                                            <img src="images/hero_slides/it_support.jpg" alt="Soporte TI" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent p-4 flex flex-col justify-end text-left">
                                                <span class="text-[11px] font-mono text-cyan-300 font-semibold tracking-wider uppercase">01 / 05 — Soporte TI</span>
                                                <h4 class="text-white font-bold text-base md:text-lg mb-0">Mantenimiento & Continuidad Operativa</h4>
                                            </div>
                                        </div>
                                        <div class="slide-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out">
                                            <img src="images/hero_slides/networking.jpg" alt="Networking" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent p-4 flex flex-col justify-end text-left">
                                                <span class="text-[11px] font-mono text-cyan-300 font-semibold tracking-wider uppercase">02 / 05 — Networking</span>
                                                <h4 class="text-white font-bold text-base md:text-lg mb-0">Mesa de Ayuda para PYMEs</h4>
                                            </div>
                                        </div>
                                        <div class="slide-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out">
                                            <img src="images/hero_slides/help_desk.png" alt="Help Desk" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent p-4 flex flex-col justify-end text-left">
                                                <span class="text-[11px] font-mono text-cyan-300 font-semibold tracking-wider uppercase">03 / 05 — Mesa de Ayuda</span>
                                                <h4 class="text-white font-bold text-base md:text-lg mb-0">Soporte Técnico Especializado 24/7</h4>
                                            </div>
                                        </div>
                                        <div class="slide-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out">
                                            <img src="images/hero_slides/ai_automation.png" alt="Seguridad IA" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent p-4 flex flex-col justify-end text-left">
                                                <span class="text-[11px] font-mono text-cyan-300 font-semibold tracking-wider uppercase">04 / 05 — Seguridad IA</span>
                                                <h4 class="text-white font-bold text-base md:text-lg mb-0">Monitoreo CCTV IP & Analítica de Video</h4>
                                            </div>
                                        </div>
                                        <div class="slide-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out">
                                            <img src="images/hero_slides/web_development.png" alt="Desarrollo Web" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent p-4 flex flex-col justify-end text-left">
                                                <span class="text-[11px] font-mono text-cyan-300 font-semibold tracking-wider uppercase">05 / 05 — Desarrollo Web</span>
                                                <h4 class="text-white font-bold text-base md:text-lg mb-0">Plataformas Web & Soluciones Digitales</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="absolute top-3 right-3 flex gap-1.5 z-20">
                                        <span class="slide-dot w-2 h-2 rounded-full bg-white opacity-100 transition-all"></span>
                                        <span class="slide-dot w-2 h-2 rounded-full bg-white opacity-40 transition-all"></span>
                                        <span class="slide-dot w-2 h-2 rounded-full bg-white opacity-40 transition-all"></span>
                                        <span class="slide-dot w-2 h-2 rounded-full bg-white opacity-40 transition-all"></span>
                                        <span class="slide-dot w-2 h-2 rounded-full bg-white opacity-40 transition-all"></span>
                                    </div>
                                </div>
                            </div>
                        <div class="absolute -z-10 -top-16 -right-16 w-64 h-64 bg-primary/10 rounded-full blur-[80px]"></div>
                        <div class="absolute -z-10 -bottom-16 -left-16 w-64 h-64 bg-secondary-container/30 rounded-full blur-[80px]"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Strip / Marquee (Animación continua a la izquierda desde MySQL) -->
    <section class="py-10 border-y border-outline-variant/20 bg-white overflow-hidden">
        <div class="marquee-container opacity-70 text-on-surface-variant font-mono">
            <div class="marquee-content">
                <?php 
                // Renderizar el set de empresas dos veces para asegurar un bucle infinito continuo
                for ($loop = 0; $loop < 2; $loop++): 
                    foreach ($empresasMarquee as $emp): 
                        $estilo = !empty($emp['estilo_css']) ? htmlspecialchars($emp['estilo_css']) : 'font-bold';
                        $nombre = htmlspecialchars($emp['nombre']);
                        $logoUrl = !empty($emp['logo_url']) ? htmlspecialchars($emp['logo_url']) : '';
                ?>
                    <div class="flex items-center gap-3 shrink-0">
                        <?php if (!empty($logoUrl)): ?>
                            <img src="<?php echo $logoUrl; ?>" alt="<?php echo $nombre; ?>" class="h-7 max-w-[120px] object-contain grayscale hover:grayscale-0 transition-all">
                        <?php endif; ?>
                        <span class="font-headline-md text-headline-md text-lg md:text-xl <?php echo $estilo; ?>"><?php echo $nombre; ?></span>
                    </div>
                <?php 
                    endforeach; 
                endfor; 
                ?>
            </div>
        </div>
    </section>

    <!-- Core Capabilities Section -->
    <section class="section-padding bg-surface-container-lowest py-20" id="capacidades">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
            <div class="mb-16 text-left">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">Nuestros Pilares de Servicio</span>
                <h2 class="font-headline-lg text-headline-lg max-w-2xl text-2xl md:text-4xl font-bold text-on-surface">Sistemas integrales diseñados para durabilidad, cumplimiento normativo y alto rendimiento.</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Pillar 1: Mesa de Ayuda para PYMEs -->
                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 hover:border-primary/50 transition-all group shadow-sm hover:shadow-md">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    </div>
                    <h3 class="font-headline-md text-xl font-bold mb-3 text-on-surface">Mesa de Ayuda para PYMEs</h3>
                    <p class="text-on-surface-variant font-body-md mb-6 leading-relaxed">Soporte técnico remoto y presencial especializado para pequeñas empresas, garantizando continuidad operativa.</p>
                    <ul class="space-y-2.5 font-label-md text-on-surface/80 list-none p-0">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Soporte Técnico Remoto &amp; Presencial</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Atención Rápida para Pequeñas Empresas</li>
                    </ul>
                </div>
                <!-- Pillar 2: Diseño Web & Software a Medida -->
                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 hover:border-primary/50 transition-all group shadow-sm hover:shadow-md">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/><polyline points="7 8 3 12 7 16"/><polyline points="17 8 21 12 17 16"/></svg>
                    </div>
                    <h3 class="font-headline-md text-xl font-bold mb-3 text-on-surface">Diseño Web &amp; Software a Medida</h3>
                    <p class="text-on-surface-variant font-body-md mb-6 leading-relaxed">Desarrollo de sitios web modernos, aplicaciones dinámicas y sistemas de software personalizados.</p>
                    <ul class="space-y-2.5 font-label-md text-on-surface/80 list-none p-0">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Sitios Web 100% Responsive</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Software &amp; Sistemas a Medida</li>
                    </ul>
                </div>
                <!-- Pillar 3: Seguridad Electrónica -->
                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 hover:border-primary/50 transition-all group shadow-sm hover:shadow-md">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path d="M9 11l2 2 4-4" /></svg>
                    </div>
                    <h3 class="font-headline-md text-xl font-bold mb-3 text-on-surface">Seguridad Electrónica</h3>
                    <p class="text-on-surface-variant font-body-md mb-6 leading-relaxed">Sistemas CCTV IP con analítica, control de acceso biométrico y alarmas de intrusión.</p>
                    <ul class="space-y-2.5 font-label-md text-on-surface/80 list-none p-0">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Analítica de Video</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Control Biométrico</li>
                    </ul>
                </div>
                <!-- Pillar 4: Mantenimiento & Soporte -->
                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 hover:border-primary/50 transition-all group shadow-sm hover:shadow-md">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14c0-4.4 3.6-8 8-8s8 3.6 8 8" /><rect x="2" y="13" width="3" height="4" rx="1" /><rect x="19" y="13" width="3" height="4" rx="1" /><path d="M19 15h-2c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h2" /></svg>
                    </div>
                    <h3 class="font-headline-md text-xl font-bold mb-3 text-on-surface">Mantenimiento &amp; Soporte</h3>
                    <p class="text-on-surface-variant font-body-md mb-6 leading-relaxed">Soporte técnico 24/7 y planes de mantenimiento proactivo para asegurar continuidad operativa.</p>
                    <ul class="space-y-2.5 font-label-md text-on-surface/80 list-none p-0">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Help Desk 24/7</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Gestión Proactiva</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Showcase (Physical Infrastructure Control) - Guardado comentado para uso futuro -->
    <?php /* 
    <section class="section-padding bg-surface py-20 overflow-hidden">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop text-center mb-16">
            <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">Operaciones Técnicas</span>
            <h2 class="font-headline-lg text-headline-lg text-2xl md:text-4xl font-bold mb-4 text-on-surface">Control total de su infraestructura física</h2>
            <p class="text-on-surface-variant font-body-lg max-w-2xl mx-auto text-base md:text-lg">Gestione sus nodos de red, sistemas de seguridad y protocolos de mantenimiento desde un único centro de comando local.</p>
        </div>
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
            <div class="glass rounded-xl shadow-[0_40px_100px_rgba(0,0,0,0.08)] overflow-hidden border border-outline-variant/50">
                <!-- Dashboard Header -->
                <div class="bg-surface-container-highest/50 px-6 py-3 border-b border-outline-variant/30 flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <div class="flex gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-error/70"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-tertiary/70"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-primary/70"></div>
                        </div>
                        <div class="h-4 w-[0.5px] bg-outline-variant/40 mx-2"></div>
                        <div class="flex items-center gap-2 font-mono text-label-md text-on-surface-variant">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" /></svg>
                            xcolnet_infra_monitor — centro de control físico
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="px-3 py-1 bg-primary/10 text-primary rounded-full text-label-md font-mono">Conexión Local Estable</div>
                    </div>
                </div>
                <!-- Dashboard Content -->
                <div class="flex flex-col md:flex-row min-h-[480px]">
                    <!-- Sidebar Rail -->
                    <div class="w-full md:w-64 border-b md:border-b-0 md:border-r border-outline-variant/20 bg-surface-container-low/30 p-6">
                        <div class="space-y-6 text-left">
                            <div class="space-y-2">
                                <p class="font-label-md text-label-md text-outline uppercase tracking-wider font-semibold">Infraestructura</p>
                                <div class="p-2.5 rounded-lg bg-primary-container/10 text-primary font-medium flex items-center gap-2.5">
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="2" width="6" height="5" rx="1" /><rect x="2" y="16" width="6" height="5" rx="1" /><rect x="16" y="16" width="6" height="5" rx="1" /><path d="M12 7v5M5 12h14v4M5 12v4" /></svg>Estado de Red
                                </div>
                                <div class="p-2.5 rounded-lg hover:bg-surface-container transition-colors flex items-center gap-2.5 text-on-surface-variant">
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8l4 4m0 0l-4 4m4-4H3M7 16l-4-4m0 0l4-4m-4 4h18" /></svg>Conectividad Física
                                </div>
                            </div>
                            <div class="space-y-2 pt-2">
                                <p class="font-label-md text-label-md text-outline uppercase tracking-wider font-semibold">Seguridad</p>
                                <div class="p-2.5 rounded-lg hover:bg-surface-container transition-colors flex items-center gap-2.5 text-on-surface-variant">
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Cámaras CCTV
                                </div>
                                <div class="p-2.5 rounded-lg hover:bg-surface-container transition-colors flex items-center gap-2.5 text-on-surface-variant">
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 11V7a4 4 0 118 0m-4 10v2m-6-8h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2z" /></svg>
                                    Accesos Biométricos
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Canvas Area -->
                    <div class="flex-1 p-6 md:p-8 overflow-y-auto text-left">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-white border border-outline-variant/30 rounded-lg p-6">
                                <p class="font-label-md text-label-md text-outline mb-1 font-semibold">ESTADO DE RED (LATENCIA)</p>
                                <p class="font-headline-md text-headline-md font-bold mb-4 text-2xl text-on-surface">12ms Promedio</p>
                                <div class="h-24 w-full bg-surface-container-low rounded-lg flex items-end gap-1.5 px-3 py-2">
                                    <div class="flex-1 bg-primary rounded-t-sm h-[40%]"></div>
                                    <div class="flex-1 bg-primary rounded-t-sm h-[35%]"></div>
                                    <div class="flex-1 bg-primary rounded-t-sm h-[45%]"></div>
                                    <div class="flex-1 bg-primary rounded-t-sm h-[30%]"></div>
                                    <div class="flex-1 bg-primary rounded-t-sm h-[38%]"></div>
                                    <div class="flex-1 bg-primary rounded-t-sm h-[42%]"></div>
                                    <div class="flex-1 bg-primary rounded-t-sm h-[35%]"></div>
                                </div>
                            </div>
                            <div class="bg-white border border-outline-variant/30 rounded-lg p-6">
                                <p class="font-label-md text-label-md text-outline mb-1 font-semibold">ACCESOS BIOMÉTRICOS</p>
                                <p class="font-headline-md text-headline-md font-bold mb-4 text-2xl text-on-surface">84 Entradas / Hoy</p>
                                <div class="h-24 w-full bg-surface-container-low rounded-lg overflow-hidden relative">
                                    <svg class="absolute bottom-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 40">
                                        <path class="text-primary" d="M0 35 L10 32 L20 38 L30 25 L40 30 L50 15 L60 25 L70 20 L80 32 L90 28 L100 35 L100 40 L0 40 Z" fill="rgba(0,102,255,0.15)" stroke="currentColor" stroke-width="1.5"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#111827] rounded-lg p-6 font-mono text-white text-label-md leading-relaxed overflow-x-auto shadow-inner">
                            <p class="text-slate-400 opacity-60 mb-2">// Verificando integridad de capa física...</p>
                            <p class="text-blue-400 mb-1"><span class="text-amber-400">$</span> xcol-net --check-connectivity</p>
                            <p class="text-slate-200 mb-1">Verificando certificación de enlaces de cobre Cat6A...</p>
                            <p class="text-blue-300 mb-1">Escaneando estado de switches en core principal [██████████████] 100%</p>
                            <p class="text-emerald-400 mb-1">Estado de enlaces troncales: OK | Sincronización: ACTIVA</p>
                            <p class="text-slate-400 mb-0">Reporte de conectividad física generado localmente en /var/logs/xcolnet/network_report.pdf</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    */ ?>

    <!-- Testimonials Section (Visualización Aleatoria) -->
    <section class="section-padding bg-surface py-20" id="testimonios">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
            <?php
            // Cargar comentarios guardados de la base de datos (más recientes primero)
            $testimoniosDb = [];
            if ($connected && $pdo) {
                try {
                    $stmt = $pdo->query("SELECT Nombre as nombre, Cargo as cargo, Texto as texto, Estrellas as estrellas FROM testimonios ORDER BY Id DESC");
                    $testimoniosDb = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Si no existe la tabla, auto-crearla para futuras lecturas
                    try {
                        $pdo->exec("CREATE TABLE IF NOT EXISTS `testimonios` (
                          `Id` int(11) NOT NULL AUTO_INCREMENT,
                          `Nombre` varchar(256) NOT NULL,
                          `Cargo` varchar(256) NOT NULL,
                          `Texto` text NOT NULL,
                          `Estrellas` int(11) NOT NULL,
                          `FechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (`Id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
                    } catch (PDOException $e2) {}
                }
            }

            // Sin comentarios ficticios/falsos: solo opiniones reales
            $testimoniosEstaticos = [];

            // Combinar testimonios: únicamente testimonios auténticos
            $testimonios = array_merge($testimoniosDb, $testimoniosEstaticos);

            // Seleccionar hasta 3 testimonios para la tanda inicial
            $testimoniosMezclados = $testimonios;
            shuffle($testimoniosMezclados);
            $tandaInicial = array_slice($testimoniosMezclados, 0, 3);
            ?>

            <!-- Schema.org AggregateRating para Resultados Enriquecidos (Google Rich Snippets con Estrellas) -->
            <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "LocalBusiness",
              "name": "XCOLNET",
              "image": "https://www.xcolnet.com/images/favicon.png",
              "url": "https://www.xcolnet.com",
              "telephone": "+57 317 087 7414",
              "priceRange": "$$",
              "address": {
                "@type": "PostalAddress",
                "addressCountry": "CO"
              },
              "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "4.9",
                "bestRating": "5",
                "worstRating": "1",
                "ratingCount": "<?php echo count($testimonios); ?>"
              }
            }
            </script>

            <div class="text-center mb-12">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">CLIENTES SATISFECHOS</span>
                <h2 class="font-headline-lg text-headline-lg text-2xl md:text-4xl font-bold text-on-surface mb-4">Confianza respaldada por resultados reales</h2>

                <!-- Insignia de Calificación Oficial Google Reviews -->
                <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-white border border-outline-variant/40 rounded-full shadow-sm mb-6 hover:shadow-md transition-shadow">
                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3h3.88c2.27-2.09 3.66-5.17 3.66-9.09z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.27v3.09C3.25 21.3 7.31 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.32c-.25-.72-.38-1.49-.38-2.32s.13-1.6.38-2.32V6.59H1.27C.46 8.21 0 10.05 0 12s.46 3.79 1.27 5.41l4.01-3.09z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.59l4.01 3.09c.95-2.83 3.6-4.93 6.72-4.93z"/>
                    </svg>
                    <div class="flex items-center gap-0.5 text-amber-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-on-surface">5.0 en Google Reviews</span>
                    <span class="text-xs text-outline font-medium">(Verificado)</span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 mb-4">
                    <button type="button" id="openCommentModalBtn" class="px-6 py-2.5 bg-primary text-white font-label-md text-label-md rounded-lg hover:bg-primary/95 transition-all shadow-md flex items-center gap-2 active:scale-95 font-medium">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Dejar un Comentario
                    </button>
                    <a href="https://www.google.com/search?q=XCOLNET+Soluciones+Tecnologicas+Colombia#lrd=0x0:0x0,3" target="_blank" rel="noopener noreferrer" class="px-5 py-2.5 bg-white border border-outline-variant/60 hover:border-primary/50 text-on-surface font-label-md text-label-md rounded-lg hover:bg-surface-container transition-all shadow-sm flex items-center gap-2 active:scale-95 font-medium no-underline">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3h3.88c2.27-2.09 3.66-5.17 3.66-9.09z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.27v3.09C3.25 21.3 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.32c-.25-.72-.38-1.49-.38-2.32s.13-1.6.38-2.32V6.59H1.27C.46 8.21 0 10.05 0 12s.46 3.79 1.27 5.41l4.01-3.09z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.59l4.01 3.09c.95-2.83 3.6-4.93 6.72-4.93z"/>
                        </svg>
                        Opinar en Google
                    </a>
                    <button type="button" id="openAllCommentsBtn" class="px-6 py-2.5 bg-surface-container-high border border-outline-variant/40 text-on-surface font-label-md text-label-md rounded-lg hover:bg-primary hover:text-white transition-all shadow-md flex items-center gap-2 active:scale-95 font-medium">
                        <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        Ver Todos los Comentarios (<?php echo count($testimonios); ?>)
                    </button>
                </div>
            </div>
            
            <div id="testimonios-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 transition-opacity duration-500 ease-in-out" style="opacity: 1;">
                <?php if (!empty($tandaInicial)): ?>
                    <?php foreach ($tandaInicial as $test): ?>
                        <div class="bg-white border border-outline-variant/30 rounded-xl p-8 shadow-sm hover:shadow-md transition-all text-left flex flex-col justify-between">
                            <div>
                                <div class="flex gap-1 mb-4 text-amber-400">
                                    <?php for ($s = 0; $s < $test['estrellas']; $s++): ?>
                                        <svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-on-surface-variant font-body-md mb-6 italic leading-relaxed"><?php echo htmlspecialchars($test['texto']); ?></p>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface mb-0"><?php echo htmlspecialchars($test['nombre']); ?></p>
                                <p class="text-label-md text-outline mb-0"><?php echo htmlspecialchars($test['cargo']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-12 px-6 text-center bg-white border border-outline-variant/30 rounded-2xl shadow-sm max-w-lg mx-auto">
                        <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <h3 class="font-headline-sm text-lg font-bold text-on-surface mb-2">¡Sé el primero en compartir tu experiencia!</h3>
                        <p class="text-sm text-on-surface-variant mb-6">Tu opinión es fundamental para nosotros y ayuda a otras empresas a conocer nuestros servicios.</p>
                        <button type="button" onclick="document.getElementById('openCommentModalBtn').click()" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/95 transition-all shadow-md">
                            Escribir un Comentario
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal para ver todos los comentarios -->
            <div id="allCommentsModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 invisible transition-all duration-300 px-4 py-6">
                <div class="bg-white border border-outline-variant/30 max-w-3xl w-full max-h-[88vh] rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-low">
                        <div>
                            <h3 class="font-bold text-xl text-on-surface mb-0.5 flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                Todos los Comentarios
                            </h3>
                            <p class="text-xs text-on-surface-variant mb-0 font-mono">Ordenados del más reciente al más antiguo (Total: <?php echo count($testimonios); ?>)</p>
                        </div>
                        <button type="button" id="closeAllCommentsModalBtn" class="text-outline hover:text-on-surface p-1.5 rounded-full hover:bg-surface-container transition-colors border-0 bg-transparent flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <!-- Buscador dentro del modal -->
                    <div class="px-6 py-3 bg-surface border-b border-outline-variant/20 flex items-center gap-2">
                        <svg class="w-5 h-5 text-outline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" id="searchAllCommentsInput" placeholder="Buscar por nombre, empresa o palabras clave..." class="w-full bg-transparent text-sm text-on-surface focus:outline-none placeholder:text-outline/70">
                    </div>

                    <!-- Lista scrollable de comentarios del más nuevo al más antiguo -->
                    <div class="p-6 overflow-y-auto space-y-4 max-h-[60vh] bg-surface-container-low/30" id="allCommentsList">
                        <?php if (empty($testimonios)): ?>
                            <div class="py-12 text-center text-on-surface-variant text-sm">
                                Aún no hay comentarios registrados. ¡Sé el primero en compartir tu experiencia!
                            </div>
                        <?php else: ?>
                            <?php foreach ($testimonios as $index => $test): ?>
                            <div class="comment-item bg-white border border-outline-variant/30 rounded-xl p-5 shadow-sm hover:shadow-md transition-all text-left flex flex-col justify-between" data-search="<?php echo htmlspecialchars(strtolower(($test['nombre'] ?? '') . ' ' . ($test['cargo'] ?? '') . ' ' . ($test['texto'] ?? ''))); ?>">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <div class="flex gap-1 text-amber-400">
                                            <?php for ($s = 0; $s < intval($test['estrellas'] ?? 5); $s++): ?>
                                                <svg class="w-4 h-4 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <?php endfor; ?>
                                        </div>
                                        <?php if ($index < count($testimoniosDb)): ?>
                                            <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-700 border border-emerald-500/30 text-[10px] font-mono font-bold rounded-full flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Comentario Reciente
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 bg-primary/10 text-primary border border-primary/20 text-[10px] font-mono font-medium rounded-full">
                                                Cliente Verificado
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-on-surface-variant font-body-md text-sm mb-4 italic leading-relaxed">
                                        <?php echo htmlspecialchars($test['texto'] ?? ''); ?>
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-outline-variant/20 flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-on-surface text-sm mb-0"><?php echo htmlspecialchars($test['nombre'] ?? ''); ?></p>
                                        <p class="text-xs text-outline mb-0"><?php echo htmlspecialchars($test['cargo'] ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-3 border-t border-outline-variant/20 flex justify-between items-center bg-surface-container-low">
                        <span class="text-xs text-outline font-mono">Mostrando del más reciente al más antiguo</span>
                        <button type="button" id="closeAllCommentsModalFooterBtn" class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-label-md font-semibold">Cerrar</button>
                    </div>
                </div>
            </div>

            <!-- Modal para dejar comentario -->
            <div id="commentModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 invisible transition-all duration-300 px-4">
                <div class="bg-white border border-outline-variant/30 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-low">
                        <h3 class="font-bold text-lg text-on-surface mb-0">Dejar un Comentario</h3>
                        <button type="button" id="closeCommentModalBtn" class="text-outline hover:text-on-surface p-1 rounded-full hover:bg-surface-container transition-colors border-0 bg-transparent flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <!-- Modal Body -->
                    <form id="commentForm" class="p-6 space-y-4 text-left">
                        <input type="hidden" name="action" value="save_comment" />
                        <div>
                            <label class="block font-medium text-label-md text-on-surface-variant mb-1" for="comment-nombre">Nombre Completo <span class="text-error">*</span></label>
                            <input type="text" id="comment-nombre" name="nombre" required class="w-full px-4 py-2 border border-outline-variant/60 rounded-lg bg-surface text-on-surface focus:outline-none focus:border-primary text-body-md" placeholder="Ej. Carlos Mendoza" />
                        </div>
                        <div>
                            <label class="block font-medium text-label-md text-on-surface-variant mb-1" for="comment-cargo">Cargo y Empresa <span class="text-error">*</span></label>
                            <input type="text" id="comment-cargo" name="cargo" required class="w-full px-4 py-2 border border-outline-variant/60 rounded-lg bg-surface text-on-surface focus:outline-none focus:border-primary text-body-md" placeholder="Ej. Director de IT, TechCorp" />
                        </div>
                        <div>
                            <label class="block font-medium text-label-md text-on-surface-variant mb-1" for="comment-texto">Comentario <span class="text-error">*</span></label>
                            <textarea id="comment-texto" name="texto" rows="3" required class="w-full px-4 py-2 border border-outline-variant/60 rounded-lg bg-surface text-on-surface focus:outline-none focus:border-primary text-body-md resize-none" placeholder="Escribe aquí tu testimonio sobre los servicios..."></textarea>
                        </div>
                        <div>
                            <label class="block font-medium text-label-md text-on-surface-variant mb-2">Calificación <span class="text-error">*</span></label>
                            <div class="flex gap-2 text-2xl cursor-pointer" id="modal-star-selector">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-8 h-8 text-amber-400 select-star cursor-pointer transition-transform hover:scale-110" data-rating="<?php echo $i; ?>" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="estrellas" id="input-estrellas" value="5" />
                        </div>
                        <!-- Modal Footer -->
                        <div class="pt-4 flex justify-end gap-3 border-t border-outline-variant/10 mt-6">
                            <button type="button" id="cancelCommentBtn" class="px-5 py-2 border border-outline-variant/60 rounded-lg hover:bg-surface-container transition-colors text-label-md text-on-surface-variant">Cancelar</button>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-label-md font-semibold">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Asistido de 1 Clic para Google Reviews -->
            <div id="googleSyncModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 md:p-8 shadow-2xl border border-outline-variant/30 transform scale-95 transition-transform duration-300 text-center relative overflow-hidden" id="googleSyncModalContent">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 via-red-500 via-amber-400 to-green-500"></div>

                    <div class="w-16 h-16 mx-auto mb-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center shadow-inner">
                        <svg class="w-9 h-9" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3h3.88c2.27-2.09 3.66-5.17 3.66-9.09z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.27v3.09C3.25 21.3 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.32c-.25-.72-.38-1.49-.38-2.32s.13-1.6.38-2.32V6.59H1.27C.46 8.21 0 10.05 0 12s.46 3.79 1.27 5.41l4.01-3.09z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.59l4.01 3.09c.95-2.83 3.6-4.93 6.72-4.93z"/>
                        </svg>
                    </div>

                    <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full mb-2">¡Comentario registrado en XCOLNET!</span>
                    <h3 class="text-xl md:text-2xl font-bold text-on-surface mb-2">¿Nos apoyas con 1 clic en Google?</h3>
                    <p class="text-sm text-on-surface-variant mb-5 leading-relaxed">
                        Tu opinión en Google Reviews nos da más visibilidad y ayuda a otras empresas a encontrar nuestros servicios.
                    </p>

                    <!-- Previsualización de la reseña -->
                    <div class="bg-surface border border-outline-variant/30 rounded-xl p-4 text-left mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-sm text-on-surface" id="syncPreviewName">Cliente</span>
                            <div class="flex gap-0.5 text-amber-400" id="syncPreviewStars"></div>
                        </div>
                        <p class="text-xs text-on-surface-variant italic mb-0 line-clamp-3" id="syncPreviewText"></p>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="space-y-3">
                        <button type="button" id="copyAndOpenGoogleBtn" class="w-full py-3.5 px-6 bg-[#1a73e8] hover:bg-[#1557b0] text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 flex items-center justify-center gap-3 transition-all active:scale-[0.98]">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                            <span>⭐ Copiar Texto y Publicar en Google</span>
                        </button>
                        <button type="button" id="closeGoogleSyncBtn" class="w-full py-2 px-4 text-xs font-medium text-outline hover:text-on-surface transition-colors">
                            Solo guardar en la web
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notificación flotante de éxito -->
            <div id="toastSuccess" class="fixed bottom-8 left-8 z-[130] bg-emerald-500 text-white px-5 py-3.5 rounded-xl shadow-xl flex items-center gap-3 transition-all duration-300 transform translate-y-20 opacity-0 pointer-events-none">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <span class="font-medium text-body-md" id="toastMessage">¡Comentario registrado con éxito!</span>
            </div>

            <script>
            const testimoniosData = <?php echo json_encode($testimonios); ?>;
            
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('testimonios-container');
                const commentModal = document.getElementById('commentModal');
                const openModalBtn = document.getElementById('openCommentModalBtn');
                const closeModalBtn = document.getElementById('closeCommentModalBtn');
                const cancelBtn = document.getElementById('cancelCommentBtn');
                const form = document.getElementById('commentForm');
                const starSelector = document.getElementById('modal-star-selector');
                const starsInput = document.getElementById('input-estrellas');
                const toast = document.getElementById('toastSuccess');

                // Elementos del modal de Google Sync
                const googleSyncModal = document.getElementById('googleSyncModal');
                const googleSyncModalContent = document.getElementById('googleSyncModalContent');
                const copyAndOpenGoogleBtn = document.getElementById('copyAndOpenGoogleBtn');
                const closeGoogleSyncBtn = document.getElementById('closeGoogleSyncBtn');
                const syncPreviewName = document.getElementById('syncPreviewName');
                const syncPreviewStars = document.getElementById('syncPreviewStars');
                const syncPreviewText = document.getElementById('syncPreviewText');
                let lastCommentForGoogle = null;

                function openGoogleSync(comment) {
                    lastCommentForGoogle = comment;
                    if (syncPreviewName) syncPreviewName.textContent = comment.nombre || 'Cliente';
                    if (syncPreviewText) syncPreviewText.textContent = `"${comment.texto || ''}"`;
                    if (syncPreviewStars) {
                        let starsHtml = '';
                        const count = parseInt(comment.estrellas) || 5;
                        for (let s = 0; s < count; s++) {
                            starsHtml += `<svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`;
                        }
                        syncPreviewStars.innerHTML = starsHtml;
                    }

                    if (googleSyncModal && googleSyncModalContent) {
                        googleSyncModal.classList.remove('opacity-0', 'pointer-events-none');
                        googleSyncModalContent.classList.remove('scale-95');
                        googleSyncModalContent.classList.add('scale-100');
                    }
                }

                function closeGoogleSync() {
                    if (googleSyncModal && googleSyncModalContent) {
                        googleSyncModal.classList.add('opacity-0', 'pointer-events-none');
                        googleSyncModalContent.classList.remove('scale-100');
                        googleSyncModalContent.classList.add('scale-95');
                    }
                }

                if (closeGoogleSyncBtn) {
                    closeGoogleSyncBtn.addEventListener('click', closeGoogleSync);
                }

                if (copyAndOpenGoogleBtn) {
                    copyAndOpenGoogleBtn.addEventListener('click', function() {
                        const text = lastCommentForGoogle ? lastCommentForGoogle.texto : '';
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).catch(() => {});
                        }
                        const googleReviewUrl = 'https://www.google.com/search?q=XCOLNET+Soluciones+Tecnologicas+Colombia#lrd=0x0:0x0,3';
                        window.open(googleReviewUrl, '_blank');
                        closeGoogleSync();
                        showToast('¡Texto copiado al portapapeles! Pégalo (Ctrl+V) en Google.');
                    });
                }

                function escapeHtml(str) {
                    return str
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                // Rotador automático
                let rotarInterval = null;
                function rotarTestimonios() {
                    if (!container || !testimoniosData || testimoniosData.length === 0) return;
                    container.style.opacity = '0';
                    setTimeout(() => {
                        const mezclados = [...testimoniosData].sort(() => 0.5 - Math.random());
                        const seleccionados = mezclados.slice(0, 3);
                        
                        let html = '';
                        seleccionados.forEach(test => {
                            let estrellasHtml = '';
                            for (let s = 0; s < test.estrellas; s++) {
                                estrellasHtml += `<svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`;
                            }
                            
                            html += `
                                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 shadow-sm hover:shadow-md transition-all text-left flex flex-col justify-between">
                                    <div>
                                        <div class="flex gap-1 mb-4 text-amber-400">
                                            ${estrellasHtml}
                                        </div>
                                        <p class="text-on-surface-variant font-body-md mb-6 italic leading-relaxed">${escapeHtml(test.texto)}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-on-surface mb-0">${escapeHtml(test.nombre)}</p>
                                        <p class="text-label-md text-outline mb-0">${escapeHtml(test.cargo)}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        container.innerHTML = html;
                        container.style.opacity = '1';
                    }, 500);
                }

                function reiniciarIntervalo() {
                    if (rotarInterval) clearInterval(rotarInterval);
                    if (testimoniosData && testimoniosData.length > 0) {
                        rotarInterval = setInterval(rotarTestimonios, 8000);
                    }
                }
                
                reiniciarIntervalo();

                // Manejo de Modal Dejar Comentario
                function openModal() {
                    commentModal.classList.remove('invisible', 'opacity-0');
                    commentModal.classList.add('visible', 'opacity-100');
                    commentModal.firstElementChild.classList.remove('scale-95');
                    commentModal.firstElementChild.classList.add('scale-100');
                    clearInterval(rotarInterval); // Pausar rotación mientras se escribe
                }

                function closeModal() {
                    commentModal.classList.remove('visible', 'opacity-100');
                    commentModal.classList.add('invisible', 'opacity-0');
                    commentModal.firstElementChild.classList.remove('scale-100');
                    commentModal.firstElementChild.classList.add('scale-95');
                    form.reset();
                    setStars(5);
                    reiniciarIntervalo(); // Reanudar rotación
                }

                if (openModalBtn) openModalBtn.addEventListener('click', openModal);
                if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
                if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

                // Manejo de Modal Ver Todos los Comentarios
                const allCommentsModal = document.getElementById('allCommentsModal');
                const openAllCommentsBtn = document.getElementById('openAllCommentsBtn');
                const closeAllCommentsModalBtn = document.getElementById('closeAllCommentsModalBtn');
                const closeAllCommentsModalFooterBtn = document.getElementById('closeAllCommentsModalFooterBtn');
                const searchAllCommentsInput = document.getElementById('searchAllCommentsInput');

                function openAllCommentsModal() {
                    if (!allCommentsModal) return;
                    allCommentsModal.classList.remove('invisible', 'opacity-0');
                    allCommentsModal.classList.add('visible', 'opacity-100');
                    allCommentsModal.firstElementChild.classList.remove('scale-95');
                    allCommentsModal.firstElementChild.classList.add('scale-100');
                    clearInterval(rotarInterval);
                }

                function closeAllCommentsModal() {
                    if (!allCommentsModal) return;
                    allCommentsModal.classList.remove('visible', 'opacity-100');
                    allCommentsModal.classList.add('invisible', 'opacity-0');
                    allCommentsModal.firstElementChild.classList.remove('scale-100');
                    allCommentsModal.firstElementChild.classList.add('scale-95');
                    if (searchAllCommentsInput) {
                        searchAllCommentsInput.value = '';
                        searchAllCommentsInput.dispatchEvent(new Event('input'));
                    }
                    reiniciarIntervalo();
                }

                if (openAllCommentsBtn) openAllCommentsBtn.addEventListener('click', openAllCommentsModal);
                if (closeAllCommentsModalBtn) closeAllCommentsModalBtn.addEventListener('click', closeAllCommentsModal);
                if (closeAllCommentsModalFooterBtn) closeAllCommentsModalFooterBtn.addEventListener('click', closeAllCommentsModal);

                // Auto-abrir modal de comentarios si viene en la URL
                if (window.location.hash === '#allCommentsModal' || new URLSearchParams(window.location.search).get('modal') === 'comentarios') {
                    setTimeout(openAllCommentsModal, 300);
                }

                // Filtrado en vivo de comentarios por búsqueda
                if (searchAllCommentsInput) {
                    searchAllCommentsInput.addEventListener('input', function(e) {
                        const term = e.target.value.toLowerCase().trim();
                        const items = document.querySelectorAll('#allCommentsList .comment-item');
                        items.forEach(item => {
                            const searchData = item.getAttribute('data-search') || '';
                            if (!term || searchData.includes(term)) {
                                item.style.display = 'flex';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                    });
                }

                // Selector de estrellas
                function setStars(rating) {
                    starsInput.value = rating;
                    const stars = starSelector.querySelectorAll('.select-star');
                    stars.forEach((star, index) => {
                        if (index < rating) {
                            star.classList.remove('text-outline-variant');
                            star.classList.add('text-amber-400');
                        } else {
                            star.classList.remove('text-amber-400');
                            star.classList.add('text-outline-variant');
                        }
                    });
                }

                if (starSelector) {
                    starSelector.addEventListener('click', function(e) {
                        const star = e.target.closest('.select-star');
                        if (star) {
                            const rating = parseInt(star.getAttribute('data-rating'));
                            setStars(rating);
                        }
                    });
                }

                // Mostrar Toast
                function showToast(msg) {
                    const toastMsg = document.getElementById('toastMessage');
                    if (toastMsg) toastMsg.innerText = msg;
                    toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
                    toast.classList.add('translate-y-0', 'opacity-100');
                    
                    setTimeout(() => {
                        toast.classList.remove('translate-y-0', 'opacity-100');
                        toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
                    }, 4000);
                }

                // Enviar formulario por AJAX
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);

                        fetch('index.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Añadir testimonio al pool local en primer lugar
                                testimoniosData.unshift(data.comment);
                                
                                // Cerrar modal de formulario y abrir asistente de Google
                                closeModal();
                                showToast('¡Gracias! Tu comentario ha sido guardado.');
                                setTimeout(() => {
                                    openGoogleSync(data.comment);
                                }, 300);
                                
                                // Mostrar inmediatamente el nuevo comentario cargado
                                container.style.opacity = '0';
                                setTimeout(() => {
                                    let estrellasHtml = '';
                                    for (let s = 0; s < data.comment.estrellas; s++) {
                                        estrellasHtml += `<svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`;
                                    }
                                    
                                    // Renderizar el nuevo comentario y los 2 siguientes de la tanda mezclada
                                    const otros = [...testimoniosData].slice(1, 3);
                                    let html = `
                                        <div class="bg-white border border-primary/20 rounded-xl p-8 shadow-md hover:shadow-lg transition-all text-left flex flex-col justify-between border-l-4 border-l-primary scale-100 ring-2 ring-primary/10">
                                            <div>
                                                <div class="flex gap-1 mb-4 text-amber-400">
                                                    ${estrellasHtml}
                                                </div>
                                                <p class="text-on-surface-variant font-body-md mb-6 italic leading-relaxed">${escapeHtml(data.comment.texto)}</p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-on-surface mb-0">${escapeHtml(data.comment.nombre)}</p>
                                                <p class="text-label-md text-outline mb-0">${escapeHtml(data.comment.cargo)}</p>
                                            </div>
                                        </div>
                                    `;
                                    
                                    otros.forEach(test => {
                                        let estrellasOtros = '';
                                        for (let s = 0; s < test.estrellas; s++) {
                                            estrellasOtros += `<svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`;
                                        }
                                        html += `
                                            <div class="bg-white border border-outline-variant/30 rounded-xl p-8 shadow-sm hover:shadow-md transition-all text-left flex flex-col justify-between">
                                                <div>
                                                    <div class="flex gap-1 mb-4 text-amber-400">
                                                        ${estrellasOtros}
                                                    </div>
                                                    <p class="text-on-surface-variant font-body-md mb-6 italic leading-relaxed">${escapeHtml(test.texto)}</p>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-on-surface mb-0">${escapeHtml(test.nombre)}</p>
                                                    <p class="text-label-md text-outline mb-0">${escapeHtml(test.cargo)}</p>
                                                </div>
                                            </div>
                                        `;
                                    });
                                    
                                    container.innerHTML = html;
                                    container.style.opacity = '1';
                                }, 500);
                            } else {
                                alert(data.message || 'Ocurrió un error al registrar el comentario.');
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            alert('No se pudo establecer conexión con el servidor.');
                        });
                    });
                }
            });
            </script>
        </div>
    </section>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="section-padding bg-surface-container-highest py-20">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop text-center">
            <div class="max-w-3xl mx-auto space-y-8">
                <h2 class="font-display text-display text-3xl md:text-5xl font-bold text-on-surface">¿Listo para fortalecer su infraestructura?</h2>
                <p class="font-body-lg text-body-lg text-on-surface-variant text-base md:text-lg">Únase a las empresas que confían su conectividad crítica a xcolnet. Soluciones robustas y soporte técnico de primer nivel.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 pt-4">
                    <a href="proyecto.php" class="w-full sm:w-auto px-7 py-3.5 bg-on-surface text-surface text-base md:text-lg rounded-lg active:scale-95 transition-all shadow-md no-underline font-semibold text-center">Hablar con un experto</a>
                    <a href="proyecto.php" class="font-label-md text-base md:text-lg text-primary flex items-center gap-2 group no-underline font-medium hover:underline">
                        Ver portafolio de proyectos
                        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>
                <div class="pt-8 flex flex-wrap justify-center items-center gap-8 text-outline">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M23 12l-2.44-2.79.34-3.69-3.61-.82-1.89-3.2L12 2.96 8.6 1.5 6.71 4.7 3.1 5.51l.34 3.69L1 12l2.44 2.79-.34 3.69 3.61.82 1.89 3.2 3.4-1.46 3.4 1.46 1.89-3.2 3.61-.82-.34-3.69L23 12zm-13 5l-4-4 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <span class="text-label-md font-medium">Instalaciones Certificadas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="text-label-md font-medium">Soporte Técnico 24/7</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('#heroSlideshow .slide-item');
    const dots = document.querySelectorAll('.slide-dot');
    if (slides.length > 0) {
        let currentIndex = 0;
        setInterval(function() {
            slides[currentIndex].classList.remove('opacity-100');
            slides[currentIndex].classList.add('opacity-0');
            if (dots[currentIndex]) {
                dots[currentIndex].classList.remove('opacity-100');
                dots[currentIndex].classList.add('opacity-40');
            }
            
            currentIndex = (currentIndex + 1) % slides.length;
            
            slides[currentIndex].classList.remove('opacity-0');
            slides[currentIndex].classList.add('opacity-100');
            if (dots[currentIndex]) {
                dots[currentIndex].classList.remove('opacity-40');
                dots[currentIndex].classList.add('opacity-100');
            }
        }, 5000);
    }
});
</script>

<?php include 'footer.php'; ?>

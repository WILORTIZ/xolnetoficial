<?php
$pageTitle = "Inicio";
include 'header.php';
require_once 'db.php';

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
                                                <h4 class="text-white font-bold text-base md:text-lg mb-0">Cableado Estructurado & Routing</h4>
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
                <!-- Pillar 1: Cableado Estructurado -->
                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 hover:border-primary/50 transition-all group shadow-sm hover:shadow-md">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v8M9 2h6M6 10h12v4a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3v-4zm6 7v5"/></svg>
                    </div>
                    <h3 class="font-headline-md text-xl font-bold mb-3 text-on-surface">Cableado Estructurado</h3>
                    <p class="text-on-surface-variant font-body-md mb-6 leading-relaxed">Certificación de redes Cat 6, 6A y 7. Organización profesional de racks y peinado de cables.</p>
                    <ul class="space-y-2.5 font-label-md text-on-surface/80 list-none p-0">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Certificación de Enlaces</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Gestión de Gabinetes</li>
                    </ul>
                </div>
                <!-- Pillar 2: Networking & Redes -->
                <div class="bg-white border border-outline-variant/30 rounded-xl p-8 hover:border-primary/50 transition-all group shadow-sm hover:shadow-md">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <svg class="w-7 h-7 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="14" width="20" height="6" rx="1" /><path d="M6 14v-4M18 14v-4M12 14v-8M10 2l2 2 2-2" /><circle cx="6" cy="17" r="0.5" fill="currentColor" /><circle cx="10" cy="17" r="0.5" fill="currentColor" /></svg>
                    </div>
                    <h3 class="font-headline-md text-xl font-bold mb-3 text-on-surface">Networking &amp; Redes</h3>
                    <p class="text-on-surface-variant font-body-md mb-6 leading-relaxed">Gestión inteligente de tráfico, switching y routing corporativo (Cisco, Aruba, MikroTik).</p>
                    <ul class="space-y-2.5 font-label-md text-on-surface/80 list-none p-0">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Alta Disponibilidad</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Wi-Fi Gestionado</li>
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

    <!-- Product Showcase (Physical Infrastructure Control) -->
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

    <!-- Testimonials Section (Visualización Aleatoria) -->
    <section class="section-padding bg-surface py-20" id="testimonios">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop">
            <div class="text-center mb-16">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-3 block font-semibold">CLIENTES SATISFECHOS</span>
                <h2 class="font-headline-lg text-headline-lg text-2xl md:text-4xl font-bold text-on-surface">Confianza respaldada por resultados reales</h2>
            </div>
            <?php
            $testimonios = [
                [
                    'texto' => '"La certificación de nuestro cableado estructurado fue impecable. El orden y la documentación técnica superaron nuestras expectativas."',
                    'nombre' => 'Carlos Mendoza',
                    'cargo' => 'Director de IT, TechCorp',
                    'estrellas' => 5
                ],
                [
                    'texto' => '"Gracias a xcolnet, nuestra red Wi-Fi corporativa ahora es estable y segura. El soporte técnico 24/7 es realmente proactivo."',
                    'nombre' => 'Elena Rodríguez',
                    'cargo' => 'Gerente de Operaciones, Global Net',
                    'estrellas' => 5
                ],
                [
                    'texto' => '"El sistema de control biométrico y CCTV integrado nos ha dado una tranquilidad total sobre la seguridad de nuestros nodos."',
                    'nombre' => 'Ricardo Silva',
                    'cargo' => 'Jefe de Infraestructura, Data Hub',
                    'estrellas' => 5
                ],
                [
                    'texto' => '"Excelente tiempo de respuesta y atención profesional. Lograron migrar nuestra infraestructura de servidores sin interrumpir la operación."',
                    'nombre' => 'Andrea Morales',
                    'cargo' => 'Líder de Sistemas, Systems S.A.',
                    'estrellas' => 5
                ],
                [
                    'texto' => '"Implementaron la infraestructura de red de nuestras nuevas oficinas con la mejor calidad y certificación Cat6A. 100% recomendados."',
                    'nombre' => 'Gabriel Torres',
                    'cargo' => 'Gerente General, InfraStruc',
                    'estrellas' => 5
                ],
                [
                    'texto' => '"El monitoreo de cámaras IP con analítica de IA revolucionó el control de acceso de nuestro centro de cómputo."',
                    'nombre' => 'Lucía Restrepo',
                    'cargo' => 'Directora de Seguridad, Global Data',
                    'estrellas' => 5
                ]
            ];

            // Ordenar los testimonios aleatoriamente en cada recarga
            shuffle($testimonios);
            ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach (array_slice($testimonios, 0, 3) as $test): ?>
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
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="section-padding bg-surface-container-highest py-20">
        <div class="max-w-container-max mx-auto px-4 md:px-margin-desktop text-center">
            <div class="max-w-3xl mx-auto space-y-8">
                <h2 class="font-display text-display text-3xl md:text-5xl font-bold text-on-surface">¿Listo para fortalecer su infraestructura?</h2>
                <p class="font-body-lg text-body-lg text-on-surface-variant text-base md:text-lg">Únase a las empresas que confían su conectividad crítica a xcolnet. Soluciones robustas y soporte técnico de primer nivel.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 pt-4">
                    <a href="proyecto.php" class="w-full sm:w-auto px-10 py-5 bg-on-surface text-surface font-headline-md text-headline-md rounded-lg active:scale-95 transition-all shadow-xl no-underline font-semibold">Hablar con un experto</a>
                    <a href="proyecto.php" class="font-label-md text-headline-md text-primary flex items-center gap-2 group no-underline font-medium hover:underline">
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

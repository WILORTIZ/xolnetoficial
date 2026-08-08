<?php
// Cabeceras HTTP de Seguridad (OWASP Mitigation)
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

if (session_status() == PHP_SESSION_NONE) {
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Generar Token Anti-CSRF si no existe en la sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Determinar el título de la página si no se define antes
if (!isset($pageTitle)) {
    $pageTitle = "Inicio";
}

$isAuthenticated = isset($_SESSION['user_id']);
$username = $isAuthenticated ? $_SESSION['username'] : '';
$userRole = $isAuthenticated ? $_SESSION['role'] : '';
$isAdmin = $isAuthenticated && (!empty($userRole) && (strtolower($userRole) === 'administrador' || strtolower($userRole) === 'admin' || strpos(strtolower($userRole), 'admin') !== false));
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle); ?> - XCOLNET | Soluciones Tecnológicas Integrales</title>
    
    <!-- Google SEO & Canonical Redirection Tags -->
    <meta name="description" content="XCOLNET - Soluciones tecnológicas integrales: Mesa de Ayuda para PYMEs, Diseño Web y Software a medida, Seguridad Electrónica (CCTV IP & Biometría), Soporte TI 24/7 y Migración de Correos Cloud." />
    <meta name="keywords" content="XCOLNET, soluciones tecnologicas, mesa de ayuda, diseño web, software a medida, soporte ti, cctv ip, seguridad electronica, migracion correos cloud" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.xcolnet.com<?php echo htmlspecialchars(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>" />

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?> - XCOLNET | Soluciones Tecnológicas Integrales" />
    <meta property="og:description" content="Mesa de Ayuda para PYMEs, Desarrollo Web, Seguridad Electrónica, Soporte TI y Servicios Cloud en Colombia." />
    <meta property="og:url" content="https://www.xcolnet.com<?php echo htmlspecialchars(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>" />
    <meta property="og:image" content="https://www.xcolnet.com/images/favicon.png" />

    <!-- Structured Data JSON-LD for Google Search Console -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "XCOLNET",
      "url": "https://www.xcolnet.com",
      "logo": "https://www.xcolnet.com/images/favicon.png",
      "description": "Soluciones tecnológicas integrales para empresas y hogares en Colombia.",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+57 317 087 7414",
        "contactType": "customer service",
        "areaServed": "CO"
      }
    }
    </script>

    <!-- Favicon / Icono de la Pestaña -->
    <link rel="icon" type="image/png" href="favicon.png?v=2" />
    <link rel="shortcut icon" href="favicon.png?v=2" type="image/png" />
    <link rel="apple-touch-icon" href="favicon.png?v=2" />
    
    <!-- Google Fonts: Geist for headlines/mono, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Geist:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL,wght@0..1,100..700&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS Engine -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
              "secondary-container": "#d9dff5",
              "secondary-fixed-dim": "#c0c6db",
              "surface-container-lowest": "#ffffff",
              "inverse-surface": "#2e303a",
              "surface-container-low": "#f2f3ff",
              "surface-container-high": "#e6e7f4",
              "on-error": "#ffffff",
              "tertiary": "#a33200",
              "on-tertiary-container": "#fff6f4",
              "background": "#faf8ff",
              "primary-container": "#0066ff",
              "on-tertiary-fixed-variant": "#832600",
              "outline": "#727687",
              "on-surface": "#191b24",
              "inverse-primary": "#b3c5ff",
              "tertiary-fixed": "#ffdbd0",
              "on-secondary-container": "#5c6274",
              "surface-bright": "#faf8ff",
              "surface-container": "#ecedfa",
              "secondary-fixed": "#dce2f7",
              "inverse-on-surface": "#eff0fd",
              "primary-fixed": "#dae1ff",
              "error": "#ba1a1a",
              "on-primary-fixed": "#001849",
              "surface-container-highest": "#e1e2ee",
              "on-secondary-fixed": "#141b2b",
              "on-secondary": "#ffffff",
              "tertiary-fixed-dim": "#ffb59d",
              "outline-variant": "#c2c6d8",
              "on-primary-fixed-variant": "#003fa4",
              "on-background": "#191b24",
              "on-tertiary": "#ffffff",
              "on-secondary-fixed-variant": "#404758",
              "on-primary": "#ffffff",
              "surface": "#faf8ff",
              "on-error-container": "#93000a",
              "on-primary-container": "#f8f7ff",
              "secondary": "#575e70",
              "surface-variant": "#e1e2ee",
              "primary": "#0050cb",
              "tertiary-container": "#cc4204",
              "on-tertiary-fixed": "#390c00",
              "surface-tint": "#0054d6",
              "on-surface-variant": "#424656",
              "surface-dim": "#d8d9e6",
              "primary-fixed-dim": "#b3c5ff",
              "error-container": "#ffdad6"
            },
            "borderRadius": {
              "DEFAULT": "0.125rem",
              "lg": "0.25rem",
              "xl": "0.5rem",
              "full": "0.75rem"
            },
            "spacing": {
              "rail-wide": "auto",
              "margin-desktop": "64px",
              "margin-mobile": "20px",
              "container-max": "1280px",
              "gutter": "24px",
              "rail-narrow": "280px"
            },
            "fontFamily": {
              "headline-md": ["Geist", "sans-serif"],
              "headline-lg": ["Geist", "sans-serif"],
              "mono": ["Geist", "monospace"],
              "headline-lg-mobile": ["Geist", "sans-serif"],
              "display": ["Geist", "sans-serif"],
              "body-lg": ["Inter", "sans-serif"],
              "body-md": ["Inter", "sans-serif"],
              "label-md": ["Geist", "sans-serif"]
            },
            "fontSize": {
              "headline-md": ["24px", {"lineHeight": "1.3", "letterSpacing": "-0.02em", "fontWeight": "500"}],
              "headline-lg": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.03em", "fontWeight": "600"}],
              "mono": ["13px", {"lineHeight": "1.6", "letterSpacing": "0em", "fontWeight": "400"}],
              "headline-lg-mobile": ["24px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "600"}],
              "display": ["48px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "600"}],
              "body-lg": ["16px", {"lineHeight": "1.6", "letterSpacing": "-0.01em", "fontWeight": "400"}],
              "body-md": ["14px", {"lineHeight": "1.5", "letterSpacing": "0em", "fontWeight": "400"}],
              "label-md": ["12px", {"lineHeight": "1", "letterSpacing": "0.02em", "fontWeight": "500"}]
            }
          },
        },
      }
    </script>
    
    <!-- CSS Resources -->
    <link rel="stylesheet" href="lib/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/site.css?v=<?php echo filemtime('css/site.css'); ?>" />
    
    <!-- Font Load Detector to prevent FOUT (unstyled text like 'v', 'e', 's' showing) -->
    <script>
        if ('fonts' in document) {
            document.fonts.load('24px "Material Symbols Outlined"').then(function() {
                if (document.fonts.check('24px "Material Symbols Outlined"')) {
                    document.documentElement.classList.add('material-symbols-loaded');
                }
            });
        }
    </script>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased overflow-x-hidden">
    <!-- TopNavBar -->
    <header class="fixed top-0 w-full z-50 bg-surface/70 backdrop-blur-xl border-b border-outline-variant/30 transition-all duration-300">
        <nav class="flex justify-between items-center max-w-container-max mx-auto px-4 md:px-margin-desktop h-16">
            <div class="flex items-center gap-8 md:gap-12">
                <a class="font-headline-md text-headline-md font-bold tracking-tight text-primary flex items-center gap-2.5 no-underline group" href="index.php">
                    <span class="brand-icon-wrapper text-primary transition-transform duration-500 ease-in-out group-hover:rotate-[180deg] group-hover:scale-110 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <!-- Central Node -->
                            <circle cx="12" cy="12" r="2.5" fill="currentColor" />
                            <!-- Top Node -->
                            <line x1="12" y1="9.5" x2="12" y2="5" />
                            <circle cx="12" cy="3.5" r="1.8" fill="currentColor" />
                            <!-- Top Right Node -->
                            <line x1="14.1" y1="10.5" x2="18.3" y2="7.5" />
                            <circle cx="19.8" cy="6.4" r="1.8" fill="currentColor" />
                            <!-- Bottom Right Node -->
                            <line x1="14.1" y1="13.5" x2="18.3" y2="16.5" />
                            <circle cx="19.8" cy="17.6" r="1.8" fill="currentColor" />
                            <!-- Bottom Left Node -->
                            <line x1="9.9" y1="13.5" x2="5.7" y2="16.5" />
                            <circle cx="4.2" cy="17.6" r="1.8" fill="currentColor" />
                            <!-- Top Left Node -->
                            <line x1="9.9" y1="10.5" x2="5.7" y2="7.5" />
                            <circle cx="4.2" cy="6.4" r="1.8" fill="currentColor" />
                        </svg>
                    </span>
                    <span class="text-primary font-bold text-2xl tracking-tight">xcolnet</span>
                </a>
                <div class="hidden md:flex items-center gap-8">
                    <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors duration-200 no-underline" href="index.php#hero">Inicio</a>
                    <div class="relative dropdown-container">
                        <button id="servicesDropdownBtn" type="button" class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors duration-200 flex items-center gap-1 bg-transparent border-0 p-0 cursor-pointer" aria-expanded="false">
                            Servicios
                            <span class="material-symbols-outlined text-[16px] transition-transform duration-200" id="servicesDropdownIcon">expand_more</span>
                        </button>
                        <div id="servicesDropdownMenu" class="absolute top-full left-0 mt-2 w-64 glass rounded-xl shadow-2xl opacity-0 invisible transition-all duration-200 p-2 border border-outline-variant/40 z-50">
                            <a class="flex items-center gap-2.5 p-2.5 hover:bg-primary/10 rounded-lg text-body-md text-on-surface hover:text-primary transition-colors no-underline font-medium" href="index.php#capacidades">
                                <span class="material-symbols-outlined text-[20px] text-primary">headset_mic</span>
                                <span>Mesa de Ayuda para PYMEs</span>
                            </a>
                            <a class="flex items-center gap-2.5 p-2.5 hover:bg-primary/10 rounded-lg text-body-md text-on-surface hover:text-primary transition-colors no-underline font-medium" href="index.php#capacidades">
                                <span class="material-symbols-outlined text-[20px] text-primary">code</span>
                                <span>Diseño Web &amp; Software a Medida</span>
                            </a>
                            <a class="flex items-center gap-2.5 p-2.5 hover:bg-primary/10 rounded-lg text-body-md text-on-surface hover:text-primary transition-colors no-underline font-medium" href="index.php#capacidades">
                                <span class="material-symbols-outlined text-[20px] text-primary">videocam</span>
                                <span>Seguridad Electrónica &amp; CCTV</span>
                            </a>
                            <a class="flex items-center gap-2.5 p-2.5 hover:bg-primary/10 rounded-lg text-body-md text-on-surface hover:text-primary transition-colors no-underline font-medium" href="index.php#capacidades">
                                <span class="material-symbols-outlined text-[20px] text-primary">build</span>
                                <span>Soporte TI &amp; Mantenimiento</span>
                            </a>
                            <a class="flex items-center gap-2.5 p-2.5 hover:bg-primary/10 rounded-lg text-body-md text-on-surface hover:text-primary transition-colors no-underline font-medium" href="proyecto.php">
                                <span class="material-symbols-outlined text-[20px] text-primary">smart_toy</span>
                                <span>Integración de Software &amp; IA</span>
                            </a>
                            <a class="flex items-center gap-2.5 p-2.5 hover:bg-primary/10 rounded-lg text-body-md text-on-surface hover:text-primary transition-colors no-underline font-medium" href="proyecto.php">
                                <span class="material-symbols-outlined text-[20px] text-primary">mark_email_unread</span>
                                <span>Migración de Correos &amp; Cloud</span>
                            </a>
                        </div>
                    </div>
                    <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors duration-200 no-underline" href="pqrs.php">PQRS</a>
                    <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary transition-colors duration-200 no-underline" href="proyecto.php">Solicitar Proyecto</a>
                    <?php if ($isAdmin): ?>
                        <a class="font-body-md text-body-md text-primary font-semibold hover:underline no-underline flex items-center gap-1.5 bg-primary/10 px-3.5 py-1.5 rounded-lg border border-primary/20 shadow-sm" href="admin_dashboard.php">
                            <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            Panel Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <?php if ($isAuthenticated): ?>
                    <span class="font-body-md text-on-surface-variant hidden sm:inline-block">Hola, <strong class="text-on-surface"><?php echo htmlspecialchars($username); ?></strong></span>
                    <a href="logout.php" class="px-5 py-2 bg-error/10 text-error font-label-md text-label-md rounded-lg hover:bg-error hover:text-white transition-all no-underline text-sm font-medium">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="px-6 py-2 bg-on-surface text-surface font-label-md text-label-md rounded-lg hover:bg-primary transition-colors no-underline text-sm font-medium">Acceso</a>
                <?php endif; ?>

                <!-- Botón Menú Móvil Hamburguesa -->
                <button id="mobileMenuBtn" type="button" class="md:hidden text-on-surface p-2 rounded-lg hover:bg-surface-container-high border border-outline-variant/30 flex items-center justify-center bg-surface-container-low" aria-label="Abrir menú">
                    <span class="material-symbols-outlined text-[24px]" id="mobileMenuIcon">menu</span>
                </button>
            </div>
        </nav>

        <!-- Drawer / Menú Móvil Desplegable -->
        <div id="mobileMenuDrawer" class="md:hidden fixed inset-x-0 top-[72px] bg-surface-container-lowest/95 backdrop-blur-md border-b border-outline-variant/30 shadow-2xl opacity-0 invisible -translate-y-4 transition-all duration-300 z-40 px-6 py-6 space-y-3">
            <a class="block font-body-md text-base text-on-surface hover:text-primary font-medium py-2 no-underline border-b border-outline-variant/20" href="index.php#hero">Inicio</a>
            
            <!-- Servicios colapsables en móvil -->
            <div class="border-b border-outline-variant/20 pb-2">
                <div class="font-body-md text-base text-on-surface font-semibold py-2 flex items-center justify-between cursor-pointer select-none" id="mobileServicesToggle">
                    <span>Servicios</span>
                    <span class="material-symbols-outlined text-[20px] transition-transform duration-200" id="mobileServicesIcon">expand_more</span>
                </div>
                <div id="mobileServicesList" class="hidden pl-4 py-2 space-y-2 border-l-2 border-primary/30 my-1">
                    <a class="block text-sm text-on-surface-variant hover:text-primary no-underline font-medium" href="index.php#capacidades">Mesa de Ayuda para PYMEs</a>
                    <a class="block text-sm text-on-surface-variant hover:text-primary no-underline font-medium" href="index.php#capacidades">Diseño Web &amp; Software a Medida</a>
                    <a class="block text-sm text-on-surface-variant hover:text-primary no-underline font-medium" href="index.php#capacidades">Seguridad Electrónica &amp; CCTV</a>
                    <a class="block text-sm text-on-surface-variant hover:text-primary no-underline font-medium" href="index.php#capacidades">Soporte TI &amp; Mantenimiento</a>
                    <a class="block text-sm text-on-surface-variant hover:text-primary no-underline font-medium" href="proyecto.php">Integración de Software &amp; IA</a>
                    <a class="block text-sm text-on-surface-variant hover:text-primary no-underline font-medium" href="proyecto.php">Migración de Correos &amp; Cloud</a>
                </div>
            </div>
            
            <a class="block font-body-md text-base text-on-surface hover:text-primary font-medium py-2 no-underline border-b border-outline-variant/20" href="pqrs.php">PQRS</a>
            <a class="block font-body-md text-base text-on-surface hover:text-primary font-medium py-2 no-underline border-b border-outline-variant/20" href="proyecto.php">Solicitar Proyecto</a>
            
            <?php if ($isAdmin): ?>
                <a class="flex items-center gap-2 font-body-md text-sm text-primary font-bold py-2.5 px-3 bg-primary/10 rounded-lg no-underline mt-3" href="admin_dashboard.php">
                    <svg class="w-5 h-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Panel de Administración
                </a>
            <?php endif; ?>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('servicesDropdownBtn');
        const menu = document.getElementById('servicesDropdownMenu');
        const icon = document.getElementById('servicesDropdownIcon');

        if (btn && menu) {
            function toggleDropdown(show) {
                const shouldShow = show !== undefined ? show : menu.classList.contains('invisible');
                if (shouldShow) {
                    menu.classList.remove('opacity-0', 'invisible');
                    menu.classList.add('opacity-100', 'visible');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                    btn.setAttribute('aria-expanded', 'true');
                } else {
                    menu.classList.remove('opacity-100', 'visible');
                    menu.classList.add('opacity-0', 'invisible');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                    btn.setAttribute('aria-expanded', 'false');
                }
            }

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown();
            });

            btn.parentElement.addEventListener('mouseenter', function() {
                toggleDropdown(true);
            });

            btn.parentElement.addEventListener('mouseleave', function() {
                toggleDropdown(false);
            });

            document.addEventListener('click', function(e) {
                if (!btn.parentElement.contains(e.target)) {
                    toggleDropdown(false);
                }
            });
        }

        // Lógica Menú Móvil
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileDrawer = document.getElementById('mobileMenuDrawer');
        const mobileIcon = document.getElementById('mobileMenuIcon');
        const mobileServicesToggle = document.getElementById('mobileServicesToggle');
        const mobileServicesList = document.getElementById('mobileServicesList');
        const mobileServicesIcon = document.getElementById('mobileServicesIcon');

        if (mobileBtn && mobileDrawer) {
            mobileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = mobileDrawer.classList.contains('invisible');
                if (isHidden) {
                    mobileDrawer.classList.remove('invisible', 'opacity-0', '-translate-y-4');
                    mobileDrawer.classList.add('visible', 'opacity-100', 'translate-y-0');
                    if (mobileIcon) mobileIcon.textContent = 'close';
                } else {
                    mobileDrawer.classList.remove('visible', 'opacity-100', 'translate-y-0');
                    mobileDrawer.classList.add('invisible', 'opacity-0', '-translate-y-4');
                    if (mobileIcon) mobileIcon.textContent = 'menu';
                }
            });

            document.addEventListener('click', function(e) {
                if (!mobileDrawer.contains(e.target) && !mobileBtn.contains(e.target)) {
                    mobileDrawer.classList.remove('visible', 'opacity-100', 'translate-y-0');
                    mobileDrawer.classList.add('invisible', 'opacity-0', '-translate-y-4');
                    if (mobileIcon) mobileIcon.textContent = 'menu';
                }
            });
        }

        if (mobileServicesToggle && mobileServicesList) {
            mobileServicesToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isClosed = mobileServicesList.classList.contains('hidden');
                if (isClosed) {
                    mobileServicesList.classList.remove('hidden');
                    if (mobileServicesIcon) mobileServicesIcon.style.transform = 'rotate(180deg)';
                } else {
                    mobileServicesList.classList.add('hidden');
                    if (mobileServicesIcon) mobileServicesIcon.style.transform = 'rotate(0deg)';
                }
            });
        }
    });
    </script>
    <div class="content-wrapper">
        <main role="main">

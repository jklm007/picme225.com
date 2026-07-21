<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo e(Setting::get('site_title','Tranxit')); ?></title>

    <meta name="description" content="PicMe225 — Votre transfert aéroport de luxe et marketplace à Abidjan">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('favicon.png')); ?>"/>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#C9A84C">
    <link rel="apple-touch-icon" href="<?php echo e(asset('logo.png')); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">

    <link href="<?php echo e(asset('asset/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('asset/font-awesome/css/font-awesome.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('asset/css/style.css')); ?>" rel="stylesheet">

    <style>
    /* ═══ PicMe225 Design System CSS Variables ═══ */
    :root {
        --color-brand-primary: #0A1628;
        --color-brand-accent: #C9A84C;
        --color-brand-success: #2E7D32;
        --color-bg-light: #F8FAFC;
        --color-text-dark: #1A202C;
    }

    /* ─── Global Font & Theme Overrides ─── */
    *, body, h1, h2, h3, h4, h5, h6, p, span, a, li, ul {
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    }
    .fa, .fas, .far, .fab {
        font-family: 'FontAwesome' !important;
    }

    /* Override any legacy green elements from style.css */
    .nav>li>a:focus, .nav>li>a:hover, .nav>li.active>a, .nav>li.active>a:focus, .nav>li.active>a:hover {
        background-color: transparent !important;
        color: #C9A84C !important;
    }
    .menu-btn, .content-more-btn {
        background-color: #C9A84C !important;
        color: #fff !important;
    }
    .content-more-btn:hover {
        background-color: #B89535 !important;
    }
    .content-more, .content-more i, .banner-form h5, .banner-form h5 i, .note-or a {
        color: #C9A84C !important;
    }

    /* ─── Top Navbar (Flexbox Layout to eliminate overlap) ─── */
    nav.navbar.navbar-fixed-top {
        background: linear-gradient(90deg, #0a1628 0%, #0f2040 100%) !important;
        border-bottom: 2px solid #C9A84C !important;
        box-shadow: 0 2px 16px rgba(0,0,0,0.4) !important;
        min-height: 64px !important;
        padding: 0 !important;
    }

    nav.navbar-fixed-top .container-fluid {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        height: 64px !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }

    .navbar-header {
        display: flex !important;
        align-items: center !important;
        height: 64px !important;
        float: none !important;
        margin: 0 !important;
    }

    .navbar-brand.pm-logo-btn {
        display: flex !important;
        align-items: center !important;
        padding: 0 20px 0 0 !important;
        height: 64px !important;
        cursor: pointer !important;
        background: transparent !important;
        border-right: 1px solid rgba(201,168,76,0.3) !important;
        margin-right: 20px !important;
        float: none !important;
        flex-shrink: 0 !important;
        max-width: 140px !important;
        overflow: hidden !important;
    }
    .navbar-brand.pm-logo-btn img {
        max-height: 38px !important;
        width: auto !important;
        display: block !important;
        transition: opacity .2s !important;
    }
    .navbar-brand.pm-logo-btn:hover img { opacity: 0.85 !important; }

    .navbar-toggle {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        border-color: rgba(201,168,76,0.4) !important;
    }
    .navbar-toggle .icon-bar { background: #C9A84C !important; }

    @media (min-width: 768px) {
        .navbar-collapse.collapse {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex: 1 !important;
            height: 64px !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .navbar-nav {
            display: flex !important;
            align-items: center !important;
            float: none !important;
            margin: 0 !important;
        }
        .navbar-nav > li {
            float: none !important;
        }
        .navbar-nav.navbar-right {
            margin-left: auto !important;
        }
    }

    /* ─── Liens de navigation ─── */
    nav.navbar-fixed-top .navbar-nav > li > a {
        font-size: 14px !important;
        font-weight: 500 !important;
        color: rgba(255,255,255,0.85) !important;
        padding: 22px 16px !important;
        white-space: nowrap !important;
        transition: color .2s, border-color .2s !important;
        border-bottom: 2px solid transparent !important;
        line-height: 20px !important;
    }
    nav.navbar-fixed-top .navbar-nav > li > a:hover,
    nav.navbar-fixed-top .navbar-nav > li.active > a {
        color: #C9A84C !important;
        background: transparent !important;
        border-bottom-color: #C9A84C !important;
    }

    /* ─── Liens droite ─── */
    nav.navbar-fixed-top .navbar-right > li > a {
        font-size: 13px !important;
        padding: 22px 10px !important;
        color: rgba(255,255,255,0.6) !important;
        white-space: nowrap !important;
        border-bottom: none !important;
    }
    nav.navbar-fixed-top .navbar-right > li > a:hover {
        color: #fff !important;
        background: transparent !important;
    }

    /* ─── Sélecteur de langue ─── */
    nav.navbar-fixed-top select.form-control {
        max-width: 70px !important;
        font-size: 13px !important;
        height: 30px !important;
        padding: 2px 4px !important;
        margin-top: 0 !important;
        background: rgba(255,255,255,0.07) !important;
        border: 1px solid rgba(201,168,76,0.3) !important;
        color: #fff !important;
        border-radius: 6px !important;
    }

    /* ─── Bouton Télécharger ─── */
    .pm-dl-app-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        background: linear-gradient(135deg, #2E7D32, #1B5E20) !important;
        color: #fff !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        padding: 7px 13px !important;
        border-radius: 8px !important;
        margin: 0 0 0 10px !important;
        border: none !important;
        text-decoration: none !important;
        transition: all .2s !important;
        white-space: nowrap !important;
    }
    .pm-dl-app-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(46,125,50,0.4) !important;
        color: #fff !important;
        text-decoration: none !important;
    }

    /* ─── Drawer (sidebar) ─── */
    #sidebar-wrapper {
        background: linear-gradient(180deg, #0a1628 0%, #0d1f3c 100%) !important;
        border-right: 1px solid rgba(201,168,76,0.12) !important;
    }
    #sidebar-wrapper .sidebar-nav li a {
        color: rgba(255,255,255,0.72) !important;
        font-size: 14px !important;
        padding: 13px 24px !important;
        border-left: 3px solid transparent !important;
        transition: all .2s !important;
        display: block;
    }
    #sidebar-wrapper .sidebar-nav li a:hover {
        color: #C9A84C !important;
        border-left-color: #C9A84C !important;
        background: rgba(201,168,76,0.05) !important;
    }
    #sidebar-wrapper .sidebar-nav li.full-white a {
        background: rgba(46,125,50,0.12) !important;
        color: #81C784 !important;
    }
    #sidebar-wrapper .sidebar-nav li.white-border a {
        border-top: 1px solid rgba(201,168,76,0.1) !important;
        border-bottom: 1px solid rgba(201,168,76,0.1) !important;
        color: #C9A84C !important;
    }

    /* ─── Footer Overrides ─── */
    .footer {
        background: linear-gradient(180deg, #070e1a 0%, #0a1628 100%) !important;
        border-top: 3px solid #C9A84C !important;
        padding: 60px 0 30px !important;
    }
    .footer h5, .footer p {
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 600 !important;
        letter-spacing: 0.5px !important;
    }
    .footer ul li a {
        color: rgba(255, 255, 255, 0.6) !important;
        transition: color 0.2s !important;
    }
    .footer ul li a:hover {
        color: #C9A84C !important;
        text-decoration: none !important;
    }
    .footer .copy {
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding-top: 20px !important;
        margin-top: 20px !important;
        color: rgba(255, 255, 255, 0.4) !important;
    }

    /* ─── Social Icons Premium ─── */
    .pm-social-links {
        display: flex !important;
        gap: 12px !important;
        padding: 0 !important;
        list-style: none !important;
        flex-wrap: wrap !important;
        margin-top: 12px !important;
    }
    .pm-social-links li { list-style: none !important; }
    .pm-social-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 42px !important;
        height: 42px !important;
        border-radius: 50% !important;
        font-size: 17px !important;
        text-decoration: none !important;
        transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s !important;
        border: 2px solid rgba(255,255,255,0.08) !important;
    }
    .pm-social-btn:hover {
        transform: translateY(-4px) scale(1.1) !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.4) !important;
        opacity: 0.92 !important;
        text-decoration: none !important;
    }
    .pm-social-btn.email    { background: #2D3748 !important; color: #fff !important; }
    .pm-social-btn.whatsapp { background: #25D366 !important; color: #fff !important; }
    .pm-social-btn.tiktok   { background: #000 !important;    color: #fff !important; }
    .pm-social-btn.facebook { background: #1877F2 !important; color: #fff !important; }
    .pm-social-btn svg { width: 18px; height: 18px; fill: #fff; display: block; }
    /* Force icon color inside social buttons — overrides .footer ul li a */
    .footer ul.pm-social-links li a.pm-social-btn,
    .footer ul.pm-social-links li a.pm-social-btn i,
    .footer ul.pm-social-links li a.pm-social-btn:hover,
    .footer ul.pm-social-links li a.pm-social-btn:hover i {
        color: #fff !important;
        font-size: 18px !important;
    }

    /* ─── Utility classes ─── */
    .pm-btn-primary {
        background: linear-gradient(135deg, #C9A84C 0%, #B89535 100%) !important;
        color: #fff !important;
        border: none !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        transition: transform 0.2s, box-shadow 0.2s !important;
    }
    .pm-btn-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 16px rgba(201,168,76,0.3) !important;
    }

    /* ─── Mobile Bottom Navigation 2026 ─── */
    .pm-bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(201, 168, 76, 0.92);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid rgba(255,255,255,0.3);
        z-index: 9999;
        padding-bottom: env(safe-area-inset-bottom);
        box-shadow: 0 -4px 24px rgba(201, 168, 76, 0.3);
    }
    .pm-bottom-nav-inner {
        display: flex;
        justify-content: space-around;
        align-items: center;
        height: 64px;
        padding: 0 10px;
    }
    .pm-bottom-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: rgba(10, 22, 40, 0.55);
        text-decoration: none !important;
        font-size: 10px;
        font-weight: 700;
        width: 25%;
        transition: all 0.2s ease;
    }
    .pm-bottom-nav-item svg {
        width: 24px;
        height: 24px;
        margin-bottom: 4px;
        fill: currentColor;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .pm-bottom-nav-item:hover, .pm-bottom-nav-item.active {
        color: #0A1628;
    }
    .pm-bottom-nav-item:hover svg, .pm-bottom-nav-item.active svg {
        transform: translateY(-2px) scale(1.1);
    }

    /* ─── Mobile Header ─── */
    @media (max-width: 767px) {
        /* Bottom nav */
        .pm-bottom-nav { display: block; }
        body { padding-bottom: 80px !important; }
        #pm-nav-collapse { display: none !important; }

        /* Navbar container : hauteur très compacte */
        header > nav.navbar.navbar-fixed-top {
            min-height: 40px !important;
            height: 40px !important;
            padding: 0 !important;
        }
        header > nav.navbar.navbar-fixed-top .container-fluid {
            height: 40px !important;
            min-height: 40px !important;
            padding: 0 4px !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Header row : logo | pub | dl droite */
        .mobile-navbar-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
            height: 40px !important;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
        }

        /* Unified Logo for Mobile (Acts as Drawer trigger) */
        .navbar-brand.pm-logo-btn.hamburger {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            margin: 0 4px 0 0 !important;
            padding: 0 4px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            border-right: none !important;
            flex-shrink: 0 !important;
            z-index: 1050 !important;
            background: transparent !important;
            width: auto !important;
            height: 40px !important;
        }
        
        .navbar-brand.pm-logo-btn.hamburger img {
            max-height: 36px !important;
            height: 36px !important;
            width: auto !important;
            display: block !important;
        }

        /* Disable CSS lines from style.css hamburger */
        .navbar-brand.pm-logo-btn.hamburger .hamb-top,
        .navbar-brand.pm-logo-btn.hamburger .hamb-middle,
        .navbar-brand.pm-logo-btn.hamburger .hamb-bottom,
        .navbar-brand.pm-logo-btn.hamburger::before,
        .navbar-brand.pm-logo-btn.hamburger::after {
            display: none !important;
            content: none !important;
        }

        .navbar-toggle { display: none !important; }

        /* Bouton Télécharger à l'extrême droite */
        #pm-dl-mobile-btn {
            flex-shrink: 0 !important;
            margin-right: 12px !important; /* Pushed away from edge to prevent cutoff */
            padding-right: 2px !important;
        }
        #pm-dl-mobile-btn .pm-dl-app-btn {
            margin: 0 !important;
            padding: 4px 8px !important;
            font-size: 11px !important;
            background: linear-gradient(135deg, #1a8a4a, #27ae60) !important;
            color: #fff !important;
            border-radius: 4px !important;
            border: none !important;
            white-space: nowrap !important;
            font-weight: 600 !important;
        }

        /* Zone pub miniature au centre */
        #header-ad-space {
            flex: 1 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 4px !important;
            height: 34px !important;
            overflow: hidden !important;
        }

        /* Hide Desktop Logo on Mobile */
        #desktop-logo {
            display: none !important;
        }

        /* Show Mobile Logo */
        #mobile-logo {
            display: flex !important;
        }

        /* Reduce padding of main banner on mobile to save space */
        .banner {
            padding-top: 50px !important;
        }

        /* Collapse menu desktop */
        .navbar-collapse.collapse.in {
            display: block !important;
            width: 100% !important;
            background: #0d1f3c !important;
            padding: 10px 0 !important;
        }
        header > nav.navbar-fixed-top .navbar-nav > li > a { padding: 12px 16px !important; }
    }

    /* Desktop reset for .navbar-brand */
    @media (min-width: 768px) {
        #mobile-logo {
            display: none !important;
        }
        #desktop-logo {
            display: flex !important;
            position: static !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 20px 0 0 !important;
            z-index: auto !important;
            align-items: center !important;
            border-right: 1px solid rgba(201,168,76,0.3) !important;
        }
        #desktop-logo img {
            max-height: 38px !important;
        }
    }

    </style>
</head>
<body>
    <div id="wrapper">
        <div class="overlay" id="overlayer" data-toggle="offcanvas"></div>

        <nav class="navbar navbar-inverse navbar-fixed-top" id="sidebar-wrapper" role="navigation">
            <ul class="nav sidebar-nav">
                <li class="full-white dropdown" style="margin-top: 15px;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="display: block;">📱 Télécharger l'App <span class="caret"></span></a>
                    <ul class="dropdown-menu" style="position: relative; width: 100%; float: none; background: #070e1a; border: none; box-shadow: none; padding: 0; margin: 0;">
                        <li><a href="<?php echo e(Setting::get('store_link_android','https://play.google.com/store/apps')); ?>" target="_blank" style="padding: 12px 32px;"><i class="fa fa-android"></i> Google Play Store</a></li>
                        <li><a href="javascript:void(0)" onclick="installPWA('<?php echo e(Setting::get('store_link_android','#')); ?>')" style="padding: 12px 32px;"><i class="fa fa-download"></i> Installer l'Application</a></li>
                    </ul>
                </li>
                <li>
                    <a href="<?php echo e(url('/marketplace')); ?>">🛒 Marketplace</a>
                </li>
                <li>
                    <a href="<?php echo e(url('/airport')); ?>">✈️ Navette Aéroport</a>
                </li>
                <li>
                    <a href="<?php echo e(url('/location')); ?>">📍 <?php echo app('translator')->get('home.location'); ?></a>
                </li>
                <li>
                    <a href="<?php echo e(url('/help')); ?>">💬 Aide & Contact</a>
                </li>
                <li>
                    <a href="<?php echo e(url('/privacy')); ?>">🛡️ Politique de confidentialité</a>
                </li>
                <li>
                    <a href="#">⚖️ Conditions générales</a>
                </li>
            </ul>
        </nav>

        <div id="page-content-wrapper">
            <header>
                <nav class="navbar navbar-fixed-top">
                    <div class="container-fluid">
                        <div class="navbar-header mobile-navbar-header">

                            
                            <a id="desktop-logo" class="navbar-brand pm-logo-btn" href="<?php echo e(url('/')); ?>">
                                <img src="<?php echo e(asset('logo.png')); ?>" alt="PicMe225">
                            </a>

                            
                            <a id="mobile-logo" class="navbar-brand pm-logo-btn hamburger is-closed" data-toggle="offcanvas" href="#" onclick="return false;" aria-label="Menu">
                                <img src="<?php echo e(asset('logo.png')); ?>" alt="PicMe225">
                            </a>

                            
                            <div id="header-ad-space" class="visible-xs">
                                <!-- Zone de pub miniature (à remplir) -->
                            </div>

                            
                            <div id="pm-dl-mobile-btn" class="visible-xs">
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle pm-dl-app-btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        📱 Télécharger <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right" style="background-color: #0d1226; border: 1px solid #C9A84C;">
                                        <li><a href="<?php echo e(Setting::get('store_link_android','https://play.google.com/store/apps')); ?>" target="_blank" style="color: #fff; padding: 10px 20px;"><i class="fa fa-android"></i> Play Store</a></li>
                                        <li><a href="javascript:void(0)" onclick="installPWA('<?php echo e(Setting::get('store_link_android','#')); ?>')" style="color: #fff; padding: 10px 20px;"><i class="fa fa-download"></i> Installer l'Application</a></li>
                                    </ul>
                                </div>
                            </div>

                            
                            <button type="button" class="navbar-toggle collapsed hidden-xs" data-toggle="collapse" data-target="#pm-nav-collapse" aria-expanded="false" style="margin-top:12px;">
                                <span class="sr-only">Menu</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>

                        </div>

                        <div class="collapse navbar-collapse" id="pm-nav-collapse">
                            <ul class="nav navbar-nav">
                                <li <?php if(Request::url() == url('/')): ?> class="active" <?php endif; ?>>
                                    <a href="<?php echo e(url('/')); ?>">🏠 Accueil</a>
                                </li>
                                <li <?php if(Request::url() == url('/marketplace')): ?> class="active" <?php endif; ?>>
                                    <a href="<?php echo e(url('/marketplace')); ?>">🛒 Marketplace</a>
                                </li>
                                <li <?php if(Request::url() == url('/airport')): ?> class="active" <?php endif; ?>>
                                    <a href="<?php echo e(url('/airport')); ?>">✈️ Aéroport</a>
                                </li>
                                <li <?php if(Request::url() == url('/location')): ?> class="active" <?php endif; ?>>
                                    <a href="<?php echo e(url('/location')); ?>">📍 <?php echo app('translator')->get('home.location'); ?></a>
                                </li>
                                <li <?php if(Request::url() == url('/drive')): ?> class="active" <?php endif; ?>>
                                    <a href="<?php echo e(url('/drive')); ?>">🚘 <?php echo app('translator')->get('home.drive'); ?></a>
                                </li>
                            </ul>
                            <ul class="nav navbar-nav navbar-right">
                                <li>
                                    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                                    <?php $current_lang = Session::get('locale') ?: (Auth::check() ? Auth::user()->language : Setting::get('language')); ?>
                                    <select class="form-control" name="language" id="language">
                                        <option disabled>Langue</option>
                                        <option <?php if($current_lang=='fr') { echo 'selected=selected'; } ?> value="fr">FR</option>
                                        <option <?php if($current_lang=='en') { echo 'selected=selected'; } ?> value="en">EN</option>
                                    </select>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle pm-dl-app-btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="padding-top: 10px; padding-bottom: 10px; margin-top: 10px; background: linear-gradient(135deg, #0d1226, #1c264a);">
                                        📱 Télécharger l'App <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" style="background-color: #0d1226; border: 1px solid #C9A84C;">
                                        <li><a href="javascript:void(0)" onclick="installPWA('<?php echo e(Setting::get('store_link_android','#')); ?>')" style="color: #fff; padding: 10px 20px;">📱 App Client (PWA)</a></li>
                                        <li><a href="<?php echo e(Setting::get('provider_store_link_android','#')); ?>" target="_blank" style="color: #fff; padding: 10px 20px;">🚗 App Chauffeur (Play Store)</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>

            <?php echo $__env->yieldContent('content'); ?>
            <div class="page-content">
                <div class="footer row no-margin">
                    <div class="container">
                        <div class="footer-logo row no-margin">
                            <div class="logo-img">
                                <img src="<?php echo e(asset('logo.png')); ?>" alt="PicMe225" style="max-height: 40px;">
                            </div>
                        </div>
                        <div class="row no-margin">
                            <div class="col-md-3 col-sm-3 col-xs-12">
                                <ul>
                                    <li><a href="#"><?php echo app('translator')->get('home.ride'); ?></a></li>
                                    <li><a href="<?php echo e(url('/drive')); ?>"><?php echo app('translator')->get('home.drive'); ?></a></li>
                                    <li><a href="<?php echo e(url('/location')); ?>"><?php echo app('translator')->get('home.location'); ?></a></li>
                                    <li><a href="<?php echo e(url('/airport')); ?>"><?php echo app('translator')->get('home.airport'); ?></a></li>
                                    <li><a href="#"><?php echo app('translator')->get('home.city'); ?></a></li>
                                    <li><a href="#"><?php echo app('translator')->get('home.fare_estimate'); ?></a></li>
                                </ul>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-12">
                                <ul>
                                    <li><a href="<?php echo e(Setting::get('store_link_android','#')); ?>" target="_blank"><?php echo app('translator')->get('home.sign_up_ride_sm'); ?></a></li>
                                    <li><a href="<?php echo e(url('/drive')); ?>"><?php echo app('translator')->get('home.become_driver_sm'); ?></a></li>
                                    <li><a href="<?php echo e(Setting::get('store_link_android','#')); ?>" target="_blank"><?php echo app('translator')->get('home.ride_now'); ?></a></li>                            
                                </ul>
                            </div>

                            <div class="col-md-3 col-sm-3 col-xs-12">
                                <h5><?php echo app('translator')->get('home.get_app'); ?></h5>
                                <ul class="app">
                                    <li>
                                        <a href="javascript:void(0)" onclick="installPWA('<?php echo e(Setting::get('store_link_android','#')); ?>')">
                                            <img src="<?php echo e(asset('asset/img/appstore.png')); ?>">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(Setting::get('provider_store_link_android','#')); ?>" target="_blank">
                                            <img src="<?php echo e(asset('asset/img/playstore.png')); ?>">
                                        </a>
                                    </li>                                                        
                                </ul>                        
                            </div>

                            <div class="col-md-3 col-sm-3 col-xs-12">                        
                                <h5><?php echo app('translator')->get('home.contact_us'); ?></h5>
                                <ul class="pm-social-links">
                                    <!-- Email -->
                                    <li>
                                        <a href="mailto:contact@picme225.com" class="pm-social-btn email" title="Email">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <!-- WhatsApp -->
                                    <li>
                                        <a href="https://wa.me/22500000000" target="_blank" class="pm-social-btn whatsapp" title="WhatsApp">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <!-- TikTok -->
                                    <li>
                                        <a href="#" target="_blank" class="pm-social-btn tiktok" title="TikTok">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.94a8.17 8.17 0 004.77 1.52V7.01a4.85 4.85 0 01-1-.32z"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <!-- Facebook -->
                                    <li>
                                        <a href="#" target="_blank" class="pm-social-btn facebook" title="Facebook">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row no-margin">
                            <div class="col-md-12 copy">
                                <p>&copy; <?php echo e(date('Y')); ?> PicMe225. Tous droits réservés. Développé par <a href="https://jews-world.com" target="_blank" style="color: #C9A84C; text-decoration: none; font-weight: bold; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Jews-World Groupe SARL</a>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo e(asset('asset/js/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('asset/js/bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(asset('asset/js/scripts.js')); ?>"></script>
    <?php if(Setting::get('demo_mode', 0) == 1): ?>
        <!-- Start of LiveChat (www.livechatinc.com) code -->
        <script type="text/javascript">
            window.__lc = window.__lc || {};
            window.__lc.license = 8256261;
            (function() {
                var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
                lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
            })();
        </script>
        <!-- End of LiveChat code -->
    <?php endif; ?>
    <script type="text/javascript">
        $('#language').on('change',function(){
           $.ajax({
            url: '/lang',
            dataType: 'json',
            type: 'POST',
            data: {
        "_token": "<?php echo e(csrf_token()); ?>",
        "id": this.value
        },
            success: function(data){
                console.log(data);
                location.reload();
            }
        });
        });
    </script>
    
    <!-- Mobile Bottom Navigation 2026 -->
    <nav class="pm-bottom-nav">
        <div class="pm-bottom-nav-inner">
            <a href="<?php echo e(url('/')); ?>" class="pm-bottom-nav-item <?php echo e(Request::url() == url('/') ? 'active' : ''); ?>">
                <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span>Accueil</span>
            </a>
            <a href="<?php echo e(url('/drive')); ?>" class="pm-bottom-nav-item <?php echo e(Request::url() == url('/drive') ? 'active' : ''); ?>">
                <svg viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
                <span>VTC</span>
            </a>
            <a href="<?php echo e(url('/marketplace')); ?>" class="pm-bottom-nav-item <?php echo e(Request::url() == url('/marketplace') ? 'active' : ''); ?>">
                <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                <span>Marché</span>
            </a>
            <a href="<?php echo e(url('/login')); ?>" class="pm-bottom-nav-item">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>Compte</span>
            </a>
        </div>
    </nav>
    
    <!-- Global Web Popup Ad -->
    <div id="pm-global-ad-popup" class="modal fade" role="dialog" style="z-index: 99999;">
        <div class="modal-dialog modal-md" style="margin-top: 10vh;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; background: transparent; box-shadow: none;">
                <div class="modal-body" style="padding: 0; text-align: center; position: relative;">
                    <button type="button" class="close" data-dismiss="modal" style="position: absolute; top: 10px; right: 15px; z-index: 10; color: #fff; opacity: 0.8; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 30px;">&times;</button>
                    <a id="pm-ad-link" href="#" target="_blank" style="display:block;">
                        <img id="pm-ad-img" src="" alt="Publicité" style="width: 100%; max-height: 80vh; object-fit: contain; display:none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PWA Service Worker Registration
        let deferredPrompt;
        
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/serviceworker.js').then(function(registration) {
                    console.log('ServiceWorker registration successful');
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }

        // Listen for beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
            // Optionally, we could show a custom button here if we wanted
        });

        // Global function to trigger PWA install manually via the 'Download' buttons
        window.installPWA = async function(fallbackUrl) {
            if (deferredPrompt) {
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                // We've used the prompt, and can't use it again, throw it away
                deferredPrompt = null;
            } else {
                // If PWA install is not available (already installed, or iOS), redirect to fallback store
                if (fallbackUrl) {
                    window.location.href = fallbackUrl;
                } else {
                    alert("Pour installer sur iOS, utilisez le bouton 'Partager' puis 'Sur l'écran d'accueil'.");
                }
            }
        };

        $(document).ready(function() {
            $.ajax({
                url: '/api/user/ad/fetch?slot_name=WEB_POPUP',
                type: 'GET',
                success: function(data) {
                    if (data && data.type === 'PRIVATE') {
                        if (data.image_url) {
                            $('#pm-ad-img').attr('src', data.image_url).show();
                        }
                        if (data.target_url) {
                            $('#pm-ad-link').attr('href', data.target_url);
                            $('#pm-ad-link').on('click', function() {
                                $.post('/api/user/ad/click', { campaign_id: data.campaign_id, _token: "<?php echo e(csrf_token()); ?>" });
                            });
                        } else {
                            $('#pm-ad-link').removeAttr('href');
                        }
                        
                        $('#pm-global-ad-popup').modal('show');
                    }
                }
            });
        });
    </script>
    
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/user/layout/app.blade.php ENDPATH**/ ?>
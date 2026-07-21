<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ Setting::get('site_favicon', asset('favicon.ico')) }}" type="image/x-icon">
    <link rel="icon" href="{{ Setting::get('site_favicon', asset('favicon.ico')) }}" type="image/x-icon">

    <title>@yield('title'){{ Setting::get('site_title', 'PicMe225') }}</title>

    <!-- Styles -->
    <link href="{{ asset('asset/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('asset/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ===== CSS VARIABLES – PicMe225 Dark Theme ===== */
        :root {
            --bg:        #0a0a0a;
            --surface:   #141414;
            --surface-2: #1e1e1e;
            --border:    rgba(255,255,255,0.08);
            --green:     #2ecc71;
            --green-dim: rgba(46,204,113,0.15);
            --gold:      #FFC107;
            --gold-glow: rgba(255,193,7,0.3);
            --white:     #ffffff;
            --gray-1:    #e0e0e0;
            --gray-2:    #a0a0a0;
            --gray-3:    #555555;
            --danger:    #e74c3c;
            --shadow:    0 8px 32px rgba(0,0,0,0.6);
            --radius:    18px;
            --drawer-w:  290px;
            --transition:0.3s cubic-bezier(.4,0,.2,1);
        }

        /* ===== RESET & LAYOUT ===== */
        *, *::before, *::after { box-sizing: border-box; }
        body, html {
            margin: 0; padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg) !important;
            color: var(--white) !important;
            overflow-x: hidden;
        }

        /* ===== LIGHT THEME (For specific pages) ===== */
        body.light-theme {
            background: #f8f9fa !important;
            color: #333333 !important;
        }
        body.light-theme #wrapper {
            background: #f8f9fa !important;
        }
        body.light-theme .pro-dashboard-content,
        body.light-theme .page-content {
            background: #f8f9fa !important;
        }

        /* Hide legacy layouts wrappers / headers / footers */
        .overlay, #overlayer { display: none !important; }
        nav.navbar-inverse, .footer-content, footer, .footer { display: none !important; }

        /* Main Page Structure */
        #wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg);
        }

        #page-content-wrapper {
            width: 100%;
            padding-top: 60px; /* Space for global header */
            padding-bottom: calc(75px + env(safe-area-inset-bottom)); /* Space for global bottom nav */
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .page-content {
            padding: 0 !important;
            margin: 0 !important;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .pro-dashboard {
            padding: 0 !important;
            margin: 0 !important;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ===== GLOBAL HEADER ===== */
        .global-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            padding-top: env(safe-area-inset-top);
        }

        .header-left, .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-btn {
            background: none;
            border: none;
            color: var(--white);
            font-size: 18px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: background 0.2s, color 0.2s;
            text-decoration: none !important;
        }
        .header-btn:hover {
            background: var(--surface-2);
            color: var(--white);
        }

        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--white);
            text-decoration: none !important;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .header-title img {
            height: 24px;
            filter: brightness(0) invert(1);
        }

        /* Header status indicator */
        .header-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gray-3);
        }
        .header-status-dot.active {
            background: var(--green);
            box-shadow: 0 0 8px var(--green);
        }

        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--gold);
        }

        /* ===== DRAWER SYSTEM ===== */
        #drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition);
            backdrop-filter: blur(3px);
        }
        #drawer-overlay.open {
            opacity: 1;
            pointer-events: all;
        }

        #drawer {
            position: fixed;
            top: 0; left: 0;
            width: var(--drawer-w);
            height: 100%;
            background: var(--surface);
            z-index: 2500;
            transform: translateX(calc(-1 * var(--drawer-w)));
            transition: transform var(--transition);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--shadow);
            border-right: 1px solid var(--border);
        }
        #drawer.open {
            transform: translateX(0);
        }

        .drawer-header {
            padding: 32px 20px 20px;
            background: linear-gradient(160deg, #141414 0%, #1a2a1a 100%);
            border-bottom: 1px solid var(--border);
        }
        .drawer-avatar-wrap {
            width: 50px; height: 50px;
            border-radius: 50%;
            border: 2.5px solid var(--green);
            background: var(--surface-2);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            color: var(--gray-2);
            margin-bottom: 10px;
            overflow: hidden;
        }
        .drawer-avatar-wrap img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .drawer-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--white);
        }
        .drawer-email {
            font-size: 12px;
            color: var(--gray-2);
            margin-top: 2px;
        }
        .drawer-badge {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 8px;
            background: var(--green-dim);
            color: var(--green);
            border: 1px solid rgba(46,204,113,0.3);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }

        .drawer-nav {
            flex: 1;
            padding: 8px 0;
            overflow-y: auto;
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .drawer-nav::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }
        .drawer-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 20px;
            color: var(--gray-1);
            text-decoration: none !important;
            font-size: 13px;
            font-weight: 400;
            transition: all 0.2s;
            border-left: 2px solid transparent;
        }
        .drawer-nav a:hover, .drawer-nav a.active {
            background: rgba(255,255,255,0.02);
            color: var(--green);
            border-left-color: var(--green);
        }
        .drawer-nav a i {
            font-size: 15px;
            width: 20px;
            text-align: center;
            opacity: 0.8;
        }
        .drawer-divider {
            height: 1px;
            background: var(--border);
            margin: 8px 24px;
        }

        .drawer-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
        }
        .drawer-logout {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--danger) !important;
            text-decoration: none !important;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 0;
            transition: opacity 0.2s;
        }
        .drawer-logout:hover {
            opacity: 0.7;
        }

        /* ===== BOTTOM NAVIGATION ===== */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 64px;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
            padding-bottom: env(safe-area-inset-bottom);
            box-shadow: 0 -4px 20px rgba(0,0,0,0.4);
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex: 1;
            height: 100%;
            color: rgba(255, 255, 255, 0.7) !important;
            text-decoration: none !important;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            transition: color var(--transition);
            cursor: pointer;
            border: none;
            background: none;
        }
        .nav-item i {
            font-size: 20px;
            transition: transform 0.2s, filter 0.2s;
        }
        .nav-item:hover {
            color: var(--white) !important;
        }
        .nav-item.active {
            color: var(--gold) !important;
        }
        .nav-item.active i {
            transform: scale(1.18);
            filter: drop-shadow(0 0 6px var(--gold-glow));
        }

        /* Badge on notifications bell */
        .notif-badge {
            position: absolute;
            top: 2px; right: 2px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            font-size: 9px;
            font-weight: 800;
            min-width: 16px;
            height: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--surface);
        }

        /* ===== PREVENT BOOTSTRAP OVERRIDES ===== */
        .btn:focus, .btn:active, button:focus {
            outline: none !important;
            box-shadow: none !important;
        }
    </style>

    @yield('styles')

    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>
<body class="@yield('body-class')">
    
    <div id="wrapper">
        {{-- ===== GLOBAL HEADER ===== --}}
        <header class="global-header">
            <div class="header-left">
                <button id="global-menu-btn" class="header-btn" aria-label="Menu Open">
                    <i class="fa fa-bars"></i>
                </button>
                <a href="{{ route('provider.index') }}" class="header-title">
                    <img src="{{ Setting::get('site_logo') ? (strpos(Setting::get('site_logo'), 'http') === 0 ? Setting::get('site_logo') : asset(Setting::get('site_logo'))) : asset('logo-black.png') }}" alt="Logo">
                    <span class="header-status-dot {{ Auth::guard('provider')->user()->service && Auth::guard('provider')->user()->service->status == 'active' ? 'active' : '' }}" id="header-status-dot"></span>
                </a>
            </div>

            <div class="header-right">
                {{-- AI Assistant Icon --}}
                <a href="{{ route('provider.support') }}" class="header-btn" aria-label="Assistant IA">
                    <i class="fa fa-headphones" style="color: var(--green);"></i>
                </a>
                {{-- Notification bell --}}
                <a href="{{ route('provider.notifications') }}" class="header-btn" style="position: relative;" aria-label="Notifications">
                    <i class="fa fa-bell"></i>
                    <span class="notif-badge" id="global-notif-badge">0</span>
                </a>
                {{-- Profile Avatar link --}}
                <a href="{{ route('provider.profile.index') }}" aria-label="Profile">
                    <img class="header-avatar" src="<?php
                        $avatar = Auth::guard('provider')->user()->avatar;
                        if ($avatar) {
                            if (strpos($avatar, 'lorempixel.com') !== false) {
                                echo asset('asset/img/provider.jpg');
                            } elseif (strpos($avatar, 'http') === 0) {
                                echo $avatar;
                            } else {
                                echo \Storage::disk('s3')->url( $avatar);
                            }
                        } else {
                            echo asset('asset/img/provider.jpg');
                        }
                    ?>" alt="Avatar">
                </a>
            </div>
        </header>

        {{-- ===== DRAWER OVERLAY ===== --}}
        <div id="drawer-overlay"></div>

        {{-- ===== SIDE DRAWER ===== --}}
        <nav id="drawer">
            <div class="drawer-header">
                <div class="drawer-avatar-wrap">
                    <img src="<?php
                        $avatar = Auth::guard('provider')->user()->avatar;
                        if ($avatar) {
                            if (strpos($avatar, 'lorempixel.com') !== false) {
                                echo asset('asset/img/provider.jpg');
                            } elseif (strpos($avatar, 'http') === 0) {
                                echo $avatar;
                            } else {
                                echo \Storage::disk('s3')->url( $avatar);
                            }
                        } else {
                            echo asset('asset/img/provider.jpg');
                        }
                    ?>" alt="Avatar">
                </div>
                <div class="drawer-name">{{ Auth::guard('provider')->user()->first_name }} {{ Auth::guard('provider')->user()->last_name }}</div>
                <div class="drawer-email">{{ Auth::guard('provider')->user()->email }}</div>
                <div class="drawer-badge">
                    <i class="fa fa-check-circle"></i>
                    Chauffeur PicMe225
                </div>
            </div>

            <div class="drawer-nav">
                <a href="{{ route('provider.index') }}" class="{{ Request::segment(2) == '' || Request::segment(2) == 'index' ? 'active' : '' }}">
                    <i class="fa fa-tachometer"></i>
                    Tableau de bord
                </a>
                <a href="{{ route('provider.trips') }}" class="{{ Request::segment(2) == 'trips' ? 'active' : '' }}">
                    <i class="fa fa-car"></i>
                    Mes courses
                </a>
                <a href="{{ route('provider.upcoming') }}" class="{{ Request::segment(2) == 'upcoming' ? 'active' : '' }}">
                    <i class="fa fa-calendar"></i>
                    Courses planifiées
                </a>
                <a href="{{ route('provider.earnings') }}" class="{{ Request::segment(2) == 'earnings' ? 'active' : '' }}">
                    <i class="fa fa-line-chart"></i>
                    Revenus
                </a>
                <a href="{{ url('/provider/wallet') }}" class="{{ Request::segment(2) == 'wallet' ? 'active' : '' }}">
                    <i class="fa fa-google-wallet"></i>
                    Portefeuille
                </a>
                <div class="drawer-divider"></div>
                <a href="{{ route('provider.profile.index') }}" class="{{ Request::segment(2) == 'profile' && Request::segment(3) != 'password' ? 'active' : '' }}">
                    <i class="fa fa-user"></i>
                    Profil
                </a>
                <a href="{{ route('provider.documents.index') }}" class="{{ Request::segment(2) == 'documents' ? 'active' : '' }}">
                    <i class="fa fa-file-text"></i>
                    Documents
                </a>
                <a href="{{ route('provider.location.index') }}" class="{{ Request::segment(2) == 'location' ? 'active' : '' }}">
                    <i class="fa fa-crosshairs"></i>
                    Ma Position
                </a>
                <a href="{{ route('provider.store.index') }}" class="{{ Request::segment(2) == 'store' ? 'active' : '' }}">
                    <i class="fa fa-shopping-cart"></i>
                    Mon Store
                </a>
                <a href="{{ route('provider.notifications') }}" class="{{ Request::segment(2) == 'notifications' ? 'active' : '' }}">
                    <i class="fa fa-bell"></i>
                    Notifications
                </a>
                <div class="drawer-divider"></div>
                <a href="{{ url('/provider/governance') }}" class="{{ Request::segment(2) == 'governance' ? 'active' : '' }}">
                    <i class="fa fa-university"></i>
                    Gouvernance DAO
                </a>
                <a href="{{ route('provider.support') }}" class="{{ Request::segment(2) == 'support' ? 'active' : '' }}">
                    <i class="fa fa-headphones"></i>
                    Assistance IA
                </a>
            </div>

            <div class="drawer-footer">
                <a href="{{ url('/provider/logout') }}" class="drawer-logout"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out"></i>
                    Se déconnecter
                </a>
                <form id="logout-form" action="{{ url('/provider/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </div>
        </nav>

        {{-- ===== MAIN CONTENT WRAPPER ===== --}}
        <div id="page-content-wrapper">
            <div class="page-content">
                <div class="pro-dashboard">
                    @yield('content')
                </div>
            </div>
        </div>

        {{-- ===== GLOBAL BOTTOM NAV ===== --}}
        <div class="bottom-nav">
            <a href="{{ route('provider.index') }}" class="nav-item {{ Request::segment(2) == '' || Request::segment(2) == 'index' ? 'active' : '' }}">
                <i class="fa fa-location-arrow"></i>
                <span>Commandes</span>
            </a>
            <a href="{{ route('provider.earnings') }}" class="nav-item {{ Request::segment(2) == 'earnings' ? 'active' : '' }}">
                <i class="fa fa-money"></i>
                <span>Argent</span>
            </a>
            <a href="{{ route('provider.support') }}" class="nav-item {{ Request::segment(2) == 'support' ? 'active' : '' }}">
                <i class="fa fa-headphones"></i>
                <span>Assistant</span>
            </a>
            <a href="{{ route('provider.profile.index') }}" class="nav-item {{ Request::segment(2) == 'profile' ? 'active' : '' }}">
                <i class="fa fa-user"></i>
                <span>Profil</span>
            </a>
        </div>
    </div>

    <div id="modal-incoming"></div>

    <!-- Scripts -->
    <script type="text/javascript" src="{{ asset('asset/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/rating.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // --- Global Drawer Handling ---
            var drawerBtn = $('#global-menu-btn');
            var drawer = $('#drawer');
            var overlay = $('#drawer-overlay');

            function openDrawer() {
                drawer.addClass('open');
                overlay.addClass('open');
                $('body').css('overflow', 'hidden');
            }

            function closeDrawer() {
                drawer.removeClass('open');
                overlay.removeClass('open');
                $('body').css('overflow', '');
            }

            drawerBtn.on('click', function(e) {
                e.stopPropagation();
                if (drawer.hasClass('open')) {
                    closeDrawer();
                } else {
                    openDrawer();
                }
            });

            overlay.on('click', closeDrawer);

            // Handle swipe to close
            var startX = 0;
            drawer.on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
            });
            drawer.on('touchend', function(e) {
                var endX = e.originalEvent.changedTouches[0].clientX;
                if (startX - endX > 60) {
                    closeDrawer();
                }
            });

            // --- Android back button handling in web PWAs ---
            window.addEventListener('popstate', function(e) {
                if (drawer.hasClass('open')) {
                    closeDrawer();
                    history.pushState(null, null, window.location.pathname);
                }
            });

            // --- Global Notification Badge ---
            function loadGlobalNotifCount() {
                $.ajax({
                    url: '{{ route('provider.notifications.unread-count') }}',
                    type: 'GET',
                    success: function(data) {
                        var count = data.count || 0;
                        var badge = $('#global-notif-badge');
                        if (count > 0) {
                            badge.text(count > 99 ? '99+' : count).css('display', 'flex');
                        } else {
                            badge.hide();
                        }
                    },
                    error: function() {}
                });
            }

            loadGlobalNotifCount();
            setInterval(loadGlobalNotifCount, 60000);
        });
    </script>

    @yield('scripts')
</body>
</html>
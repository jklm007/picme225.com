<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Setting::get('site_title','PicMe225') }} – @yield('title')</title>
    <link rel="shortcut icon" type="image/png" href="{{ Setting::get('site_icon') }}"/>

    <!-- PWA Meta -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0D1B2A">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="{{ asset('asset/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <!-- jQuery (needed for some pages) -->
    <script src="{{ asset('asset/js/jquery.min.js') }}"></script>

    <style>
        /* ── RESET & VARIABLES ── */
        :root {
            --navy:      #0D1B2A;
            --navy-2:    #1a2840;
            --gold:      #C9A84C;
            --gold-light:#E2C06E;
            --white:     #FFFFFF;
            --gray-50:   #F8FAFC;
            --gray-100:  #F1F5F9;
            --gray-200:  #E2E8F0;
            --gray-400:  #94A3B8;
            --gray-600:  #475569;
            --gray-800:  #1E293B;
            --radius:    18px;
            --radius-sm: 12px;
            --shadow:    0 4px 24px rgba(0,0,0,0.10);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --nav-h:     70px;
            --header-h:  64px;
            --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            height: 100%;
        }

        /* ── HIDE WEB WRAPPERS ── */
        header.pm-web-header,
        .navbar-fixed-top,
        .dash-left,
        footer,
        .overlay.pm-web-overlay { display: none !important; }

        /* ── PWA HEADER ── */
        .pwa-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--header-h);
            background: var(--navy);
            z-index: 50000;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 12px;
            padding-top: env(safe-area-inset-top, 0px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }
        .pwa-header-menu {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: #fff;
            font-size: 18px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background var(--transition);
        }
        .pwa-header-menu:hover { background: rgba(255,255,255,0.15); }
        .pwa-header-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border: 2px solid var(--gold);
            overflow: hidden;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: var(--navy);
            background-size: cover; background-position: center;
        }
        .pwa-header-info {
            flex: 1;
            min-width: 0;
        }
        .pwa-header-name {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pwa-header-sub {
            font-size: 11px;
            color: rgba(255,255,255,0.55);
            margin-top: 1px;
        }
        .pwa-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .pwa-header-wallet {
            background: rgba(201,168,76,0.15);
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            color: var(--gold);
        }
        .pwa-header-notif {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            position: relative;
        }
        .pwa-header-notif .badge {
            position: absolute;
            top: -2px; right: -2px;
            width: 16px; height: 16px;
            background: #e74c3c;
            border-radius: 50%;
            font-size: 9px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            border: 2px solid var(--navy);
        }

        /* ── PWA DRAWER ── */
        .pwa-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0);
            z-index: 99998;
            display: none;
            transition: background 0.35s ease;
        }
        .pwa-drawer-overlay.active {
            display: block;
            background: rgba(0,0,0,0.55);
        }
        .pwa-drawer {
            position: fixed;
            top: 0; left: -300px; bottom: 0;
            width: 290px;
            background: var(--navy);
            z-index: 99999;
            transition: left 0.35s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .pwa-drawer.active { left: 0; }
        .pwa-drawer-head {
            padding: 48px 20px 24px 20px;
            background: linear-gradient(135deg, var(--navy-2), #0f2340);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .pwa-drawer-avatar-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }
        .pwa-drawer-avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            background-size: cover; background-position: center;
            background-color: #2c446b;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800; color: var(--gold);
            flex-shrink: 0;
        }
        .pwa-drawer-user-name { font-size: 17px; font-weight: 700; color: #fff; }
        .pwa-drawer-user-email { font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 3px; }
        .pwa-drawer-wallet-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(201,168,76,0.15);
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--gold);
        }
        .pwa-drawer-menu {
            flex: 1;
            overflow-y: auto;
            padding: 12px 0;
        }
        .pwa-drawer-menu a,
        .pwa-drawer-menu button {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            color: rgba(255,255,255,0.75);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .pwa-drawer-menu a:hover,
        .pwa-drawer-menu button:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .pwa-drawer-menu a.active { color: var(--gold); border-left-color: var(--gold); background: rgba(201,168,76,0.08); }
        .pwa-drawer-menu .drawer-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.07);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .pwa-drawer-menu a.active .drawer-icon { background: rgba(201,168,76,0.15); color: var(--gold); }
        .pwa-drawer-sep {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 8px 0;
        }
        .pwa-drawer-logout {
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .pwa-drawer-logout button {
            color: #fc8181 !important;
        }

        /* ── MAIN CONTENT ── */
        .pwa-content {
            margin-top: var(--header-h);
            padding-bottom: calc(var(--nav-h) + env(safe-area-inset-bottom, 0px));
            min-height: calc(100vh - var(--header-h));
        }

        /* ── BOTTOM NAV (override) ── */
        .pm-bottom-nav {
            z-index: 49999 !important; /* Below drawer, above content */
        }
    </style>

    @yield('styles')
</head>
<body>

    {{-- ── PWA HEADER ──────────────────────────────────────── --}}
    <header class="pwa-header" id="pwa-global-header">
        <button class="pwa-header-menu" onclick="pwaToggleDrawer()" id="pwa-menu-btn" aria-label="Menu">
            <i class="fa fa-bars"></i>
        </button>

        @php
            $avatarUrl = Auth::user()->picture ? img(Auth::user()->picture) : null;
        @endphp
        @if($avatarUrl)
            <div class="pwa-header-avatar" style="background-image:url('{{ $avatarUrl }}')"></div>
        @else
            <div class="pwa-header-avatar">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}</div>
        @endif

        <div class="pwa-header-info">
            <div class="pwa-header-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
            <div class="pwa-header-sub">@yield('header-sub', 'Où allons-nous ?')</div>
        </div>

        <div class="pwa-header-right">
            <div class="pwa-header-wallet">
                <i class="fa fa-money" style="margin-right:4px;"></i>
                {{ currency(Auth::user()->wallet_balance) }}
            </div>
            <div class="pwa-header-notif">
                <i class="fa fa-bell-o"></i>
            </div>
        </div>
    </header>

    {{-- ── DRAWER OVERLAY ──────────────────────────────────── --}}
    <div class="pwa-drawer-overlay" id="pwa-drawer-overlay" onclick="pwaToggleDrawer()"></div>

    {{-- ── DRAWER ───────────────────────────────────────────── --}}
    <div class="pwa-drawer" id="pwa-drawer">
        <div class="pwa-drawer-head">
            <div class="pwa-drawer-avatar-wrap">
                @if($avatarUrl)
                    <div class="pwa-drawer-avatar" style="background-image:url('{{ $avatarUrl }}')"></div>
                @else
                    <div class="pwa-drawer-avatar">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}</div>
                @endif
                <div>
                    <div class="pwa-drawer-user-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                    <div class="pwa-drawer-user-email">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="pwa-drawer-wallet-badge">
                <i class="fa fa-google-wallet"></i>
                {{ currency(Auth::user()->wallet_balance) }}
            </div>
        </div>

        <nav class="pwa-drawer-menu">
            <a href="{{ url('dashboard') }}" class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-home"></i></span>
                Accueil
            </a>
            <a href="{{ url('trips') }}" class="{{ Request::is('trips') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-history"></i></span>
                Mes Trajets
            </a>
            <a href="{{ url('upcoming/trips') }}" class="{{ Request::is('upcoming/trips') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-calendar"></i></span>
                Trajets planifiés
            </a>
            <a href="{{ route('user.marketplace.explore') }}" class="{{ Request::is('user/store') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-shopping-bag"></i></span>
                Store
            </a>
            <div class="pwa-drawer-sep"></div>
            <a href="{{ url('profile') }}" class="{{ Request::is('profile') || Request::is('user/profile') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-user"></i></span>
                Mon Profil
            </a>
            <a href="{{ url('wallet') }}" class="{{ Request::is('wallet') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-leaf" style="color:#2ecc71;"></i></span>
                ECO / Wallet
                <span style="margin-left:auto;font-size:11px;background:rgba(46,204,113,0.15);color:#2ecc71;border-radius:10px;padding:2px 8px;">{{ Auth::user()->eco_token_balance ?? 0 }}</span>
            </a>
            <a href="{{ url('change/password') }}">
                <span class="drawer-icon"><i class="fa fa-lock"></i></span>
                Changer le mot de passe
            </a>
            <a href="{{ url('promotions') }}" class="{{ Request::is('promotions') ? 'active' : '' }}">
                <span class="drawer-icon"><i class="fa fa-tag"></i></span>
                Promotions
            </a>
            <div class="pwa-drawer-sep"></div>
            <div class="pwa-drawer-logout">
                <form action="{{ url('/logout') }}" method="POST" id="pwa-logout-form">
                    {{ csrf_field() }}
                </form>
                <button onclick="document.getElementById('pwa-logout-form').submit()">
                    <span class="drawer-icon" style="background:rgba(252,129,129,0.1);"><i class="fa fa-sign-out"></i></span>
                    Déconnexion
                </button>
            </div>
        </nav>
    </div>

    {{-- ── CONTENT ──────────────────────────────────────────── --}}
    <main class="pwa-content" id="pwa-main">
        @yield('content')
    </main>

    {{-- ── BOTTOM NAV ───────────────────────────────────────── --}}
    @include('user.include.bottom_nav', ['active' => $bottomNavActive ?? ''])

    {{-- ── GLOBAL SCRIPTS ──────────────────────────────────── --}}
    <script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>

    <script>
    // ── PWA Drawer Toggle ──────────────────────────────────────
    function pwaToggleDrawer(forceClose) {
        var overlay = document.getElementById('pwa-drawer-overlay');
        var drawer  = document.getElementById('pwa-drawer');
        if (!overlay || !drawer) return;

        var isOpen = drawer.classList.contains('active');
        if (forceClose || isOpen) {
            drawer.classList.remove('active');
            overlay.classList.remove('active');
        } else {
            drawer.classList.add('active');
            overlay.classList.add('active');
        }
    }

    // ── PWA Service Worker ─────────────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/serviceworker.js').catch(function(){});
        });
    }

    // Prevent body scroll when drawer is open
    document.getElementById('pwa-drawer').addEventListener('transitionend', function() {
        document.body.style.overflow = this.classList.contains('active') ? 'hidden' : '';
    });
    </script>

    @yield('scripts')

</body>
</html>

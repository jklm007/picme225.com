<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

    <title>{{Setting::get('site_title','Tranxit')}} - @yield('title') - User Dashboard</title>
    <link rel="shortcut icon" type="image/png" href="{{ Setting::get('site_icon') }}"/>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#D4AF37">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link href="{{asset('asset/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/slick.css')}}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('asset/css/slick-theme.css')}}"/>
    <link href="{{asset('asset/css/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/bootstrap-timepicker.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/dashboard-style.css')}}" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── PWA GLOBAL VARIABLES & STYLES ── */
        :root {
            --navy:      #0D1B2A;
            --navy-2:    #1a2840;
            --navy-3:    #1e3048;
            --gold:      #C9A84C;
            --gold-light:#E2C06E;
            --gold-pale: rgba(201,168,76,0.12);
            --gold-glow: rgba(201,168,76,0.3);
            --white:     #FFFFFF;
            --gray-50:   #f9fafc;
            --gray-100:  #f0f2f7;
            --gray-200:  #e4e7ef;
            --gray-400:  #adb5c9;
            --gray-500:  #7a8bad;
            --danger:    #e74c3c;
            --radius:    20px;
            --radius-sm: 10px;
            --shadow:    0 -6px 32px rgba(13,27,42,0.16);
            --shadow-sm: 0 2px 12px rgba(13,27,42,0.08);
            --nav-h:     70px;
            --header-h:  64px;
            --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        body, html {
            margin: 0; padding: 0; height: 100%; overflow: hidden; font-family: 'Inter', sans-serif;
            background: #f8fafc; /* Standard background for non-map pages */
        }

        /* Hide the legacy Bootstrap desktop components */
        .navbar-fixed-top,
        .menu-toggle,
        .overlay,
        header,
        .dash-left,
        .footer-content {
            display: none !important;
        }

        /* Adjust main container */
        .page-content.dashboard-page {
            padding: 0 !important;
            margin: 0 !important;
            height: 100vh;
            width: 100vw;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* ===================== TOP BAR (Unified) ==================== */
        .pm-top-bar {
            position: fixed;
            top: 14px; left: 10px; right: 10px;
            z-index: 50000;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255,255,255,0.93);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 7px 12px;
            border-radius: 50px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.1);
        }
        .pm-user-info { display: flex; align-items: center; gap: 9px; cursor: pointer; }
        .pm-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: center/cover url('{{ img(Auth::user()->picture ?? '') }}') no-repeat;
            border: 2px solid var(--gold);
            flex-shrink: 0;
            box-shadow: 0 0 0 3px var(--gold-glow);
        }
        .pm-avatar-initials {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--navy), var(--navy-3));
            border: 2px solid var(--gold);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px; color: var(--gold); flex-shrink: 0;
        }
        .pm-name { font-weight: 700; font-size: 13px; color: var(--navy); line-height: 1.2; }
        .pm-subtitle { font-size: 10px; color: var(--gray-500); font-weight: 500; }
        .pm-top-actions { display: flex; gap: 7px; align-items: center; }
        .pm-wallet {
            background: var(--gold-pale);
            padding: 5px 10px; border-radius: 20px;
            display: flex; align-items: center; gap: 5px;
            font-weight: 700; font-size: 12px; color: var(--gold);
            border: 1px solid rgba(201,168,76,0.2);
            text-decoration: none;
        }
        .pm-wallet:hover { text-decoration: none; color: var(--navy); background: var(--gold); }
        .pm-notif-btn {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--gray-100);
            display: flex; align-items: center; justify-content: center;
            color: var(--navy); font-size: 14px; text-decoration: none;
            position: relative;
        }
        .pm-notif-btn .badge {
            position: absolute; top: 4px; right: 4px;
            width: 7px; height: 7px; border-radius: 50%; padding: 0;
            background: var(--danger); border: 2px solid white;
        }

        /* ===================== DRAWER (Unified) ===================== */
        .pwa-drawer-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0);
            z-index: 999998;
            display: none;
            transition: background 0.35s ease;
        }
        .pwa-drawer-overlay.active {
            display: block; background: rgba(0,0,0,0.55);
        }
        .pwa-drawer {
            position: fixed; top: 0; left: -280px; bottom: 0;
            width: 270px; background: #ffffff;
            z-index: 999999;
            transition: left 0.35s cubic-bezier(0.4,0,0.2,1);
            display: flex; flex-direction: column; overflow: hidden;
            box-shadow: 2px 0 12px rgba(0,0,0,0.05);
        }
        .pwa-drawer.active { left: 0; }
        .pwa-drawer-head {
            padding: 30px 20px 20px 20px;
            background: #ffffff; border-bottom: 1px solid #f1f5f9;
        }
        .pwa-drawer-avatar-wrap { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .pwa-drawer-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background-size: cover; background-position: center; background-color: var(--navy-2);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .pwa-drawer-user-name { font-size: 15px; font-weight: 700; color: var(--navy); }
        .pwa-drawer-user-email { font-size: 11px; color: #64748b; margin-top: 2px; }
        .pwa-drawer-wallet-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 4px 10px; font-size: 12px; font-weight: 700; color: var(--navy);
        }
        .pwa-drawer-menu { flex: 1; overflow-y: auto; padding: 10px 0; }
        .pwa-drawer-menu a, .pwa-drawer-menu button {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 20px; color: #475569; font-size: 14px; font-weight: 500;
            text-decoration: none; border: none; background: none; width: 100%; text-align: left;
            cursor: pointer; transition: all 0.2s; border-left: 3px solid transparent;
        }
        .pwa-drawer-menu a:hover, .pwa-drawer-menu button:hover { background: #f8fafc; color: var(--navy); }
        .pwa-drawer-menu a.active { color: var(--gold); border-left-color: var(--gold); background: #fffcf2; font-weight: 600; }
        .pwa-drawer-menu .drawer-icon { width: 24px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; color: #94a3b8; }
        .pwa-drawer-menu a.active .drawer-icon { color: var(--gold); }
        .pwa-drawer-sep { height: 1px; background: #f1f5f9; margin: 6px 0; }
        .pwa-drawer-logout { border-top: 1px solid #f1f5f9; }
        .pwa-drawer-logout button { color: #ef4444 !important; }
        .pwa-drawer-logout button .drawer-icon { color: #ef4444 !important; }

        /* Legacy Overrides */
        .nav-pills > li.active > a, .nav-pills > li.active > a:focus, .nav-pills > li.active > a:hover {
            background-color: var(--gold); color: var(--navy);
        }
        .nav-pills > li > a { color: #555; background-color: #e9ecef; margin: 0 5px; }
        .input-group-addon { background-color: #fff; border-right: 0; }
        .form-control { border-left: 0; box-shadow: none; }
        .form-control:focus { box-shadow: none; border-color: #ccc; }
        
        /* Layout container for normal pages to offset for Top Bar */
        .pm-standard-layout {
            padding-top: 80px; 
            padding-bottom: 90px; /* space for bottom nav */
            height: 100%;
            overflow-y: auto;
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- TOP BAR -->
    <div class="pm-top-bar">
        <div class="pm-user-info" onclick="toggleDrawer()">
            @if(Auth::user() && Auth::user()->picture)
                <div class="pm-avatar"></div>
            @else
                <div class="pm-avatar-initials">{{ substr(Auth::user()->first_name ?? 'P',0,1) }}{{ substr(Auth::user()->last_name ?? 'M',0,1) }}</div>
            @endif
            <div>
                <div class="pm-name">{{ Auth::user()->first_name ?? 'Invité' }} {{ Auth::user()->last_name ?? '' }}</div>
                <div class="pm-subtitle">Passager PicMe</div>
            </div>
        </div>
        <div class="pm-top-actions">
            <a href="{{url('/wallet')}}" class="pm-wallet">
                <i class="fa fa-google-wallet"></i> {{currency(Auth::user()->wallet_balance ?? 0)}}
            </a>
            <a href="{{url('notifications')}}" class="pm-notif-btn">
                <i class="fa fa-bell"></i>
                <span class="badge"></span>
            </a>
        </div>
    </div>

    <!-- DRAWER -->
    <div class="pwa-drawer-overlay" onclick="toggleDrawer()"></div>
    <div class="pwa-drawer">
        <div class="pwa-drawer-head">
            <div class="pwa-drawer-avatar-wrap">
                <div class="pwa-drawer-avatar" style="background-image: url('{{ img(Auth::user()->picture ?? '') }}');">
                    @if(!Auth::user() || !Auth::user()->picture)
                        {{ substr(Auth::user()->first_name ?? 'P',0,1) }}{{ substr(Auth::user()->last_name ?? 'M',0,1) }}
                    @endif
                </div>
                <div>
                    <div class="pwa-drawer-user-name">{{ Auth::user()->first_name ?? 'Invité' }} {{ Auth::user()->last_name ?? '' }}</div>
                    <div class="pwa-drawer-user-email">{{ Auth::user()->email ?? '' }}</div>
                </div>
            </div>
            <div class="pwa-drawer-wallet-badge">
                <i class="fa fa-leaf" style="color: #27ae60;"></i> {{ Auth::user()->eco_token_balance ?? '0' }} ECO
            </div>
        </div>
        <div class="pwa-drawer-menu">
            <a href="{{url('dashboard')}}" class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <div class="drawer-icon"><i class="fa fa-taxi"></i></div> VTC
            </a>
            <a href="{{url('trips')}}" class="{{ Request::is('trips') ? 'active' : '' }}">
                <div class="drawer-icon"><i class="fa fa-history"></i></div> @lang('user.my_trips')
            </a>
            <a href="{{url('wallet')}}" class="{{ Request::is('wallet') ? 'active' : '' }}">
                <div class="drawer-icon"><i class="fa fa-google-wallet"></i></div> @lang('user.my_wallet')
            </a>
            <a href="{{url('promotions')}}" class="{{ Request::is('promotions') ? 'active' : '' }}">
                <div class="drawer-icon"><i class="fa fa-tag"></i></div> @lang('user.promotion')
            </a>
            <div class="pwa-drawer-sep"></div>
            <a href="{{url('profile')}}">
                <div class="drawer-icon"><i class="fa fa-user"></i></div> @lang('user.profile.profile')
            </a>
            <a href="{{url('change/password')}}">
                <div class="drawer-icon"><i class="fa fa-lock"></i></div> @lang('user.profile.change_password')
            </a>
            <div class="pwa-drawer-sep"></div>
            <div class="pwa-drawer-logout">
                <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div class="drawer-icon"><i class="fa fa-sign-out"></i></div> @lang('user.profile.logout')
                </button>
            </div>
        </div>
    </div>
    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>

    <!-- CONTENT -->
    <div class="page-content dashboard-page" style="padding-top:0 !important;">    
        <div class="container pm-wrapper-content" style="height: 100%; padding:0 !important;">
            @yield('content')
        </div>
    </div>

    <!-- BOTTOM NAV -->
    @include('user.include.bottom_nav', ['active' => $bottomNavActive ?? $active ?? ''])

    <!-- SCRIPTS -->
    <script src="{{asset('asset/js/jquery.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap.min.js')}}"></script>       
    <script type="text/javascript" src="{{asset('asset/js/slick.min.js')}}"></script>
    
    <script>
        // Drawer toggle
        function toggleDrawer() {
            const drawer = document.querySelector('.pwa-drawer');
            const overlay = document.querySelector('.pwa-drawer-overlay');
            drawer.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>

    <!-- Global Web Popup Ad (Sponsored Ads with internal redirection) -->
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
                            var targetUrl = data.target_url;
                            var isAuth = {{ Auth::check() ? 'true' : 'false' }};
                            
                            // Check if targetUrl is just a numeric ID
                            if (!isNaN(targetUrl) && targetUrl.toString().trim() !== '') {
                                var numId = targetUrl.toString().trim();
                                targetUrl = isAuth ? '/user/store/product/' + numId : '/marketplace/detail/' + numId;
                            } else {
                                // It's an actual URL, check if it's a marketplace URL that needs rewriting for auth users
                                if(isAuth && targetUrl && targetUrl.indexOf('/marketplace/') !== -1) {
                                    var parts = targetUrl.split('/marketplace/');
                                    if(parts.length > 1) {
                                        var id = parts[1].replace('detail/', '').replace('/', '');
                                        targetUrl = '/user/store/product/' + id;
                                    }
                                }
                            }
                            $('#pm-ad-link').attr('href', targetUrl);
                            $('#pm-ad-link').off('click').on('click', function() {
                                $.post('/api/user/ad/click', { campaign_id: data.campaign_id, _token: "{{ csrf_token() }}" });
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

    @yield('scripts')
    @include('common.pwa_installer')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
    <style>
        /* ── PWA GLOBAL VARIABLES & STYLES ── */
        :root {
            --navy:      #0D1B2A;
            --navy-2:    #1a2840;
            --gold:      #D4AF37;
            --gold-light:#F3E5AB;
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

        /* Hide the legacy Bootstrap desktop components */
        .navbar-fixed-top,
        .menu-toggle,
        .overlay {
            display: none !important;
        }

        /* Hide desktop sidebar */
        .col-md-3 {
            display: none !important;
        }

        /* Adjust main container to full width for mobile PWA */
        .col-md-9 {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .page-content.dashboard-page {
            padding-top: 0;
            padding-bottom: var(--nav-h) !important;
            margin: 0 !important;
        }

        .page-content.dashboard-page .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
        }

        /* PWA Header styles */
        .pwa-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--header-h);
            background: var(--navy);
            z-index: 50000;
            display: flex !important;
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
            border: none;
            flex-shrink: 0;
            transition: background var(--transition);
        }
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
            background: rgba(201,168,76,0.15);
            border: 1px solid rgba(201,168,76,0.3);
            color: var(--gold);
            font-size: 16px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            position: relative;
            transition: transform 0.2s ease, background 0.2s;
        }
        .pwa-header-notif:hover, .pwa-header-notif:focus {
            transform: scale(1.08);
            background: rgba(201,168,76,0.25);
            color: var(--gold-light);
        }
        .pwa-notif-badge {
            position: absolute;
            top: -2px; right: -2px;
            background: #e74c3c;
            color: white;
            font-size: 9px;
            font-weight: 700;
            border-radius: 50%;
            width: 14px; height: 14px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--navy);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* PWA Drawer Overlay & Drawer styles */
        .pwa-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0);
            /* HIGHEST z-index: above bottom-nav(50000), autocomplete(60000), sheet(8000) */
            z-index: 999998;
            display: none;
            transition: background 0.35s ease;
        }
        .pwa-drawer-overlay.active {
            display: block;
            background: rgba(0,0,0,0.55);
        }
        .pwa-drawer {
            position: fixed;
            top: 0; left: -280px; bottom: 0;
            width: 270px;
            background: #ffffff;
            /* HIGHEST z-index on the page */
            z-index: 999999;
            transition: left 0.35s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 2px 0 12px rgba(0,0,0,0.05);
        }
        .pwa-drawer.active { left: 0; }
        .pwa-drawer-head {
            padding: 30px 20px 20px 20px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }
        .pwa-drawer-avatar-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .pwa-drawer-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background-size: cover; background-position: center;
            background-color: var(--navy-2);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .pwa-drawer-user-name { font-size: 15px; font-weight: 700; color: var(--navy); }
        .pwa-drawer-user-email { font-size: 11px; color: #64748b; margin-top: 2px; }
        .pwa-drawer-wallet-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            color: var(--navy);
        }
        .pwa-drawer-menu {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        .pwa-drawer-menu a,
        .pwa-drawer-menu button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: #475569;
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
        .pwa-drawer-menu button:hover { background: #f8fafc; color: var(--navy); }
        .pwa-drawer-menu a.active { color: var(--gold); border-left-color: var(--gold); background: #fffcf2; font-weight: 600; }
        .pwa-drawer-menu .drawer-icon {
            width: 24px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            color: #94a3b8;
        }
        .pwa-drawer-menu a.active .drawer-icon { color: var(--gold); }
        .pwa-drawer-sep {
            height: 1px;
            background: #f1f5f9;
            margin: 6px 0;
        }
        .pwa-drawer-logout {
            border-top: 1px solid #f1f5f9;
        }
        .pwa-drawer-logout button { color: #ef4444 !important; }
        .pwa-drawer-logout button .drawer-icon { color: #ef4444 !important; }

        /* Custom Modern Overrides */
        .nav-pills > li.active > a, .nav-pills > li.active > a:focus, .nav-pills > li.active > a:hover {
            background-color: var(--gold);
            color: var(--navy);
        }
        .nav-pills > li > a {
            color: #555;
            background-color: #e9ecef;
            margin: 0 5px;
        }
        .input-group-addon {
            background-color: #fff;
            border-right: 0;
        }
        .form-control {
            border-left: 0;
            box-shadow: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ccc;
        }
    </style>
    @yield('styles')
</head>

<body>

    @include('user.include.header')
    @include('user.include.nav')

    <div class="page-content dashboard-page">    
        <div class="container">
            
            @yield('content')

        </div>
    </div>

    @include('user.include.bottom_nav', ['active' => $bottomNavActive ?? $active ?? ''])

    @include('user.include.footer')


    <script src="{{asset('asset/js/jquery.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap.min.js')}}"></script>       
    <script type="text/javascript" src="{{asset('asset/js/jquery.mousewheel.js')}}"></script>
    <script type="text/javascript" src="{{asset('asset/js/jquery-migrate-1.2.1.min.js')}}"></script> 
    <script type="text/javascript" src="{{asset('asset/js/slick.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap-timepicker.js')}}"></script>
    <script src="{{asset('asset/js/dashboard-scripts.js')}}"></script>
    @if(Setting::get('demo_mode', 0) == 1)
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
    @endif

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
                            $('#pm-ad-link').on('click', function() {
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

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        window.installPWA = async function(fallbackUrl) {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
            } else {
                if (fallbackUrl) {
                    window.location.href = fallbackUrl;
                } else {
                    alert("Pour installer sur iOS, utilisez le bouton 'Partager' puis 'Sur l'écran d'accueil'.");
                }
            }
        };
    </script>

    @yield('scripts')

    @include('common.pwa_installer')
</body>
</html>
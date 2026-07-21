@extends('user.layout.base')

@section('title', 'Dashboard – PicMe225')

@section('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
{{-- Google Fonts --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ==========================================================
   PICME DASHBOARD v3 — Compact, Mobile-First, Premium UX
   ========================================================== */
:root {
    --navy:       #0D1B2A;
    --navy-2:     #162436;
    --navy-3:     #1e3048;
    --gold:       #C9A84C;
    --gold-light: #E2C06E;
    --gold-pale:  rgba(201,168,76,0.12);
    --gold-glow:  rgba(201,168,76,0.3);
    --white:      #ffffff;
    --gray-50:    #f9fafc;
    --gray-100:   #f0f2f7;
    --gray-200:   #e4e7ef;
    --gray-400:   #adb5c9;
    --gray-500:   #7a8bad;
    --success:    #27ae60;
    --danger:     #e74c3c;
    --radius:     20px;
    --radius-sm:  10px;
    --shadow:     0 -6px 32px rgba(13,27,42,0.16);
    --shadow-sm:  0 2px 12px rgba(13,27,42,0.08);
    --sheet-h:    60vh;
}

/* Reset layout */
header,.dash-left,.footer-content,.menu-toggle,.overlay{display:none!important}
body,html{margin:0;padding:0;height:100%;overflow:hidden;background:#000;font-family:'Inter',sans-serif}
.page-content.dashboard-page{padding:0!important;margin:0!important;height:100vh;width:100vw}
.container{width:100%!important;max-width:100%!important;padding:0!important;margin:0!important}

/* ===================== WRAPPER ===================== */
#pm-wrapper{width:100%;height:100vh;position:relative;overflow:hidden}

/* ===================== MAP ========================= */
#pm-map{
    position:absolute;top:0;left:0;
    width:100%;height:72%;
    z-index:1;
}
#pm-map::after{
    content:'';
    position:absolute;bottom:0;left:0;right:0;height:60px;
    background:linear-gradient(transparent,rgba(255,255,255,0.65));
    z-index:2;pointer-events:none;
}

/* ===================== TOP BAR ==================== */
.pm-top-bar{
    position:absolute;top:14px;left:10px;right:10px;
    z-index:20;
    display:flex;justify-content:space-between;align-items:center;
    background:rgba(255,255,255,0.93);
    backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px);
    padding:7px 12px;
    border-radius:50px;
    box-shadow:0 4px 18px rgba(0,0,0,0.1);
}
.pm-user-info{display:flex;align-items:center;gap:9px;cursor:pointer}
.pm-avatar{
    width:36px;height:36px;border-radius:50%;
    background:center/cover url('{{ img(Auth::user()->picture) }}') no-repeat;
    border:2px solid var(--gold);
    flex-shrink:0;
    box-shadow:0 0 0 3px var(--gold-glow);
}
.pm-avatar-initials{
    width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,var(--navy),var(--navy-3));
    border:2px solid var(--gold);
    display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:12px;color:var(--gold);flex-shrink:0;
}
.pm-name{font-weight:700;font-size:13px;color:var(--navy);line-height:1.2}
.pm-subtitle{font-size:10px;color:var(--gray-500);font-weight:500}
.pm-top-actions{display:flex;gap:7px;align-items:center}
.pm-wallet{
    background:var(--gold-pale);
    padding:5px 10px;border-radius:20px;
    display:flex;align-items:center;gap:5px;
    font-weight:700;font-size:12px;color:var(--gold);
    border:1px solid rgba(201,168,76,0.2);
}
.pm-notif-btn{
    width:34px;height:34px;border-radius:50%;
    background:var(--gray-100);
    display:flex;align-items:center;justify-content:center;
    color:var(--navy);font-size:14px;text-decoration:none;
    position:relative;
}
.pm-notif-btn .badge{
    position:absolute;top:4px;right:4px;
    width:7px;height:7px;border-radius:50%;
    background:var(--danger);border:2px solid white;
}

/* ===================== RECENTER BTN ============== */
.pm-recenter-btn{
    position:absolute;right:12px;bottom:calc(var(--sheet-h) + 64px + 12px);
    z-index:15;
    width:40px;height:40px;border-radius:50%;
    background:white;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 2px 10px rgba(0,0,0,0.16);
    cursor:pointer;border:none;color:var(--navy);font-size:16px;
    transition:transform 0.15s;
}
.pm-recenter-btn:active{transform:scale(0.93)}

/* ================== BOTTOM SHEET ================ */
.pm-sheet{
    position:absolute;
    bottom:64px;left:0;right:0;
    height:var(--sheet-h);
    background:var(--white);
    border-top-left-radius:var(--radius);
    border-top-right-radius:var(--radius);
    box-shadow:var(--shadow);
    z-index:10;
    display:flex;flex-direction:column;
    transition:transform 0.35s cubic-bezier(0.175,0.885,0.32,1.2);
    will-change:transform;
}
.pm-drag-handle{
    width:32px;height:3px;
    background:var(--gray-200);
    border-radius:10px;
    margin:7px auto 3px;
    cursor:grab;flex-shrink:0;
}
/* Scroll area inside sheet */
.pm-sheet-scroll{
    flex:1;overflow-y:auto;padding:0 10px 0;
    -webkit-overflow-scrolling:touch;
}
.pm-sheet-scroll::-webkit-scrollbar{display:none}

/* ===== STICKY RESERVE BUTTON (always visible) ==== */
.pm-sheet-footer{
    padding:8px 10px 10px;
    background:var(--white);
    border-top:1px solid var(--gray-100);
    flex-shrink:0;
    border-radius:0 0 var(--radius) var(--radius);
}

/* ============= QUICK STATS ====================== */
.pm-stats-row{
    display:flex;gap:6px;overflow-x:auto;
    padding-bottom:5px;margin-bottom:4px;
}
.pm-stats-row::-webkit-scrollbar{display:none}
.pm-stat-chip{
    background:var(--gray-100);
    border:1px solid var(--gray-200);
    border-radius:16px;padding:4px 9px;
    display:flex;align-items:center;gap:4px;
    white-space:nowrap;font-size:10px;font-weight:600;
    color:var(--navy);flex-shrink:0;
}
.pm-stat-chip.eco{color:var(--success);border-color:rgba(39,174,96,0.2);background:rgba(39,174,96,0.07)}
.pm-stat-chip.promo{color:var(--danger);border-color:rgba(231,76,60,0.2);background:rgba(231,76,60,0.07)}

/* ============= TABS (Category bar, horiz scroll) = */
.pm-tabs{
    display:flex;gap:3px;overflow-x:auto;
    background:var(--gray-100);
    border-radius:12px;padding:3px;
    margin-bottom:7px;
}
.pm-tabs::-webkit-scrollbar{display:none}
.pm-tab{
    flex-shrink:0;display:flex;flex-direction:column;
    align-items:center;gap:2px;
    padding:6px 10px;border-radius:10px;
    font-size:10px;font-weight:700;cursor:pointer;
    color:var(--gray-500);border:none;background:transparent;
    transition:all 0.2s;white-space:nowrap;
}
.pm-tab img{width:20px;height:20px;object-fit:contain;opacity:0.5;transition:opacity 0.2s}
.pm-tab.active{background:var(--white);color:var(--navy);box-shadow:var(--shadow-sm)}
.pm-tab.active img{opacity:1}

/* ============= VARIANTS PILLS =================== */
.pm-pills{display:flex;gap:5px;overflow-x:auto;padding-bottom:5px;margin-bottom:7px}
.pm-pills::-webkit-scrollbar{display:none}
.pm-pill{
    padding:5px 12px;border-radius:18px;
    background:var(--gray-100);border:1.5px solid var(--gray-200);
    font-size:11px;font-weight:600;color:var(--gray-500);
    cursor:pointer;transition:all 0.2s;white-space:nowrap;flex-shrink:0;
}
.pm-pill input{display:none}
.pm-pill.active{background:var(--navy);color:var(--white);border-color:var(--navy);}

/* ============= INPUTS (with Autocomplete) ======= */
.pm-connector{position:relative;margin-bottom:7px}
.pm-input-wrap{position:relative;margin-bottom:4px}
.pm-input-wrap .icon{
    position:absolute;left:12px;top:50%;transform:translateY(-50%);
    font-size:13px;z-index:3;pointer-events:none;
}
.icon-origin{color:var(--success)}
.icon-dest{color:var(--danger)}
.icon-misc{color:var(--gray-500)}
.pm-input{
    width:100%;padding:11px 36px 11px 38px;
    border:1.5px solid var(--gray-200);border-radius:11px;
    font-size:13px;color:var(--navy);background:var(--gray-50);
    transition:all 0.2s;outline:none;font-weight:500;
    box-sizing:border-box;
}
.pm-input:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px var(--gold-pale);}
.pm-swap-btn{
    position:absolute;right:10px;top:50%;transform:translateY(-50%);
    background:var(--white);border:1.5px solid var(--gray-200);
    border-radius:7px;width:28px;height:28px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;color:var(--navy);font-size:12px;z-index:3;transition:all 0.2s;
}
.pm-swap-btn:hover{background:var(--gold);color:white;border-color:var(--gold)}
.pm-dash{
    width:2px;height:10px;
    background:repeating-linear-gradient(to bottom,var(--gray-200) 0,var(--gray-200) 3px,transparent 3px,transparent 6px);
    margin:0 0 3px 18px;
}

/* ============= AUTOCOMPLETE DROPDOWN ============= */
.pm-autocomplete-wrap{position:relative}
.pm-autocomplete-list{
    position:absolute;top:calc(100% + 3px);left:0;right:0;
    background:var(--white);border-radius:12px;
    box-shadow:0 8px 28px rgba(13,27,42,0.16);
    z-index:1000;max-height:240px;overflow-y:auto;
    border:1px solid var(--gray-200);display:none;
}
.pm-autocomplete-list.open{display:block;animation:slideDown 0.15s ease}
@keyframes slideDown{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:none}}
.pm-autocomplete-item{
    display:flex;align-items:center;gap:10px;
    padding:10px 12px;cursor:pointer;border-bottom:1px solid var(--gray-100);
    transition:background 0.15s;
}
.pm-autocomplete-item:last-child{border-bottom:none}
.pm-autocomplete-item:hover,.pm-autocomplete-item.highlighted{background:var(--gray-50)}
.pm-place-thumb{width:36px;height:36px;border-radius:8px;object-fit:cover;flex-shrink:0;background:var(--gray-100);}
.pm-place-icon-wrap{width:36px;height:36px;border-radius:8px;background:var(--gold-pale);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px;color:var(--gold);}
.pm-place-text{flex:1;min-width:0}
.pm-place-main{font-weight:600;font-size:12px;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pm-place-sub{font-size:10px;color:var(--gray-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pm-place-dist{font-size:10px;color:var(--gold);font-weight:600;flex-shrink:0}

/* ============= VEHICLE CARDS (compact) ========== */
.pm-vehicles{
    display:flex;gap:8px;overflow-x:auto;
    margin-bottom:6px;padding-bottom:4px;
}
.pm-vehicles::-webkit-scrollbar{display:none}
.pm-vcard{
    flex-shrink:0;min-width:100px;
    display:flex;flex-direction:column;align-items:center;
    padding:8px 8px;gap:4px;
    border:2px solid var(--gray-200);border-radius:12px;
    cursor:pointer;transition:all 0.2s;background:var(--white);
    position:relative;
}
.pm-vcard.active{border-color:var(--gold);background:var(--gold-pale)}
.pm-vcard input[type=radio]{display:none}
/* Smaller images for compact layout */
.pm-vcard-img{width:52px;height:34px;object-fit:contain}
.pm-vcard-name{font-weight:700;font-size:11px;color:var(--navy);white-space:nowrap}
.pm-vcard-seats{font-size:9px;color:var(--gray-500)}
.pm-vcard-price{font-weight:800;font-size:12px;color:var(--navy)}
.pm-vcard-badge{
    position:absolute;top:-5px;right:-5px;
    background:var(--gold);color:var(--navy);
    font-size:8px;font-weight:800;
    padding:1px 5px;border-radius:8px;white-space:nowrap;
}
.pm-vcard-eta{font-size:9px;color:var(--success);font-weight:600;display:flex;align-items:center;gap:2px;}

/* ============= ESTIMATE STRIP =================== */
.pm-estimate{
    border-radius:10px;padding:8px 12px;
    background:linear-gradient(135deg,var(--navy),var(--navy-3));
    display:none;justify-content:space-between;align-items:center;
    margin-bottom:6px;
}
.pm-estimate.visible{display:flex;animation:fadeIn 0.3s ease}
.pm-estimate-label{font-size:10px;color:var(--gray-400);font-weight:500}
.pm-estimate-val{font-size:14px;font-weight:800;color:var(--gold)}
.pm-estimate-block{text-align:center}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}

/* ============= STICKY BOOK BUTTON =============== */
.pm-book-btn{
    width:100%;padding:13px;
    background:linear-gradient(135deg,var(--gold),var(--gold-light));
    color:var(--navy);border:none;border-radius:12px;
    font-size:14px;font-weight:800;letter-spacing:0.3px;
    cursor:pointer;transition:all 0.2s;
    box-shadow:0 4px 16px rgba(201,168,76,0.4);
    position:relative;overflow:hidden;
}
.pm-book-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.15),transparent);}
.pm-book-btn:active{transform:scale(0.98);box-shadow:0 2px 8px rgba(201,168,76,0.3)}

/* ============= BOTTOM NAV ======================= */
.pm-bottom-nav{
    position:absolute;bottom:0;left:0;right:0;height:64px;
    background:var(--navy);
    display:flex;justify-content:space-around;align-items:center;
    z-index:20;padding-bottom:6px;
    border-top-left-radius:18px;border-top-right-radius:18px;
}
.pm-nav-item{
    display:flex;flex-direction:column;align-items:center;gap:3px;
    color:var(--gray-500);text-decoration:none;
    font-size:10px;font-weight:700;transition:color 0.2s;
    min-width:56px;
}
.pm-nav-item i{font-size:20px}
.pm-nav-item.active{color:var(--gold)}
.pm-nav-item:hover{color:var(--white);text-decoration:none}
/* Center FAB button */
.pm-nav-fab{
    width:52px;height:52px;border-radius:50%;
    background:linear-gradient(135deg,var(--gold),var(--gold-light));
    display:flex;align-items:center;justify-content:center;
    margin-top:-24px;
    box-shadow:0 4px 20px rgba(201,168,76,0.5);
    text-decoration:none;
    transition:transform 0.2s;
}
.pm-nav-fab:hover{transform:scale(1.08);text-decoration:none}
.pm-nav-fab i{font-size:22px;color:var(--navy)}

/* ============= LOADING SPINNER ================== */
.pm-spinner{
    display:inline-block;width:14px;height:14px;
    border:2px solid rgba(255,255,255,0.4);
    border-top-color:white;border-radius:50%;
    animation:spin 0.7s linear infinite;
    vertical-align:middle;margin-right:6px;
}
@keyframes spin{to{transform:rotate(360deg)}}

/* ============= NEARBY DRIVER DOT ================ */
.driver-marker-pulse{
    width:14px;height:14px;border-radius:50%;
    background:var(--gold);
    box-shadow:0 0 0 0 var(--gold-glow);
    animation:pulse 1.8s infinite;
}
@keyframes pulse{
    0%{box-shadow:0 0 0 0 rgba(201,168,76,0.6)}
    70%{box-shadow:0 0 0 10px rgba(201,168,76,0)}
    100%{box-shadow:0 0 0 0 rgba(201,168,76,0)}
}

/* ============= MAP ATTRIBUTION ================== */
.leaflet-control-attribution{font-size:9px!important}

/* ============= PREMIUM DRAWER ==================== */
.pm-drawer-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0, 0, 0, 0.4); z-index: 1000;
    opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
}
.pm-drawer-overlay.active {
    opacity: 1; pointer-events: auto;
}
.pm-drawer {
    position: fixed; top: 0; left: -280px; width: 280px; height: 100vh;
    background: var(--navy); z-index: 1001;
    box-shadow: 5px 0 25px rgba(0,0,0,0.3);
    display: flex; flex-direction: column;
    transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.pm-drawer.active {
    left: 0;
}
.pm-drawer-header {
    padding: 24px 16px; background: var(--navy-2);
    display: flex; align-items: center; gap: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.pm-drawer-avatar {
    width: 46px; height: 46px; border-radius: 50%;
    background: center/cover no-repeat; border: 2.5px solid var(--gold);
}
.pm-drawer-avatar-initials {
    width: 46px; height: 46px; border-radius: 50%;
    background: linear-gradient(135deg, var(--navy-3), var(--navy));
    border: 2.5px solid var(--gold);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--gold); font-size: 14px;
}
.pm-drawer-user-info { min-width: 0; }
.pm-drawer-name { font-weight: 700; font-size: 14px; color: var(--white); }
.pm-drawer-email { font-size: 11px; color: var(--gray-500); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.pm-drawer-body { flex: 1; overflow-y: auto; padding: 12px 8px; }
.pm-drawer-menu { list-style: none; padding: 0; margin: 0; }
.pm-drawer-menu li { margin-bottom: 4px; }
.pm-drawer-menu li a {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; border-radius: 10px;
    color: var(--gray-400); text-decoration: none;
    font-weight: 600; font-size: 13px; transition: all 0.2s;
}
.pm-drawer-menu li a i { font-size: 16px; width: 20px; text-align: center; color: var(--gold); }
.pm-drawer-menu li a:hover, .pm-drawer-menu li a.active {
    background: var(--navy-2); color: var(--white);
}
.pm-drawer-menu li.logout-item a { color: var(--danger); }
.pm-drawer-menu li.logout-item a i { color: var(--danger); }

.pm-badge-green {
    background: var(--success); color: white; padding: 2px 8px;
    border-radius: 10px; font-size: 10px; margin-left: auto;
}
</style>
@endsection

@section('content')
<div id="pm-wrapper">

    <div class="pm-drawer-overlay" id="pm-drawer-overlay" onclick="toggleDrawer()"></div>
    <div class="pm-drawer" id="pm-drawer">
        <div class="pm-drawer-header">
            @if(Auth::user()->picture)
                <div class="pm-drawer-avatar" style="background-image:url('{{ img(Auth::user()->picture) }}')"></div>
            @else
                <div class="pm-drawer-avatar-initials">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}{{ strtoupper(substr(Auth::user()->last_name,0,1)) }}</div>
            @endif
            <div class="pm-drawer-user-info">
                <div class="pm-drawer-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                <div class="pm-drawer-email">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <div class="pm-drawer-body">
            <ul class="pm-drawer-menu">
                <li><a href="{{ url('dashboard') }}" class="active"><i class="fa fa-home"></i> Accueil</a></li>
                <li><a href="{{ url('wallet') }}"><i class="fa fa-leaf" style="color: #2ecc71;"></i> ECO/CFA Wallet <span class="pm-badge-green">{{ Auth::user()->eco_token_balance ?? '0' }}</span></a></li>
                <li><a href="{{ url('dao/proposals') }}"><i class="fa fa-university"></i> Governance DAO</a></li>
                <li><a href="{{ url('trips') }}"><i class="fa fa-qrcode"></i> Tickets QR</a></li>
                <li><a href="{{ url('trips') }}"><i class="fa fa-history"></i> Historique trajets</a></li>
                <li><a href="{{ url('upcoming/trips') }}"><i class="fa fa-calendar"></i> Trajets planifiés</a></li>
                <li><a href="{{ url('profile') }}"><i class="fa fa-user"></i> Mon Profil</a></li>
                <li><a href="{{ url('change/password') }}"><i class="fa fa-cog"></i> Changer mot de passe</a></li>
                <li><a href="{{ url('payment') }}"><i class="fa fa-credit-card"></i> Mode de paiement</a></li>
                <li><a href="{{ url('promotions') }}"><i class="fa fa-tag"></i> Code Promos</a></li>
                <li class="logout-item">
                    <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-drawer').submit();">
                        <i class="fa fa-sign-out"></i> Déconnexion
                    </a>
                </li>
            </ul>
            <form id="logout-form-drawer" action="{{ url('/logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
    </div>

    {{-- MAP --}}
    <div id="pm-map"></div>

    {{-- TOP BAR --}}
    <div class="pm-top-bar">
        <div class="pm-user-info" onclick="toggleDrawer()" style="cursor:pointer">
            @if(Auth::user()->picture)
                <div class="pm-avatar" style="background-image:url('{{ img(Auth::user()->picture) }}')"></div>
            @else
                <div class="pm-avatar-initials">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}{{ strtoupper(substr(Auth::user()->last_name,0,1)) }}</div>
            @endif
            <div>
                <div class="pm-name">{{ Auth::user()->first_name }}</div>
                <div class="pm-subtitle">Où allons-nous ?</div>
            </div>
        </div>
        <div class="pm-top-actions">
            <div class="pm-wallet">
                <i class="fa fa-money"></i> {{ currency(Auth::user()->wallet_balance) }}
            </div>
            <a href="{{ url('notifications') }}" class="pm-notif-btn">
                <i class="fa fa-bell"></i>
                <span class="badge"></span>
            </a>
        </div>
    </div>

    {{-- RECENTRER --}}
    <button class="pm-recenter-btn" id="pm-recenter" title="Ma position">
        <i class="fa fa-crosshairs"></i>
    </button>

    {{-- BOTTOM SHEET --}}
    <div class="pm-sheet" id="pm-sheet">
        <div class="pm-drag-handle" id="pm-drag"></div>
        <div class="pm-sheet-scroll">

            {{-- Quick Stats --}}
            <div class="pm-stats-row">
                @if(Auth::user()->eco_token_balance > 0)
                <div class="pm-stat-chip eco">
                    <i class="fa fa-leaf"></i> ECO {{ Auth::user()->eco_token_balance }}
                </div>
                @endif
                <div class="pm-stat-chip" id="drivers-count-chip" style="display:none">
                    <i class="fa fa-taxi" style="color:var(--gold)"></i>
                    <span id="drivers-count">0</span> chauffeurs disponibles
                </div>
            </div>

            <form action="{{ url('confirm/ride') }}" method="GET" id="booking-form" onkeypress="return event.key!='Enter'">
                <input type="hidden" name="ride_variant" id="selected_ride_variant" value="prive">
                <input type="hidden" name="s_latitude"       id="origin_latitude">
                <input type="hidden" name="s_longitude"      id="origin_longitude">
                <input type="hidden" name="d_latitude"       id="destination_latitude">
                <input type="hidden" name="d_longitude"      id="destination_longitude">
                <input type="hidden" name="current_latitude" id="lat">
                <input type="hidden" name="current_longitude" id="long">
                <input type="hidden" name="trip_o_lat" id="trip_o_lat">
                <input type="hidden" name="trip_o_lng" id="trip_o_lng">
                <input type="hidden" name="trip_d_lat" id="trip_d_lat">
                <input type="hidden" name="trip_d_lng" id="trip_d_lng">
                <input type="hidden" name="rental_lat" id="rental_lat">
                <input type="hidden" name="rental_lng" id="rental_lng">
                <input type="hidden" name="shared_s_latitude"  id="shared_origin_latitude">
                <input type="hidden" name="shared_s_longitude" id="shared_origin_longitude">
                <input type="hidden" name="shared_d_latitude"  id="shared_destination_latitude">
                <input type="hidden" name="shared_d_longitude" id="shared_destination_longitude">

                {{-- CATEGORY TABS --}}
                <div class="pm-tabs" role="tablist">
                    @foreach($categories as $category)
                    <button type="button" class="pm-tab {{ $loop->first ? 'active' : '' }}"
                            onclick="pmSwitchTab({{ $category->id }})"
                            id="pm-tab-{{ $category->id }}" role="tab">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}">
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>

                {{-- PANES --}}
                @foreach($categories as $category)
                <div id="pm-pane-{{ $category->id }}" class="pm-pane" style="display:{{ $loop->first ? 'block' : 'none' }}">

                    {{-- Variant Pills --}}
                    <div class="pm-pills" id="pills-{{ $category->id }}">
                        <label class="pm-pill active" onclick="pmSetVariant({{ $category->id }},'prive',this)">
                            <input type="radio" name="var_{{ $category->id }}" value="prive" checked> 🚗 Privé
                        </label>
                        <label class="pm-pill" onclick="pmSetVariant({{ $category->id }},'partage',this)">
                            <input type="radio" name="var_{{ $category->id }}" value="partage"> 👥 Partage
                        </label>
                        <label class="pm-pill" onclick="pmSetVariant({{ $category->id }},'arret_pdp',this)">
                            <input type="radio" name="var_{{ $category->id }}" value="arret_pdp"> 🚌 Gare-à-Gare
                        </label>
                        <label class="pm-pill" onclick="pmSetVariant({{ $category->id }},'arret_hybride',this)">
                            <input type="radio" name="var_{{ $category->id }}" value="arret_hybride"> 📍 Dernier Km
                        </label>
                    </div>

                    {{-- INPUTS --}}
                    @if(strtolower($category->name) == 'location')
                        <div class="pm-autocomplete-wrap">
                            <div class="pm-input-wrap">
                                <i class="fa fa-map-marker icon icon-origin"></i>
                                <input type="text" class="pm-input pm-autocomplete-input"
                                    id="rental_location_{{ $category->id }}" name="rental_location"
                                    placeholder="Adresse de prise en charge"
                                    autocomplete="off"
                                    data-target="origin">
                            </div>
                            <div class="pm-autocomplete-list" id="ac-rental-{{ $category->id }}"></div>
                        </div>
                        <div class="pm-input-wrap">
                            <i class="fa fa-clock-o icon icon-misc"></i>
                            <select class="pm-input" name="package_id" style="padding-left:42px">
                                <option value="0">Choisir un forfait</option>
                                @foreach($package as $p)
                                    <option value="{{ $p->id }}">{{ $p->kilometer }} Km — {{ $p->hour }}H</option>
                                @endforeach
                            </select>
                        </div>

                    @elseif(strtolower($category->name) == 'voyage' || strtolower($category->name) == 'outstation')
                        <div class="pm-connector">
                            <div class="pm-autocomplete-wrap">
                                <div class="pm-input-wrap">
                                    <i class="fa fa-dot-circle-o icon icon-origin"></i>
                                    <input type="text" class="pm-input pm-autocomplete-input"
                                        id="o_trip_tab_{{ $category->id }}" name="o_trip_tab"
                                        placeholder="Ville de départ" autocomplete="off"
                                        data-target="origin" data-cat="{{ $category->id }}">
                                </div>
                                <div class="pm-autocomplete-list" id="ac-o-{{ $category->id }}"></div>
                            </div>
                            <div class="pm-dash"></div>
                            <div class="pm-autocomplete-wrap">
                                <div class="pm-input-wrap">
                                    <i class="fa fa-map-marker icon icon-dest"></i>
                                    <input type="text" class="pm-input pm-autocomplete-input"
                                        id="d_trip_tab_{{ $category->id }}" name="d_trip_tab"
                                        placeholder="Ville de destination" autocomplete="off"
                                        data-target="destination" data-cat="{{ $category->id }}">
                                </div>
                                <div class="pm-autocomplete-list" id="ac-d-{{ $category->id }}"></div>
                            </div>
                        </div>
                        <label style="font-weight:600;font-size:13px;margin-bottom:10px;display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="round_trip" value="1" style="accent-color:var(--gold);width:16px;height:16px">
                            Aller-Retour
                        </label>

                    @else
                        {{-- Default: Taxi / Livraison --}}
                        <div class="pm-connector" style="position:relative">
                            <div class="pm-autocomplete-wrap">
                                <div class="pm-input-wrap">
                                    <i class="fa fa-dot-circle-o icon icon-origin"></i>
                                    <input type="text" class="pm-input pm-autocomplete-input"
                                        id="origin-input-{{ $category->id }}" name="s_address"
                                        placeholder="Point de départ" autocomplete="off"
                                        data-target="origin" data-cat="{{ $category->id }}">
                                </div>
                                <div class="pm-autocomplete-list" id="ac-origin-{{ $category->id }}"></div>
                            </div>
                            <div class="pm-dash"></div>
                            <div class="pm-autocomplete-wrap">
                                <div class="pm-input-wrap">
                                    <i class="fa fa-map-marker icon icon-dest"></i>
                                    <input type="text" class="pm-input pm-autocomplete-input"
                                        id="dest-input-{{ $category->id }}" name="d_address"
                                        placeholder="Où allez-vous ?" autocomplete="off"
                                        data-target="destination" data-cat="{{ $category->id }}">
                                </div>
                                <div class="pm-autocomplete-list" id="ac-dest-{{ $category->id }}"></div>
                            </div>
                            {{-- Swap origin/destination --}}
                            <button type="button" class="pm-swap-btn"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%)"
                                onclick="pmSwapAddresses({{ $category->id }})">
                                <i class="fa fa-exchange fa-rotate-90"></i>
                            </button>
                        </div>
                    @endif

                    {{-- Estimate strip --}}
                    <div class="pm-estimate" id="estimate-{{ $category->id }}">
                        <div class="pm-estimate-block">
                            <div class="pm-estimate-label">Distance</div>
                            <div class="pm-estimate-val" id="est-dist-{{ $category->id }}">—</div>
                        </div>
                        <div class="pm-estimate-block">
                            <div class="pm-estimate-label">Durée</div>
                            <div class="pm-estimate-val" id="est-dur-{{ $category->id }}">—</div>
                        </div>
                        <div class="pm-estimate-block">
                            <div class="pm-estimate-label">Prix estimé</div>
                            <div class="pm-estimate-val" id="est-price-{{ $category->id }}">—</div>
                        </div>
                    </div>

                    {{-- Vehicle Cards --}}
                    <div class="pm-vehicles" id="grid-{{ $category->id }}">
                        @foreach($category->serviceTypes as $service)
                            @php
                                $variants = is_array($service->allowed_variants)
                                    ? $service->allowed_variants
                                    : json_decode($service->allowed_variants, true) ?? [];
                            @endphp
                            <label class="pm-vcard variant-card {{ $loop->first ? 'active' : '' }}"
                                   data-variants="{{ json_encode($variants) }}"
                                   for="svc_{{ $service->id }}">
                                @if($loop->first)
                                    <span class="pm-vcard-badge">⭐ Populaire</span>
                                @endif
                                <input type="radio" name="service_type" value="{{ $service->id }}"
                                       id="svc_{{ $service->id }}"
                                       {{ $loop->first ? 'checked' : '' }}
                                       onchange="pmSelectVehicle(this)">
                                <img class="pm-vcard-img" src="{{ $service->image_url }}" alt="{{ $service->name }}">
                                <div class="pm-vcard-name">{{ $service->name }}</div>
                                <div class="pm-vcard-seats"><i class="fa fa-user"></i> 1–{{ $service->capacity }}</div>
                                <div class="pm-vcard-price">{{ currency($service->price) }}</div>
                                <div class="pm-vcard-eta"><i class="fa fa-clock-o"></i> ~{{ rand(3,8) }} min</div>
                            </label>
                        @endforeach
                        @if($category->serviceTypes->isEmpty())
                            <div style="font-size:12px;color:var(--gray-500);padding:20px 0">
                                Aucun véhicule disponible pour cette catégorie.
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach

            </form>
        </div>
        {{-- Sticky footer with the Reserve button, always visible --}}
        <div class="pm-sheet-footer">
            <button type="submit" form="booking-form" class="pm-book-btn" id="pm-book-btn">
                🚕 Réserver maintenant
            </button>
        </div>
    </div>

    {{-- BOTTOM NAV --}}
    <nav class="pm-bottom-nav">
        <a href="{{ url('dashboard') }}" class="pm-nav-item active">
            <i class="fa fa-home"></i> Accueil
        </a>
        <a href="{{ url('trips') }}" class="pm-nav-item">
            <i class="fa fa-history"></i> Trajets
        </a>
        <a href="{{ url('confirm/ride') }}" class="pm-nav-fab">
            <i class="fa fa-taxi"></i>
        </a>
        <a href="{{ url('wallet') }}" class="pm-nav-item">
            <i class="fa fa-google-wallet"></i> Wallet
        </a>
        <a href="javascript:void(0)" onclick="toggleDrawer()" class="pm-nav-item">
            <i class="fa fa-user"></i> Profil
        </a>
    </nav>

</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
/* ===========================================================
   PICME DASHBOARD v3 — Complete JS
   =========================================================== */

// ── Globals ──────────────────────────────────────────────────
var CUR_LAT = 5.3599517, CUR_LNG = -4.0082563;
var mapInst, userMarker, routeLine, destMarker;
var driverMarkers = [];
var acTimers = {};

// ── MAP INIT ─────────────────────────────────────────────────
function initMap() {
    mapInst = L.map('pm-map', {
        zoomControl: false,
        attributionControl: true
    }).setView([CUR_LAT, CUR_LNG], 14);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap, © CARTO',
        maxZoom: 19
    }).addTo(mapInst);

    // User marker (pulsating dot)
    var userIcon = L.divIcon({
        className: '',
        html: '<div style="width:16px;height:16px;background:#3498db;border-radius:50%;border:3px solid white;box-shadow:0 0 8px rgba(52,152,219,0.6)"></div>',
        iconSize: [16, 16], iconAnchor: [8, 8]
    });
    userMarker = L.marker([CUR_LAT, CUR_LNG], { icon: userIcon }).addTo(mapInst);

    // Drag to update origin
    userMarker.on('dragend', function(e) {
        var p = e.target.getLatLng();
        CUR_LAT = p.lat; CUR_LNG = p.lng;
        updateOriginCoords(p.lat, p.lng);
        reverseGeocode(p.lat, p.lng, 'origin');
    });

    // Recenter button
    document.getElementById('pm-recenter').addEventListener('click', function() {
        mapInst.flyTo([CUR_LAT, CUR_LNG], 15, { duration: 0.8 });
    });

    // Load nearby drivers
    loadNearbyDrivers();
    setInterval(loadNearbyDrivers, 30000);
}

// ── GEOLOCATION (centred autocomplete) ──────────────────────
var GEO_LAT = 5.3599517, GEO_LNG = -4.0082563; // Default: Abidjan

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        GEO_LAT = pos.coords.latitude;
        GEO_LNG = pos.coords.longitude;
        CUR_LAT = GEO_LAT;
        CUR_LNG = GEO_LNG;
        updateOriginCoords(CUR_LAT, CUR_LNG);
        initMap();
        reverseGeocode(CUR_LAT, CUR_LNG, 'origin');
    }, function() { initMap(); }, { timeout: 8000 });
} else { initMap(); }

function updateOriginCoords(lat, lng) {
    ['origin_latitude','lat','shared_origin_latitude','trip_o_lat','rental_lat'].forEach(function(id) {
        var el = document.getElementById(id); if(el) el.value = lat;
    });
    ['origin_longitude','long','shared_origin_longitude','trip_o_lng','rental_lng'].forEach(function(id) {
        var el = document.getElementById(id); if(el) el.value = lng;
    });
}

// ── NEARBY DRIVERS ────────────────────────────────────────────
function loadNearbyDrivers() {
    fetch('/api/show/providers', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : '' }
    })
    .then(function(r) { if(!r.ok) throw new Error(); return r.json(); })
    .then(function(data) {
        var providers = data.providers || data.data || [];
        // Clear old markers
        driverMarkers.forEach(function(m) { mapInst.removeLayer(m); });
        driverMarkers = [];

        var count = 0;
        providers.slice(0, 20).forEach(function(driver) {
            if (!driver.latitude || !driver.longitude) return;
            count++;
            var icon = L.divIcon({
                className: '',
                html: '<div class="driver-marker-pulse"></div>',
                iconSize: [14, 14], iconAnchor: [7, 7]
            });
            var m = L.marker([driver.latitude, driver.longitude], { icon: icon });
            m.bindPopup('<b>' + (driver.first_name || 'Chauffeur') + '</b><br><small>En ligne</small>', { offset: [0, -6] });
            m.addTo(mapInst);
            driverMarkers.push(m);
        });

        // Update chip
        if (count > 0) {
            document.getElementById('drivers-count').textContent = count;
            document.getElementById('drivers-count-chip').style.display = 'flex';
        }
    })
    .catch(function() {}); // Silently fail — no drivers API or auth required
}

// ── AUTOCOMPLETE (Photon/Nominatim avec images) ───────────────
var PHOTON_URL = '{{ env("PHOTON_URL", "https://photon.komoot.io") }}';

function pmAutocomplete(inputId, listId, target, catId) {
    var input = document.getElementById(inputId);
    var list  = document.getElementById(listId);
    if (!input || !list) return;

    input.addEventListener('input', function() {
        var q = this.value.trim();
        clearTimeout(acTimers[inputId]);
        if (q.length < 2) { list.classList.remove('open'); return; }
        acTimers[inputId] = setTimeout(function() {
            fetchSuggestions(q, list, input, target, catId);
        }, 280);
    });

    input.addEventListener('focus', function() {
        if (this.value.length >= 2) list.classList.add('open');
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            list.classList.remove('open');
        }
    });
}

function fetchSuggestions(query, list, input, target, catId) {
    // Use precise geolocation coords for proximity-based ranking
    var lat  = (typeof GEO_LAT !== 'undefined') ? GEO_LAT : CUR_LAT;
    var lng  = (typeof GEO_LNG !== 'undefined') ? GEO_LNG : CUR_LNG;
    var url = '/places/search?q=' + encodeURIComponent(query)
            + '&lat=' + lat + '&lng=' + lng
            + '&limit=8';

    fetch(url)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        list.innerHTML = '';
        var features = data.features || [];
        if (!features.length) { list.classList.remove('open'); return; }

        features.forEach(function(feat, idx) {
            var props = feat.properties || {};
            var coords = feat.geometry && feat.geometry.coordinates ? feat.geometry.coordinates : [CUR_LNG, CUR_LAT];
            var lng = coords[0], lat = coords[1];

            var mainName = props.name || props.street || props.city || query;
            var subName  = [props.city, props.state, props.country].filter(Boolean).join(', ');

            // Calculate distance
            var dist = haversineKm(CUR_LAT, CUR_LNG, lat, lng);
            var distStr = dist < 1 ? Math.round(dist * 1000) + 'm' : dist.toFixed(1) + 'km';

            // Determine icon from OSM type
            var iconEmoji = getPlaceIcon(props.osm_value || props.type || '');

            var item = document.createElement('div');
            item.className = 'pm-autocomplete-item';
            if (idx === 0) item.classList.add('highlighted');

            // Try Wikimedia Commons thumbnail via Nominatim lookup (async, best effort)
            var thumbHtml = '<div class="pm-place-icon-wrap">' + iconEmoji + '</div>';

            item.innerHTML = thumbHtml +
                '<div class="pm-place-text">' +
                    '<div class="pm-place-main">' + escHtml(mainName) + '</div>' +
                    '<div class="pm-place-sub">'  + escHtml(subName)  + '</div>' +
                '</div>' +
                '<div class="pm-place-dist">' + distStr + '</div>';

            item.addEventListener('click', function() {
                input.value = mainName + (subName ? ', ' + subName.split(',')[0] : '');
                list.classList.remove('open');
                setCoords(target, catId, lat, lng);
                updateMapForTarget(target, catId, lat, lng, mainName);
            });

            list.appendChild(item);

            // Async: enrich first result with Nominatim image
            if (idx === 0 && props.osm_id) {
                enrichWithImage(props.osm_id, props.osm_type, item);
            }
        });

        list.classList.add('open');
    })
    .catch(function() {});
}

function enrichWithImage(osmId, osmType, item) {
    if (!osmId || !osmType) return;
    var typeChar = osmType === 'node' ? 'N' : osmType === 'way' ? 'W' : 'R';
    fetch('https://nominatim.openstreetmap.org/lookup?osm_ids=' + typeChar + osmId + '&format=json', {
        headers: { 'Accept-Language': 'fr' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var res = data[0];
        if (res && res.extratags && res.extratags.image) {
            var iconWrap = item.querySelector('.pm-place-icon-wrap');
            if (iconWrap) {
                iconWrap.outerHTML =
                    '<img class="pm-place-thumb" src="' + res.extratags.image + '" '
                    + 'onerror="this.style.display=\'none\'" alt="">';
            }
        }
    })
    .catch(function() {});
}

function getPlaceIcon(type) {
    var icons = {
        restaurant: '🍽️', cafe: '☕', hotel: '🏨', hospital: '🏥',
        pharmacy: '💊', school: '🏫', university: '🎓', bank: '🏦',
        supermarket: '🛒', fuel: '⛽', airport: '✈️', bus_stop: '🚌',
        station: '🚉', park: '🌳', beach: '🏖️', museum: '🏛️',
        stadium: '🏟️', mosque: '🕌', church: '⛪', government: '🏛️'
    };
    return icons[type] || '📍';
}

function setCoords(target, catId, lat, lng) {
    if (target === 'origin') {
        updateOriginCoords(lat, lng);
    } else {
        ['destination_latitude','shared_destination_latitude','trip_d_lat'].forEach(function(id) {
            var el = document.getElementById(id); if(el) el.value = lat;
        });
        ['destination_longitude','shared_destination_longitude','trip_d_lng'].forEach(function(id) {
            var el = document.getElementById(id); if(el) el.value = lng;
        });
    }
    // Try to compute estimate if both are set
    tryComputeEstimate(catId);
}

// ── MAP UPDATE ────────────────────────────────────────────────
function updateMapForTarget(target, catId, lat, lng, label) {
    if (!mapInst) return;

    if (target === 'origin') {
        userMarker.setLatLng([lat, lng]);
        mapInst.panTo([lat, lng]);
    } else {
        if (destMarker) mapInst.removeLayer(destMarker);
        var destIcon = L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;background:var(--danger,#e74c3c);border-radius:50%;border:3px solid white;box-shadow:0 0 8px rgba(231,76,60,0.6)"></div>',
            iconSize: [14, 14], iconAnchor: [7, 7]
        });
        destMarker = L.marker([lat, lng], { icon: destIcon }).bindPopup(label).addTo(mapInst);

        // Draw route
        var oLat = document.getElementById('origin_latitude').value;
        var oLng = document.getElementById('origin_longitude').value;
        if (oLat && oLng) {
            drawRoute(parseFloat(oLat), parseFloat(oLng), lat, lng, catId);
        }
        mapInst.fitBounds([[parseFloat(oLat || CUR_LAT), parseFloat(oLng || CUR_LNG)], [lat, lng]], { padding: [60, 60] });
    }
}

function drawRoute(oLat, oLng, dLat, dLng, catId) {
    var osrmUrl = 'https://router.project-osrm.org/route/v1/driving/'
        + oLng + ',' + oLat + ';' + dLng + ',' + dLat
        + '?overview=full&geometries=geojson';

    fetch(osrmUrl)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.routes || !data.routes[0]) return;
        var route = data.routes[0];
        var geojson = route.geometry;
        var dist = (route.distance / 1000).toFixed(1);
        var dur  = Math.round(route.duration / 60);

        if (routeLine) mapInst.removeLayer(routeLine);
        routeLine = L.geoJSON(geojson, {
            style: { color: '#C9A84C', weight: 4, opacity: 0.8, lineCap: 'round' }
        }).addTo(mapInst);

        // Update estimate strip
        document.getElementById('est-dist-' + catId).textContent = dist + ' km';
        document.getElementById('est-dur-' + catId).textContent  = dur + ' min';
        // Rough price estimate (base_price * dist)
        document.getElementById('est-price-' + catId).textContent = 'voir tarifs';
        document.getElementById('estimate-' + catId).classList.add('visible');
    })
    .catch(function() {});
}

function tryComputeEstimate(catId) {
    var oLat = parseFloat(document.getElementById('origin_latitude').value);
    var oLng = parseFloat(document.getElementById('origin_longitude').value);
    var dLat = parseFloat(document.getElementById('destination_latitude').value);
    var dLng = parseFloat(document.getElementById('destination_longitude').value);
    if (oLat && oLng && dLat && dLng) {
        drawRoute(oLat, oLng, dLat, dLng, catId);
    }
}

// ── REVERSE GEOCODE ───────────────────────────────────────────
function reverseGeocode(lat, lng, target) {
    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&accept-language=fr')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data || !data.display_name) return;
        var short = data.display_name.split(',').slice(0, 3).join(', ');
        // Fill visible origin inputs
        document.querySelectorAll('[data-target="origin"]').forEach(function(el) {
            if (!el.value) el.value = short;
        });
    })
    .catch(function() {});
}

// ── TABS ──────────────────────────────────────────────────────
function pmSwitchTab(id) {
    document.querySelectorAll('.pm-pane').forEach(function(p) { p.style.display = 'none'; });
    document.querySelectorAll('.pm-tab').forEach(function(t) { t.classList.remove('active'); });
    var pane = document.getElementById('pm-pane-' + id);
    var tab  = document.getElementById('pm-tab-' + id);
    if (pane) pane.style.display = 'block';
    if (tab)  tab.classList.add('active');
    // Reset variant to prive
    pmSetVariant(id, 'prive', document.querySelector('#pills-' + id + ' .pm-pill'));
}

// ── VARIANTS ──────────────────────────────────────────────────
function pmSetVariant(catId, variant, el) {
    document.getElementById('selected_ride_variant').value = variant;
    var pills = document.getElementById('pills-' + catId);
    if (pills) pills.querySelectorAll('.pm-pill').forEach(function(p) { p.classList.remove('active'); });
    if (el) el.classList.add('active');

    var grid = document.getElementById('grid-' + catId);
    if (!grid) return;
    var first = null;
    grid.querySelectorAll('.variant-card').forEach(function(card) {
        var v = card.getAttribute('data-variants');
        var allowed = [];
        try { allowed = JSON.parse(v) || []; } catch(e) {}
        var show = allowed.includes(variant) || (variant === 'prive' && allowed.length === 0) || !v || v === 'null';
        card.style.display = show ? 'flex' : 'none';
        if (!show) { card.classList.remove('active'); var inp = card.querySelector('input'); if(inp) inp.checked = false; }
        else if (!first) first = card;
    });
    if (first) {
        var inp = first.querySelector('input'); if(inp) { inp.checked = true; first.classList.add('active'); }
    }
}

// ── VEHICLE SELECTION ─────────────────────────────────────────
function pmSelectVehicle(radio) {
    document.querySelectorAll('.pm-vcard').forEach(function(c) { c.classList.remove('active'); });
    var label = document.querySelector('label[for="' + radio.id + '"]');
    if (label) label.classList.add('active');
}

// ── SWAP ORIGIN/DESTINATION ───────────────────────────────────
function pmSwapAddresses(catId) {
    var originInput = document.getElementById('origin-input-' + catId);
    var destInput   = document.getElementById('dest-input-' + catId);
    if (!originInput || !destInput) return;

    var tmpVal = originInput.value; originInput.value = destInput.value; destInput.value = tmpVal;

    var oLat = document.getElementById('origin_latitude').value;
    var oLng = document.getElementById('origin_longitude').value;
    var dLat = document.getElementById('destination_latitude').value;
    var dLng = document.getElementById('destination_longitude').value;

    document.getElementById('origin_latitude').value      = dLat;
    document.getElementById('origin_longitude').value     = dLng;
    document.getElementById('destination_latitude').value = oLat;
    document.getElementById('destination_longitude').value = oLng;

    tryComputeEstimate(catId);
}

// ── UTILS ─────────────────────────────────────────────────────
function haversineKm(lat1, lng1, lat2, lng2) {
    var R = 6371;
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLng = (lng2 - lng1) * Math.PI / 180;
    var a = Math.sin(dLat/2)*Math.sin(dLat/2)
        + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)*Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── DRAG SHEET ────────────────────────────────────────────────
(function() {
    var sheet   = document.getElementById('pm-sheet');
    var handle  = document.getElementById('pm-drag');
    var startY  = 0, startTranslate = 0, isDragging = false;

    handle.addEventListener('touchstart', function(e) {
        isDragging = true;
        startY = e.touches[0].clientY;
        var t = sheet.style.transform;
        startTranslate = t ? parseInt(t.replace(/[^-0-9]/g,'')) : 0;
    }, { passive: true });

    document.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        var dy = e.touches[0].clientY - startY;
        var newT = Math.max(-200, Math.min(200, startTranslate + dy));
        sheet.style.transform = 'translateY(' + newT + 'px)';
    }, { passive: true });

    document.addEventListener('touchend', function() {
        if (!isDragging) return;
        isDragging = false;
        var t = sheet.style.transform;
        var val = t ? parseInt(t.replace(/[^-0-9]/g,'')) : 0;
        sheet.style.transition = 'transform 0.3s ease';
        if (val > 80) {
            sheet.style.transform = 'translateY(70%)'; // collapsed
        } else {
            sheet.style.transform = 'translateY(0)'; // expanded
        }
        setTimeout(function() { sheet.style.transition = ''; }, 300);
    });
})();

// ── DRAWER TOGGLE ────────────────────────────────────────────
function toggleDrawer() {
    var overlay = document.getElementById('pm-drawer-overlay');
    var drawer  = document.getElementById('pm-drawer');
    if (overlay && drawer) {
        overlay.classList.toggle('active');
        drawer.classList.toggle('active');
    }
}

// ── AUTOCOMPLETE WIRING ───────────────────────────────────────
window.addEventListener('load', function() {
    @foreach($categories as $category)
    @php $cid = $category->id; @endphp
    @if(strtolower($category->name) == 'location')
        pmAutocomplete('rental_location_{{ $cid }}', 'ac-rental-{{ $cid }}', 'origin', {{ $cid }});
    @elseif(strtolower($category->name) == 'voyage' || strtolower($category->name) == 'outstation')
        pmAutocomplete('o_trip_tab_{{ $cid }}', 'ac-o-{{ $cid }}', 'origin',      {{ $cid }});
        pmAutocomplete('d_trip_tab_{{ $cid }}', 'ac-d-{{ $cid }}', 'destination', {{ $cid }});
    @else
        pmAutocomplete('origin-input-{{ $cid }}', 'ac-origin-{{ $cid }}', 'origin',      {{ $cid }});
        pmAutocomplete('dest-input-{{ $cid }}',   'ac-dest-{{ $cid }}',   'destination', {{ $cid }});
    @endif
    @endforeach

    // Init first tab variant filter
    @if($categories->isNotEmpty())
    pmSetVariant({{ $categories->first()->id }}, 'prive',
        document.querySelector('#pills-{{ $categories->first()->id }} .pm-pill'));
    @endif
});
</script>
@endsection
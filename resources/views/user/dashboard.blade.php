@extends('user.layout.user_dashboard', ['active' => 'vtc'])

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
    --navy-2:     #1B263B;
    --navy-3:     #415A77;
    --lime:       #22c55e; /* Vibrant premium green */
    --lime-dark:  #15803d; /* Deeper contrast green */
    --lime-glow:  rgba(34,197,94,0.4);
    --gold:       #C9A84C;
    --gold-light: #E2C06E;
    --gold-pale:  rgba(201,168,76,0.12);
    --gold-glow:  rgba(201,168,76,0.4);
    --white:      #ffffff;
    --gray-50:    #f8fafc;
    --gray-100:   #f1f5f9;
    --gray-200:   #e2e8f0;
    --gray-400:   #64748b; /* Improved readability */
    --gray-500:   #475569; /* Improved readability for inactive text */
    --success:    #27ae60;
    --danger:     #e74c3c;
    --radius:     20px;
    --radius-sm:  10px;
    --shadow:     0 -6px 32px rgba(13,27,42,0.16);
    --shadow-sm:  0 2px 12px rgba(13,27,42,0.08);
    --sheet-min:  20vh;
    --sheet-mid:  40vh;
    --sheet-max:  85vh;
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
    width:100%;height:100vh;
    z-index:1;
}
#pm-map::after{
    content:'';
    position:absolute;bottom:0;left:0;right:0;height:60px;
    background:linear-gradient(transparent,rgba(255,255,255,0.65));
    z-index:2;pointer-events:none;
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
    height:var(--sheet-max); /* Always full height internally, we slide it down */
    transform: translateY(calc(var(--sheet-max) - var(--sheet-mid))); /* Start at mid */
    background:var(--white);
    border-top-left-radius:var(--radius);
    border-top-right-radius:var(--radius);
    box-shadow:var(--shadow);
    z-index:10;
    display:flex;flex-direction:column;
    transition:transform 0.35s cubic-bezier(0.175,0.885,0.32,1.2);
    will-change:transform;
}
.pm-sheet.dragging {
    transition: none; /* remove transition when dragging */
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

/* ============= CATEGORY TABS (HORIZONTAL SCROLL) ======= */
.pm-tabs{
    display:flex;gap:8px;overflow-x:auto;
    margin:10px 12px;padding-bottom:6px;
}
.pm-tabs::-webkit-scrollbar{display:none;}
.pm-tab{
    flex-shrink:0;min-width:75px;
    padding:6px 4px 8px;border-radius:12px;
    background:var(--gray-100);border:1.5px solid transparent;
    color:var(--navy);font-weight:800;font-size:11px;
    cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:4px;
    transition:all 0.2s;text-align:center;
}
.pm-tab-label {
    background: rgba(255,255,255,0.7);
    padding: 2px 6px;
    border-radius: 6px;
    width: 95%;
}
.pm-tab.active {
    background:var(--lime);color:var(--navy);border-color:var(--lime-dark);box-shadow:0 4px 10px var(--lime-glow);
}
.pm-tab.active .pm-tab-label {
    background: transparent;
}
.pm-tab img{width:26px;height:26px;object-fit:contain;opacity:0.6;transition:opacity 0.2s}
.pm-tab.active img{opacity:1}

/* ============= VARIANTS PILLS =================== */
.pm-pills{display:flex;gap:8px;padding-bottom:5px;margin-bottom:4px}
.pm-pills::-webkit-scrollbar{display:none}
.pm-pill{
    flex:1;text-align:center;
    padding:6px 4px;border-radius:12px;
    background:var(--gray-100);border:1.5px solid var(--gray-200);
    font-size:11px;font-weight:700;color:var(--gray-500);
    cursor:pointer;transition:all 0.2s;white-space:nowrap;
}
.pm-pill input{display:none}
.pm-pill.active{background:var(--lime);color:var(--navy);border-color:var(--lime-dark);box-shadow:0 2px 6px var(--lime-glow);}

/* ============= INPUTS (with Autocomplete) ======= */
.pm-connector{position:relative;margin-bottom:2px}
.pm-input-wrap{position:relative;margin-bottom:2px}
.pm-input-wrap .icon{
    position:absolute;left:10px;top:50%;transform:translateY(-50%);
    font-size:11px;z-index:3;pointer-events:none;
}
.icon-origin{color:var(--success)}
.icon-dest{color:var(--danger)}
.icon-misc{color:var(--gray-500)}
.pm-input{
    width:100%;padding:8px 42px 8px 30px;
    border:1.5px solid var(--gray-200);border-radius:8px;
    font-size:13px;color:var(--navy);background:var(--gray-50);
    transition:all 0.2s;outline:none;font-weight:600;
    box-sizing:border-box;
}
.pm-input:focus{border-color:var(--navy);background:var(--white);box-shadow:0 0 0 3px rgba(13,27,42,0.05);}
.pm-swap-btn{
    position:absolute;right:8px;top:50%;transform:translateY(-50%);
    background:var(--white);border:1.5px solid var(--gray-200);
    border-radius:6px;width:24px;height:24px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;color:var(--navy);font-size:11px;z-index:3;transition:all 0.2s;
}
.pm-swap-btn:hover{background:var(--gold);color:white;border-color:var(--gold)}
.pm-dash{
    width:2px;height:8px;
    background:repeating-linear-gradient(to bottom,var(--gray-200) 0,var(--gray-200) 3px,transparent 3px,transparent 6px);
    margin:0 0 2px 18px;
}

/* ============= AUTOCOMPLETE YANGO-STYLE ============= */
.pm-autocomplete-wrap{position:relative}
.pm-autocomplete-list{
    position:absolute;top:calc(100% + 3px);left:0;right:0;
    background:var(--white);border-radius:12px;
    box-shadow:0 8px 28px rgba(13,27,42,0.16);
    z-index:1000;max-height:200px;overflow-y:auto;
    border:1px solid var(--gray-200);display:none;
}
.pm-autocomplete-list.open{display:block;animation:slideDown 0.15s ease}
@keyframes slideDown{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:none}}

/* Immersive Search Mode */
.search-mode .pm-autocomplete-list.open {
    position: static;
    box-shadow: none;
    border: none;
    max-height: none;
    margin-top: 10px;
    background: transparent;
}
.search-mode .pm-vehicles, 
.search-mode .pm-pills, 
.search-mode .pm-estimate, 
.search-mode .pm-book-btn-wrap,
.search-mode .pm-carousel-wrap,
.search-mode .pm-tabs,
.search-mode .pm-ads-scroll,
.search-mode .pm-stats-row {
    display: none !important;
}

/* Hide ads when a route is active */
.route-active .pm-ads-scroll {
    display: none !important;
}

.pm-autocomplete-item{
    display:flex;align-items:center;gap:14px;
    padding:12px 10px;cursor:pointer;border-bottom:1px solid var(--gray-100);
    transition:background 0.15s;
}
.pm-autocomplete-item:last-child{border-bottom:none}
.pm-autocomplete-item:hover,.pm-autocomplete-item.highlighted{background:var(--gray-50)}
.pm-place-thumb{width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;background:var(--gray-100);}
.pm-place-icon-wrap{width:32px;height:32px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;color:var(--gray-500);}
.pm-place-text{flex:1;min-width:0}
.pm-place-main{font-weight:700;font-size:14px;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pm-place-sub{font-size:12px;color:var(--gray-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.pm-place-dist{font-size:11px;color:var(--gray-400);font-weight:600;flex-shrink:0}

/* Clear button */
.pm-clear-btn {
    position:absolute; right:42px; top:50%; transform:translateY(-50%);
    width:22px; height:22px; border-radius:50%; background:var(--gray-200);
    color:var(--gray-500); display:flex; align-items:center; justify-content:center;
    font-size:12px; cursor:pointer; z-index:4; border:none;
}
.pm-clear-btn:hover { background:var(--gray-400); color:white; }

/* ============= VEHICLE CARDS (compact) ========== */
.pm-vehicles{
    display:flex;gap:6px;overflow-x:auto;
    margin-bottom:6px;padding-bottom:4px;
}
.pm-vehicles::-webkit-scrollbar{display:none}
.pm-vcard{
    flex-shrink:0;min-width:80px;
    display:flex;flex-direction:column;align-items:center;
    padding:4px 4px;gap:2px;
    border:1.5px solid var(--gray-200);border-radius:10px;
    cursor:pointer;transition:all 0.2s;background:var(--white);
    position:relative;
}
.pm-vcard.active{border-color:var(--gold);background:var(--gold-pale)}
.pm-vcard input[type=radio]{display:none}
/* Smaller images for compact layout */
.pm-vcard-img{width:40px;height:26px;object-fit:contain}
.pm-vcard-name{font-weight:800;font-size:11px;color:var(--navy);white-space:nowrap}
.pm-vcard-seats{font-size:9px;color:var(--gray-500);font-weight:600;}
.pm-vcard-price{font-weight:800;font-size:12px;color:var(--navy)}
.pm-vcard-badge{
    position:absolute;top:-4px;right:-4px;
    background:var(--gold);color:var(--navy);
    font-size:7px;font-weight:800;
    padding:1px 4px;border-radius:6px;white-space:nowrap;
}
.pm-vcard-eta{font-size:8px;color:var(--success);font-weight:600;display:flex;align-items:center;gap:2px;}

/* ============= ESTIMATE STRIP =================== */
.pm-estimate{
    border-radius:8px;padding:6px 12px;
    background:linear-gradient(135deg,var(--success),#15803d);
    display:none;justify-content:space-between;align-items:center;
    margin-bottom:6px;box-shadow:0 2px 6px rgba(34,197,94,0.3);
}
.pm-estimate.visible{display:flex;animation:fadeIn 0.3s ease}
.pm-estimate-label{font-size:10px;color:rgba(255,255,255,0.85);font-weight:600;letter-spacing:0.3px;}
.pm-estimate-val{font-size:13px;font-weight:700;color:var(--white);margin-top:2px}
.pm-estimate-block{text-align:center}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}

/* ============= STICKY BOOK BUTTON =============== */
.pm-book-btn{
    width:100%;padding:10px;border-radius:var(--radius-sm);border:none;
    background:linear-gradient(135deg,var(--lime),var(--lime-dark));
    color:var(--navy);font-size:15px;font-weight:800;
    box-shadow:0 6px 16px var(--lime-glow);
    display:flex;align-items:center;justify-content:center;gap:12px;
    cursor:pointer;position:relative;overflow:hidden;
    transition:transform 0.2s,box-shadow 0.2s;
}
.pm-book-btn-wrap {
    order: 7;
    margin: 4px 12px 10px 12px;
}
.pm-book-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.15),transparent);}
.pm-book-btn:active{transform:scale(0.98);box-shadow:0 2px 8px rgba(201,168,76,0.3)}

/* ============= LINEAR LAYOUT ORDERS ======================= */
#booking-form { display: flex; flex-direction: column; }
.pm-carousel-wrap { order: 1; margin: 0 0 10px 0; }
.pm-pane { display: none; }
.pm-pane.active { display: contents; }
.pm-pane > .pm-connector, .pm-pane > .pm-autocomplete-wrap, .pm-pane > label, .pm-pane > .pm-input-wrap { order: 2; margin: 0 12px; }
.pm-tabs { order: 3; margin: 10px 0; padding: 0 12px; }
.pm-pane > .pm-estimate { order: 4; margin: 0 12px 8px 12px; }
.pm-pane > .pm-vehicles { order: 5; margin: 0 12px 8px 12px; }
.pm-pane > .pm-pills { order: 6; margin: 0 12px 8px 12px; }

/* Ads Scroll */
.pm-ads-scroll {
    display: flex; gap: 10px; overflow-x: auto; padding: 0 12px 10px 12px;
    scroll-snap-type: x mandatory; scrollbar-width: none;
}
.pm-ads-scroll::-webkit-scrollbar { display: none; }
.pm-ad-banner {
    flex: 0 0 75%; scroll-snap-align: center;
    background: linear-gradient(135deg, var(--lime), var(--lime-dark));
    border-radius: 12px; padding: 12px 14px; position: relative; text-decoration: none; color: var(--navy);
    display: flex; flex-direction: column; gap: 4px; overflow: hidden; box-shadow: 0 4px 10px var(--lime-glow);
}
.pm-ad-bg-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.15; }
.pm-ad-content { position: relative; z-index: 2; }
.pm-ad-tag { align-self: flex-start; background: var(--navy); color: var(--white); font-size: 9px; font-weight: 800; padding: 3px 6px; border-radius: 4px; text-transform: uppercase; margin-bottom: 4px; display: inline-block; }
.pm-ad-title { font-size: 14px; font-weight: 800; margin: 0; line-height: 1.2; color: var(--navy); }
.pm-ad-desc { font-size: 11px; color: rgba(13,27,42,0.8); margin: 0; line-height: 1.3; }

.pm-ad-banner-img {
    background: none !important;
}

.pm-ad-banner-img .pm-ad-title,
.pm-ad-banner-img .pm-ad-desc {
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.pm-ad-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.6) 100%);
    z-index: 1;
}

/* Removed duplicate bottom nav styles */

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

/* ============= SPECIFIC VTC TOP BAR OVERRIDE ==== */
.pwa-top-bar {
    background: linear-gradient(135deg, var(--lime), var(--lime-dark)) !important;
    color: var(--navy) !important;
}
.pwa-menu-btn, .pwa-notif-btn, .pwa-header-title {
    color: var(--navy) !important;
}
.pwa-wallet-pill {
    color: var(--navy) !important;
    background: rgba(13, 27, 42, 0.1) !important;
}
</style>
@endsection

@section('content')
<div id="pm-wrapper">



    {{-- MAP --}}
    <div id="pm-map"></div>



    {{-- RECENTRER --}}
    <button class="pm-recenter-btn" id="pm-recenter" title="Ma position">
        <i class="fa fa-crosshairs"></i>
    </button>

    {{-- BOTTOM SHEET --}}
    <div class="pm-sheet" id="pm-sheet">
        <div class="pm-drag-handle" id="pm-drag"></div>
        <div class="pm-sheet-scroll" style="padding-bottom: 20px;">

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

            {{-- INFOS COMPACTES (NOTE & BADGE) --}}
            <div class="pm-user-stats-compact" style="display: flex; gap: 10px; margin: 0 16px 10px 16px; align-items: center;">
                <div style="background: rgba(13,27,42,0.05); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 4px;">
                    <i class="fa fa-star" style="color: var(--gold);"></i>
                    {{ Auth::user()->rating ?? '5.0' }}
                </div>
                <div style="background: rgba(34,197,94,0.1); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; color: var(--lime-dark); display: flex; align-items: center; gap: 4px;">
                    <i class="fa fa-shield"></i>
                    {{ Auth::user()->user_badge ?? 'Explorateur' }}
                </div>
            </div>

            {{-- CAROUSEL --}}
            <div class="pm-carousel-wrap">
                <div class="pm-ads-scroll">
                    @if(isset($ads) && $ads->count() > 0)
                        @foreach($ads as $ad)
                            @php 
                                $content = $ad->contents->first(); 
                                $imgUrl = $content ? $content->image_url : null;
                                $cta = $content ? trim($content->call_to_action) : '#';
                                
                                if (is_numeric($cta)) {
                                    $targetUrl = url('/user/store/product/' . $cta);
                                } elseif ($cta && strpos($cta, '/marketplace/') !== false) {
                                    $parts = explode('/marketplace/', $cta);
                                    if (count($parts) > 1) {
                                        $id = str_replace(['detail/', '/'], '', $parts[1]);
                                        $targetUrl = url('/user/store/product/' . $id);
                                    } else {
                                        $targetUrl = $cta;
                                    }
                                } elseif ($cta && strpos($cta, 'http') === false && $cta !== '#') {
                                    $targetUrl = url($cta);
                                } else {
                                    $targetUrl = $cta;
                                }
                            @endphp
                            <a href="{{ $targetUrl }}" class="pm-ad-banner {{ $imgUrl ? 'pm-ad-banner-img' : '' }}" 
                               @if($imgUrl) style="background: url('{{ $imgUrl }}') center/cover no-repeat !important;" @endif>
                                @if($imgUrl)
                                    <div class="pm-ad-overlay"></div>
                                @endif
                                <div class="pm-ad-content">
                                    <span class="pm-ad-tag">Sponsorisé</span>
                                    <h3 class="pm-ad-title">{{ $content->title ?? $ad->name }}</h3>
                                    <p class="pm-ad-desc">{{ $content->headline ?? $ad->description ?? '' }}</p>
                                </div>
                            </a>
                        @endforeach
                    @endif

                    <a href="{{ route('user.marketplace.explore') }}" class="pm-ad-banner">
                        <div class="pm-ad-content">
                            <span class="pm-ad-tag">Nouveau</span>
                            <h3 class="pm-ad-title">PicMe225 Rewards</h3>
                            <p class="pm-ad-desc">Gagnez des points à chaque trajet.</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- CATEGORY TABS --}}
            <div class="pm-tabs" role="tablist">
                @foreach($categories as $category)
                <button type="button" class="pm-tab {{ $loop->first ? 'active' : '' }}"
                        onclick="pmSwitchTab({{ $category->id }})"
                        id="pm-tab-{{ $category->id }}" role="tab">
                    <div class="pm-tab-label">{{ $category->name }}</div>
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}">
                </button>
                @endforeach
            </div>

            {{-- PANES --}}
            @foreach($categories as $category)
            <div id="pm-pane-{{ $category->id }}" class="pm-pane {{ $loop->first ? 'active' : '' }}" style="display:{{ $loop->first ? 'contents' : 'none' }}">

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

                {{-- Book Button (Order 6) --}}
                <div class="pm-book-btn-wrap">
                    <button type="submit" class="pm-book-btn" id="pm-book-btn">
                        🚕 Réserver maintenant
                    </button>
                </div>
            </form>
    </div>
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

    function triggerAutocomplete() {
        var q = input.value.trim();
        clearTimeout(acTimers[inputId]);
        // Allow 0 letter (for 'Ma position') or 1+ letter
        acTimers[inputId] = setTimeout(function() {
            fetchSuggestions(q, list, input, target, catId);
        }, 200);
    }

    input.addEventListener('input', triggerAutocomplete);
    input.addEventListener('focus', function() {
        document.getElementById('pm-sheet').classList.add('search-mode');
        triggerAutocomplete();
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            list.classList.remove('open');
        }
    });
}

function fetchSuggestions(query, list, input, target, catId) {
    var lat  = (typeof GEO_LAT !== 'undefined') ? GEO_LAT : (typeof CUR_LAT !== 'undefined' ? CUR_LAT : 5.3096600);
    var lng  = (typeof GEO_LNG !== 'undefined') ? GEO_LNG : (typeof CUR_LNG !== 'undefined' ? CUR_LNG : -4.0126600);
    
    // Always create 'Ma position' item
    var renderList = function(features) {
        list.innerHTML = '';
        
        // Add "Ma position" at top
        var itemPos = document.createElement('div');
        itemPos.className = 'pm-autocomplete-item highlighted';
        itemPos.innerHTML = '<div class="pm-place-icon-wrap" style="background:var(--navy);color:white"><i class="fa fa-crosshairs"></i></div>' +
            '<div class="pm-place-text">' +
                '<div class="pm-place-main">Ma position</div>' +
                '<div class="pm-place-sub">Utiliser ma position actuelle</div>' +
            '</div>';
        itemPos.addEventListener('click', function() {
            input.value = 'Ma position';
            list.classList.remove('open');
            document.getElementById('pm-sheet').classList.remove('search-mode');
            if (window.pmSnapSheetMid) window.pmSnapSheetMid();
            setCoords(target, catId, lat, lng);
            updateMapForTarget(target, catId, lat, lng, 'Ma position');
        });
        list.appendChild(itemPos);

        // Choisir sur la carte
        var itemMap = document.createElement('div');
        itemMap.className = 'pm-autocomplete-item';
        itemMap.innerHTML = '<div class="pm-place-icon-wrap"><i class="fa fa-map"></i></div>' +
            '<div class="pm-place-text">' +
                '<div class="pm-place-main">Choisir sur la carte</div>' +
            '</div>';
        itemMap.addEventListener('click', function() {
            list.classList.remove('open');
            document.getElementById('pm-sheet').classList.remove('search-mode');
            if (window.pmSnapSheetMid) window.pmSnapSheetMid();
        });
        list.appendChild(itemMap);

        // Domicile & Travail si la recherche est vide
        if (query.length === 0 || isDefaultSearch) {
            var itemHome = document.createElement('div');
            itemHome.className = 'pm-autocomplete-item';
            itemHome.innerHTML = '<div class="pm-place-icon-wrap"><i class="fa fa-home"></i></div>' +
                '<div class="pm-place-text">' +
                    '<div class="pm-place-main">Domicile</div>' +
                    '<div class="pm-place-sub">Ajouter une adresse</div>' +
                '</div>';
            itemHome.addEventListener('click', function() {
                input.value = 'Domicile';
                list.classList.remove('open');
                document.getElementById('pm-sheet').classList.remove('search-mode');
                if (window.pmSnapSheetMid) window.pmSnapSheetMid();
            });
            list.appendChild(itemHome);

            var itemWork = document.createElement('div');
            itemWork.className = 'pm-autocomplete-item';
            itemWork.innerHTML = '<div class="pm-place-icon-wrap"><i class="fa fa-briefcase"></i></div>' +
                '<div class="pm-place-text">' +
                    '<div class="pm-place-main">Travail</div>' +
                    '<div class="pm-place-sub">Ajouter une adresse</div>' +
                '</div>';
            itemWork.addEventListener('click', function() {
                input.value = 'Travail';
                list.classList.remove('open');
                document.getElementById('pm-sheet').classList.remove('search-mode');
                if (window.pmSnapSheetMid) window.pmSnapSheetMid();
            });
            list.appendChild(itemWork);
        }

        if (!features.length && !query) { list.classList.add('open'); if (window.pmSnapSheetMax) window.pmSnapSheetMax(); return; }

        features.forEach(function(feat, idx) {
            var props = feat.properties || {};
            var coords = feat.geometry && feat.geometry.coordinates ? feat.geometry.coordinates : [lng, lat];
            var fLng = coords[0], fLat = coords[1];

            var mainName = props.name || props.street || props.city || query;
            var subName  = [props.city, props.state, props.country].filter(Boolean).join(', ');

            var dist = haversineKm(lat, lng, fLat, fLng);
            var distStr = dist < 1 ? Math.round(dist * 1000) + 'm' : dist.toFixed(1) + 'km';
            var iconEmoji = getPlaceIcon(props.osm_value || props.type || '');

            var item = document.createElement('div');
            item.className = 'pm-autocomplete-item';
            
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
                document.getElementById('pm-sheet').classList.remove('search-mode');
                if (window.pmSnapSheetMid) window.pmSnapSheetMid();
                setCoords(target, catId, fLat, fLng);
                updateMapForTarget(target, catId, fLat, fLng, mainName);
            });
            list.appendChild(item);

            if (idx === 0 && props.osm_id && !isDefaultSearch) {
                enrichWithImage(props.osm_id, props.osm_type, item);
            }
        });
        list.classList.add('open');
        if (window.pmSnapSheetMax) window.pmSnapSheetMax();
    };

    var isDefaultSearch = false;
    if (query.length === 0) {
        query = "pharmacie"; // Terme par défaut géolocalisé
        isDefaultSearch = true;
    }

    if (query.length >= 1) {
        var url = '/places/search?q=' + encodeURIComponent(query) + '&lat=' + lat + '&lng=' + lng + '&limit=8';
        fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            renderList(data.features || []);
        }).catch(function() { renderList([]); });
    } else {
        renderList([]);
    }
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
        restaurant: '<i class="fa fa-cutlery"></i>', cafe: '<i class="fa fa-coffee"></i>', hotel: '<i class="fa fa-bed"></i>', hospital: '<i class="fa fa-hospital-o"></i>',
        pharmacy: '<i class="fa fa-medkit"></i>', school: '<i class="fa fa-graduation-cap"></i>', university: '<i class="fa fa-university"></i>', bank: '<i class="fa fa-university"></i>',
        supermarket: '<i class="fa fa-shopping-cart"></i>', fuel: '<i class="fa fa-car"></i>', airport: '<i class="fa fa-plane"></i>', bus_stop: '<i class="fa fa-bus"></i>',
        station: '<i class="fa fa-train"></i>', park: '<i class="fa fa-tree"></i>', beach: '<i class="fa fa-sun-o"></i>', museum: '<i class="fa fa-building-o"></i>',
        stadium: '<i class="fa fa-futbol-o"></i>', mosque: '<i class="fa fa-moon-o"></i>', church: '<i class="fa fa-plus"></i>', government: '<i class="fa fa-bank"></i>'
    };
    return icons[type] || '<i class="fa fa-map-marker"></i>';
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
        
        var estDiv = document.getElementById('estimate-' + catId);
        estDiv.setAttribute('data-dist', dist);
        estDiv.classList.add('visible');
        var sheet = document.getElementById('pm-sheet');
        if (sheet) sheet.classList.add('route-active');
        updateEstimatePrice(catId);
    })
    .catch(function() {
        var sheet = document.getElementById('pm-sheet');
        if (sheet) sheet.classList.remove('route-active');
        document.getElementById('estimate-' + catId).classList.remove('visible');
        if (routeLine) { mapInst.removeLayer(routeLine); routeLine = null; }
    });
}

function updateEstimatePrice(catId) {
    var estDiv = document.getElementById('estimate-' + catId);
    if (!estDiv || !estDiv.classList.contains('visible')) return;
    
    var dist = parseFloat(estDiv.getAttribute('data-dist')) || 0;
    if (dist <= 0) return;
    
    var activeCard = document.querySelector('#grid-' + catId + ' .pm-vcard.active');
    var basePrice = 500;
    if (activeCard) {
        var priceText = activeCard.querySelector('.pm-vcard-price').textContent;
        basePrice = parseInt(priceText.replace(/\D/g, '')) || 500;
    }
    
    // Estimation basique: Prix de base + 200 FCFA par km
    var estPrice = basePrice + (dist * 200);
    
    // Ajustement selon la variante
    var variant = document.getElementById('selected_ride_variant').value || 'prive';
    if (variant === 'partage') estPrice = estPrice * 0.65; // 35% de remise
    if (variant === 'arret_pdp') estPrice = estPrice * 0.3; // 70% de remise
    
    estPrice = Math.round(estPrice / 100) * 100; // Arrondi à la centaine
    
    var priceEl = document.getElementById('est-price-' + catId);
    if (priceEl) {
        priceEl.textContent = '~' + estPrice + ' FCFA';
    }
}

function tryComputeEstimate(catId) {
    var oLat = parseFloat(document.getElementById('origin_latitude').value);
    var oLng = parseFloat(document.getElementById('origin_longitude').value);
    var dLat = parseFloat(document.getElementById('destination_latitude').value);
    var dLng = parseFloat(document.getElementById('destination_longitude').value);
    if (oLat && oLng && dLat && dLng) {
        drawRoute(oLat, oLng, dLat, dLng, catId);
    } else {
        var sheet = document.getElementById('pm-sheet');
        if (sheet) sheet.classList.remove('route-active');
        var estDiv = document.getElementById('estimate-' + catId);
        if (estDiv) estDiv.classList.remove('visible');
        if (typeof routeLine !== 'undefined' && routeLine) { mapInst.removeLayer(routeLine); routeLine = null; }
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
    document.querySelectorAll('.pm-pane').forEach(function(p) { p.classList.remove('active'); p.style.display = 'none'; });
    document.querySelectorAll('.pm-tab').forEach(function(t) { t.classList.remove('active'); });
    var pane = document.getElementById('pm-pane-' + id);
    var tab  = document.getElementById('pm-tab-' + id);
    if (pane) { pane.classList.add('active'); pane.style.display = 'contents'; }
    if (tab)  tab.classList.add('active');
    // Reset variant to prive
    var firstPill = document.querySelector('#pills-' + id + ' .pm-pill');
    if(firstPill) pmSetVariant(id, 'prive', firstPill);
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
    updateEstimatePrice(catId);
}

// ── VEHICLE SELECTION ─────────────────────────────────────────
function pmSelectVehicle(radio) {
    document.querySelectorAll('.pm-vcard').forEach(function(c) { c.classList.remove('active'); });
    var label = document.querySelector('label[for="' + radio.id + '"]');
    if (label) {
        label.classList.add('active');
        var grid = label.closest('.pm-vehicles');
        if (grid) {
            var catId = grid.id.replace('grid-', '');
            updateEstimatePrice(catId);
        }
    }
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

// ── DRAG SHEET (Modern DraggableScrollableSheet) ──────────────────────────
(function() {
    var sheet = document.getElementById('pm-sheet');
    var handle = document.getElementById('pm-drag');
    var scrollArea = document.querySelector('.pm-sheet-scroll');
    
    var startY = 0;
    var currentY = 0;
    var isDragging = false;
    
    // Calculate viewport height states
    var vh = window.innerHeight;
    var minH = vh * 0.20; // 20vh
    var midH = vh * 0.58; // 58vh (increased to show more content)
    var maxH = vh * 0.85; // 85vh
    
    // We store the current translation from the max state
    // Initial state is mid.
    var currentTranslate = maxH - midH; 
    if (scrollArea) scrollArea.style.paddingBottom = (currentTranslate + 20) + 'px';
    
    function setTranslate(val) {
        sheet.style.transform = 'translateY(' + val + 'px)';
        // Add dynamic bottom padding equal to the hidden part of the sheet
        // so that the bottom button is always reachable via scrolling
        if (scrollArea) {
            scrollArea.style.paddingBottom = (val + 20) + 'px';
        }
    }
    
    function snapTo(val) {
        sheet.classList.remove('dragging');
        currentTranslate = val;
        setTranslate(val);
    }
    
    function onTouchStart(e) {
        // If touching inside scroll area, only drag if we are at the top
        if (e.target.closest('.pm-sheet-scroll') && !e.target.closest('.pm-drag-handle')) {
            if (scrollArea.scrollTop > 0) return;
        }
        
        isDragging = true;
        startY = e.touches[0].clientY;
        sheet.classList.add('dragging');
    }
    
    function onTouchMove(e) {
        if (!isDragging) return;
        var dy = e.touches[0].clientY - startY;
        var newT = currentTranslate + dy;
        
        // Prevent dragging higher than max (newT < 0) or lower than min
        if (newT < 0) newT = 0; // Max expanded
        if (newT > (maxH - minH)) newT = maxH - minH; // Min collapsed
        
        setTranslate(newT);
        
        // Prevent internal scroll when dragging sheet
        if (e.target.closest('.pm-sheet-scroll') && newT > 0) {
            if(e.cancelable) e.preventDefault();
        }
    }
    
    function onTouchEnd(e) {
        if (!isDragging) return;
        isDragging = false;
        
        var dy = e.changedTouches[0].clientY - startY;
        var newT = currentTranslate + dy;
        
        // Snap logic based on velocity & position
        var snapPoints = [0, maxH - midH, maxH - minH]; // max, mid, min
        var closest = snapPoints.reduce(function(prev, curr) {
            return (Math.abs(curr - newT) < Math.abs(prev - newT) ? curr : prev);
        });
        
        // Directional bias for fast swipes
        if (dy < -30) { // Swiped up fast
            if (currentTranslate === snapPoints[2]) closest = snapPoints[1];
            else if (currentTranslate === snapPoints[1]) closest = snapPoints[0];
        } else if (dy > 30) { // Swiped down fast
            if (currentTranslate === snapPoints[0]) closest = snapPoints[1];
            else if (currentTranslate === snapPoints[1]) closest = snapPoints[2];
        }
        
        snapTo(closest);
    }
    
    // Attach events
    handle.addEventListener('touchstart', onTouchStart, {passive: false});
    document.addEventListener('touchmove', onTouchMove, {passive: false});
    document.addEventListener('touchend', onTouchEnd);
    
    // Also allow drag from the top header of the sheet (tabs)
    var tabs = document.querySelector('.pm-tabs');
    if (tabs) {
        tabs.addEventListener('touchstart', onTouchStart, {passive: false});
    }

    // Expose snapping functions for Autocomplete
    window.pmSnapSheetMax = function() { snapTo(0); };
    window.pmSnapSheetMid = function() { snapTo(maxH - midH); };
    
    // Allow dragging from empty spaces in the scroll area (when scrollTop is 0)
    scrollArea.addEventListener('touchstart', onTouchStart, {passive: false});
    
    // Window resize recalibration
    window.addEventListener('resize', function() {
        vh = window.innerHeight;
        minH = vh * 0.20;
        midH = vh * 0.58;
        maxH = vh * 0.85;
        // Snap to mid on resize
        snapTo(maxH - midH);
    });
})();



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

    // Add clear buttons dynamically
    document.querySelectorAll('.pm-autocomplete-input').forEach(function(input) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pm-clear-btn';
        btn.innerHTML = '<i class="fa fa-times"></i>';
        btn.onclick = function(e) {
            e.preventDefault(); e.stopPropagation();
            input.value = '';
            input.focus();
            input.dispatchEvent(new Event('input', { bubbles: true }));
        };
        input.parentNode.appendChild(btn);
        
        input.addEventListener('input', function() {
            btn.style.display = input.value.length > 0 ? 'flex' : 'none';
            if (input.value.length === 0) {
                var target = input.getAttribute('data-target');
                if (target === 'origin') {
                    document.getElementById('origin_latitude').value = '';
                    document.getElementById('origin_longitude').value = '';
                } else if (target === 'destination') {
                    document.getElementById('destination_latitude').value = '';
                    document.getElementById('destination_longitude').value = '';
                }
                var catId = input.getAttribute('data-cat');
                if (catId) tryComputeEstimate(catId);
            }
        });
        if(input.value.length > 0) btn.style.display = 'flex';
    });

    // Global click listener to remove search-mode when clicking completely outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.pm-autocomplete-wrap') && !e.target.closest('.pm-autocomplete-list')) {
            var sheet = document.getElementById('pm-sheet');
            if (sheet && sheet.classList.contains('search-mode')) {
                sheet.classList.remove('search-mode');
                if (window.pmSnapSheetMid) window.pmSnapSheetMid();
            }
        }
    });
});
</script>
@endsection
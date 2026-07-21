@extends('user.layout.app')

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   PICME225 — LANDING PAGE (Ola Cabs Inspiration)
═══════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

*, *::before, *::after { box-sizing: border-box; }

.pm-landing {
    font-family: 'Inter', system-ui, sans-serif;
    background: #0A1628;
    color: #F8FAFC;
    overflow-x: hidden;
}

/* ─── HERO MAP ───────────────────────────────────── */
.pm-hero-map {
    position: relative;
    height: 100vh;
    min-height: 550px;
    width: 100%;
    overflow: visible;
    background: #0A1628;
}

#leaflet-map {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

/* Overlay sombre pour faire ressortir le widget */
.pm-hero-map::after {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: linear-gradient(180deg, rgba(10,22,40,0.55) 0%, rgba(10,22,40,0.3) 60%, rgba(10,22,40,0.5) 100%);
    z-index: 2;
    pointer-events: none;
}

.pm-hero-map .container {
    position: relative;
    z-index: 3;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 100px;
    padding-bottom: 20px;
}

/* ─── BANDEAU AEROPORT (NEW) ───────────────────────── */
.pm-airport-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 420px;
    max-width: calc(100% - 32px); /* 100% moins 16px de chaque côté */
    height: 64px;
    background: linear-gradient(135deg, #fcd34d 0%, #eab308 100%);
    border-radius: 20px;
    padding: 0 16px;
    margin-bottom: 12px;
    box-shadow: 0 8px 24px rgba(234, 179, 8, 0.3);
    cursor: pointer;
    text-decoration: none;
    color: #000;
    transition: all 0.3s ease;
    animation: slideDownFade 0.35s ease-out forwards;
    opacity: 0;
}

@keyframes slideDownFade {
    0% { opacity: 0; transform: translateY(-10px); }
    100% { opacity: 1; transform: translateY(0); }
}

.pm-airport-banner:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(234, 179, 8, 0.4);
    text-decoration: none;
    color: #000;
}

.pm-airport-banner:active {
    transform: scale(0.98);
}

.pm-ab-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pm-ab-icon {
    font-size: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pm-ab-text {
    display: flex;
    flex-direction: column;
}

.pm-ab-title {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
}

.pm-ab-subtitle {
    font-size: 12px;
    font-weight: 600;
    color: rgba(0, 0, 0, 0.7);
    margin: 0;
}

.pm-ab-btn {
    background: #ca8a04;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 12px;
    white-space: nowrap;
}

/* ─── BOOKING WIDGET (App-like) ───────────────────────── */
.pm-booking-widget {
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    width: 420px;
    max-width: 100%;
    padding: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    font-family: 'Inter', sans-serif;
}

/* Address Inputs */
.pm-address-container {
    background: #1e293b;
    border-radius: 12px;
    padding: 10px;
    position: relative;
    margin-bottom: 12px;
    z-index: 50;
}

.pm-address-line {
    position: absolute;
    left: 22px;
    top: 26px;
    bottom: 26px;
    width: 2px;
    background: rgba(255,255,255,0.1);
    z-index: 1;
}

.pm-input-wrapper {
    position: relative;
}
.pm-address-container .pm-input-wrapper:nth-child(2) { z-index: 10; margin-bottom: 8px; }
.pm-address-container .pm-input-wrapper:nth-child(3) { z-index: 9; }

.pm-dot {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.pm-dot-green { background: #10b981; }
.pm-dot-red { background: #ef4444; }
.pm-dot-flag { background: none; font-size: 12px; left: 6px; top: 50%; transform: translateY(-50%); }

.pm-address-container .form-control {
    width: 100%;
    height: 40px;
    background: #0f172a;
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 8px;
    padding: 8px 12px 8px 30px;
    font-size: 13px;
    color: #f8fafc;
    font-weight: 500;
}
.pm-address-container .form-control::placeholder { color: #64748b; }
.pm-address-container .form-control:focus { outline: none; border-color: #C9A84C; }

/* Autocomplete suggestions */
.photon-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    margin-top: 4px;
    z-index: 1000;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    max-height: 250px;
    overflow-y: auto;
    display: none;
}
.photon-suggestion-item {
    padding: 12px 16px;
    cursor: pointer;
    font-size: 13px;
    color: #f8fafc;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background 0.2s;
    text-align: left;
}
.photon-suggestion-item:last-child { border-bottom: none; }
.photon-suggestion-item:hover { background: rgba(234, 179, 8, 0.1); }
.photon-suggestion-item strong { display: block; font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.photon-suggestion-item .city { font-size: 11px; color: #94a3b8; }
.photon-suggestion-item.geo-item { color: #eab308; }
.photon-suggestion-item.geo-item strong { color: #eab308; }

/* ─── SECTION TITLES (light bg) ──────────────────────── */
.pm-section-heading {
    font-size: clamp(28px, 4vw, 40px);
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -1px;
    line-height: 1.2;
    margin-bottom: 12px;
}
.pm-section-subheading {
    font-size: 17px;
    color: #475569;
    font-weight: 500;
    line-height: 1.6;
    margin-bottom: 0;
}
.pm-section-label {
    display: inline-block;
    font-size: 12px;
    font-weight: 700;
    color: #eab308;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 14px;
    background: rgba(234,179,8,0.1);
    padding: 4px 12px;
    border-radius: 20px;
}

/* ─── CATEGORY PILLS ──────────────────────────────────── */
.pm-categories-scroll {
    display: flex;
    overflow-x: auto;
    gap: 8px;
    padding-bottom: 6px;
    margin-bottom: 12px;
    scrollbar-width: none;
}
.pm-categories-scroll::-webkit-scrollbar { display: none; }

.pm-cat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
    color: #cbd5e1;
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.05);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}
.pm-cat-pill:hover {
    transform: translateY(-2px);
    background: #334155;
}
.pm-cat-pill img {
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    margin: 0 !important;
}
.pm-cat-pill.active {
    background: #eab308;
    color: #000;
    border-color: #eab308;
}
.pm-cat-pill.active:hover {
    background: #eab308;
    transform: translateY(-2px) scale(1.02);
}

/* Vehicle Cards */
.pm-vehicles-scroll {
    display: flex;
    overflow-x: auto;
    gap: 8px;
    padding-bottom: 4px;
    margin-bottom: 12px;
    scrollbar-width: none;
}
.pm-vehicles-scroll::-webkit-scrollbar { display: none; }

.pm-vehicle-card {
    min-width: 100px;
    background: #1e293b;
    border-radius: 12px;
    padding: 10px 8px;
    text-align: center;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.pm-vehicle-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border-color: rgba(234,179,8,0.5);
}
.pm-vehicle-card.active {
    border-color: #eab308;
    background: rgba(234, 179, 8, 0.15);
    box-shadow: 0 4px 12px rgba(234,179,8,0.2);
}
.pm-vehicle-card img {
    width: 32px;
    height: 32px;
    object-fit: contain;
    margin-bottom: 4px;
}
.pm-vehicle-name { font-size: 12px; font-weight: 700; color: #f8fafc; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px; margin-left: auto; margin-right: auto; }
.pm-vehicle-meta { font-size: 10px; color: #94a3b8; display: flex; justify-content: center; gap: 4px; font-weight: 600; margin-bottom: 4px; }
.pm-vehicle-meta i { font-size: 9px; }
.pm-vehicle-price { font-size: 11px; font-weight: 800; color: #eab308; background: rgba(234,179,8,0.1); border-radius: 6px; padding: 2px 6px; display: inline-block; min-height: 18px; }

/* Download button below widget */
.pm-hero-dl-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #eab308;
    color: #000;
    font-weight: 800;
    font-size: 15px;
    padding: 14px 28px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    margin-top: 18px;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(234,179,8,0.35);
    transition: transform 0.2s, opacity 0.2s;
    width: 420px;
    max-width: 100%;
    justify-content: center;
}
.pm-hero-dl-btn:hover { transform: translateY(-2px); opacity: 0.9; color: #000; text-decoration: none; }

/* Ride Types Toggle */
.pm-types-toggle {
    display: flex;
    background: #1e293b;
    border-radius: 10px;
    padding: 2px;
    margin-bottom: 12px;
}
.pm-type-btn {
    flex: 1;
    text-align: center;
    padding: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #cbd5e1;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.pm-type-btn.active {
    background: #eab308;
    color: #000;
}

/* Install Banner */
.pm-install-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #1e293b;
    border-radius: 12px;
    padding: 10px;
    margin-bottom: 16px;
    border: 1px solid rgba(234, 179, 8, 0.4);
    background-image: linear-gradient(45deg, rgba(234, 179, 8, 0.05), transparent);
}
.pm-install-info { display: flex; align-items: center; gap: 8px; }
.pm-install-icon { width: 28px; height: 28px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.1); }
.pm-install-icon img { width: 16px; height: 16px; }
.pm-install-text h5 { margin: 0; font-size: 13px; font-weight: 700; color: #fff; }
.pm-install-text p { display: none; }
.pm-install-btn { background: #eab308; color: #000; border: none; padding: 4px 10px; border-radius: 16px; font-size: 11px; font-weight: 700; cursor: pointer; }

/* Main CTA */
.pm-btn-primary {
    width: 100%;
    height: 46px;
    background: #eab308;
    color: #000;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.2s, opacity 0.2s;
}
.pm-btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }

/* Photon Autocomplete Suggestions */
.photon-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    margin-top: 4px;
    z-index: 10;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    display: none;
}
.photon-suggestion-item {
    padding: 12px 16px;
    font-size: 13px;
    color: #f8fafc;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.photon-suggestion-item:last-child { border-bottom: none; }
.photon-suggestion-item:hover { background: rgba(255,255,255,0.05); }
.photon-suggestion-item .city { font-size: 11px; color: #94a3b8; display: block; margin-top: 2px; }

/* ─── STATS SECTION ───────────────────────────────────── */
.pm-stats {
    padding: 80px 0;
    background: #0A1628;
    text-align: center;
    border-top: 1px solid rgba(201,168,76,0.1);
}
.pm-stats-title {
    font-size: 32px;
    font-weight: 900;
    color: #F8FAFC;
    margin-bottom: 60px;
}
.pm-stat-item {
    padding: 20px;
}
.pm-stat-value {
    font-size: 42px;
    font-weight: 900;
    color: #C9A84C;
    margin-bottom: 12px;
}
.pm-stat-label {
    font-size: 18px;
    font-weight: 700;
    color: #F8FAFC;
    margin-bottom: 8px;
}
.pm-stat-desc {
    font-size: 14px;
    color: #a0aec0;
}

/* ─── SERVICES GRID (Ola Style) ───────────────────────── */
.pm-services {
    padding: 80px 0;
    background: #0D1F3C;
}
.pm-services h2 {
    font-size: 32px;
    font-weight: 900;
    margin-bottom: 40px;
    text-align: center;
    color: #F8FAFC;
}
.pm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}
.pm-card {
    background: rgba(10, 22, 40, 0.6);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: transform 0.3s, box-shadow 0.3s;
    text-decoration: none;
    color: inherit;
    display: block;
    border: 1px solid rgba(255,255,255,0.05);
}
.pm-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.4);
    text-decoration: none;
    color: inherit;
    border-color: #C9A84C;
    background: rgba(10, 22, 40, 0.8);
}
.pm-card-icon {
    font-size: 40px;
    margin-bottom: 20px;
}
.pm-card h3 {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 10px;
    color: #F8FAFC;
}
.pm-card p {
    font-size: 14px;
    color: #a0aec0;
    line-height: 1.6;
    margin-bottom: 16px;
}
.pm-card-link {
    font-size: 14px;
    font-weight: 700;
    color: #C9A84C;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* ─── DOWNLOAD ────────────────────────────────────────── */
.pm-download {
    padding: 100px 0;
    background: #0A1628;
    color: #F8FAFC;
    text-align: center;
    border-top: 1px solid rgba(201,168,76,0.1);
}
.pm-download h2 {
    font-size: 32px;
    font-weight: 900;
    margin-bottom: 20px;
    color: #F8FAFC;
}
.pm-download p {
    font-size: 16px;
    color: #a0aec0;
    max-width: 600px;
    margin: 0 auto 40px;
}
.pm-dl-flex {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
}
.pm-dl-btn-box {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(201,168,76,0.2);
    padding: 30px;
    border-radius: 20px;
    width: 300px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.pm-dl-btn-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(201,168,76,0.15);
}
.pm-dl-btn-box h4 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #C9A84C;
}
.pm-store-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: #F8FAFC;
    color: #0A1628;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 800;
    text-decoration: none;
    transition: transform 0.2s;
}
.pm-store-btn:hover {
    transform: translateY(-2px);
    text-decoration: none;
    color: #0A1628;
    background: #ffffff;
}
.pm-store-btn i { font-size: 24px; color: #2E7D32; }

@media (max-width: 768px) {
    .pm-hero { padding: 100px 0 60px; }
    .pm-hero-content { flex-direction: column; text-align: center; }
    .pm-hero-text p { margin: 0 auto 30px; }
    .pm-widget-col { width: 100%; }
    .pm-stat-item { margin-bottom: 40px; }
}
</style>

<div class="pm-landing">

    <!-- ═══ HERO & WIDGET ═══ -->
    <!-- ═══ HERO MAP & WIDGET ═══ -->
    <section class="pm-hero-map" id="map-hero">
        <div id="leaflet-map"></div>
        <div class="container">
            <h1 style="font-size: clamp(18px, 3vw, 28px); font-weight: 800; color: #ffffff; text-shadow: 0 4px 24px rgba(0,0,0,0.7); margin-top: 0; margin-bottom: 12px; line-height: 1.1; letter-spacing: -1px; text-align: center; padding: 0 16px;">
                Le transport ivoirien,&nbsp;<span style="color: #eab308;">réinventé.</span>
            </h1>

            <a href="{{ url('/airport') }}" class="pm-airport-banner">
                <div class="pm-ab-left">
                    <div class="pm-ab-icon">✈️</div>
                    <div class="pm-ab-text">
                        <span class="pm-ab-title">Aéroport</span>
                        <span class="pm-ab-subtitle">Départs & Arrivées<br>24h/24</span>
                    </div>
                </div>
                <div class="pm-ab-btn">Réserver &rarr;</div>
            </a>

            <div class="pm-booking-widget">
                <!-- Address Inputs -->
                <div class="pm-address-container">
                    <div class="pm-address-line"></div>
                    <div class="pm-input-wrapper">
                        <div class="pm-dot pm-dot-green"></div>
                        <input type="text" id="origin-input" class="form-control" placeholder="Point de départ" autocomplete="off">
                        <input type="hidden" id="s_latitude">
                        <input type="hidden" id="s_longitude">
                        <div class="photon-suggestions" id="origin-suggestions"></div>
                    </div>
                    <div class="pm-input-wrapper">
                        <div class="pm-dot pm-dot-flag">🏁</div>
                        <input type="text" id="destination-input" class="form-control" placeholder="Où allez-vous ?" autocomplete="off">
                        <input type="hidden" id="d_latitude">
                        <input type="hidden" id="d_longitude">
                        <div class="photon-suggestions" id="dest-suggestions"></div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="pm-categories-scroll" id="categories-list">
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $index => $cat)
                            @php
                                $catImg = $cat->image_url ?: ($cat->image ? img($cat->image) : null);
                            @endphp
                            <div class="pm-cat-pill {{ $index == 0 ? 'active' : '' }}"
                                 data-id="{{ $cat->id }}"
                                 data-services="{{ json_encode($cat->serviceTypes->map(function($s){ return ['id'=>$s->id,'name'=>$s->name,'image'=>$s->image,'image_url'=>$s->image_url,'capacity'=>$s->capacity ?? '','fixed_price'=>$s->pivot->price ?? 0,'per_km_price'=>$s->pivot->distance ?? 0,'allowed_variants'=>$s->allowed_variants ?? [],'arret_discount_percent'=>$s->arret_discount_percent ?? 0]; })) }}">
                                @if($catImg)
                                    <img src="{{ $catImg }}" alt="{{ $cat->name }}" style="width:20px;height:20px;border-radius:50%;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'">
                                @endif
                                {{ $cat->name }}
                            </div>
                        @endforeach
                    @else
                        <div class="pm-cat-pill active">🚕 Taxi</div>
                        <div class="pm-cat-pill">📦 Livraison</div>
                        <div class="pm-cat-pill">🏠 Location</div>
                        <div class="pm-cat-pill">✈️ Aéroport</div>
                        <div class="pm-cat-pill">🚑 Urgence</div>
                    @endif
                </div>

                <!-- Vehicles -->
                <div class="pm-vehicles-scroll" id="services-list">
                    <!-- Injected via JS based on active category -->
                </div>
                <input type="hidden" id="selected_service_id">

                <!-- Types Toggle -->
                <div class="pm-types-toggle">
                    <div class="pm-type-btn active" data-variant="prive">🚗 Privé</div>
                    <div class="pm-type-btn" data-variant="partage">👥 Partagé</div>
                    <div class="pm-type-btn" data-variant="arret_pdp">🚐 Gare-à-Gare</div>
                </div>

                <!-- Install Banner -->
                <div class="pm-install-banner">
                    <div class="pm-install-info">
                        <div class="pm-install-icon">
                            <img src="{{ asset('logo.png') }}" alt="PicMe">
                        </div>
                        <div class="pm-install-text">
                            <h5>Installez PicMe225</h5>
                            <p>✓ Plus rapide ✓ Plein écran ✓ Notifications</p>
                        </div>
                    </div>
                    <button class="pm-install-btn" onclick="installPWA('{{ Setting::get('store_link_android','#') }}')">Installer</button>
                </div>

                <!-- Submit Button -->
                <button type="button" class="pm-btn-primary" id="btn-estimate" onclick="installPWA('{{ Setting::get('store_link_android','#') }}')">
                    🚗 Estimer le trajet & Réserver
                </button>
            </div>
        </div>
    </section>

    <!-- ═══ INFO & HOW IT WORKS ═══ -->
    <section class="pm-info-section" style="padding: 100px 0; background: #ffffff; overflow: hidden;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h2 class="pm-section-heading">
                        Commandez votre transport ou rejoignez-nous comme chauffeur.
                    </h2>
                    <p class="pm-section-subheading" style="margin-bottom:30px;">
                        Deux applications dédiées pour une expérience fluide et sur-mesure.
                    </p>

                    <div style="margin-bottom: 36px;">
                        <h4 style="font-size: 13px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">Paiements sécurisés</h4>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span class="payment-pill" style="background:#ff6600; color:#fff; font-size:13px; font-weight:700; padding:8px 16px; border-radius:12px; box-shadow:0 4px 10px rgba(255,102,0,0.2);">🟠 Orange Money</span>
                            <span class="payment-pill" style="background:#1da1f2; color:#fff; font-size:13px; font-weight:700; padding:8px 16px; border-radius:12px; box-shadow:0 4px 10px rgba(29,161,242,0.2);">🌊 Wave</span>
                            <span class="payment-pill" style="background:#ffcc00; color:#000; font-size:13px; font-weight:700; padding:8px 16px; border-radius:12px; box-shadow:0 4px 10px rgba(255,204,0,0.2);">⭐ MTN MoMo</span>
                        </div>
                    </div>

                    <h3 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 14px;">Comment ça marche ?</h3>
                    <p style="font-size: 16px; color: #475569; line-height: 1.7; margin-bottom: 0;">
                        Découvrez la fluidité de notre application. En quelques clics, choisissez votre destination, sélectionnez votre véhicule et suivez l'arrivée de votre chauffeur en temps réel.
                    </p>
                </div>
                <div class="col-lg-6 text-center">
                    <div style="position: relative; display: inline-block; width: 100%;">
                        <div style="position: absolute; top: -30px; right: -30px; width: 200px; height: 200px; background: #eab308; opacity: 0.1; filter: blur(40px); border-radius: 50%; z-index: 0;"></div>
                        <div style="position: absolute; bottom: -30px; left: -30px; width: 200px; height: 200px; background: #3b82f6; opacity: 0.1; filter: blur(40px); border-radius: 50%; z-index: 0;"></div>
                        <div style="position: relative; z-index: 1; background: #f8fafc; padding: 20px; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;" class="img-floating">
                            <img src="{{ asset('asset/img/picme_app_workflow.png') }}" alt="Workflow App PicMe" style="max-width: 100%; border-radius: 20px;" onerror="this.parentElement.style.minHeight='300px'; this.style.display='none';">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ STATS ═══ -->
    <section class="pm-stats" style="padding: 80px 0; background: #0f172a; color: #fff;">
        <div class="container">
            <h2 style="font-size: 32px; font-weight: 800; text-align: center; color: #fff; margin-bottom: 60px;">Un service pensé pour vous</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-5 mb-md-0 stat-block">
                    <div class="stat-digit" style="font-size: 56px; font-weight: 800; color: #eab308; margin-bottom: 16px; line-height: 1;">24/7</div>
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 12px; color: #f8fafc;">Disponible 24h/24</div>
                    <p style="color: #94a3b8; font-size: 15px; margin: 0 auto; max-width: 250px;">Des chauffeurs actifs à toute heure, même la nuit.</p>
                </div>
                <div class="col-md-4 mb-5 mb-md-0 stat-block">
                    <div class="stat-digit" style="font-size: 56px; font-weight: 800; color: #eab308; margin-bottom: 16px; line-height: 1;">100%</div>
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 12px; color: #f8fafc;">Paiement sécurisé</div>
                    <p style="color: #94a3b8; font-size: 15px; margin: 0 auto; max-width: 250px;">Payez en espèces ou via Mobile Money directement depuis l'application en toute sécurité.</p>
                </div>
                <div class="col-md-4 stat-block">
                    <div class="stat-digit" style="font-size: 56px; font-weight: 800; color: #eab308; margin-bottom: 16px; line-height: 1;">+ 5</div>
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 12px; color: #f8fafc;">Services dédiés</div>
                    <p style="color: #94a3b8; font-size: 15px; margin: 0 auto; max-width: 250px;">@lang('home.all_services_desc')</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ SERVICES GRID ═══ -->
    <section class="pm-services">
        <div class="container">
            <h2>@lang('home.services_title')</h2>
            
            <div class="pm-grid">
                
                <a href="{{ Setting::get('store_link_android') ?: '/download/user' }}" class="pm-card">
                    <div class="pm-card-icon">🚖</div>
                    <h3>@lang('home.service_vtc_title')</h3>
                    <p>@lang('home.service_vtc_desc')</p>
                    <span class="pm-card-link">@lang('home.install_app') <i class="fa fa-arrow-right"></i></span>
                </a>
                
                <a href="{{ Setting::get('store_link_android') ?: '/download/user' }}" class="pm-card">
                    <div class="pm-card-icon">🚌</div>
                    <h3>@lang('home.service_intercity_title')</h3>
                    <p>@lang('home.service_intercity_desc')</p>
                    <span class="pm-card-link">@lang('home.book_via_app') <i class="fa fa-arrow-right"></i></span>
                </a>

                <a href="{{ url('/airport') }}" class="pm-card">
                    <div class="pm-card-icon">✈️</div>
                    <h3>@lang('home.service_airport_title')</h3>
                    <p>@lang('home.service_airport_desc')</p>
                    <span class="pm-card-link">@lang('home.book_now') <i class="fa fa-arrow-right"></i></span>
                </a>

                <a href="{{ url('/marketplace') }}" class="pm-card">
                    <div class="pm-card-icon">🛒</div>
                    <h3>@lang('home.service_marketplace_title')</h3>
                    <p>@lang('home.service_marketplace_desc')</p>
                    <span class="pm-card-link">@lang('home.view_listings') <i class="fa fa-arrow-right"></i></span>
                </a>

                <a href="/download/driver" class="pm-card">
                    <div class="pm-card-icon">📦</div>
                    <h3>@lang('home.service_delivery_title')</h3>
                    <p>@lang('home.service_delivery_desc')</p>
                    <span class="pm-card-link">@lang('home.ship_via_app') <i class="fa fa-arrow-right"></i></span>
                </a>

                <a href="{{ url('/provider/register') }}" class="pm-card" style="border: 2px solid #C9A84C; background: #fffcf5;">
                    <div class="pm-card-icon">🤝</div>
                    <h3>@lang('home.service_partner_title')</h3>
                    <p>@lang('home.service_partner_desc')</p>
                    <span class="pm-card-link">@lang('home.sign_up_btn') <i class="fa fa-arrow-right"></i></span>
                </a>

            </div>
        </div>
    </section>

    <!-- ═══ DOWNLOAD SECTION ═══ -->
    <section class="pm-download" id="telecharger">
        <div class="container">
            <h2>@lang('home.download_title')</h2>
            <p>Scannez le QR Code ou cliquez sur le bouton pour installer l'application qui correspond à vos besoins.</p>

            <style>
            .pm-dl-flex {
                display: flex;
                gap: 40px;
                justify-content: center;
                flex-wrap: wrap;
                margin-top: 40px;
            }
            .pm-dl-btn-box {
                background: rgba(255,255,255,0.05);
                border: 1px solid rgba(201,168,76,0.3);
                border-radius: 24px;
                padding: 40px 36px;
                text-align: center;
                min-width: 260px;
                max-width: 300px;
                backdrop-filter: blur(12px);
                transition: transform 0.3s, box-shadow 0.3s;
            }
            .pm-dl-btn-box:hover {
                transform: translateY(-6px);
                box-shadow: 0 20px 50px rgba(201,168,76,0.15);
                border-color: #C9A84C;
            }
            .pm-dl-btn-box h4 {
                font-size: 20px;
                font-weight: 800;
                color: #ffffff;
                margin-bottom: 8px;
            }
            .pm-dl-btn-box .pm-dl-sub {
                font-size: 13px;
                color: #a0aec0;
                margin-bottom: 24px;
            }
            .pm-qr-wrap {
                background: #ffffff;
                border-radius: 16px;
                padding: 12px;
                display: inline-block;
                margin-bottom: 24px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            }
            .pm-qr-wrap img {
                width: 160px;
                height: 160px;
                display: block;
            }
            .pm-store-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 14px 28px;
                background: linear-gradient(135deg, #C9A84C, #B89535);
                color: #fff !important;
                border-radius: 12px;
                font-weight: 700;
                font-size: 15px;
                text-decoration: none !important;
                width: 100%;
                transition: opacity 0.2s, transform 0.2s;
                box-shadow: 0 6px 20px rgba(201,168,76,0.4);
            }
            .pm-store-btn:hover {
                opacity: 0.9;
                transform: translateY(-2px);
            }
            .pm-dl-size {
                font-size: 11px;
                color: #a0aec0;
                margin-top: 12px;
                display: block;
            }

            /* ─── APP MODAL ──────────────────────────────────────── */
            .pm-app-modal {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(13,18,38,.6);
                backdrop-filter: blur(8px);
                z-index: 999999;
                align-items: center;
                justify-content: center;
            }
            .pm-app-modal.show { display: flex; }
            .pm-modal-box {
                background: #ffffff;
                border: 1px solid #edf2f7;
                border-radius: 24px;
                padding: 40px;
                max-width: 440px;
                width: 90%;
                text-align: center;
                position: relative;
                box-shadow: 0 40px 80px rgba(0,0,0,.15);
                animation: pm-modal-in .3s ease;
                color: #1a202c;
            }
            @keyframes pm-modal-in {
                from { opacity: 0; transform: scale(.9) translateY(20px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .pm-modal-close {
                position: absolute;
                top: 16px; right: 20px;
                background: none;
                border: none;
                color: #a0aec0;
                font-size: 24px;
                cursor: pointer;
            }
            .pm-qr-box { margin: 0 auto 20px; width: 140px; height: 140px; }
            .pm-qr-box img { width: 100%; height: 100%; border-radius: 12px; }
            .pm-modal-box h3 { font-size: 22px; font-weight: 800; margin-bottom: 12px; color: #1a202c; }
            .pm-modal-box p { font-size: 15px; color: #718096; margin-bottom: 24px; line-height: 1.5; }
            .pm-modal-dl-btns { display: flex; flex-direction: column; gap: 12px; }
            .pm-modal-btn {
                display: flex; align-items: center; justify-content: center; gap: 10px;
                padding: 14px; border-radius: 14px; font-weight: 700; font-size: 16px;
                text-decoration: none; transition: .2s;
            }
            .pm-modal-btn:hover { transform: translateY(-2px); text-decoration: none; }
            .pm-modal-btn-play { background: #0A1628; color: #ffffff !important; }
            .pm-modal-btn-apk { background: #e2e8f0; color: #4a5568 !important; }
            </style>

            <div class="pm-dl-flex">
                <!-- ══ APP CLIENT ══ -->
                <div class="pm-dl-btn-box">
                    <h4>🚕 Pour les Clients</h4>
                    <p class="pm-dl-sub">Réservez votre VTC en quelques secondes</p>
                    <div class="pm-qr-wrap">
                        <img src="{{ asset('asset/img/qr-user.png') }}" alt="QR Code App Client PicMe225" onerror="this.style.display='none'">
                    </div>
                    <a href="javascript:void(0)" onclick="installPWA('{{ Setting::get('store_link_android','#') }}')" class="pm-store-btn">
                        <i class="fa fa-android" style="font-size:20px;"></i> Installer l'Application
                    </a>
                    <span class="pm-dl-size">PWA Web Légère</span>
                </div>

                <!-- ══ APP CHAUFFEUR ══ -->
                <div class="pm-dl-btn-box">
                    <h4>🚗 Pour les Chauffeurs</h4>
                    <p class="pm-dl-sub">Gérez vos courses et gagnez plus</p>
                    <div class="pm-qr-wrap">
                        <img src="{{ asset('asset/img/qr-driver.png') }}" alt="QR Code App Chauffeur PicMe225" onerror="this.style.display='none'">
                    </div>
                    <a href="javascript:void(0)" onclick="installPWA('{{ Setting::get('store_link_android','#') }}')" class="pm-store-btn">
                        <i class="fa fa-android" style="font-size:20px;"></i> Installer l'Application
                    </a>
                    <span class="pm-dl-size">PWA Web Légère</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ APP DOWNLOAD MODAL ═══ -->
    <div class="pm-app-modal" id="pm-app-modal">
        <div class="pm-modal-box">
            <button class="pm-modal-close" onclick="document.getElementById('pm-app-modal').classList.remove('show')">✕</button>
            <div class="pm-qr-box">
                @php
                    $storeLink = Setting::get('store_link_android', 'https://play.google.com');
                    if(empty($storeLink)) $storeLink = 'https://play.google.com';
                @endphp
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($storeLink) }}" alt="QR Code App" onerror="this.style.display='none'" />
            </div>
            <h3>Télécharger l'application</h3>
            <p>Profitez d'une expérience plus rapide, plus fluide, avec les notifications en temps réel en installant notre application.</p>
            <div class="pm-modal-dl-btns">
                <a href="{{ Setting::get('store_link_android','https://play.google.com/store/apps') }}" target="_blank" class="pm-modal-btn pm-modal-btn-play" id="btn-modal-play">
                    <i class="fa fa-android"></i> Google Play Store
                </a>
                <a href="javascript:void(0)" onclick="installPWA('{{ Setting::get('store_link_android','#') }}')" class="pm-modal-btn pm-modal-btn-apk" id="btn-modal-apk">
                    <i class="fa fa-download"></i> Installer l'Application
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ─── CARTE OSM ARRIÈRE-PLAN (══ Section héro) ══
window.addEventListener('load', function() {
    const mapEl = document.getElementById('leaflet-map');
    if (!mapEl || typeof L === 'undefined') return;

    const map = L.map('leaflet-map', {
        zoomControl: false,
        attributionControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        touchZoom: false,
        keyboard: false,
        tap: false
    }).setView([5.3599517, -4.0082563], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        crossOrigin: true
    }).addTo(map);

    setTimeout(() => map.invalidateSize(), 300);
    window._heroMap = map;
});

// ─── Variables globales ───
let originMarker, destMarker, routeLine;
let currentDistanceKm = null;
let currentVariant = 'prive';
let currentServicesJson = '[]';

// Gestion des Catégories et Services
const allCatPills = document.querySelectorAll('.pm-cat-pill');
const servicesList = document.getElementById('services-list');
const selectedServiceId = document.getElementById('selected_service_id');

function getHeroMap() { return window._heroMap; }

function calcPrice(baseFare, perKm, distKm) {
    if (!distKm) return baseFare;
    let price = baseFare + perKm * distKm;
    
    // Ajustement selon la variante
    if (typeof currentVariant !== 'undefined') {
        if (currentVariant === 'partage') price = price * 0.65; // 35% de remise
        if (currentVariant === 'arret_pdp') price = price * 0.3; // 70% de remise
    }
    
    return Math.round(price / 100) * 100;
}

function renderServices(servicesJson) {
    if (servicesJson) currentServicesJson = servicesJson;
    servicesList.innerHTML = '';
    let services;
    try { services = JSON.parse(currentServicesJson || '[]'); } catch(e) { services = []; }

    // Filter by variant
    services = services.filter(svc => {
        if (!svc.allowed_variants || !svc.allowed_variants.length) return true;
        return svc.allowed_variants.includes(currentVariant);
    });

    if (services.length === 0) {
        servicesList.innerHTML = '<div style="color:#94a3b8; font-size:13px; padding: 10px;">Aucun véhicule disponible pour cette option.</div>';
        if (selectedServiceId) selectedServiceId.value = '';
        return;
    }

    services.forEach((svc, index) => {
        const isActive = index === 0 ? 'active' : '';
        if (index === 0 && selectedServiceId) selectedServiceId.value = svc.id;

        let baseFare = svc.pivot?.price || svc.fixed_price || 500;
        let perKm = svc.pivot?.distance || svc.per_km_price || 200;

        const distKm = currentDistanceKm;
        const priceLabel = distKm
            ? `~${calcPrice(baseFare, perKm, distKm).toLocaleString()} FCFA`
            : `À partir de ${Math.round(baseFare).toLocaleString()} FCFA`;

        // Récupérer l'image depuis image_url (appends R2) ou image
        const imgSrc = svc.image_url || svc.image || '';

        const card = document.createElement('div');
        card.className = `pm-vehicle-card ${isActive}`;
        card.dataset.id = svc.id;
        card.dataset.baseFare = baseFare;
        card.dataset.perKm = perKm;
        card.innerHTML = `
            <img src="${imgSrc}" alt="${svc.name}" onerror="this.style.display='none'">
            <div class="pm-vehicle-name">${svc.name}</div>
            <div class="pm-vehicle-meta">
                <span><i class="fa fa-user"></i> ${svc.capacity || '1-4'}</span>
                <span><i class="fa fa-clock-o"></i> ~${Math.floor(Math.random() * 4) + 2} min</span>
            </div>
            <div class="pm-vehicle-price" id="price-${svc.id}">${priceLabel}</div>
        `;

        card.addEventListener('click', () => {
            document.querySelectorAll('.pm-vehicle-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            if (selectedServiceId) selectedServiceId.value = svc.id;
        });

        servicesList.appendChild(card);
    });
}

function updateAllPrices() {
    document.querySelectorAll('.pm-vehicle-card').forEach(card => {
        const id = card.dataset.id;
        const baseFare = parseFloat(card.dataset.baseFare) || 500;
        const perKm = parseFloat(card.dataset.perKm) || 200;
        const priceEl = document.getElementById('price-' + id);
        if (priceEl) {
            priceEl.textContent = currentDistanceKm
                ? `~${calcPrice(baseFare, perKm, currentDistanceKm).toLocaleString()} FCFA`
                : `À partir de ${baseFare.toLocaleString()} FCFA`;
        }
    });
}

// Init avec la première catégorie
if(allCatPills.length > 0) {
    const firstCat = document.querySelector('.pm-cat-pill.active');
    if(firstCat) renderServices(firstCat.getAttribute('data-services'));

    allCatPills.forEach(cat => {
        cat.addEventListener('click', () => {
            allCatPills.forEach(c => c.classList.remove('active'));
            cat.classList.add('active');
            renderServices(cat.getAttribute('data-services'));
        });
    });

    // Types Toggle Logic
    document.querySelectorAll('.pm-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.pm-type-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentVariant = this.dataset.variant;
            renderServices(currentServicesJson);
        });
    });
}

// 3. Autocomplétion Photon — focalisée Abidjan, Ma position en premier
function getDistanceKm(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function tryCalculateDistance() {
    const slat = parseFloat(document.getElementById('s_latitude').value);
    const slng = parseFloat(document.getElementById('s_longitude').value);
    const dlat = parseFloat(document.getElementById('d_latitude').value);
    const dlng = parseFloat(document.getElementById('d_longitude').value);
    const mapObj = window._heroMap;
    if (!isNaN(slat) && !isNaN(slng) && !isNaN(dlat) && !isNaN(dlng) && mapObj) {
        currentDistanceKm = getDistanceKm(slat, slng, dlat, dlng);
        updateAllPrices();
        // Draw route line
        if(routeLine) mapObj.removeLayer(routeLine);
        routeLine = L.polyline([[slat, slng], [dlat, dlng]], {color: '#eab308', weight: 3, dashArray: '6,6'}).addTo(mapObj);
        mapObj.fitBounds([[slat, slng], [dlat, dlng]], {padding: [60, 60]});
    }
}

// Helper: build geoitem
function buildGeoItem(latId, lngId, isOrigin, input, box) {
    const geoItem = document.createElement('div');
    geoItem.className = 'photon-suggestion-item';
    geoItem.innerHTML = `<strong>📍 Ma position actuelle</strong> <span class="city">GPS — Cliquez pour localiser</span>`;
    geoItem.addEventListener('click', () => {
        box.style.display = 'none';
        if (!navigator.geolocation) { alert('Géolocalisation non supportée'); return; }
        input.value = 'Localisation en cours...';
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            document.getElementById(latId).value = lat;
            document.getElementById(lngId).value = lng;
            input.value = 'Ma position';
            const latlng = [lat, lng];
            const mapObj = window._heroMap;
            if (mapObj) {
                mapObj.setView(latlng, 15);
                if(isOrigin) { if(originMarker) mapObj.removeLayer(originMarker); originMarker = L.marker(latlng).addTo(mapObj); }
                else { if(destMarker) mapObj.removeLayer(destMarker); destMarker = L.marker(latlng).addTo(mapObj); }
            }
            tryCalculateDistance();
        }, () => { input.value = ''; alert('Position non disponible. Vérifiez vos permissions.'); });
    });
    return geoItem;
}

// Helper: search photon
function doSearch(q, box, latId, lngId, isOrigin, input, limit = 10) {
    // Si la recherche est vide, on utilise "Abidjan" par défaut pour afficher 10 suggestions
    const searchQuery = q.trim() === '' ? 'Abidjan' : q;
    fetch(`/places/search?q=${encodeURIComponent(searchQuery)}&lat=5.3599&lon=-4.0082&limit=${limit}`)
    .then(res => res.json())
    .then(data => {
        // Remove old results (keep geo item at top)
        const existingItems = box.querySelectorAll('.photon-suggestion-item:not(.geo-item)');
        existingItems.forEach(el => el.remove());

        if(data.features && data.features.length > 0) {
            data.features.slice(0, limit).forEach(f => {
                const props = f.properties;
                const coords = f.geometry.coordinates;
                const name = props.name || props.street || '';
                const city = props.city || props.county || props.state || 'Côte d\'Ivoire';
                if(!name) return;
                
                let highlightedName = name;
                if (q.trim() !== '') {
                    const escapedQ = q.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp(`(${escapedQ})`, 'gi');
                    highlightedName = name.replace(regex, '<span style="color: #eab308;">$1</span>');
                }

                const item = document.createElement('div');
                item.className = 'photon-suggestion-item';
                item.innerHTML = `<strong>${highlightedName}</strong> <span class="city">${city}</span>`;
                item.addEventListener('click', () => {
                    input.value = `${name}${city ? ', ' + city : ''}`;
                    document.getElementById(latId).value = coords[1];
                    document.getElementById(lngId).value = coords[0];
                    box.style.display = 'none';
                    const latlng = [coords[1], coords[0]];
                    const mapObj = window._heroMap;
                    if (mapObj) {
                        mapObj.setView(latlng, 15);
                        if(isOrigin) { if(originMarker) mapObj.removeLayer(originMarker); originMarker = L.marker(latlng).addTo(mapObj); }
                        else { if(destMarker) mapObj.removeLayer(destMarker); destMarker = L.marker(latlng).addTo(mapObj); }
                    }
                    tryCalculateDistance();
                });
                box.appendChild(item);
            });
        }
        box.style.display = 'block';
    }).catch(() => {});
}

function setupPhotonAutocomplete(inputId, suggestionBoxId, latId, lngId, isOrigin) {
    const input = document.getElementById(inputId);
    const box = document.getElementById(suggestionBoxId);
    let timeout = null;

    // Au click/focus : toujours montrer Ma position et 10 suggestions par défaut si vide
    input.addEventListener('focus', function() {
        clearTimeout(timeout);
        box.innerHTML = '';
        const geoItem = buildGeoItem(latId, lngId, isOrigin, input, box);
        geoItem.classList.add('geo-item');
        box.appendChild(geoItem);
        
        if (this.value.length >= 1) {
            doSearch(this.value, box, latId, lngId, isOrigin, input, 10);
        } else {
            doSearch('Abidjan', box, latId, lngId, isOrigin, input, 10);
        }
        box.style.display = 'block';
    });

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const q = this.value;
        if (q.length === 0) {
            box.innerHTML = '';
            const geoItem = buildGeoItem(latId, lngId, isOrigin, input, box);
            geoItem.classList.add('geo-item');
            box.appendChild(geoItem);
            doSearch('Abidjan', box, latId, lngId, isOrigin, input, 10);
            box.style.display = 'block';
            return;
        }
        timeout = setTimeout(() => doSearch(q, box, latId, lngId, isOrigin, input), 350);
    });

    document.addEventListener('click', (e) => {
        if (!box.contains(e.target) && e.target !== input) box.style.display = 'none';
    });
}

setupPhotonAutocomplete('origin-input', 'origin-suggestions', 's_latitude', 's_longitude', true);
setupPhotonAutocomplete('destination-input', 'dest-suggestions', 'd_latitude', 'd_longitude', false);

// 4. Action de Réservation
document.getElementById('btn-estimate').addEventListener('click', function() {
    const slat = document.getElementById('s_latitude').value;
    const slng = document.getElementById('s_longitude').value;
    const dlat = document.getElementById('d_latitude').value;
    const dlng = document.getElementById('d_longitude').value;
    const serviceId = document.getElementById('selected_service_id').value;

    if(!slat || !dlat) {
        alert("Veuillez sélectionner le point de départ et la destination depuis les suggestions.");
        return;
    }
    if(!serviceId) {
        alert("Veuillez sélectionner un type de véhicule.");
        return;
    }

    // Redirect to the actual app client booking page
    window.location.href = `{{ url('/login') }}?redirect=ride`;
});

// ─── Close modal on backdrop ────────────────────────────
document.getElementById('pm-app-modal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});

// ─── Auto-show App Modal ────────────────────────────────
document.addEventListener("DOMContentLoaded", function() {
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (!isStandalone && !sessionStorage.getItem('pmAppModalShown_v2')) {
        setTimeout(function() {
            var modal = document.getElementById('pm-app-modal');
            if (modal) {
                modal.classList.add('show');
                sessionStorage.setItem('pmAppModalShown_v2', 'true');
            }
        }, 4000); // Popup au bout de 4 secondes
    }
});
</script>
@endsection

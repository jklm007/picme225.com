@extends('user.layout.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
*, *::before, *::after { box-sizing: border-box; }

body { background: #0d1226; }

.pm-detail {
    font-family: 'Inter', system-ui, sans-serif;
    background: #0d1226;
    color: #e2e8f0;
    min-height: 100vh;
    padding-bottom: 80px;
}

/* ── BREADCRUMB ── */
.pm-breadcrumb {
    padding: 24px 0 0;
    font-size: 13px;
    color: #718096;
}
.pm-breadcrumb a { color: #C9A84C; text-decoration: none; }
.pm-breadcrumb a:hover { text-decoration: underline; }
.pm-breadcrumb span { margin: 0 6px; }

/* ── GALLERY ── */
.pm-gallery { margin: 24px 0; }

.pm-gallery-main {
    width: 100%;
    height: 420px;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    background: #1a2035;
    cursor: zoom-in;
}
.pm-gallery-main img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
}
.pm-gallery-main img:hover { transform: scale(1.02); }
.pm-gallery-main .pm-no-img {
    display: flex; align-items: center; justify-content: center;
    height: 100%; font-size: 80px;
}

.pm-photo-count {
    position: absolute;
    bottom: 16px; right: 16px;
    background: rgba(0,0,0,.65);
    backdrop-filter: blur(8px);
    color: #fff;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    display: flex; align-items: center; gap: 6px;
}

.pm-gallery-thumbs {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
    scrollbar-color: #C9A84C transparent;
}
.pm-gallery-thumbs::-webkit-scrollbar { height: 4px; }
.pm-gallery-thumbs::-webkit-scrollbar-thumb { background: #C9A84C; border-radius: 2px; }

.pm-thumb {
    flex-shrink: 0;
    width: 90px; height: 70px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all .2s;
    background: #1a2035;
}
.pm-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pm-thumb.active { border-color: #C9A84C; }
.pm-thumb:hover { border-color: rgba(201,168,76,.5); }

/* ── LIGHTBOX ── */
.pm-lightbox {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.92);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
.pm-lightbox.open { display: flex; }
.pm-lightbox img {
    max-width: 90vw; max-height: 85vh;
    border-radius: 12px;
    object-fit: contain;
}
.pm-lightbox-close {
    position: absolute; top: 20px; right: 24px;
    color: #fff; font-size: 32px; cursor: pointer;
    background: none; border: none; line-height: 1;
}
.pm-lightbox-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,.15); border: none; color: #fff;
    font-size: 24px; width: 50px; height: 50px;
    border-radius: 50%; cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    transition: background .2s;
}
.pm-lightbox-nav:hover { background: rgba(201,168,76,.4); }
.pm-lightbox-prev { left: 20px; }
.pm-lightbox-next { right: 20px; }
.pm-lightbox-counter {
    position: absolute; bottom: 20px;
    color: #a0aec0; font-size: 13px;
}

/* ── INFO PANEL ── */
.pm-info-panel {
    background: #131929;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 28px;
    height: fit-content;
}
.pm-badge {
    display: inline-block;
    background: rgba(201,168,76,.15);
    color: #C9A84C;
    border: 1px solid rgba(201,168,76,.3);
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 14px;
}
.pm-detail-title {
    font-size: clamp(20px, 3vw, 28px);
    font-weight: 800;
    color: #fff;
    line-height: 1.3;
    margin-bottom: 16px;
}
.pm-detail-price {
    font-size: 32px;
    font-weight: 900;
    color: #C9A84C;
    margin-bottom: 20px;
    line-height: 1;
}
.pm-detail-price small {
    font-size: 14px;
    font-weight: 400;
    color: #718096;
}

.pm-meta-list {
    list-style: none;
    padding: 0; margin: 0 0 24px;
    display: flex; flex-direction: column; gap: 14px;
}
.pm-meta-list li {
    display: flex; align-items: center; gap: 12px;
    font-size: 15px; color: #cbd5e0;
}
.pm-meta-list li .pm-meta-icon {
    width: 36px; height: 36px;
    background: rgba(201,168,76,0.12);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #C9A84C !important; font-size: 15px;
    flex-shrink: 0;
}
.pm-meta-list li .pm-meta-icon i { color: #C9A84C !important; }
.pm-meta-list li strong { color: #ffffff; font-weight: 700; }
.pm-meta-list li .pm-meta-value { color: #e2e8f0; font-weight: 500; }

.pm-divider {
    border: none;
    border-top: 1px solid rgba(255,255,255,0.10);
    margin: 22px 0;
}

.pm-desc-title {
    font-size: 16px; font-weight: 700; color: #ffffff;
    margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.pm-desc {
    font-size: 15px; color: #cbd5e0;
    line-height: 1.9;
    white-space: pre-line;
}

/* ── CTA BUTTONS ── */
.pm-cta-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 16px;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 800;
    background: linear-gradient(135deg, #C9A84C, #ecc94b);
    color: #0d1226;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all .25s;
    margin-bottom: 12px;
}
.pm-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(201,168,76,.3);
    color: #0d1226; text-decoration: none;
}
.pm-cta-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    background: rgba(255,255,255,0.07);
    color: #e2e8f0;
    border: 1px solid rgba(255,255,255,0.12);
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    margin-bottom: 8px;
}
.pm-cta-secondary:hover {
    background: rgba(255,255,255,0.12);
    color: #fff; text-decoration: none;
}
.pm-cta-whatsapp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 16px;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 800;
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: #fff;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all .25s;
    margin-bottom: 12px;
    box-shadow: 0 4px 20px rgba(37,211,102,0.3);
}
.pm-cta-whatsapp:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(37,211,102,0.45);
    color: #fff; text-decoration: none;
}

/* Social share buttons */
.pm-share-btn {
    width: 42px; height: 42px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    transition: all .2s;
    background: #000;
    color: #C9A84C !important;
    border: 1px solid rgba(201,168,76,0.5);
    font-size: 17px;
}
.pm-share-btn svg { fill: #C9A84C !important; }
.pm-share-btn:hover { 
    transform: translateY(-3px) scale(1.1); 
    text-decoration: none; 
    border-color: #C9A84C; 
    box-shadow: 0 4px 12px rgba(201,168,76,0.2); 
}

/* ── RELATED ── */
.pm-related { margin-top: 48px; }
.pm-related-title { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 20px; }
.pm-related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}
.pm-rel-card {
    background: #131929;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    text-decoration: none;
    display: block;
    transition: all .25s;
}
.pm-rel-card:hover { transform: translateY(-4px); border-color: rgba(201,168,76,.3); text-decoration: none; }
.pm-rel-card img, .pm-rel-card .pm-rel-no-img {
    width: 100%; height: 140px;
    object-fit: cover;
    display: flex; align-items: center; justify-content: center;
    background: #1a2035; font-size: 40px;
}
.pm-rel-body { padding: 14px; }
.pm-rel-title { font-size: 14px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
.pm-rel-price { font-size: 15px; font-weight: 800; color: #C9A84C; }

/* ── APP MODAL ── */
.pm-app-modal {
    display: none; position: fixed; inset: 0;
    background: rgba(13,18,38,.7); backdrop-filter: blur(10px);
    z-index: 9998; align-items: center; justify-content: center;
}
.pm-app-modal.show { display: flex; }
.pm-modal-box {
    background: #131929;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    padding: 40px;
    max-width: 440px; width: 90%;
    text-align: center;
    position: relative;
    box-shadow: 0 40px 80px rgba(0,0,0,.5);
    animation: pm-in .3s ease;
    color: #e2e8f0;
}
@keyframes pm-in { from { opacity:0; transform:scale(.9) translateY(20px); } to { opacity:1; transform:scale(1) translateY(0); } }
.pm-modal-close { position:absolute; top:16px; right:20px; background:none; border:none; color:#718096; font-size:22px; cursor:pointer; }
.pm-modal-close:hover { color:#fff; }
.pm-modal-box h3 { font-size:22px; font-weight:800; margin-bottom:10px; color:#fff; }
.pm-modal-box p { font-size:14px; color:#a0aec0; margin-bottom:24px; line-height:1.7; }
.pm-modal-dl-btns { display:flex; flex-direction:column; gap:12px; }
.pm-modal-btn {
    display:flex; align-items:center; justify-content:center; gap:12px;
    padding:14px 24px; border-radius:12px; font-size:15px; font-weight:700;
    text-decoration:none; transition:all .2s;
}
.pm-modal-btn:hover { text-decoration:none; transform:translateY(-2px); }
.pm-modal-btn-play { background:linear-gradient(135deg,#34a853,#1a8e3e); color:#fff; }
.pm-modal-btn-apk { background:rgba(255,255,255,.08); color:#e2e8f0; border:1px solid rgba(255,255,255,.12); }
.pm-modal-btn-apk:hover { background:rgba(255,255,255,.14); color:#fff; }
.pm-qr-box { width:140px; height:140px; background:#fff; border-radius:16px; padding:8px; margin:0 auto 20px; }
.pm-qr-box img { width:100%; height:100%; }
</style>

{{-- LIGHTBOX --}}
<div class="pm-lightbox" id="pm-lightbox">
    <button class="pm-lightbox-close" onclick="closeLightbox()">✕</button>
    <button class="pm-lightbox-nav pm-lightbox-prev" onclick="lightboxNav(-1)">&#8249;</button>
    <img id="pm-lightbox-img" src="" alt="Photo annonce" />
    <button class="pm-lightbox-nav pm-lightbox-next" onclick="lightboxNav(1)">&#8250;</button>
    <span class="pm-lightbox-counter" id="pm-lightbox-counter"></span>
</div>

{{-- APP MODAL --}}
<div class="pm-app-modal" id="pm-app-modal">
    <div class="pm-modal-box">
        <button class="pm-modal-close" onclick="document.getElementById('pm-app-modal').classList.remove('show')">✕</button>
        <div class="pm-qr-box">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ urlencode(Setting::get('store_link_android','https://play.google.com')) }}" alt="QR App" />
        </div>
        <h3>Discuter avec le vendeur</h3>
        <p>Pour contacter le vendeur et finaliser votre achat en toute sécurité, téléchargez l'application <strong>PickMe225</strong> sur votre smartphone.</p>
        <div class="pm-modal-dl-btns">
            <a href="{{ Setting::get('store_link_android','https://play.google.com/store/apps') }}" target="_blank" class="pm-modal-btn pm-modal-btn-play">
                <i class="fa fa-android" style="font-size:20px"></i> Google Play Store
            </a>
            <a href="javascript:void(0)" onclick="installPWA('{{ Setting::get('store_link_android','#') }}')" class="pm-modal-btn pm-modal-btn-apk">
                <i class="fa fa-download"></i> Installer l'Application
            </a>
        </div>
    </div>
</div>

<div class="pm-detail">
<div class="container">

    {{-- BREADCRUMB --}}
    <nav class="pm-breadcrumb">
        <a href="/marketplace">Marketplace</a>
        <span>›</span>
        <span>{{ $listing->category ?? 'Annonce' }}</span>
        <span>›</span>
        <span style="color:#e2e8f0;">{{ \Illuminate\Support\Str::limit($listing->title, 40) }}</span>
    </nav>

    <div class="row mt-4" style="gap:0;">

        {{-- LEFT: GALLERY --}}
        <div class="col-lg-7">
            <div class="pm-gallery">
                @php
                    $allImages = [];
                    if ($listing->cover_image) $allImages[] = $listing->cover_image;
                    if (!empty($listing->images)) {
                        foreach ($listing->images as $img) {
                            if ($img && $img !== $listing->cover_image) $allImages[] = $img;
                        }
                    }
                    $firstImg = $allImages[0] ?? null;

                    $imgUrlFn = function($src) {
                        return img($src);
                    };
                    $catEmojiFn = function($cat) {
                        if (!$cat) return '📦';
                        $cat = strtoupper($cat);
                        if (str_contains($cat,'VEHICL') || str_contains($cat,'AUTO')) return '🚗';
                        if (str_contains($cat,'REAL_ESTATE') || str_contains($cat,'IMMO')) return '🏠';
                        if (str_contains($cat,'TICKET') || str_contains($cat,'EVENT')) return '🎫';
                        if (str_contains($cat,'SERVICE')) return '🛠️';
                        return '📦';
                    };
                @endphp

                <div class="pm-gallery-main" id="pm-main-img-wrap" onclick="openLightbox(0)">
                    @if($firstImg)
                        <img id="pm-main-img" src="{{ $imgUrlFn($firstImg) }}" alt="{{ $listing->title }}" />
                    @else
                        <div class="pm-no-img">{{ $catEmojiFn($listing->category) }}</div>
                    @endif
                    @if(count($allImages) > 1)
                    <div class="pm-photo-count">
                        <i class="fa fa-camera"></i> {{ count($allImages) }} photos
                    </div>
                    @endif
                </div>

                @if(count($allImages) > 1)
                <div class="pm-gallery-thumbs" id="pm-thumbs">
                    @foreach($allImages as $idx => $img)
                    <div class="pm-thumb {{ $idx === 0 ? 'active' : '' }}" onclick="setMainImg({{ $idx }})" data-idx="{{ $idx }}">
                        <img src="{{ $imgUrlFn($img) }}" alt="Photo {{ $idx + 1 }}" loading="lazy" />
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- DESCRIPTION (mobile: below info, desktop: below gallery) --}}
            @if($listing->description)
            <div class="pm-info-panel d-none d-lg-block mt-3">
                <div class="pm-desc-title">📋 Description</div>
                <div class="pm-desc">{{ $listing->description }}</div>
            </div>
            @endif
        </div>

        {{-- RIGHT: INFO PANEL --}}
        <div class="col-lg-5 ps-lg-4 mt-4 mt-lg-0">
            <div class="pm-info-panel">

                <div class="pm-badge">{{ $listing->category ?? 'Annonce' }}{{ $listing->sub_category ? ' · ' . $listing->sub_category : '' }}</div>
                @if($listing->type === 'SEARCH')
                <div class="pm-badge" style="background:#FF4B7C; color:#fff; border:none; letter-spacing:.5px; margin-left: 8px;"><i class="fa fa-search"></i> RECHERCHE</div>
                @endif

                <div class="pm-detail-title">{{ $listing->title }}</div>

                <div class="pm-detail-price">
                    {{ number_format($listing->price, 0, ',', ' ') }}
                    <small>{{ $listing->price_unit ?? 'FCFA' }}</small>
                </div>

                <ul class="pm-meta-list">
                    @if($listing->location_city || true)
                    @php
                        $loc = $listing->location_city;
                        if (!$loc || strtolower(trim($loc)) === 'non spécifié' || strtolower(trim($loc)) === 'non specifie') {
                            $loc = 'Abidjan';
                        }
                    @endphp
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-map-marker"></i></div>
                        <span><strong>Lieu</strong> &nbsp;<span class="pm-meta-value">{{ $loc }}</span></span>
                    </li>
                    @endif
                    @if($listing->brand)
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-tag"></i></div>
                        <span><strong>Marque</strong> &nbsp;<span class="pm-meta-value">{{ $listing->brand }}</span></span>
                    </li>
                    @endif
                    @if($listing->model ?? ($listing->metadata['model'] ?? null))
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-car"></i></div>
                        <span><strong>Modèle</strong> &nbsp;<span class="pm-meta-value">{{ $listing->model ?? ($listing->metadata['model'] ?? '') }}</span></span>
                    </li>
                    @endif
                    @if($listing->year ?? ($listing->metadata['year'] ?? null))
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-calendar"></i></div>
                        <span><strong>Année</strong> &nbsp;<span class="pm-meta-value">{{ $listing->year ?? ($listing->metadata['year'] ?? '') }}</span></span>
                    </li>
                    @endif
                    @if(isset($listing->metadata['condition']) || isset($listing->metadata['etat']))
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-star"></i></div>
                        <span><strong>État</strong> &nbsp;<span class="pm-meta-value">{{ $listing->metadata['condition'] ?? ($listing->metadata['etat'] ?? 'Non spécifié') }}</span></span>
                    </li>
                    @endif
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-user"></i></div>
                        <span><strong>Vendeur</strong> &nbsp;<span class="pm-meta-value">{{ $listing->owner_name ?? ($listing->user ? $listing->user->first_name . ' ' . $listing->user->last_name : 'Particulier') }}</span></span>
                    </li>
                    <li>
                        <div class="pm-meta-icon"><i class="fa fa-clock-o"></i></div>
                        <span><strong>Publié</strong> &nbsp;<span class="pm-meta-value">{{ $listing->created_at->diffForHumans() }}</span></span>
                    </li>
                </ul>

                <hr class="pm-divider">

                {{-- CTA --}}
                {{-- Bouton principal : Contacter le Vendeur via WhatsApp --}}
                @php
                    $sellerPhone = $listing->owner_phone ?? ($listing->user->mobile ?? '2250759747444');
                    $cleanPhone = preg_replace('/[^0-9]/', '', $sellerPhone);
                    if (!str_starts_with($cleanPhone, '225') && strlen($cleanPhone) == 10) {
                        $cleanPhone = '225' . $cleanPhone;
                    }
                @endphp
                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Bonjour, je suis intéressé(e) par l\'annonce : ' . $listing->title . ' — ' . url('/marketplace/' . $listing->id)) }}" class="pm-cta-whatsapp" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.374 0 0 5.373 0 12c0 2.117.554 4.103 1.523 5.83L.057 23.999l6.305-1.654A11.937 11.937 0 0012 24c6.626 0 12-5.374 12-12S18.626 0 12 0zm0 21.818a9.818 9.818 0 01-5.006-1.376l-.358-.214-3.741.981.999-3.648-.233-.374A9.817 9.817 0 012.182 12C2.182 6.578 6.578 2.182 12 2.182S21.818 6.578 21.818 12 17.422 21.818 12 21.818z"/></svg>
                    Contacter le Vendeur
                </a>

                {{-- Bouton secondaire : Télécharger l'app --}}
                <button class="pm-cta-secondary" onclick="document.getElementById('pm-app-modal').classList.add('show')">
                    <i class="fa fa-download"></i> Télécharger l'app PickMe225
                </button>

                <hr class="pm-divider">

                {{-- PARTAGE --}}
                <div style="display: flex; gap: 12px; margin-bottom: 20px; align-items: center; flex-wrap: wrap;">
                    <strong style="color: #ffffff; font-size: 14px; font-weight: 700;">Partager :</strong>

                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/marketplace/' . $listing->id)) }}" target="_blank" class="pm-share-btn pm-share-fb" title="Partager sur Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>

                    {{-- TikTok --}}
                    <a href="https://www.tiktok.com/@picme225" target="_blank" class="pm-share-btn pm-share-tiktok" title="TikTok">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 448 512" fill="currentColor"><path d="M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z"/></svg>
                    </a>

                    {{-- WhatsApp --}}
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($listing->title . ' — ' . url('/marketplace/' . $listing->id)) }}" target="_blank" class="pm-share-btn pm-share-wa" title="Partager sur WhatsApp">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.374 0 0 5.373 0 12c0 2.117.554 4.103 1.523 5.83L.057 23.999l6.305-1.654A11.937 11.937 0 0012 24c6.626 0 12-5.374 12-12S18.626 0 12 0zm0 21.818a9.818 9.818 0 01-5.006-1.376l-.358-.214-3.741.981.999-3.648-.233-.374A9.817 9.817 0 012.182 12C2.182 6.578 6.578 2.182 12 2.182S21.818 6.578 21.818 12 17.422 21.818 12 21.818z"/></svg>
                    </a>
                </div>

            </div>

            {{-- DESCRIPTION on mobile --}}
            @if($listing->description)
            <div class="pm-info-panel d-lg-none mt-3">
                <div class="pm-desc-title">📋 Description</div>
                <div class="pm-desc">{{ $listing->description }}</div>
            </div>
            @endif
        </div>

    </div>{{-- /row --}}

    {{-- RELATED LISTINGS --}}
    @if(isset($related) && $related->count() > 0)
    <div class="pm-related">
        <div class="pm-related-title">Annonces similaires</div>
        <div class="pm-related-grid">
            @foreach($related as $rel)
            @php
                $relUrl = $imgUrlFn($rel->cover_image);
            @endphp
            <a href="/marketplace/{{ $rel->id }}" class="pm-rel-card">
                @if($relUrl)
                    <img src="{{ $relUrl }}" alt="{{ $rel->title }}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\'pm-rel-no-img\'>📦</div>'" />
                @else
                    <div class="pm-rel-no-img">{{ $catEmojiFn($rel->category) }}</div>
                @endif
                <div class="pm-rel-body">
                    <div class="pm-rel-title">{{ $rel->title }}</div>
                    <div class="pm-rel-price">{{ number_format($rel->price, 0, ',', ' ') }} FCFA</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
</div>
@endsection

@section('scripts')
<script>
// ─── Images data ────────────────────────────────────
var pmImages = [
    @foreach($allImages as $img)
    @php $pmImgUrl = img($img); @endphp
    "{{ addslashes($pmImgUrl) }}",
    @endforeach
];
var pmCurrent = 0;

// ─── Thumbnail switcher ─────────────────────────────
function setMainImg(idx) {
    pmCurrent = idx;
    var mainImg = document.getElementById('pm-main-img');
    if (mainImg && pmImages[idx]) {
        mainImg.src = pmImages[idx];
    }
    document.querySelectorAll('.pm-thumb').forEach(function(t) {
        t.classList.toggle('active', parseInt(t.dataset.idx) === idx);
    });
}

// ─── Lightbox ───────────────────────────────────────
function openLightbox(idx) {
    if (!pmImages.length) return;
    pmCurrent = idx;
    document.getElementById('pm-lightbox-img').src = pmImages[idx];
    document.getElementById('pm-lightbox-counter').textContent = (idx+1) + ' / ' + pmImages.length;
    document.getElementById('pm-lightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('pm-lightbox').classList.remove('open');
}
function lightboxNav(dir) {
    pmCurrent = (pmCurrent + dir + pmImages.length) % pmImages.length;
    document.getElementById('pm-lightbox-img').src = pmImages[pmCurrent];
    document.getElementById('pm-lightbox-counter').textContent = (pmCurrent+1) + ' / ' + pmImages.length;
}
document.getElementById('pm-lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});
document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('pm-lightbox');
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') lightboxNav(1);
    if (e.key === 'ArrowLeft') lightboxNav(-1);
});

// ─── Close app modal on backdrop ────────────────────
document.getElementById('pm-app-modal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});
</script>
@endsection

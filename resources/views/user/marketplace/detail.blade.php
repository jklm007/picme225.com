@extends('user.layout.base')

@section('title', $listing->title)

@section('styles')
<style>
    .dash-left, .footer-content, .menu-toggle, .overlay { display: none !important; }
    
    :root {
        --primary-bg: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-lg: 20px;
        --radius-md: 12px;
        --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .pm-detail-wrapper {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background: var(--primary-bg);
        min-height: 100vh;
        padding-top: var(--header-h);
        padding-bottom: calc(var(--nav-h) + 40px);
    }

    .pm-detail-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 16px;
    }

    .pm-back-bar {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }

    .pm-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--navy);
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        background: var(--card-bg);
        padding: 10px 18px;
        border-radius: 30px;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }

    .pm-back-btn:hover {
        transform: translateX(-4px);
        color: var(--gold);
        text-decoration: none;
    }

    .pm-main-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        margin-bottom: 24px;
    }

    /* Carousel / Gallery */
    .pm-gallery-container {
        position: relative;
        background: #000;
        height: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pm-gallery-main {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .pm-gallery-main img, .pm-gallery-main video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pm-gallery-thumbs {
        display: flex;
        gap: 10px;
        padding: 12px 16px;
        overflow-x: auto;
        background: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        -webkit-overflow-scrolling: touch;
    }

    .pm-gallery-thumbs::-webkit-scrollbar {
        display: none;
    }

    .pm-thumb-item {
        width: 64px;
        height: 64px;
        border-radius: var(--radius-md);
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .pm-thumb-item.active {
        border-color: var(--gold);
        transform: scale(1.05);
    }

    .pm-thumb-video-wrapper {
        position: relative;
        width: 64px;
        height: 64px;
        flex-shrink: 0;
        border-radius: var(--radius-md);
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .pm-thumb-video-wrapper.active {
        border-color: var(--gold);
        transform: scale(1.05);
    }

    .pm-thumb-video-wrapper video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pm-thumb-video-wrapper i {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ffffff;
        font-size: 20px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    /* Product Details */
    .pm-details-body {
        padding: 24px;
    }

    .pm-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }

    .pm-badge-cat {
        display: inline-block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--gold);
        letter-spacing: 1px;
        background: rgba(201, 168, 76, 0.1);
        padding: 4px 10px;
        border-radius: 20px;
    }

    .pm-badge-digital {
        display: inline-block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #3b82f6;
        letter-spacing: 1px;
        background: rgba(59, 130, 246, 0.1);
        padding: 4px 10px;
        border-radius: 20px;
    }

    .pm-product-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        margin: 8px 0;
        line-height: 1.3;
    }

    .pm-product-price {
        font-size: 26px;
        font-weight: 900;
        color: var(--gold);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pm-product-price small {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .pm-divider {
        height: 1px;
        background: var(--border-color);
        margin: 24px 0;
    }

    /* Technical Attributes Grid */
    .pm-tech-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .pm-tech-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .pm-tech-card {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        padding: 12px 16px;
        border-radius: var(--radius-md);
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .pm-tech-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pm-tech-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--navy);
    }

    /* Sections */
    .pm-section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pm-desc-text {
        font-size: 14px;
        line-height: 1.6;
        color: #334155;
        margin-bottom: 0;
        white-space: pre-wrap;
    }

    /* Vendor card */
    .pm-vendor-box {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #f8fafc;
        border: 1px solid var(--border-color);
        padding: 16px;
        border-radius: var(--radius-md);
    }

    .pm-vendor-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--navy);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
    }

    .pm-vendor-info {
        flex: 1;
    }

    .pm-vendor-name {
        font-weight: 700;
        font-size: 15px;
        color: var(--text-main);
    }

    .pm-vendor-badge {
        font-size: 11px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Action buttons */
    .pm-actions-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 24px;
    }

    .pm-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 700;
        font-size: 14px;
        padding: 14px;
        border-radius: var(--radius-md);
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pm-btn:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }

    .pm-btn-call {
        background: var(--gold);
        color: var(--navy);
        box-shadow: 0 4px 14px rgba(201, 168, 76, 0.25);
    }

    .pm-btn-call:hover {
        background: var(--gold-light);
        color: var(--navy);
    }

    .pm-btn-wa {
        background: #25d366;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(37, 211, 102, 0.25);
    }

    .pm-btn-wa:hover {
        background: #20ba5a;
        color: #ffffff;
    }

    .pm-btn-buy {
        grid-column: span 2;
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        font-size: 16px;
        padding: 16px;
    }

    .pm-btn-buy:hover {
        background: #059669;
        color: #ffffff;
    }

    .pm-btn-download {
        grid-column: span 2;
        background: #3b82f6;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.25);
        font-size: 16px;
        padding: 16px;
    }

    .pm-btn-download:hover {
        background: #2563eb;
        color: #ffffff;
    }

    /* Similar Products */
    .pm-similar-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--navy);
        margin-bottom: 16px;
    }

    .pm-similar-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .pm-similar-card {
        background: var(--card-bg);
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        overflow: hidden;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }

    .pm-similar-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        text-decoration: none;
    }

    .pm-similar-img {
        height: 120px;
        background: #f1f5f9;
        position: relative;
    }

    .pm-similar-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pm-similar-body {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .pm-similar-name {
        font-weight: 700;
        font-size: 13px;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pm-similar-price {
        font-weight: 800;
        font-size: 14px;
        color: var(--gold);
    }
.page-content.dashboard-page { padding-top: 0 !important; }
</style>
@endsection

@section('content')
@php
    // Web Desktop and PWA proxy their phone actions to the administrator to keep transaction tracking secure
    $useAdminContact = true; 
    $adminPhone = \Setting::get('contact_number', '911');
    
    // Choose appropriate number
    $phoneToCall = $adminPhone;
    
    // Normalize phone numbers for WhatsApp
    $waPhone = preg_replace('/[^0-9]/', '', $phoneToCall);
@endphp

<div class="pm-detail-wrapper">
    <div class="pm-detail-container">
        
        {{-- Back Action --}}
        <div class="pm-back-bar">
            <a href="{{ route('user.marketplace.explore') }}" class="pm-back-btn">
                <i class="fa fa-arrow-left"></i> Retour au Marché
            </a>
        </div>

        {{-- Main Detail Card --}}
        <div class="pm-main-card">
            
            {{-- Image Gallery / Carousel --}}
            <div class="pm-gallery-container">
                @php
                    $allImages = $listing->images ?? [];
                    $cover = $listing->cover_image ? img($listing->cover_image) : asset('images/default_product.png');
                    $is_video = str_ends_with(strtolower($cover), '.mp4') || str_ends_with(strtolower($cover), '.mov') || str_ends_with(strtolower($cover), '.avi');
                @endphp
                <img src="{{ $cover }}" id="mainGalleryImage" alt="{{ $listing->title }}" style="width:100%; height:100%; object-fit:cover; {{ $is_video ? 'display:none;' : 'display:block;' }}">
                <video id="mainGalleryVideo" src="{{ $is_video ? $cover : '' }}" controls style="width:100%; height:100%; object-fit:cover; {{ $is_video ? 'display:block;' : 'display:none;' }}"></video>
            </div>

            {{-- Thumbnails list --}}
            @if(count($allImages) > 1)
            <div class="pm-gallery-thumbs">
                @foreach($allImages as $idx => $img)
                    @php
                        $resolvedImg = img($img);
                        $thumb_is_video = str_ends_with(strtolower($resolvedImg), '.mp4') || str_ends_with(strtolower($resolvedImg), '.mov') || str_ends_with(strtolower($resolvedImg), '.avi');
                    @endphp
                    @if($thumb_is_video)
                        <div class="pm-thumb-video-wrapper {{ $idx === 0 ? 'active' : '' }}" onclick="switchGallery('{{ $resolvedImg }}', this, true)">
                            <video src="{{ $resolvedImg }}" muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                            <i class="fa fa-play-circle"></i>
                        </div>
                    @else
                        <img src="{{ $resolvedImg }}" class="pm-thumb-item {{ $idx === 0 ? 'active' : '' }}" onclick="switchGallery('{{ $resolvedImg }}', this, false)" alt="Thumbnail">
                    @endif
                @endforeach
            </div>
            @endif

            {{-- Body Info --}}
            <div class="pm-details-body">
                <div class="pm-meta-row">
                    <span class="pm-badge-cat">{{ $listing->category }}</span>
                    @if($listing->is_digital)
                        <span class="pm-badge-digital"><i class="fa fa-cloud-download"></i> Produit Digital</span>
                    @endif
                </div>

                <h2 class="pm-product-title">{{ $listing->title }}</h2>
                <div class="pm-product-price">
                    {{ number_format($listing->price, 0, ',', ' ') }} <small>{{ $listing->price_unit ?? 'FCFA' }}</small>
                </div>

                <div class="pm-divider"></div>

                {{-- Fields Grid depending on Category --}}
                @php
                    $cat = strtoupper((string) $listing->category);
                    $showTechGrid = (strpos($cat, 'VEHIC') !== false || strpos($cat, 'REAL_ESTATE') !== false || strpos($cat, 'IMMOBILIER') !== false);
                @endphp

                @if($showTechGrid)
                    <div class="pm-tech-grid">
                        @if($listing->brand)
                            <div class="pm-tech-card">
                                <span class="pm-tech-label">Marque</span>
                                <span class="pm-tech-value">{{ $listing->brand }}</span>
                            </div>
                        @endif
                        @if($listing->model)
                            <div class="pm-tech-card">
                                <span class="pm-tech-label">Modèle</span>
                                <span class="pm-tech-value">{{ $listing->model }}</span>
                            </div>
                        @endif
                        @if($listing->year)
                            <div class="pm-tech-card">
                                <span class="pm-tech-label">Année</span>
                                <span class="pm-tech-value">{{ $listing->year }}</span>
                            </div>
                        @endif
                        @if($listing->color)
                            <div class="pm-tech-card">
                                <span class="pm-tech-label">Couleur</span>
                                <span class="pm-tech-value">{{ $listing->color }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Condition & Location (Not displayed for Digital Products / Services) --}}
                @if(!$listing->is_digital && strpos($cat, 'SERVICE') === false)
                    <div class="pm-tech-grid" style="margin-top: -12px; margin-bottom: 24px;">
                        <div class="pm-tech-card">
                            <span class="pm-tech-label">Localisation</span>
                            <span class="pm-tech-value"><i class="fa fa-map-marker" style="color:var(--gold);"></i> {{ $listing->location_city ?? 'Abidjan' }}</span>
                        </div>
                        <div class="pm-tech-card">
                            <span class="pm-tech-label">État</span>
                            <span class="pm-tech-value">{{ isset($listing->metadata['condition']) && $listing->metadata['condition'] === 'new' ? 'Neuf' : 'Excellent état' }}</span>
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                <div class="pm-section-title"><i class="fa fa-file-text-o"></i> Description</div>
                <p class="pm-desc-text">{{ $listing->description }}</p>

                <div class="pm-divider"></div>

                {{-- Seller detail block (Only displayed for Non-Digital goods) --}}
                @if(!$listing->is_digital)
                    <div class="pm-section-title"><i class="fa fa-user-circle-o"></i> Vendeur</div>
                    <div class="pm-vendor-box">
                        <div class="pm-vendor-avatar">
                            {{ strtoupper(substr($listing->user->first_name ?? ($useAdminContact ? 'A' : 'V'), 0, 1)) }}
                        </div>
                        <div class="pm-vendor-info">
                            <div class="pm-vendor-name">
                                @if($useAdminContact)
                                    Service Client PicMe (Administrateur)
                                @else
                                    {{ $listing->user->first_name ?? 'Vendeur' }} {{ $listing->user->last_name ?? '' }}
                                @endif
                            </div>
                            <div class="pm-vendor-badge"><i class="fa fa-check-circle" style="color:#27ae60;"></i> Membre Vérifié PicMe</div>
                        </div>
                    </div>
                @endif

                {{-- Action Panel --}}
                @if($listing->category === 'TICKETS')
                    @php
                        $passes = \App\Models\EventPassType::where('listing_id', $listing->id)->get();
                    @endphp
                    @if($passes->count() > 0)
                        <div class="pm-section-title" style="margin-top: 24px;"><i class="fa fa-ticket"></i> Sélection de Billets</div>
                        <select id="ticketPassSelect" class="pm-tech-card" style="width:100%; border:1px solid #e2e8f0; margin-bottom: 16px; font-size: 14px; padding: 12px; font-family: inherit;">
                            <option value="-1">Billet Standard - {{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->price_unit ?? 'FCFA' }}</option>
                            @foreach($passes as $pass)
                                <option value="{{ $pass->id }}">{{ $pass->pass_name }} - {{ number_format($pass->price, 0, ',', ' ') }} {{ $listing->price_unit ?? 'FCFA' }} ({{ $pass->persons_per_pass }} pers.)</option>
                            @endforeach
                        </select>
                    @endif
                @endif
                
                <div class="pm-actions-bar">
                    @if($listing->category === 'TICKETS')
                        <button onclick="buyTicketEvent()" class="pm-btn pm-btn-buy">
                            <i class="fa fa-ticket"></i> Acheter le Billet
                        </button>
                    @elseif($listing->is_digital)
                        @if($isPurchased)
                            <a href="{{ route('user.marketplace.download_digital', $listing->id) }}" class="pm-btn pm-btn-download">
                                <i class="fa fa-download"></i> Télécharger le Produit
                            </a>
                        @else
                            <button onclick="buyDigitalProduct()" class="pm-btn pm-btn-buy">
                                <i class="fa fa-shopping-basket"></i> Acheter Maintenant ({{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->price_unit ?? 'FCFA' }})
                            </button>
                        @endif
                    @else
                        {{-- Standard physical product contact panel --}}
                        <a href="tel:{{ $phoneToCall }}" class="pm-btn pm-btn-call">
                            <i class="fa fa-phone"></i> Appeler
                        </a>
                        <a href="https://wa.me/{{ $waPhone }}?text=Bonjour,%20je%20suis%20intéressé%20par%20votre%20annonce%20:%20{{ urlencode($listing->title) }}" class="pm-btn pm-btn-wa" target="_blank">
                            <i class="fa fa-whatsapp"></i> WhatsApp
                        </a>
                    @endif
                </div>

            </div>
        </div>

        {{-- Related / Similar listings --}}
        @if(count($related) > 0)
        <div class="pm-similar-section">
            <h3 class="pm-similar-title">Annonces Similaires</h3>
            <div class="pm-similar-grid">
                @foreach($related as $rel)
                    @php
                        $relCover = $rel->cover_image ? img($rel->cover_image) : asset('images/default_product.png');
                    @endphp
                    <a href="{{ route('user.marketplace.detail', $rel->id) }}" class="pm-similar-card">
                        <div class="pm-similar-img">
                            <img src="{{ $relCover }}" alt="{{ $rel->title }}">
                        </div>
                        <div class="pm-similar-body">
                            <div class="pm-similar-name">{{ $rel->title }}</div>
                            <div class="pm-similar-price">{{ number_format($rel->price, 0, ',', ' ') }} {{ $rel->price_unit ?? 'FCFA' }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@include('user.include.bottom_nav', ['active' => 'store'])
@endsection

@section('scripts')
<script>
function switchGallery(src, el, isVideo) {
    const imgEl = document.getElementById('mainGalleryImage');
    const videoEl = document.getElementById('mainGalleryVideo');
    
    if (isVideo) {
        imgEl.style.display = 'none';
        videoEl.src = src;
        videoEl.style.display = 'block';
        videoEl.play();
    } else {
        videoEl.pause();
        videoEl.style.display = 'none';
        videoEl.src = '';
        imgEl.src = src;
        imgEl.style.display = 'block';
    }
    
    document.querySelectorAll('.pm-gallery-thumbs img, .pm-gallery-thumbs .pm-thumb-video-wrapper').forEach(function(thumb) {
        thumb.classList.remove('active');
    });
    el.classList.add('active');
}

function buyDigitalProduct() {
    if (confirm("Voulez-vous acheter ce produit digital pour {{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->price_unit ?? 'FCFA' }} avec votre solde ?")) {
        $.ajax({
            url: "{{ route('user.marketplace.purchase_digital', $listing->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status === 402) {
                    const data = xhr.responseJSON;
                    if (confirm(data.message + "\nSouhaitez-vous recharger votre portefeuille ?")) {
                        window.location.href = data.recharge_url;
                    }
                } else {
                    alert("Une erreur s'est produite lors de l'achat : " + (xhr.responseJSON?.error || xhr.statusText));
                }
            }
        });
    }
}
function buyTicketEvent() {
    let passId = -1;
    const select = document.getElementById('ticketPassSelect');
    if (select) {
        passId = select.value;
    }

    if (confirm("Voulez-vous acheter ce billet avec votre solde principal ?")) {
        $.ajax({
            url: "{{ route('user.marketplace.buy_ticket', $listing->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                payment_mode: 'WALLET',
                pass_type_id: passId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    if (response.ticket_url) {
                        window.location.href = response.ticket_url;
                    } else {
                        window.location.reload();
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 402) {
                    const data = xhr.responseJSON;
                    if (confirm(data.message + "\nSouhaitez-vous recharger votre portefeuille ?")) {
                        window.location.href = data.recharge_url;
                    }
                } else {
                    alert("Une erreur s'est produite lors de l'achat : " + (xhr.responseJSON?.error || xhr.statusText));
                }
            }
        });
    }
}
</script>
@endsection

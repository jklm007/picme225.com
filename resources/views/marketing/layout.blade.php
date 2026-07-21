<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">

    {{-- SEO --}}
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:site_name" content="PicMe">

    {{-- Favicon --}}
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="{{ asset('asset/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    {{-- Marketing CSS --}}
    <link href="{{ asset('asset/css/marketing.css') }}" rel="stylesheet">

    {{-- Google Tag Manager --}}
    @include('marketing.partials.tracking-head')

    {{-- Schema.org --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "PicMe Pro",
        "description": "{{ $description }}",
        "url": "https://www.picme225.site",
        "telephone": "{{ $phone_display }}",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "{{ $city }}",
            "addressCountry": "CI"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "5.3600",
            "longitude": "-4.0083"
        },
        "openingHoursSpecification": {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
            "opens": "00:00",
            "closes": "23:59"
        },
        "priceRange": "$$"
    }
    </script>
</head>
<body>
    {{-- GTM noscript --}}
    @include('marketing.partials.tracking-body')

    {{-- Navigation --}}
    <nav class="mkt-nav">
        <div class="mkt-nav-inner">
            <a href="{{ url('/') }}" class="mkt-logo">
                <img src="{{ asset('logo.png') }}" alt="PicMe">
            </a>
            <div class="mkt-nav-links">
                <a href="{{ url('/marketplace') }}" class="{{ isset($service_type) && $service_type == 'marketplace' ? 'active' : '' }}">🛒 Marketplace</a>
                <a href="{{ url('/airport') }}" class="{{ isset($service_type) && $service_type == 'airport' ? 'active' : '' }}">✈️ Aéroport</a>
                <a href="{{ url('/location') }}" class="{{ isset($service_type) && $service_type == 'rental' ? 'active' : '' }}">🚗 Location</a>
                <a href="{{ url('/drive') }}" class="{{ isset($service_type) && $service_type == 'drive' ? 'active' : '' }}">🏎️ Conduite</a>
            </div>
            <div class="mkt-nav-actions" style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ url('/login') }}" class="mkt-login-cta" style="color: #fff; text-decoration: none; font-weight: 600; padding: 10px 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.2s;">
                    <i class="fa fa-user"></i> Compte
                </a>
                <a href="{{ $whatsapp_link ?? 'https://wa.me/2250000000' }}" target="_blank" class="mkt-nav-cta" id="nav-whatsapp-cta">
                    <i class="fa fa-whatsapp"></i> Réserver
                </a>
            </div>
        </div>
    </nav>

    {{-- Page Content --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="mkt-footer">
        <div class="mkt-footer-inner">
            <div class="mkt-footer-grid">
                <div class="mkt-footer-col">
                    <img src="{{ asset('logo.png') }}" alt="PicMe" class="mkt-footer-logo">
                    <p>Votre partenaire de transport premium à {{ $city }}. Disponible 24h/24, 7j/7.</p>
                </div>
                <div class="mkt-footer-col">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="{{ url('/marketplace') }}">🛒 Marketplace</a></li>
                        <li><a href="{{ url('/airport') }}">✈️ Transfert Aéroport</a></li>
                        <li><a href="{{ url('/location') }}">🚗 Location Véhicule</a></li>
                        <li><a href="{{ url('/drive') }}">🏎️ Conduite</a></li>
                    </ul>
                </div>
                <div class="mkt-footer-col">
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="{{ $whatsapp_link }}" target="_blank"><i class="fa fa-whatsapp"></i> {{ $phone_display }}</a></li>
                        <li><a href="{{ $phone_link }}" id="footer-call-cta"><i class="fa fa-phone"></i> Appeler</a></li>
                    </ul>
                </div>
            </div>
            <div class="mkt-footer-bottom">
                <p>&copy; {{ date('Y') }} PicMe Pro — Tous droits réservés</p>
            </div>
        </div>
    </footer>

    {{-- WhatsApp Floating Button --}}
    <a href="{{ $whatsapp_link }}" target="_blank" class="mkt-whatsapp-float" id="float-whatsapp-cta" title="Contactez-nous sur WhatsApp">
        <i class="fa fa-whatsapp"></i>
    </a>

    {{-- Tracking JS --}}
    <script src="{{ asset('asset/js/marketing-tracking.js') }}"></script>
</body>
</html>

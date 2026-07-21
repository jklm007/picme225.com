@extends('admin.layout.base')

@section('title', 'Connexion WhatsApp — ')

@section('styles')
<style>
    .wa-connect-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 200px);
        padding: 30px 20px;
    }
    .wa-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        max-width: 480px;
        width: 100%;
        box-shadow: 0 8px 40px rgba(0,0,0,0.10);
        border: 1px solid #e8f5e9;
    }
    .wa-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 6px;
    }
    .wa-logo-icon {
        background: #25D366;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .wa-logo-icon i { color: #fff; font-size: 22px; }
    .wa-logo-title { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
    .wa-subtitle { color: #888; font-size: 0.85rem; margin-bottom: 24px; }
    .wa-heading { font-size: 1.15rem; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
    .wa-instructions {
        color: #666; font-size: 0.85rem; line-height: 1.7;
        background: #f8fdf9; border-radius: 10px; padding: 14px;
        margin-bottom: 22px; border-left: 3px solid #25D366;
    }
    .qr-frame {
        background: #fff;
        border: 3px solid #25D366;
        border-radius: 16px;
        padding: 14px;
        display: inline-block;
        margin-bottom: 18px;
        box-shadow: 0 4px 20px rgba(37,211,102,0.18);
        position: relative;
    }
    #qr-image { width: 260px; height: 260px; display: block; }
    .qr-spinner {
        width: 260px; height: 260px;
        display: flex; align-items: center;
        justify-content: center; flex-direction: column; gap: 12px;
        color: #25D366;
    }
    .qr-spinner svg { animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Status badge */
    .status-badge {
        display: inline-flex; align-items: center; gap: 8px;
        border-radius: 20px; padding: 6px 16px;
        font-size: 0.82rem; font-weight: 600; margin-bottom: 14px;
    }
    .status-badge.waiting  { background: #fff8e1; color: #f59e0b; border: 1px solid #fbbf24; }
    .status-badge.active   { background: #e8f5e9; color: #25D366;  border: 1px solid #a7f3d0; }
    .status-badge.error-st { background: #fef2f2; color: #ef4444;  border: 1px solid #fca5a5; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.35;} }

    /* Countdown bar */
    .countdown-bar-wrap { background: #f0fdf4; border-radius: 8px; height: 6px; margin-bottom: 8px; overflow: hidden; }
    .countdown-bar { height: 100%; background: linear-gradient(90deg, #25D366, #128c7e); border-radius: 8px; transition: width 1s linear; }
    .countdown-text { color: #aaa; font-size: 0.78rem; margin-bottom: 10px; }

    /* Connected state */
    .connected-block {
        padding: 24px;
        background: linear-gradient(135deg, #e8f5e9, #f0fdf4);
        border-radius: 14px;
        border: 1px solid #a7f3d0;
    }
    .connected-block .icon { font-size: 3rem; margin-bottom: 10px; }
    .connected-block h3 { color: #15803d; font-weight: 800; margin-bottom: 6px; }
    .connected-block p { color: #555; font-size: 0.88rem; }

    /* Refresh button */
    .btn-wa {
        background: #25D366; color: #fff; border: none;
        border-radius: 10px; padding: 10px 28px;
        font-size: 0.9rem; font-weight: 600; cursor: pointer;
        transition: background 0.2s;
    }
    .btn-wa:hover { background: #1da851; color: #fff; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h4 class="page-title"><i class="fa fa-whatsapp text-success"></i> Connexion WhatsApp</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Connexion WhatsApp</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="wa-connect-wrapper">
        <div class="wa-card">
            <div class="wa-logo">
                <div class="wa-logo-icon"><i class="fa fa-whatsapp"></i></div>
                <span class="wa-logo-title">PicMe225</span>
            </div>
            <div class="wa-subtitle">Bot IA WhatsApp — Administration</div>
            <h2 class="wa-heading">Connecter le numéro WhatsApp</h2>
            <div class="wa-instructions">
                Sur votre téléphone WhatsApp dédié :<br>
                <strong>⚙️ Paramètres → Appareils connectés → Connecter un appareil</strong><br>
                puis scannez le QR Code ci-dessous.
            </div>

            <!-- Status Badge -->
            <div id="status-badge" class="status-badge waiting">
                <span class="status-dot"></span>
                <span id="status-text">En attente du QR Code…</span>
            </div>

            <!-- QR Frame -->
            <div id="qr-section">
                <div class="qr-frame">
                    <div id="qr-spinner" class="qr-spinner">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                        Chargement du QR Code…
                    </div>
                    <img id="qr-image" src="" alt="QR Code WhatsApp" style="display:none; width:260px; height:260px;">
                </div>

                <!-- Countdown bar -->
                <div class="countdown-bar-wrap">
                    <div id="countdown-bar" class="countdown-bar" style="width:100%"></div>
                </div>
                <div id="countdown-text" class="countdown-text">Le QR code se renouvelle automatiquement</div>

                <div id="message-area"></div>

                <button class="btn-wa" onclick="forceRefresh()">
                    <i class="fa fa-refresh"></i> Nouveau QR Code
                </button>
            </div>

            <!-- Connected State (hidden by default) -->
            <div id="connected-section" style="display:none;">
                <div class="connected-block">
                    <div class="icon">✅</div>
                    <h3>WhatsApp Connecté !</h3>
                    <p>Le numéro PicMe225 est maintenant actif.<br>
                    Tous les messages entrants seront analysés par l'IA et transformés en annonces marketplace.</p>
                </div>
                <br>
                <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-success mt-2">
                    <i class="fa fa-list"></i> Voir les Annonces IA WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const QR_API_URL   = '/admin/whatsapp-listings/qr';
    const EXPIRE_SECS  = 60; // QR code expires in ~60s

    let countdownTimer  = null;
    let countdownRemain = EXPIRE_SECS;
    let isFetching      = false;

    function fetchQr() {
        if (isFetching) return;
        isFetching = true;
        clearCountdown();

        fetch(QR_API_URL, { headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken } })
            .then(res => res.json())
            .then(data => {
                isFetching = false;
                if (data.status === 'connected') {
                    showConnected();
                } else if (data.status === 'qr' && data.base64) {
                    showQr(data.base64);
                } else {
                    showError(data.message || 'QR code indisponible, nouvel essai dans 10s…');
                    setTimeout(fetchQr, 10000);
                }
            })
            .catch(() => {
                isFetching = false;
                showError('Erreur réseau, nouvel essai dans 10s…');
                setTimeout(fetchQr, 10000);
            });
    }

    function showQr(base64) {
        document.getElementById('qr-spinner').style.display = 'none';
        const img = document.getElementById('qr-image');
        img.src = base64;
        img.style.display = 'block';

        const badge = document.getElementById('status-badge');
        badge.className = 'status-badge active';
        document.getElementById('status-text').textContent = '✅ QR Code prêt — scannez maintenant';
        document.getElementById('message-area').innerHTML = '';

        startCountdown(EXPIRE_SECS);
    }

    function showConnected() {
        clearCountdown();
        document.getElementById('qr-section').style.display       = 'none';
        document.getElementById('connected-section').style.display = 'block';

        const badge = document.getElementById('status-badge');
        badge.className = 'status-badge active';
        document.getElementById('status-text').textContent = '✅ WhatsApp Connecté !';
    }

    function showError(msg) {
        document.getElementById('qr-image').style.display   = 'none';
        document.getElementById('qr-spinner').style.display = 'flex';
        document.getElementById('message-area').innerHTML =
            '<div class="alert alert-warning mt-2" style="font-size:0.82rem;">' + msg + '</div>';
        const badge = document.getElementById('status-badge');
        badge.className = 'status-badge error-st';
        document.getElementById('status-text').textContent = 'En attente…';
    }

    function startCountdown(seconds) {
        countdownRemain = seconds;
        updateBar();
        countdownTimer = setInterval(() => {
            countdownRemain--;
            updateBar();
            if (countdownRemain <= 0) {
                clearCountdown();
                document.getElementById('countdown-text').textContent = 'Renouvellement du QR Code…';
                document.getElementById('qr-image').style.display   = 'none';
                document.getElementById('qr-spinner').style.display = 'flex';
                fetchQr();
            }
        }, 1000);
    }

    function updateBar() {
        const pct = (countdownRemain / EXPIRE_SECS) * 100;
        document.getElementById('countdown-bar').style.width = pct + '%';
        document.getElementById('countdown-text').textContent =
            'Renouvellement automatique dans ' + countdownRemain + 's';
    }

    function clearCountdown() {
        if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null; }
    }

    function forceRefresh() {
        document.getElementById('qr-image').style.display   = 'none';
        document.getElementById('qr-spinner').style.display = 'flex';
        clearCountdown();
        fetchQr();
    }

    // Start on load
    fetchQr();
</script>
@endsection

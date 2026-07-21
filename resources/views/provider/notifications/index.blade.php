@extends('provider.layout.app')

@section('title', 'Notifications - ')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --navy:    #0D1B2A;
        --navy-2:  #162436;
        --gold:    #D4AF37;
        --white:   #ffffff;
        --success: #2ecc71;
        --danger:  #e74c3c;
        --gray:    #f4f6f9;
        --muted:   #94a3b8;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--gray);
        margin: 0; padding: 0;
    }

    header, .footer-content, .navbar,
    nav.navbar.navbar-fixed-top,
    .footer { display: none !important; }

    /* ===== PAGE HEADER ===== */
    .notif-header {
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
        padding: 20px 20px 60px;
        position: relative;
        z-index: 1;
    }
    .notif-header-top {
        display: flex; align-items: center; gap: 12px; margin-bottom: 8px;
    }
    .back-btn {
        width: 38px; height: 38px;
        background: rgba(255,255,255,0.12);
        border: none; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 16px; text-decoration: none;
        transition: background 0.2s;
        cursor: pointer;
    }
    .back-btn:hover { background: rgba(255,255,255,0.22); color: white; }
    .notif-header h1 {
        color: var(--white); font-size: 20px; font-weight: 800;
        margin: 0; letter-spacing: 0.2px;
    }
    .notif-header p {
        color: rgba(255,255,255,0.65); font-size: 13px; margin: 4px 0 0;
    }

    /* ===== CARD CONTAINER ===== */
    .notif-body {
        margin: -40px 16px 100px;
        position: relative; z-index: 2;
    }

    .notif-actions {
        display: flex; justify-content: flex-end; margin-bottom: 12px;
    }
    .btn-read-all {
        background: var(--navy); color: var(--gold);
        border: none; border-radius: 20px;
        padding: 7px 16px; font-size: 12px; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        text-decoration: none; transition: opacity 0.2s;
    }
    .btn-read-all:hover { opacity: 0.85; color: var(--gold); }

    /* ===== NOTIFICATION CARD ===== */
    .notif-list { display: flex; flex-direction: column; gap: 10px; }
    .notif-card {
        background: var(--white);
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 2px 10px rgba(13,27,42,0.07);
        display: flex; gap: 14px; align-items: flex-start;
        border-left: 4px solid transparent;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .notif-card.unread {
        border-left-color: var(--gold);
        background: #fffdf0;
    }
    .notif-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(13,27,42,0.12);
    }

    .notif-icon {
        width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .notif-icon.unread { background: rgba(212,175,55,0.15); color: var(--gold); }
    .notif-icon.read   { background: var(--gray); color: var(--muted); }

    .notif-body-text { flex: 1; min-width: 0; }
    .notif-title {
        font-size: 14px; font-weight: 700; color: var(--navy);
        margin: 0 0 3px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .notif-message {
        font-size: 13px; color: #4a5568; margin: 0 0 6px;
        line-height: 1.45;
    }
    .notif-meta {
        display: flex; align-items: center; gap: 8px;
    }
    .notif-time {
        font-size: 11px; color: var(--muted); font-weight: 500;
    }
    .notif-unread-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--gold); flex-shrink: 0;
    }

    .notif-action-btn {
        background: none; border: none; padding: 4px 8px;
        color: var(--navy); font-size: 11px; font-weight: 700;
        border: 1px solid #e2e8f0; border-radius: 8px;
        cursor: pointer; transition: all 0.15s; text-decoration: none;
    }
    .notif-action-btn:hover { background: var(--navy); color: white; border-color: var(--navy); }

    /* ===== EMPTY STATE ===== */
    .notif-empty {
        text-align: center; padding: 60px 20px;
        background: var(--white); border-radius: 20px;
        box-shadow: 0 2px 10px rgba(13,27,42,0.07);
    }
    .notif-empty-icon {
        font-size: 56px; color: var(--gold); margin-bottom: 16px;
        display: block; animation: bell-ring 2s ease-in-out infinite;
    }
    @keyframes bell-ring {
        0%,100% { transform: rotate(0); }
        10%      { transform: rotate(14deg); }
        20%      { transform: rotate(-12deg); }
        30%      { transform: rotate(10deg); }
        40%      { transform: rotate(-8deg); }
        50%      { transform: rotate(0); }
    }
    .notif-empty h3 {
        font-size: 18px; font-weight: 800; color: var(--navy); margin: 0 0 8px;
    }
    .notif-empty p {
        font-size: 14px; color: var(--muted); margin: 0;
    }

    /* ===== BOTTOM NAV ===== */
    .bottom-nav {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: var(--navy);
        display: flex;
        z-index: 200;
        box-shadow: 0 -2px 16px rgba(0,0,0,0.25);
    }
    .bottom-nav .nav-item {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 10px 4px 8px;
        text-decoration: none; color: rgba(255,255,255,0.5);
        font-size: 9px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.4px;
        transition: color 0.2s; gap: 4px;
    }
    .bottom-nav .nav-item i { font-size: 18px; }
    .bottom-nav .nav-item.active,
    .bottom-nav .nav-item:hover { color: var(--gold); }

    @media (min-width: 768px) {
        .notif-body { margin: -40px 10% 100px; }
    }
</style>
@endsection

@section('content')
<div class="notif-header">
    <div class="notif-header-top">
        <a href="{{ route('provider.index') }}" class="back-btn">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div>
            <h1>Notifications</h1>
            <p>Vos derniers messages et alertes</p>
        </div>
    </div>
</div>

<div class="notif-body">

    @if($notifications->count() > 0)
        <div class="notif-actions">
            <form action="{{ route('provider.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn-read-all">
                    <i class="fa fa-check-circle"></i> Tout marquer comme lu
                </button>
            </form>
        </div>

        <div class="notif-list">
            @foreach($notifications as $notif)
            <div class="notif-card {{ !$notif['read'] ? 'unread' : '' }}">
                <div class="notif-icon {{ !$notif['read'] ? 'unread' : 'read' }}">
                    <i class="fa fa-bell"></i>
                </div>
                <div class="notif-body-text">
                    <p class="notif-title">{{ $notif['title'] }}</p>
                    @if($notif['message'])
                        <p class="notif-message">{{ $notif['message'] }}</p>
                    @endif
                    <div class="notif-meta">
                        @if(!$notif['read'])
                            <span class="notif-unread-dot"></span>
                        @endif
                        <span class="notif-time">
                            <i class="fa fa-clock-o"></i>
                            {{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}
                        </span>
                        @if(!$notif['read'])
                            <form action="{{ route('provider.notifications.read', $notif['id']) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="notif-action-btn">Marquer lu</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    @else
        <div class="notif-empty">
            <span class="notif-empty-icon"><i class="fa fa-bell-o"></i></span>
            <h3>Aucune notification</h3>
            <p>Vous êtes à jour ! Revenez plus tard.</p>
        </div>
    @endif

</div>

{{-- BOTTOM NAV --}}
<nav class="bottom-nav">
    <a href="{{ route('provider.index') }}" class="nav-item">
        <i class="fa fa-location-arrow"></i>
        <span>Commandes</span>
    </a>
    <a href="{{ route('provider.earnings') }}" class="nav-item">
        <i class="fa fa-money"></i>
        <span>Argent</span>
    </a>
    <a href="{{ route('provider.support') }}" class="nav-item">
        <i class="fa fa-paper-plane"></i>
        <span>Assistance</span>
    </a>
    <a href="{{ route('provider.profile.index') }}" class="nav-item">
        <i class="fa fa-user"></i>
        <span>Profil</span>
    </a>
</nav>
@endsection

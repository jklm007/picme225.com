@extends('user.layout.user_dashboard', ['active' => 'trips'])

@section('title', 'Mes Trajets')
@section('header-sub', 'Historique de vos trajets')
@php $bottomNavActive = 'trips'; @endphp

@section('styles')
<style>
    /* ── TRIPS PAGE ── */
    .trips-page {
        background: #F0F2F5;
        min-height: calc(100vh - 64px);
        padding: 0 0 20px 0;
    }

    /* ── HERO SUMMARY BAR ── */
    .trips-hero {
        background: linear-gradient(135deg, var(--lime, #22C55E), var(--lime-dark, #15803D));
        padding: 90px 16px 40px 16px;
        position: relative;
        color: #ffffff;
    }
    .trips-hero-title {
        font-size: 22px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 4px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .trips-hero-sub {
        font-size: 13px;
        color: rgba(255,255,255,0.9);
        font-weight: 500;
    }
    .trips-stats-row {
        display: flex;
        gap: 12px;
        margin-top: 18px;
    }
    .trips-stat-pill {
        flex: 1;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 14px;
        padding: 12px;
        text-align: center;
        backdrop-filter: blur(4px);
    }
    .trips-stat-pill .num {
        font-size: 20px;
        font-weight: 800;
        color: #ffffff;
    }
    .trips-stat-pill .lbl {
        font-size: 10px;
        color: rgba(255,255,255,0.85);
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── CARD LIST ── */
    .trips-list {
        padding: 16px;
        position: relative;
        z-index: 2;
        margin-top: -24px;
    }

    /* ── TRIP CARD ── */
    .trip-card {
        background: #fff;
        border-radius: 18px;
        margin-bottom: 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        animation: slideUp 0.4s ease both;
    }
    .trip-card:active { transform: scale(0.98); }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .trip-card-header {
        display: flex;
        align-items: center;
        padding: 14px 16px 10px 16px;
        gap: 12px;
        border-bottom: 1px solid #F5F7FA;
    }
    .trip-card-icon {
        width: 44px; height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(201,168,76,0.15), rgba(226,192,110,0.1));
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .trip-card-meta {
        flex: 1;
        min-width: 0;
    }
    .trip-card-service {
        font-size: 14px;
        font-weight: 700;
        color: #1C2E4A;
    }
    .trip-card-date {
        font-size: 11px;
        color: #94A3B8;
        margin-top: 2px;
    }
    .trip-card-amount {
        font-size: 17px;
        font-weight: 800;
        color: #C9A84C;
        flex-shrink: 0;
    }

    .trip-card-body {
        padding: 12px 16px 14px 16px;
    }
    .trip-route {
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
    }
    .trip-route::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 16px;
        bottom: 16px;
        width: 2px;
        background: linear-gradient(to bottom, #2ecc71, #C9A84C);
        border-radius: 2px;
    }
    .trip-route-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-left: 4px;
    }
    .trip-route-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    .trip-route-dot.from { background: #2ecc71; box-shadow: 0 0 0 3px rgba(46,204,113,0.2); }
    .trip-route-dot.to   { background: #C9A84C; box-shadow: 0 0 0 3px rgba(201,168,76,0.2); }
    .trip-route-address {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
    }

    .trip-card-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 16px 14px 16px;
        flex-wrap: wrap;
    }
    .trip-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .trip-badge.completed { background: rgba(46,204,113,0.12); color: #27ae60; }
    .trip-badge.payment   { background: rgba(52,152,219,0.12); color: #2980b9; }
    .trip-badge.provider  { background: rgba(201,168,76,0.12); color: #C9A84C; }

    /* ── EMPTY STATE ── */
    .trips-empty {
        text-align: center;
        padding: 60px 24px;
    }
    .trips-empty-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    .trips-empty h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1C2E4A;
        margin-bottom: 8px;
    }
    .trips-empty p {
        font-size: 14px;
        color: #94A3B8;
        margin-bottom: 24px;
    }
    .trips-empty a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #C9A84C, #E2C06E);
        color: #0D1B2A;
        border-radius: 24px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
    }

    /* ── PAGINATION ── */
    .trips-pagination {
        padding: 8px 16px;
        display: flex;
        justify-content: center;
    }
    .trips-pagination .pagination {
        display: flex;
        gap: 6px;
    }
    .trips-pagination .page-item .page-link {
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        color: #1C2E4A;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
    }
    .trips-pagination .page-item.active .page-link {
        background: #C9A84C;
        border-color: #C9A84C;
        color: #0D1B2A;
    }
</style>
@endsection

@section('content')
<div class="trips-page">

    {{-- ── HERO ── --}}
    <div class="trips-hero">
        <div class="trips-hero-title">Mes Trajets</div>
        <div class="trips-hero-sub">Historique de toutes vos courses</div>
        <div class="trips-stats-row">
            <div class="trips-stat-pill">
                <div class="num">{{ method_exists($trips, 'total') ? $trips->total() : count($trips) }}</div>
                <div class="lbl">Trajets totaux</div>
            </div>
            <div class="trips-stat-pill">
                <div class="num">{{ currency(Auth::user()->wallet_balance) }}</div>
                <div class="lbl">Solde Wallet</div>
            </div>
        </div>
    </div>

    {{-- ── TRIP CARDS ── --}}
    <div class="trips-list">

        @if((method_exists($trips,'count') ? $trips->count() : count($trips)) > 0)
            @foreach($trips as $index => $trip)
            <div class="trip-card" style="animation-delay: {{ $index * 0.05 }}s;">
                <div class="trip-card-header">
                    <div class="trip-card-icon">
                        @if($trip->service_type)
                            <img src="{{ $trip->service_type->image ?? '' }}" alt="" style="width:28px;height:28px;object-fit:contain;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <i class="fa fa-car" style="display:none;color:#C9A84C;font-size:22px;"></i>
                        @else
                            <i class="fa fa-car" style="color:#C9A84C;font-size:22px;"></i>
                        @endif
                    </div>
                    <div class="trip-card-meta">
                        <div class="trip-card-service">
                            {{ $trip->service_type ? $trip->service_type->name : 'Course' }}
                            @if($trip->booking_id)
                                <span style="font-size:11px;color:#94A3B8;font-weight:400;"> — {{ $trip->booking_id }}</span>
                            @endif
                        </div>
                        <div class="trip-card-date">
                            <i class="fa fa-clock-o" style="margin-right:4px;"></i>
                            @if($trip->assigned_at)
                                {{ \Carbon\Carbon::parse($trip->assigned_at)->format('d/m/Y à H\hi') }}
                            @else
                                Date inconnue
                            @endif
                        </div>
                    </div>
                    <div class="trip-card-amount">
                        @if($trip->payment)
                            {{ currency($trip->payment->total) }}
                        @else
                            –
                        @endif
                    </div>
                </div>

                <div class="trip-card-body">
                    <div class="trip-route">
                        <div class="trip-route-item">
                            <span class="trip-route-dot from"></span>
                            <span class="trip-route-address">{{ $trip->s_address ?? 'Départ inconnu' }}</span>
                        </div>
                        <div class="trip-route-item">
                            <span class="trip-route-dot to"></span>
                            <span class="trip-route-address">{{ $trip->d_address ?? 'Destination inconnue' }}</span>
                        </div>
                    </div>
                </div>

                <div class="trip-card-footer">
                    <span class="trip-badge completed">
                        <i class="fa fa-check-circle"></i> Terminé
                    </span>
                    @if($trip->payment_mode)
                    <span class="trip-badge payment">
                        <i class="fa fa-credit-card"></i> {{ $trip->payment_mode }}
                    </span>
                    @endif
                    @if($trip->provider)
                    <span class="trip-badge provider">
                        <i class="fa fa-user"></i> {{ $trip->provider->first_name }} {{ $trip->provider->last_name }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Pagination --}}
            @if(method_exists($trips, 'links'))
            <div class="trips-pagination">
                {{ $trips->links() }}
            </div>
            @endif

        @else
            {{-- Empty State --}}
            <div class="trips-empty">
                <div class="trips-empty-icon">🚖</div>
                <h3>Aucun trajet pour l'instant</h3>
                <p>Vos courses terminées apparaîtront ici.<br>Réservez votre première course dès maintenant !</p>
                <a href="{{ url('dashboard') }}">
                    <i class="fa fa-taxi"></i>
                    Réserver une course
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
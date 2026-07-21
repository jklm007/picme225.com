@extends('user.layout.base')

@section('title', 'Mes Achats et Billets – PicMe225')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --navy:#0D1B2A; --navy-2:#162436; --navy-3:#1e3048;
    --gold:#C9A84C; --gold-light:#E2C06E;
    --white:#fff; --gray-50:#f9fafc; --gray-100:#f0f2f7; --gray-200:#e4e7ef;
    --success:#27ae60; --danger:#e74c3c; --warning:#f39c12;
}
header,.dash-left,.footer-content,.menu-toggle,.overlay{display:none!important}
body,html{margin:0;padding:0;background:var(--gray-50);font-family:'Inter',sans-serif}
.pm-mk-header{
    background:var(--navy);
    padding:16px; position:sticky; top:0; z-index:50;
    display:flex;align-items:center;gap:12px;
    box-shadow:0 2px 16px rgba(0,0,0,0.1);
}
.pm-mk-header h1{font-size:17px;font-weight:800;color:#ffffff;margin:0;flex:1;}
.pm-mk-back{color:#ffffff;font-size:20px;text-decoration:none}
.pm-mk-body{padding:14px 14px 90px}

.pm-ticket-card{
    background:var(--white);border-radius:14px;padding:16px;margin-bottom:12px;
    display:flex;flex-direction:column;gap:12px;
    box-shadow:0 2px 10px rgba(13,27,42,0.06);
    border-left:4px solid var(--success);
}
.pm-ticket-header{
    display:flex;justify-content:space-between;align-items:flex-start;
}
.pm-ticket-title{font-weight:700;font-size:15px;color:var(--navy);margin-bottom:4px;}
.pm-ticket-cat{font-size:12px;color:var(--gray-500);}
.pm-ticket-price{font-weight:800;font-size:15px;color:var(--gold);}
.pm-ticket-date{font-size:11px;color:var(--gray-500);margin-top:4px;}

.pm-actions-bar{display:flex;gap:8px;margin-top:8px;}
.pm-btn{
    flex:1;text-align:center;padding:10px;border-radius:12px;font-weight:700;font-size:13px;
    text-decoration:none;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;
}
.pm-btn-primary{background:var(--navy);color:#fff;}
.pm-btn-download{background:var(--success);color:#fff;}

.pm-empty{text-align:center;padding:50px 20px;color:var(--gray-500)}
.pm-empty i{font-size:48px;color:var(--gray-200);display:block;margin-bottom:16px}
.pm-empty h3{font-size:16px;color:var(--navy);margin-bottom:8px}
.pm-empty-btn{
    display:inline-flex;align-items:center;gap:8px;padding:12px 24px;
    background:var(--navy);color:#fff;border-radius:12px;font-weight:700;font-size:14px;text-decoration:none;
}
.page-content.dashboard-page { padding-top: 0 !important; }

/* Tabs */
.pm-tabs { display:flex; background:var(--white); border-radius:12px; overflow:hidden; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.pm-tab { flex:1; text-align:center; padding:12px; font-weight:600; font-size:13px; color:var(--gray-500); cursor:pointer; }
.pm-tab.active { background:var(--navy); color:#fff; }
</style>
@endsection

@section('content')
<div class="page-content">
<div class="pm-mk-header">
    <a href="{{ route('user.marketplace.explore') }}" class="pm-mk-back"><i class="fa fa-arrow-left"></i></a>
    <h1>Mes Achats & Billets</h1>
</div>

<div class="pm-mk-body">

    <div class="pm-tabs">
        <div class="pm-tab active" onclick="switchTab('tickets', this)">Billetterie</div>
        <div class="pm-tab" onclick="switchTab('digital', this)">Achats (Boutique)</div>
    </div>

    <!-- TICKETS TAB -->
    <div id="tab-tickets">
        @if($purchases->isEmpty())
        <div class="pm-empty">
            <i class="fa fa-ticket"></i>
            <h3>Aucun billet</h3>
            <p>Vous n'avez acheté aucun billet d'événement pour le moment.</p>
            <a href="{{ route('user.marketplace.explore') }}?category=TICKETS" class="pm-empty-btn">Voir les Événements</a>
        </div>
        @else
            @foreach($purchases as $ticket)
            <div class="pm-ticket-card">
                <div class="pm-ticket-header">
                    <div>
                        <div class="pm-ticket-title">{{ $ticket->listing->title ?? 'Événement Inconnu' }}</div>
                        <div class="pm-ticket-cat">
                            @if($ticket->pass)
                                {{ $ticket->pass->pass_name }} ({{ $ticket->pass->persons_per_pass }} pers.)
                            @else
                                Billet Standard
                            @endif
                            &bull; 
                            <span style="color: {{ $ticket->payment_status === 'PAID' ? 'var(--success)' : 'var(--warning)' }}">
                                {{ $ticket->payment_status === 'PAID' ? 'Payé' : 'En attente' }}
                            </span>
                        </div>
                        <div class="pm-ticket-date">Acheté le {{ $ticket->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div class="pm-ticket-price">{{ number_format($ticket->total_price, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="pm-actions-bar">
                    <a href="{{ route('ticket.view', ['booking_id' => $ticket->qr_code]) }}" class="pm-btn pm-btn-primary">
                        <i class="fa fa-eye"></i> Voir le Billet
                    </a>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- DIGITAL/RENTALS TAB -->
    <div id="tab-digital" style="display:none;">
        @if($bookings->isEmpty())
        <div class="pm-empty">
            <i class="fa fa-shopping-basket"></i>
            <h3>Aucun achat</h3>
            <p>Vous n'avez pas encore effectué d'achat sur la marketplace.</p>
            <a href="{{ route('user.marketplace.explore') }}" class="pm-empty-btn">Explorer la Boutique</a>
        </div>
        @else
            @foreach($bookings as $booking)
            <div class="pm-ticket-card" style="border-left-color: var(--navy)">
                <div class="pm-ticket-header">
                    <div>
                        <div class="pm-ticket-title">{{ $booking->listing->title ?? 'Article Inconnu' }}</div>
                        <div class="pm-ticket-cat">Statut: {{ $booking->status }}</div>
                        <div class="pm-ticket-date">Réservé le {{ $booking->created_at->format('d/m/Y') }}</div>
                    </div>
                    <div class="pm-ticket-price">{{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</div>
                </div>
                @if($booking->listing && $booking->listing->is_digital && $booking->status === 'COMPLETED')
                <div class="pm-actions-bar">
                    <a href="{{ route('user.marketplace.download_digital', $booking->listing->id) }}" class="pm-btn pm-btn-download">
                        <i class="fa fa-download"></i> Télécharger
                    </a>
                </div>
                @endif
            </div>
            @endforeach
        @endif
    </div>

</div>
</div>

@include('user.include.bottom_nav', ['active' => 'store'])
@endsection

@section('scripts')
<script>
function switchTab(tab, el) {
    document.querySelectorAll('.pm-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    
    document.getElementById('tab-tickets').style.display = 'none';
    document.getElementById('tab-digital').style.display = 'none';
    
    document.getElementById('tab-' + tab).style.display = 'block';
}
</script>
@endsection

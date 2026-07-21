@extends('user.layout.base')

@section('title', 'Mes Annonces – PicMe225 Marketplace')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --navy:#0D1B2A; --navy-2:#162436; --navy-3:#1e3048;
    --gold:#C9A84C; --gold-light:#E2C06E; --gold-pale:rgba(201,168,76,0.12);
    --white:#fff; --gray-50:#f9fafc; --gray-100:#f0f2f7; --gray-200:#e4e7ef;
    --success:#27ae60; --danger:#e74c3c; --warning:#f39c12;
}
header,.dash-left,.footer-content,.menu-toggle,.overlay{display:none!important}
body,html{margin:0;padding:0;background:var(--gray-50);font-family:'Inter',sans-serif}
.pm-mk-header{
    background:linear-gradient(135deg,var(--lime, #22C55E),var(--lime-dark, #15803D));
    padding:16px; position:sticky; top:0; z-index:50;
    display:flex;align-items:center;gap:12px;
    box-shadow:0 2px 16px rgba(0,0,0,0.1);
}
.pm-mk-header h1{font-size:17px;font-weight:800;color:#ffffff;margin:0;flex:1;text-shadow: 0 1px 2px rgba(0,0,0,0.1);}
.pm-mk-back{color:#ffffff;font-size:20px;text-decoration:none}
.pm-mk-body{padding:14px 14px 90px}
.pm-mk-publish-btn{
    display:flex;align-items:center;gap:6px;padding:9px 16px;
    background:#ffffff;
    color:var(--lime-dark, #15803D);border:none;border-radius:20px;font-weight:800;font-size:12px;
    text-decoration:none;cursor:pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
@if(session('success'))
.pm-alert-ok{background:rgba(39,174,96,0.1);border:1.5px solid var(--success);border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:var(--success);display:flex;align-items:center;gap:8px;}
@endif
.pm-listing-card{
    background:var(--white);border-radius:14px;padding:12px;margin-bottom:12px;
    display:flex;gap:12px;align-items:flex-start;
    box-shadow:0 2px 10px rgba(13,27,42,0.06);
    border-left:4px solid var(--gray-200);
    transition:all 0.2s;
}
.pm-listing-card.ACTIVE,.pm-listing-card.APPROVED{border-left-color:var(--success)}
.pm-listing-card.PENDING{border-left-color:var(--warning)}
.pm-listing-card.REJECTED{border-left-color:var(--danger)}
.pm-listing-thumb{
    width:72px;height:72px;border-radius:10px;object-fit:cover;flex-shrink:0;
    background:var(--gray-100);
}
.pm-listing-info{flex:1;min-width:0}
.pm-listing-title{font-weight:700;font-size:13px;color:var(--navy);margin-bottom:4px;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pm-listing-cat{font-size:11px;color:var(--gray-500);margin-bottom:6px}
.pm-listing-price{font-weight:800;font-size:14px;color:var(--gold)}
.pm-status-badge{
    display:inline-flex;align-items:center;gap:4px;
    padding:3px 9px;border-radius:12px;font-size:10px;font-weight:700;
}
.badge-ACTIVE,.badge-APPROVED{background:rgba(39,174,96,0.1);color:var(--success)}
.badge-PENDING{background:rgba(243,156,18,0.1);color:var(--warning)}
.badge-REJECTED{background:rgba(231,76,60,0.1);color:var(--danger)}
.badge-DRAFT{background:var(--gray-100);color:var(--gray-500)}
.pm-listing-actions{display:flex;gap:6px;margin-top:8px}
.pm-action-btn{
    padding:5px 10px;border-radius:8px;font-size:11px;font-weight:600;
    text-decoration:none;border:1.5px solid;cursor:pointer;
    display:flex;align-items:center;gap:4px;
}
.pm-action-btn.view{color:var(--navy);border-color:var(--gray-200);background:var(--gray-50)}
.pm-action-btn.del{color:var(--danger);border-color:rgba(231,76,60,0.3);background:rgba(231,76,60,0.05)}
.pm-empty{text-align:center;padding:50px 20px;color:var(--gray-500)}
.pm-empty i{font-size:48px;color:var(--gray-200);display:block;margin-bottom:16px}
.pm-empty h3{font-size:16px;color:var(--navy);margin-bottom:8px}
.pm-empty p{font-size:13px;margin-bottom:20px}
.pm-empty-btn{
    display:inline-flex;align-items:center;gap:8px;padding:12px 24px;
    background:linear-gradient(135deg,var(--gold),var(--gold-light));
    color:var(--navy);border-radius:12px;font-weight:700;font-size:14px;text-decoration:none;
}
.page-content.dashboard-page { padding-top: 0 !important; }
</style>
@endsection

@section('content')
<div class="page-content">
<div class="pm-mk-header">
    <a href="{{ route('user.marketplace.explore') }}" class="pm-mk-back"><i class="fa fa-arrow-left"></i></a>
    <h1>Mes Annonces</h1>
    <a href="{{ route('user.marketplace.create') }}" class="pm-mk-publish-btn">
        <i class="fa fa-plus"></i> Publier
    </a>
</div>

<div class="pm-mk-body">

    @if(session('success'))
    <div class="pm-alert-ok"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px;">
        <div style="background:var(--white); border-radius:12px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); text-align:center;">
            <div style="color:var(--gray-500); font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Annonces Actives</div>
            <div style="color:var(--navy); font-size:22px; font-weight:800;">{{ $activeListings ?? 0 }}<span style="font-size:14px; color:var(--gray-400);">/{{ $totalListings ?? 0 }}</span></div>
        </div>
        <div style="background:var(--white); border-radius:12px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); text-align:center;">
            <div style="color:var(--gray-500); font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Ventes / Résa</div>
            <div style="color:var(--success); font-size:22px; font-weight:800;">{{ $totalSales ?? 0 }}</div>
        </div>
        <div style="background:linear-gradient(135deg,var(--gold),var(--gold-light)); border-radius:12px; padding:16px; box-shadow:0 4px 12px rgba(201,168,76,0.3); text-align:center;">
            <div style="color:rgba(255,255,255,0.9); font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Revenus Générés</div>
            <div style="color:#ffffff; font-size:16px; font-weight:800;">{{ number_format($totalRevenue ?? 0, 0, ',', ' ') }} <small>FCFA</small></div>
        </div>
    </div>

    @if($listings->isEmpty())
    <div class="pm-empty">
        <i class="fa fa-shopping-bag"></i>
        <h3>Aucune annonce</h3>
        <p>Vous n'avez pas encore publié d'annonce.<br>Commencez à vendre dès maintenant !</p>
        <a href="{{ route('user.marketplace.create') }}" class="pm-empty-btn">
            <i class="fa fa-plus"></i> Publier ma première annonce
        </a>
    </div>
    @else
    @foreach($listings as $listing)
    <div class="pm-listing-card {{ $listing->status }}">
        @php
            $img = $listing->media_url;
        @endphp
        @if($img)
            <img class="pm-listing-thumb" src="{{ $img }}" alt="{{ $listing->title }}" onerror="this.style.display='none'">
        @else
            <div class="pm-listing-thumb" style="display:flex;align-items:center;justify-content:center;font-size:24px;color:var(--gray-400)">
                <i class="fa fa-image"></i>
            </div>
        @endif
        <div class="pm-listing-info">
            <div class="pm-listing-title">{{ $listing->title }}</div>
            <div class="pm-listing-cat">{{ $listing->category }}{{ $listing->sub_category ? ' › ' . $listing->sub_category : '' }}</div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                <span class="pm-listing-price">{{ number_format($listing->price, 0, ',', ' ') }} FCFA</span>
                <span class="pm-status-badge badge-{{ $listing->status }}">
                    @if($listing->status === 'ACTIVE' || $listing->status === 'APPROVED')
                        <i class="fa fa-check-circle"></i> Publiée
                    @elseif($listing->status === 'PENDING')
                        <i class="fa fa-clock-o"></i> En attente
                    @elseif($listing->status === 'REJECTED')
                        <i class="fa fa-times-circle"></i> Rejetée
                    @else
                        <i class="fa fa-edit"></i> Brouillon
                    @endif
                </span>
                @if($listing->is_digital)
                    <span style="font-size:10px;font-weight:700;color:#fff;background:var(--navy);padding:2px 6px;border-radius:6px;"><i class="fa fa-download"></i> Numérique</span>
                @endif
                @if(($listing->sales_count ?? 0) > 0)
                    <span style="font-size:11px;font-weight:700;color:var(--success);"><i class="fa fa-shopping-cart"></i> {{ $listing->sales_count }} Vente(s)</span>
                @endif
            </div>
            <div class="pm-listing-actions">
                <a href="{{ route('marketplace.detail', $listing->id) }}" class="pm-action-btn view">
                    <i class="fa fa-eye"></i> Voir
                </a>
                <a href="{{ route('user.marketplace.edit', $listing->id) }}" class="pm-action-btn view" style="border-color:var(--gold); color:var(--gold); background:var(--gold-pale);">
                    <i class="fa fa-edit"></i> Modifier
                </a>
                <form method="POST" action="{{ route('user.marketplace.destroy', $listing->id) }}"
                      onsubmit="return confirm('Supprimer cette annonce ?')" style="margin:0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="pm-action-btn del">
                        <i class="fa fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <div style="margin-top:16px">{{ $listings->links() }}</div>
    @endif
</div>
@include('user.include.bottom_nav', ['active' => 'store'])
</div>
@endsection

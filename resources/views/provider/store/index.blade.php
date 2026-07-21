@extends('provider.layout.app')

@section('title', 'Mon Store - ')

@section('styles')
<style>
    .store-container {
        padding: 24px;
        background: #f7f8fc;
        min-height: 100vh;
        color: #0D1B2A;
    }
    .store-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .store-title {
        font-size: 24px;
        font-weight: 800;
        color: #0D1B2A;
    }
    .btn-gold {
        background: linear-gradient(135deg, #C9A84C, #E2C06E);
        color: #0D1B2A;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-gold:hover, .btn-gold:focus {
        color: #0D1B2A;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(201,168,76,0.3);
    }
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(13,27,42,0.05);
        border-left: 4px solid #C9A84C;
    }
    .stat-label {
        font-size: 12px;
        color: #7a8bad;
        text-transform: uppercase;
        font-weight: 600;
    }
    .stat-val {
        font-size: 28px;
        font-weight: 800;
        color: #0D1B2A;
        margin-top: 8px;
    }
    .listings-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(13,27,42,0.05);
    }
    .listing-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 0;
        border-bottom: 1px solid #f0f2f7;
    }
    .listing-item:last-child {
        border-bottom: none;
    }
    .listing-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .listing-img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        background: #f0f2f7;
    }
    .listing-details h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #0D1B2A;
    }
    .listing-details p {
        margin: 4px 0 0;
        font-size: 13px;
        color: #7a8bad;
    }
    .badge-status {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .badge-active { background: rgba(39, 174, 96, 0.1); color: #27ae60; }
    .badge-pending { background: rgba(201, 168, 76, 0.1); color: #C9A84C; }
    .listing-actions {
        display: flex;
        gap: 8px;
    }
</style>
@endsection

@section('content')
<div class="store-container">
    <div class="store-header">
        <h1 class="store-title">Mon Store (Annonces)</h1>
        <a href="{{ route('provider.store.create') }}" class="btn btn-gold">
            <i class="fa fa-plus"></i> Créer une annonce
        </a>
    </div>

    @if(Session::has('flash_success'))
        <div class="alert alert-success">
            {{ Session::get('flash_success') }}
        </div>
    @endif

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Annonces</div>
            <div class="stat-val">{{ $total }}</div>
        </div>
        <div class="stat-card" style="border-left-color: #27ae60;">
            <div class="stat-label">Annonces Actives</div>
            <div class="stat-val">{{ $active }}</div>
        </div>
        <div class="stat-card" style="border-left-color: #f39c12;">
            <div class="stat-label">En attente de validation</div>
            <div class="stat-val">{{ $pending }}</div>
        </div>
    </div>

    <div class="listings-card">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-weight: 800;">Mes articles en vente / location</h3>

        @if($listings->isEmpty())
            <div class="text-center" style="padding: 40px 0;">
                <i class="fa fa-shopping-bag" style="font-size: 48px; color: #adb5c9; margin-bottom: 16px;"></i>
                <p style="color: #7a8bad; font-size: 16px;">Vous n'avez pas encore publié d'annonce dans votre store.</p>
                <a href="{{ route('provider.store.create') }}" class="btn btn-gold" style="margin-top: 10px;">
                    Publier ma première annonce
                </a>
            </div>
        @else
            @foreach($listings as $listing)
                <div class="listing-item">
                    <div class="listing-info">
                        <img class="listing-img" src="{{ $listing->media_url ?? asset('images/default_category.png') }}" onerror="this.src='{{ asset('images/default_category.png') }}'">
                        <div class="listing-details">
                            <h4>{{ $listing->title }}</h4>
                            <p>
                                <span style="font-weight: 700; color: #0D1B2A;">{{ currency($listing->price) }}</span>
                                <span style="font-size: 11px;">/ {{ $listing->price_unit }}</span>
                                &bull; Catégorie : {{ $listing->category }}
                            </p>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <span class="badge-status {{ $listing->status == 'ACTIVE' ? 'badge-active' : 'badge-pending' }}">
                            {{ $listing->status == 'ACTIVE' ? 'Actif' : 'En attente' }}
                        </span>

                        <div class="listing-actions">
                            <a href="{{ route('provider.store.edit', $listing->id) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-pencil"></i> Modifier
                            </a>
                            <form action="{{ route('provider.store.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette annonce ?');" style="display: inline;">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <div style="margin-top: 20px;">
                {{ $listings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

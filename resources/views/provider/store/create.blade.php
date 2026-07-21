@extends('provider.layout.app')

@section('title', 'Créer une annonce - ')

@section('styles')
<style>
    .store-container {
        padding: 24px;
        background: #f7f8fc;
        min-height: 100vh;
        color: #0D1B2A;
    }
    .form-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(13,27,42,0.05);
        max-width: 700px;
        margin: 0 auto;
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
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(201,168,76,0.3);
    }
</style>
@endsection

@section('content')
<div class="store-container">
    <div class="form-card">
        <h2 style="margin-top: 0; margin-bottom: 24px; font-weight: 800;">Publier une nouvelle annonce</h2>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('provider.store.store') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}

            <div class="form-group">
                <label style="font-weight: 700;">Titre de l'annonce</label>
                <input type="text" name="title" class="form-control" placeholder="Ex: Peugeot 208 en excellent état" value="{{ old('title') }}" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 700;">Description détaillée</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Décrivez votre véhicule ou service..." required>{{ old('description') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Catégorie principale</label>
                    <select name="category" class="form-control" required>
                        <option value="">Sélectionner</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Sous-catégorie</label>
                    <input type="text" name="sub_category" class="form-control" placeholder="Ex: Citadine, Berline" value="{{ old('sub_category') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Prix</label>
                    <input type="number" name="price" class="form-control" placeholder="Ex: 25000" value="{{ old('price') }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Unité de prix</label>
                    <select name="price_unit" class="form-control">
                        <option value="DAY">Par jour</option>
                        <option value="HOUR">Par heure</option>
                        <option value="FIXED">Prix fixe</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Téléphone de contact</label>
                    <input type="text" name="phone" class="form-control" placeholder="Ex: +2250707070707" value="{{ old('phone', Auth::guard('provider')->user()->mobile) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Ville / Localité</label>
                    <input type="text" name="location_city" class="form-control" placeholder="Ex: Abidjan, Cocody" value="{{ old('location_city') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">État de l'objet</label>
                    <select name="condition" class="form-control">
                        <option value="new">Neuf</option>
                        <option value="excellent">Excellent état</option>
                        <option value="used" selected>Bon état (D'occasion)</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Stock disponible</label>
                    <input type="number" name="stock_quantity" class="form-control" value="1">
                </div>
            </div>

            <div class="form-group">
                <label style="font-weight: 700;">Photos (Max 6)</label>
                <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                <small class="text-muted">Sélectionnez plusieurs fichiers à la fois. La première photo sera utilisée comme couverture.</small>
            </div>

            <div class="form-group">
                <label style="font-weight: 700;">Informations complémentaires</label>
                <input type="text" name="extra_info" class="form-control" placeholder="Ex: Climatisation, Assurance incluse..." value="{{ old('extra_info') }}">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-gold">
                    <i class="fa fa-paper-plane"></i> Publier l'annonce
                </button>
                <a href="{{ route('provider.store.index') }}" class="btn btn-default">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

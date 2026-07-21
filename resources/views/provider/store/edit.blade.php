@extends('provider.layout.app')

@section('title', 'Modifier l\'annonce - ')

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
    .current-photos {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .photo-preview {
        width: 80px;
        height: 80px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #e0e0e0;
    }
</style>
@endsection

@section('content')
<div class="store-container">
    <div class="form-card">
        <h2 style="margin-top: 0; margin-bottom: 24px; font-weight: 800;">Modifier mon annonce</h2>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('provider.store.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            {{ method_field('PUT') }}

            <div class="form-group">
                <label style="font-weight: 700;">Titre de l'annonce</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 700;">Description détaillée</label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description', $listing->description) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Catégorie principale</label>
                    <select name="category" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ $listing->category == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Sous-catégorie</label>
                    <input type="text" name="sub_category" class="form-control" value="{{ old('sub_category', $listing->sub_category) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Prix</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', (int)$listing->price) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Unité de prix</label>
                    <select name="price_unit" class="form-control">
                        <option value="DAY" {{ $listing->price_unit == 'DAY' ? 'selected' : '' }}>Par jour</option>
                        <option value="HOUR" {{ $listing->price_unit == 'HOUR' ? 'selected' : '' }}>Par heure</option>
                        <option value="FIXED" {{ $listing->price_unit == 'FIXED' ? 'selected' : '' }}>Prix fixe</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Téléphone de contact</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $listing->owner_phone) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Ville / Localité</label>
                    <input type="text" name="location_city" class="form-control" value="{{ old('location_city', $listing->location_city) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">État de l'objet</label>
                    <select name="condition" class="form-control">
                        <option value="new" {{ ($listing->metadata['condition'] ?? '') == 'new' ? 'selected' : '' }}>Neuf</option>
                        <option value="excellent" {{ ($listing->metadata['condition'] ?? '') == 'excellent' ? 'selected' : '' }}>Excellent état</option>
                        <option value="used" {{ ($listing->metadata['condition'] ?? '') == 'used' ? 'selected' : '' }}>Bon état (D'occasion)</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label style="font-weight: 700;">Stock disponible</label>
                    <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $listing->metadata['stock_quantity'] ?? 1) }}">
                </div>
            </div>

            <div class="form-group">
                <label style="font-weight: 700;">Remplacer les photos (Optionnel)</label>
                <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                <small class="text-muted">Laissez vide pour conserver les photos actuelles. Sélectionner de nouveaux fichiers écrasera la galerie précédente.</small>
                
                @if(!empty($listing->images))
                    <div style="margin-top: 15px;">
                        <label style="font-weight: 600; font-size: 13px;">Photos actuelles :</label>
                        <div class="current-photos">
                            @foreach($listing->images as $img)
                                <img class="photo-preview" src="{{ str_starts_with($img, 'http') ? $img : url('storage/' . $img) }}">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label style="font-weight: 700;">Informations complémentaires</label>
                <input type="text" name="extra_info" class="form-control" value="{{ old('extra_info', $listing->metadata['extra_info'] ?? '') }}">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-gold">
                    <i class="fa fa-save"></i> Enregistrer les modifications
                </button>
                <a href="{{ route('provider.store.index') }}" class="btn btn-default">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

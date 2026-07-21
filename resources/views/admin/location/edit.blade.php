@extends('admin.layout.base')

@section('title', 'Modifier le Véhicule de Location')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif !important; background-color: #f4f6f9; }
        .box { border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03); border: 1px solid rgba(0,0,0,0.05); background: #ffffff; padding: 30px; }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box">
                <h4 class="mb-4">Modifier : {{ $vehicle->title }}</h4>

                <form action="{{ route('admin.location.update', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom de l'annonce / Titre</label>
                                <input type="text" name="title" class="form-control" value="{{ $vehicle->title }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Marque</label>
                                <input type="text" name="brand" class="form-control" value="{{ $vehicle->brand }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Modèle</label>
                                <input type="text" name="model" class="form-control" value="{{ $vehicle->model }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prix par jour (CFA)</label>
                                <input type="number" name="price" class="form-control" value="{{ $vehicle->price }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ville / Localisation</label>
                                <input type="text" name="location_city" class="form-control" value="{{ $vehicle->location_city }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Immatriculation (Optionnel)</label>
                                <input type="text" name="plate_number" class="form-control" value="{{ $vehicle->plate_number }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom du Propriétaire</label>
                                <input type="text" name="owner_name" class="form-control" value="{{ $vehicle->owner_name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Téléphone WhatsApp (Format: 225...)</label>
                                <input type="text" name="owner_phone" class="form-control" value="{{ $vehicle->owner_phone }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ $vehicle->description }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Image principale (Laissez vide pour conserver l'actuelle)</label>
                        <input type="file" name="cover_image" class="dropify" data-default-file="{{ url($vehicle->cover_image) }}">
                    </div>

                    <div class="text-right">
                        <a href="{{ route('admin.location.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre à jour le véhicule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

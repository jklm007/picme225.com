@extends('admin.layout.base')

@section('title', 'Ajouter un Véhicule de Location')

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
                <h4 class="mb-4">Ajouter un Nouveau Véhicule</h4>

                <form action="{{ route('admin.location.store') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom de l'annonce / Titre</label>
                                <input type="text" name="title" class="form-control" placeholder="Ex: Toyota Corolla propre" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Marque</label>
                                <input type="text" name="brand" class="form-control" placeholder="Toyota" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Modèle</label>
                                <input type="text" name="model" class="form-control" placeholder="Corolla" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prix par jour (CFA)</label>
                                <input type="number" name="price" class="form-control" placeholder="25000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ville / Localisation</label>
                                <input type="text" name="location_city" class="form-control" placeholder="Abidjan, Cocody" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Immatriculation (Optionnel)</label>
                                <input type="text" name="plate_number" class="form-control" placeholder="1234 AB 01">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom du Propriétaire</label>
                                <input type="text" name="owner_name" class="form-control" placeholder="M. Koné" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Téléphone WhatsApp (Format: 225...)</label>
                                <input type="text" name="owner_phone" class="form-control" placeholder="2250707070707" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Détails sur l'état du véhicule, climatisé, etc."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Image principale</label>
                        <input type="file" name="cover_image" class="dropify" required>
                    </div>

                    <div class="text-right">
                        <a href="{{ route('admin.location.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer le véhicule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

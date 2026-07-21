@extends('admin.layout.base')

@section('title', 'Modifier le Groupe WhatsApp')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.whatsapp-groups.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>

            <h5 style="margin-bottom: 2em;">Modifier le Groupe WhatsApp</h5>

            <form class="form-horizontal" action="{{ route('admin.whatsapp-groups.update', $group->id) }}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PATCH">
                
                <div class="form-group row">
                    <label for="group_id" class="col-xs-12 col-form-label">WhatsApp Group ID</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $group->group_id }}" name="group_id" required id="group_id" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Nom du Groupe</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $group->name }}" name="name" required id="name" placeholder="Nom pour affichage">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="default_category" class="col-xs-12 col-form-label">Catégorie Marketplace</label>
                    <div class="col-xs-10">
                        <select name="default_category" id="default_category" class="form-control" required>
                            <option value="VEHICULES" {{ $group->default_category == 'VEHICULES' ? 'selected' : '' }}>Véhicules / Automobile</option>
                            <option value="IMMOBILIER" {{ $group->default_category == 'IMMOBILIER' ? 'selected' : '' }}>Immobilier / Location</option>
                            <option value="ELECTRONIQUE" {{ $group->default_category == 'ELECTRONIQUE' ? 'selected' : '' }}>Électronique</option>
                            <option value="SERVICES" {{ $group->default_category == 'SERVICES' ? 'selected' : '' }}>Services</option>
                            <option value="AUTRE" {{ $group->default_category == 'AUTRE' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="insert_mode" class="col-xs-12 col-form-label">Mode de Publication</label>
                    <div class="col-xs-10">
                        <select name="insert_mode" id="insert_mode" class="form-control" required>
                            <option value="PENDING_VALIDATION" {{ $group->insert_mode == 'PENDING_VALIDATION' ? 'selected' : '' }}>Contrôle Manuel (En attente de validation)</option>
                            <option value="APPROVED" {{ $group->insert_mode == 'APPROVED' ? 'selected' : '' }}>Automatique (Publié directement)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="is_active" class="col-xs-12 col-form-label">Statut</label>
                    <div class="col-xs-10">
                        <select name="is_active" id="is_active" class="form-control" required>
                            <option value="1" {{ $group->is_active == 1 ? 'selected' : '' }}>Actif (Surveillé par l'IA)</option>
                            <option value="0" {{ $group->is_active == 0 ? 'selected' : '' }}>Inactif (Ignoré)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="zipcode" class="col-xs-12 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="{{ route('admin.whatsapp-groups.index') }}" class="btn btn-default">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

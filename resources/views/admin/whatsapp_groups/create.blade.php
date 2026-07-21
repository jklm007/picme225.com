@extends('admin.layout.base')

@section('title', 'Ajouter un Groupe WhatsApp')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.whatsapp-groups.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>

            <h5 style="margin-bottom: 2em;">Ajouter un Groupe WhatsApp</h5>

            <form class="form-horizontal" action="{{ route('admin.whatsapp-groups.store') }}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}
                
                @if(isset($apiError))
                    <div class="alert alert-danger">{{ $apiError }}</div>
                @endif

                <div class="form-group row" id="group_select_container">
                    <label for="group_id" class="col-xs-12 col-form-label">Sélectionnez un Groupe WhatsApp</label>
                    <div class="col-xs-10">
                        @if(isset($whatsappGroups) && count($whatsappGroups) > 0)
                            <select name="group_id" id="group_select" class="form-control" required onchange="updateGroupName()">
                                <option value="">-- Choisir un groupe --</option>
                                @foreach($whatsappGroups as $g)
                                    <option value="{{ $g['id'] }}" data-subject="{{ htmlspecialchars($g['subject'] ?? '') }}">
                                        {{ $g['subject'] ?? $g['id'] }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">La liste provient directement d'Evolution API. <a href="javascript:void(0)" onclick="toggleManualMode(true)">Saisie manuelle</a></small>
                        @else
                            <input class="form-control" type="text" name="group_id" id="group_id_manual" placeholder="Ex: 1203630232490123@g.us" required>
                            <small class="form-text text-muted" style="color: #d9534f !important;">Saisie manuelle active (API indisponible ou lente).</small>
                        @endif
                    </div>
                </div>

                <div class="form-group row" id="group_name_container">
                    <label for="name" class="col-xs-12 col-form-label">Nom du Groupe</label>
                    <div class="col-xs-10">
                        @if(isset($whatsappGroups) && count($whatsappGroups) > 0)
                            <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="group_name" placeholder="Le nom se remplira automatiquement" readonly>
                        @else
                            <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="group_name_manual" placeholder="Entrez le nom du groupe">
                        @endif
                    </div>
                </div>

                <script>
                    function updateGroupName() {
                        var select = document.getElementById('group_select');
                        var nameInput = document.getElementById('group_name');
                        if(select && select.selectedIndex > 0) {
                            var subject = select.options[select.selectedIndex].getAttribute('data-subject');
                            nameInput.value = subject;
                        } else if(nameInput) {
                            nameInput.value = '';
                        }
                    }

                    function toggleManualMode(showManual) {
                        var selectContainer = document.getElementById('group_select_container');
                        var nameContainer = document.getElementById('group_name_container');
                        
                        if (showManual) {
                            selectContainer.innerHTML = '<label class="col-xs-12 col-form-label">ID du Groupe WhatsApp (Saisie Manuelle)</label><div class="col-xs-10"><input class="form-control" type="text" name="group_id" placeholder="Ex: 1203630232490123@g.us" required></div>';
                            nameContainer.innerHTML = '<label class="col-xs-12 col-form-label">Nom du Groupe</label><div class="col-xs-10"><input class="form-control" type="text" name="name" placeholder="Nom du groupe" required></div>';
                        }
                    }
                </script>

                <div class="form-group row">
                    <label for="default_category" class="col-xs-12 col-form-label">Catégorie Marketplace</label>
                    <div class="col-xs-10">
                        <select name="default_category" id="default_category" class="form-control" required>
                            <option value="VEHICULES">Véhicules / Automobile</option>
                            <option value="IMMOBILIER">Immobilier / Location</option>
                            <option value="ELECTRONIQUE">Électronique</option>
                            <option value="SERVICES">Services</option>
                            <option value="AUTRE">Autre</option>
                        </select>
                        <small class="form-text text-muted">Catégorie assignée aux annonces de ce groupe.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="insert_mode" class="col-xs-12 col-form-label">Mode de Publication</label>
                    <div class="col-xs-10">
                        <select name="insert_mode" id="insert_mode" class="form-control" required>
                            <option value="PENDING_VALIDATION">Contrôle Manuel (En attente de validation)</option>
                            <option value="APPROVED">Automatique (Publié directement)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="is_active" class="col-xs-12 col-form-label">Statut</label>
                    <div class="col-xs-10">
                        <select name="is_active" id="is_active" class="form-control" required>
                            <option value="1">Actif (Surveillé par l'IA)</option>
                            <option value="0">Inactif (Ignoré)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="zipcode" class="col-xs-12 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                        <a href="{{ route('admin.whatsapp-groups.index') }}" class="btn btn-default">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

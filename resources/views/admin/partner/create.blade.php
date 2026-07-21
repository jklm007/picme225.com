@extends('admin.layout.base')

@section('title', 'Ajouter un Partenaire')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.partner.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 style="margin-bottom: 2em;">Ajouter un Nouveau Partenaire</h5>

            <form class="form-horizontal" action="{{route('admin.partner.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                
                <div class="row">
                    <!-- SECTION : TYPE ET STATUT -->
                    <div class="col-md-6">
                        <h6 class="mb-2">Paramètres du Partenaire</h6>
                        
                        <div class="form-group row">
                            <label for="type" class="col-xs-12 col-form-label">Type de Partenaire</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="type" id="type_select" required>
                                    <option value="">Sélectionnez un type...</option>
                                    <option value="FLEET_OWNER" {{ old('type') == 'FLEET_OWNER' ? 'selected' : '' }}>Propriétaire de Flotte</option>
                                    <option value="STATION_AGENT" {{ old('type') == 'STATION_AGENT' ? 'selected' : '' }}>Agent de Gare</option>
                                    <option value="SYNDICATE" {{ old('type') == 'SYNDICATE' ? 'selected' : '' }}>Syndicat</option>
                                    <option value="RECRUITER" {{ old('type') == 'RECRUITER' ? 'selected' : '' }}>Recruteur</option>
                                    <option value="AMBASSADOR" {{ old('type') == 'AMBASSADOR' ? 'selected' : '' }}>Ambassadeur</option>
                                    <option value="SPONSOR" {{ old('type') == 'SPONSOR' ? 'selected' : '' }}>Sponsor</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="status" class="col-xs-12 col-form-label">Statut</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="status" required>
                                    <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                                    <option value="PENDING" {{ old('status') == 'PENDING' ? 'selected' : '' }}>En attente</option>
                                    <option value="APPROVED" {{ old('status') == 'APPROVED' ? 'selected' : '' }}>Approuvé</option>
                                    <option value="SUSPENDED" {{ old('status') == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tier" class="col-xs-12 col-form-label">Tier / Niveau</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="tier" required>
                                    <option value="STANDARD" {{ old('tier') == 'STANDARD' ? 'selected' : '' }}>Standard</option>
                                    <option value="CERTIFIED" {{ old('tier') == 'CERTIFIED' ? 'selected' : '' }}>Certifié</option>
                                    <option value="PREMIUM" {{ old('tier') == 'PREMIUM' ? 'selected' : '' }}>Premium</option>
                                    <option value="PERMANENT" {{ old('tier') == 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="logo" class="col-xs-12 col-form-label">Logo / Image</label>
                            <div class="col-xs-10">
                                <input type="file" accept="image/*" name="logo" class="dropify form-control-file" aria-describedby="fileHelp">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="commission_rules" class="col-xs-12 col-form-label">Règles de Commission (JSON optionnel)</label>
                            <div class="col-xs-10">
                                <textarea class="form-control" name="commission_rules" rows="4" placeholder='Ex: {"passenger_cfa": 50, "trip_share_percent": 10}'>{{ old('commission_rules') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION : UTILISATEUR & DONNEES SPECIFIQUES -->
                    <div class="col-md-6">
                        <h6 class="mb-2">Utilisateur Lié</h6>
                        
                        <div class="form-group row">
                            <label class="col-xs-12 col-form-label">Lier à un compte existant ?</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="user_id" id="user_id_select">
                                    <option value="">-- Créer un NOUVEL utilisateur --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="new_user_fields">
                            <div class="form-group row">
                                <div class="col-xs-5">
                                    <input class="form-control" type="text" name="new_user_first_name" value="{{ old('new_user_first_name') }}" placeholder="Prénom">
                                </div>
                                <div class="col-xs-5">
                                    <input class="form-control" type="text" name="new_user_last_name" value="{{ old('new_user_last_name') }}" placeholder="Nom">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-xs-10">
                                    <input class="form-control" type="email" name="new_user_email" value="{{ old('new_user_email') }}" placeholder="Email">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-xs-10">
                                    <input class="form-control" type="text" name="new_user_mobile" value="{{ old('new_user_mobile') }}" placeholder="Téléphone">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-xs-10">
                                    <input class="form-control" type="password" name="new_user_password" placeholder="Mot de passe">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-2 mt-2">Détails Spécifiques</h6>
                        
                        <div class="form-group row" id="company_name_group" style="display: none;">
                            <label for="company_name" class="col-xs-12 col-form-label">Nom de la Compagnie / Flotte</label>
                            <div class="col-xs-10">
                                <input class="form-control" type="text" name="company_name" value="{{ old('company_name') }}" id="company_name" placeholder="Nom de l'entité">
                            </div>
                        </div>

                        <div id="station_fields_group" style="display: none;">
                            <div class="form-group row">
                                <label for="interurban_company_id" class="col-xs-12 col-form-label">Compagnie Interurbaine</label>
                                <div class="col-xs-10">
                                    <select class="form-control" name="interurban_company_id" id="interurban_company_id">
                                        <option value="">Sélectionnez une compagnie...</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('interurban_company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="pdp_stop_id" class="col-xs-12 col-form-label">Gare d'Affectation</label>
                                <div class="col-xs-10">
                                    <select class="form-control" name="pdp_stop_id" id="pdp_stop_id">
                                        <option value="">Sélectionnez une gare...</option>
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}" {{ old('pdp_stop_id') == $station->id ? 'selected' : '' }}>{{ $station->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="col-xs-12 text-center">
                        <button type="submit" class="btn btn-primary">Enregistrer le Partenaire</button>
                        <a href="{{route('admin.partner.index')}}" class="btn btn-default">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle new user fields
        $('#user_id_select').change(function() {
            if($(this).val() == '') {
                $('#new_user_fields').slideDown();
            } else {
                $('#new_user_fields').slideUp();
            }
        });

        // Toggle specific fields based on partner type
        $('#type_select').change(function() {
            var type = $(this).val();
            if(type == 'FLEET_OWNER' || type == 'SYNDICATE' || type == 'SPONSOR') {
                $('#company_name_group').slideDown();
                $('#station_fields_group').slideUp();
            } else if(type == 'STATION_AGENT') {
                $('#company_name_group').slideUp();
                $('#station_fields_group').slideDown();
            } else {
                $('#company_name_group').slideUp();
                $('#station_fields_group').slideUp();
            }
        });

        // Trigger on load
        $('#user_id_select').trigger('change');
        $('#type_select').trigger('change');
    });
</script>
@endsection

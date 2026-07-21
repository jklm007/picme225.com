@extends('admin.layout.base')

@section('title', 'Modifier le Partenaire')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.partner.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Retour</a>
            <h5 style="margin-bottom: 2em;">Modifier le Partenaire : {{ $partner->partner_code }}</h5>

            <form class="form-horizontal" action="{{route('admin.partner.update', $partner->id)}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="PATCH">
                
                <div class="row">
                    <!-- SECTION : TYPE ET STATUT -->
                    <div class="col-md-6">
                        <h6 class="mb-2">Paramètres du Partenaire</h6>
                        
                        <div class="form-group row">
                            <label for="type" class="col-xs-12 col-form-label">Type de Partenaire</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="type" id="type_select" required>
                                    <option value="FLEET_OWNER" {{ $partner->type == 'FLEET_OWNER' ? 'selected' : '' }}>Propriétaire de Flotte</option>
                                    <option value="STATION_AGENT" {{ $partner->type == 'STATION_AGENT' ? 'selected' : '' }}>Agent de Gare</option>
                                    <option value="SYNDICATE" {{ $partner->type == 'SYNDICATE' ? 'selected' : '' }}>Syndicat</option>
                                    <option value="RECRUITER" {{ $partner->type == 'RECRUITER' ? 'selected' : '' }}>Recruteur</option>
                                    <option value="AMBASSADOR" {{ $partner->type == 'AMBASSADOR' ? 'selected' : '' }}>Ambassadeur</option>
                                    <option value="SPONSOR" {{ $partner->type == 'SPONSOR' ? 'selected' : '' }}>Sponsor</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="status" class="col-xs-12 col-form-label">Statut</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="status" required>
                                    <option value="ACTIVE" {{ $partner->status == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                                    <option value="PENDING" {{ $partner->status == 'PENDING' ? 'selected' : '' }}>En attente</option>
                                    <option value="APPROVED" {{ $partner->status == 'APPROVED' ? 'selected' : '' }}>Approuvé</option>
                                    <option value="SUSPENDED" {{ $partner->status == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tier" class="col-xs-12 col-form-label">Tier / Niveau</label>
                            <div class="col-xs-10">
                                <select class="form-control" name="tier" required>
                                    <option value="STANDARD" {{ $partner->tier == 'STANDARD' ? 'selected' : '' }}>Standard</option>
                                    <option value="CERTIFIED" {{ $partner->tier == 'CERTIFIED' ? 'selected' : '' }}>Certifié</option>
                                    <option value="PREMIUM" {{ $partner->tier == 'PREMIUM' ? 'selected' : '' }}>Premium</option>
                                    <option value="PERMANENT" {{ $partner->tier == 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="logo" class="col-xs-12 col-form-label">Logo / Image</label>
                            <div class="col-xs-10">
                                @if($partner->logo)
                                    <div style="margin-bottom: 10px;">
                                        <img src="{{ img($partner->logo) }}" style="max-height: 100px;">
                                    </div>
                                @endif
                                <input type="file" accept="image/*" name="logo" class="dropify form-control-file" aria-describedby="fileHelp">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="commission_rules" class="col-xs-12 col-form-label">Règles de Commission (JSON optionnel)</label>
                            <div class="col-xs-10">
                                <textarea class="form-control" name="commission_rules" rows="4">{{ $partner->commission_rules ? json_encode($partner->commission_rules, JSON_PRETTY_PRINT) : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION : UTILISATEUR & DONNEES SPECIFIQUES -->
                    <div class="col-md-6">
                        <h6 class="mb-2">Utilisateur Lié</h6>
                        
                        <div class="form-group row">
                            <label class="col-xs-12 col-form-label">Utilisateur</label>
                            <div class="col-xs-10">
                                <input type="text" class="form-control" value="{{ $partner->user->first_name }} {{ $partner->user->last_name }} ({{ $partner->user->email }})" disabled>
                                <small>L'utilisateur lié ne peut pas être modifié après création.</small>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-2 mt-2">Détails Spécifiques</h6>
                        
                        <div class="form-group row" id="company_name_group" style="display: none;">
                            <label for="company_name" class="col-xs-12 col-form-label">Nom de la Compagnie / Flotte</label>
                            <div class="col-xs-10">
                                <input class="form-control" type="text" name="company_name" value="{{ $partner->company_name }}" id="company_name" placeholder="Nom de l'entité">
                            </div>
                        </div>

                        <div id="station_fields_group" style="display: none;">
                            <div class="form-group row">
                                <label for="interurban_company_id" class="col-xs-12 col-form-label">Compagnie Interurbaine</label>
                                <div class="col-xs-10">
                                    <select class="form-control" name="interurban_company_id" id="interurban_company_id">
                                        <option value="">Sélectionnez une compagnie...</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ $partner->interurban_company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
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
                                            <option value="{{ $station->id }}" {{ $partner->pdp_stop_id == $station->id ? 'selected' : '' }}>{{ $station->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="col-xs-12 text-center">
                        <button type="submit" class="btn btn-primary">Mettre à jour le Partenaire</button>
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
        $('#type_select').trigger('change');
    });
</script>
@endsection

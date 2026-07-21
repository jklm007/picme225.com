@extends('admin.layout.base')

@section('title', 'Modifier l\'itinéraire')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.pdp-route.index') }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Modifier l'itinéraire: {{ $route->name }}</h5>

            <form class="form-horizontal" action="{{ route('admin.pdp-route.update', $route->id) }}" method="POST" role="form">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Nom de l'itinéraire *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name', $route->name) }}" name="name" required id="name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="type" class="col-xs-12 col-form-label">Type *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="type" name="type" required>
                            <option value="COMMUNAL" {{ old('type', $route->type) == 'COMMUNAL' ? 'selected' : '' }}>Communal</option>
                            <option value="INTER_COMMUNAL" {{ old('type', $route->type) == 'INTER_COMMUNAL' ? 'selected' : '' }}>Inter-communal</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="status" class="col-xs-12 col-form-label">Statut *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="status" name="status" required>
                            <option value="PROPOSED" {{ old('status', $route->status) == 'PROPOSED' ? 'selected' : '' }}>Proposé</option>
                            <option value="VOTING" {{ old('status', $route->status) == 'VOTING' ? 'selected' : '' }}>En vote</option>
                            <option value="APPROVED" {{ old('status', $route->status) == 'APPROVED' ? 'selected' : '' }}>Approuvé</option>
                            <option value="REJECTED" {{ old('status', $route->status) == 'REJECTED' ? 'selected' : '' }}>Rejeté</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-xs-12 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" name="description" id="description" rows="4">{{ old('description', $route->description) }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="max_detour_communal" class="col-xs-12 col-form-label">Détour maximum communal (km)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('max_detour_communal', $route->max_detour_communal) }}" name="max_detour_communal" id="max_detour_communal" min="0">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="max_detour_intercommunal" class="col-xs-12 col-form-label">Détour maximum inter-communal (km)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('max_detour_intercommunal', $route->max_detour_intercommunal) }}" name="max_detour_intercommunal" id="max_detour_intercommunal" min="0">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $route->is_active) ? 'checked' : '' }}> 
                        <label>Actif</label>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('admin.pdp-route.index') }}" class="btn btn-danger btn-block">Annuler</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Mettre à jour</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


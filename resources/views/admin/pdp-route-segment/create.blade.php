@extends('admin.layout.base')

@section('title', 'Créer un segment')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.pdp-route-segment.index', ['route_id' => $routeId]) }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Créer un nouveau segment</h5>

            <form class="form-horizontal" action="{{ route('admin.pdp-route-segment.store') }}" method="POST" role="form">
                {{ csrf_field() }}

                <div class="form-group row">
                    <label for="pdp_route_id" class="col-xs-12 col-form-label">Itinéraire *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="pdp_route_id" name="pdp_route_id" required>
                            <option value="">Sélectionner un itinéraire</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ old('pdp_route_id', $routeId) == $route->id ? 'selected' : '' }}>
                                    {{ $route->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-xs-12 col-form-label">Types de service autorisés</label>
                    <div class="col-xs-10">
                        @foreach($serviceTypes as $serviceType)
                            <label class="checkbox-inline" style="margin-right: 15px; font-weight: normal; cursor: pointer;">
                                <input type="checkbox" name="allowed_service_types[]" value="{{ $serviceType->id }}"
                                    {{ is_array(old('allowed_service_types')) && in_array($serviceType->id, old('allowed_service_types')) ? 'checked' : '' }}>
                                {{ $serviceType->name }}
                            </label>
                        @endforeach
                        <p class="help-block" style="margin-top: 5px; color: #999; font-size: 0.9em;">
                            Sélectionnez un ou plusieurs types de service. Laissez vide pour rendre ce segment accessible à tous les types de service.
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="from_stop_id" class="col-xs-12 col-form-label">Arrêt de départ *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="from_stop_id" name="from_stop_id" required>
                            <option value="">Sélectionner un arrêt</option>
                            @foreach($stops as $stop)
                                <option value="{{ $stop->id }}" {{ old('from_stop_id') == $stop->id ? 'selected' : '' }}>
                                    {{ $stop->order }}. {{ $stop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="to_stop_id" class="col-xs-12 col-form-label">Arrêt d'arrivée *</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="to_stop_id" name="to_stop_id" required>
                            <option value="">Sélectionner un arrêt</option>
                            @foreach($stops as $stop)
                                <option value="{{ $stop->id }}" {{ old('to_stop_id') == $stop->id ? 'selected' : '' }}>
                                    {{ $stop->order }}. {{ $stop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="order" class="col-xs-12 col-form-label">Ordre *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('order') }}" name="order" required id="order" min="1" placeholder="Position du segment">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-xs-12 col-form-label">Prix (FCFA) *</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" step="0.01" value="{{ old('price', 200) }}" name="price" required id="price" min="0" placeholder="200">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="distance_km" class="col-xs-12 col-form-label">Distance (km)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" step="0.01" value="{{ old('distance_km') }}" name="distance_km" id="distance_km" min="0" placeholder="Distance en kilomètres">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="commune" class="col-xs-12 col-form-label">Commune</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('commune') }}" name="commune" id="commune" placeholder="Ex: Cocody">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> 
                        <label>Actif</label>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('admin.pdp-route-segment.index', ['route_id' => $routeId]) }}" class="btn btn-danger btn-block">Annuler</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Créer le segment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('pdp_route_id').addEventListener('change', function() {
    var routeId = this.value;
    if (routeId) {
        // Recharger la page avec le nouveau route_id pour charger les stops
        window.location.href = '{{ route("admin.pdp-route-segment.create") }}?route_id=' + routeId;
    }
});
</script>
@endsection


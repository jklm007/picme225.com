@extends('admin.layout.base')

@section('title', 'Détails de l\'itinéraire')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.pdp-route.index') }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Détails de l'itinéraire: {{ $route->name }}</h5>

            <div class="row">
                <div class="col-md-6">
                    <h6>Informations générales</h6>
                    <table class="table">
                        <tr>
                            <th>Nom:</th>
                            <td>{{ $route->name }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>
                                @if($route->type == 'COMMUNAL')
                                    <span class="label label-info">Communal</span>
                                @else
                                    <span class="label label-warning">Inter-communal</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($route->status == 'APPROVED')
                                    <span class="label label-success">Approuvé</span>
                                @elseif($route->status == 'VOTING')
                                    <span class="label label-warning">En vote</span>
                                @elseif($route->status == 'PROPOSED')
                                    <span class="label label-default">Proposé</span>
                                @else
                                    <span class="label label-danger">Rejeté</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $route->description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Détour max communal:</th>
                            <td>{{ $route->max_detour_communal }} km</td>
                        </tr>
                        <tr>
                            <th>Détour max inter-communal:</th>
                            <td>{{ $route->max_detour_intercommunal }} km</td>
                        </tr>
                        <tr>
                            <th>Actif:</th>
                            <td>
                                @if($route->is_active)
                                    <span class="label label-success">Oui</span>
                                @else
                                    <span class="label label-danger">Non</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <h6>Arrêts ({{ $route->stops->count() }})</h6>
                    <a href="{{ route('admin.pdp-stop.create', ['route_id' => $route->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Ajouter un arrêt
                    </a>
                    <table class="table table-striped" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Nom</th>
                                <th>Commune</th>
                                <th>Coordonnées</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($route->stops as $stop)
                            <tr>
                                <td>{{ $stop->order }}</td>
                                <td>{{ $stop->name }}</td>
                                <td>{{ $stop->commune ?? 'N/A' }}</td>
                                <td>{{ $stop->latitude }}, {{ $stop->longitude }}</td>
                                <td>
                                    <a href="{{ route('admin.pdp-stop.edit', $stop->id) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <h6>Segments ({{ $route->segments->count() }})</h6>
                    <a href="{{ route('admin.pdp-route-segment.create', ['route_id' => $route->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Ajouter un segment
                    </a>
                    <table class="table table-striped" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>De</th>
                                <th>Vers</th>
                                <th>Prix</th>
                                <th>Distance</th>
                                <th>Service Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($route->segments as $segment)
                            <tr>
                                <td>{{ $segment->order }}</td>
                                <td>{{ $segment->fromStop->name ?? 'N/A' }}</td>
                                <td>{{ $segment->toStop->name ?? 'N/A' }}</td>
                                <td>{{ number_format($segment->price, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $segment->distance_km ?? 'N/A' }} km</td>
                                <td>{{ $segment->serviceType->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.pdp-route-segment.edit', $segment->id) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-xs-10">
                    <a href="{{ route('admin.pdp-route.edit', $route->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


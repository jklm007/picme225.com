@extends('admin.layout.base')

@section('title', 'Détails de l\'arrêt')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.pdp-stop.index', ['route_id' => $stop->pdp_route_id]) }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Détails de l'arrêt: {{ $stop->name }}</h5>

            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>Nom:</th>
                            <td>{{ $stop->name }}</td>
                        </tr>
                        <tr>
                            <th>Itinéraire:</th>
                            <td>{{ $stop->route->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Ordre:</th>
                            <td>{{ $stop->order }}</td>
                        </tr>
                        <tr>
                            <th>Commune:</th>
                            <td>{{ $stop->commune ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse:</th>
                            <td>{{ $stop->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Coordonnées:</th>
                            <td>{{ $stop->latitude }}, {{ $stop->longitude }}</td>
                        </tr>
                        <tr>
                            <th>Temps d'attente max:</th>
                            <td>{{ $stop->max_waiting_time ?? 'N/A' }} minutes</td>
                        </tr>
                        <tr>
                            <th>Actif:</th>
                            <td>
                                @if($stop->is_active)
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
                <div class="col-xs-10">
                    <a href="{{ route('admin.pdp-stop.edit', $stop->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


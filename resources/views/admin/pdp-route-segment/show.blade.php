@extends('admin.layout.base')

@section('title', 'Détails du segment')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.pdp-route-segment.index', ['route_id' => $segment->pdp_route_id]) }}" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> Retour
            </a>

            <h5 style="margin-bottom: 2em;">Détails du segment</h5>

            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>Itinéraire:</th>
                            <td>{{ $segment->route->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Type de service:</th>
                            <td>{{ $segment->serviceType->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Arrêt de départ:</th>
                            <td>{{ $segment->fromStop->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Arrêt d'arrivée:</th>
                            <td>{{ $segment->toStop->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Ordre:</th>
                            <td>{{ $segment->order }}</td>
                        </tr>
                        <tr>
                            <th>Prix:</th>
                            <td>{{ number_format($segment->price, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <th>Distance:</th>
                            <td>{{ $segment->distance_km ?? 'N/A' }} km</td>
                        </tr>
                        <tr>
                            <th>Commune:</th>
                            <td>{{ $segment->commune ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Actif:</th>
                            <td>
                                @if($segment->is_active)
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
                    <a href="{{ route('admin.pdp-route-segment.edit', $segment->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('admin.layout.base')

@section('title', 'Itinéraires PDP')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif 
            <h5 class="mb-1">Itinéraires PDP</h5>
            <a href="{{ route('admin.pdp-route.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Ajouter un itinéraire</a>
            <table class="table table-striped table-bordered dataTable" id="table-routes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Créateur</th>
                        <th>Arrêts</th>
                        <th>Segments</th>
                        <th>Détour Max (Comm)</th>
                        <th>Détour Max (Inter)</th>
                        <th>Actif</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($routes as $index => $route)
                    <tr>
                        <td>{{ $route->id }}</td>
                        <td>{{ $route->name }}</td>
                        <td>
                            @if($route->type == 'COMMUNAL')
                                <span class="label label-info">Communal</span>
                            @else
                                <span class="label label-warning">Inter-communal</span>
                            @endif
                        </td>
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
                        <td>{{ $route->creator->first_name ?? 'N/A' }} {{ $route->creator->last_name ?? '' }}</td>
                        <td>{{ $route->stops->count() }}</td>
                        <td>{{ $route->segments->count() }}</td>
                        <td>{{ $route->max_detour_communal }} km</td>
                        <td>{{ $route->max_detour_intercommunal }} km</td>
                        <td>
                            @if($route->is_active)
                                <span class="label label-success">Oui</span>
                            @else
                                <span class="label label-danger">Non</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.pdp-route.destroy', $route->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('admin.pdp-route.show', $route->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> Voir
                                </a>
                                <a href="{{ route('admin.pdp-route.edit', $route->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-pencil"></i> Modifier
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr?')">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


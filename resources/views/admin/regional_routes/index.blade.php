@extends('admin.layout.base')

@section('title', 'Lignes Régionales')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Lignes Régionales (Voyage)</h5>
                <a href="{{ route('admin.regional-routes.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Ajouter une ligne</a>

                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Départ</th>
                            <th>Destination</th>
                            <th>Distance (km)</th>
                            <th>Actif</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($regional_routes as $index => $route)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $route->departure_city }}</td>
                            <td>{{ $route->destination_city }}</td>
                            <td>{{ $route->distance_km }}</td>
                            <td>{{ $route->is_active ? 'Oui' : 'Non' }}</td>
                            <td>
                                <form action="{{ route('admin.regional-routes.destroy', $route->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    <a href="{{ route('admin.regional-routes.edit', $route->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Modifier</a>
                                    <button class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?')"><i class="fa fa-trash"></i> Supprimer</button>
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

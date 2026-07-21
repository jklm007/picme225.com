@extends('admin.layout.base')

@section('title', 'Arrêts PDP')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                @if(Setting::get('demo_mode') == 1)
                    <div class="col-md-12" style="height:50px;color:red;">
                        ** Demo Mode : No Permission to Edit and Delete.
                    </div>
                @endif
                <h5 class="mb-1">Arrêts PDP</h5>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('admin.pdp-stop.index') }}">
                            <select name="route_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Tous les itinéraires</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-8">
                        <a href="{{ route('admin.pdp-stop.create', ['route_id' => request('route_id')]) }}"
                            class="btn btn-primary pull-right">
                            <i class="fa fa-plus"></i> Ajouter un arrêt
                        </a>
                    </div>
                </div>
                <table class="table table-striped table-bordered dataTable" id="table-stops">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th>Itinéraire</th>
                            <th>Ordre</th>
                            <th>Commune</th>
                            <th>Coordonnées</th>
                            <th>Actif</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stops as $index => $stop)
                            <tr>
                                <td>{{ $stop->id }}</td>
                                <td>{{ $stop->name }}</td>
                                <td>{{ ucfirst($stop->type ?? 'arret') }}</td>
                                <td>{{ ucfirst($stop->vehicle_category ?? 'both') }}</td>
                                <td>
                                    @if($stop->routes->count() > 0)
                                        @foreach($stop->routes as $r)
                                            <span class="label label-primary mb-1 d-inline-block">{{ $r->name }}</span>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if(request('route_id') && $stop->routes->contains(request('route_id')))
                                        {{ $stop->routes->find(request('route_id'))->pivot->order }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $stop->commune ?? 'N/A' }}</td>
                                <td>{{ $stop->latitude }}, {{ $stop->longitude }}</td>
                                <td>
                                    @if($stop->is_active)
                                        <span class="label label-success">Oui</span>
                                    @else
                                        <span class="label label-danger">Non</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.pdp-stop.destroy', $stop->id) }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        @if(Setting::get('demo_mode') == 0)
                                            <a href="{{ route('admin.pdp-stop.edit', $stop->id) }}" class="btn btn-info btn-sm">
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
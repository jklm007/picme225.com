@extends('admin.layout.base')

@section('title', 'Segments PDP')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif 
            <h5 class="mb-1">Segments PDP</h5>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('admin.pdp-route-segment.index') }}">
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
                    <a href="{{ route('admin.pdp-route-segment.create', ['route_id' => request('route_id')]) }}" class="btn btn-primary pull-right">
                        <i class="fa fa-plus"></i> Ajouter un segment
                    </a>
                </div>
            </div>
            <table class="table table-striped table-bordered dataTable" id="table-segments">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Itinéraire</th>
                        <th>Ordre</th>
                        <th>De</th>
                        <th>Vers</th>
                        <th>Prix</th>
                        <th>Distance</th>
                        <th>Commune</th>
                        <th>Service Type</th>
                        <th>Actif</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($segments as $index => $segment)
                    <tr>
                        <td>{{ $segment->id }}</td>
                        <td>{{ $segment->route->name ?? 'N/A' }}</td>
                        <td>{{ $segment->order }}</td>
                        <td>{{ $segment->fromStop->name ?? 'N/A' }}</td>
                        <td>{{ $segment->toStop->name ?? 'N/A' }}</td>
                        <td>{{ number_format($segment->price, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $segment->distance_km ?? 'N/A' }} km</td>
                        <td>{{ $segment->commune ?? 'N/A' }}</td>
                        <td>{{ $segment->serviceType->name ?? 'N/A' }}</td>
                        <td>
                            @if($segment->is_active)
                                <span class="label label-success">Oui</span>
                            @else
                                <span class="label label-danger">Non</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.pdp-route-segment.destroy', $segment->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('admin.pdp-route-segment.edit', $segment->id) }}" class="btn btn-info btn-sm">
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


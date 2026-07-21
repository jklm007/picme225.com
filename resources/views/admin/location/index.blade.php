@extends('admin.layout.base')

@section('title', 'Gestion des Véhicules de Location')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            background-color: #f4f6f9;
        }
        .box {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0,0,0,0.05);
            background: #ffffff;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .btn-add {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box bg-white">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark">Véhicules de Location</h4>
                        <p class="text-muted small mb-0">Gérez le catalogue des véhicules disponibles à la location</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.location.create') }}" class="btn btn-add">
                            <i class="fa fa-plus mr-2"></i> Ajouter un Véhicule
                        </a>
                    </div>
                </div>

                <div class="table-responsive p-3">
                    <table class="table table-hover w-100" id="table-2">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Véhicule</th>
                                <th>Prix / Jour</th>
                                <th>Localisation</th>
                                <th>Propriétaire</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $index => $vehicle)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ url($vehicle->cover_image) }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                                            <div>
                                                <strong>{{ $vehicle->title }}</strong><br>
                                                <small class="text-muted">{{ $vehicle->brand }} {{ $vehicle->model }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($vehicle->price) }} CFA</td>
                                    <td>{{ $vehicle->location_city }}</td>
                                    <td>
                                        {{ $vehicle->owner_name }}<br>
                                        <small>{{ $vehicle->owner_phone }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $vehicle->status == 'ACTIVE' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $vehicle->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.location.destroy', $vehicle->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <a href="{{ route('admin.location.edit', $vehicle->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce véhicule ?')"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('admin.layout.base')

@section('title', 'Itinéraires PDP')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
        }

        .box {
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            color: #495057;
        }

        .btn-add {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
            color: white;
        }

        .btn-import {
            background: linear-gradient(135deg, #6c757d 0%, #343a40 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            color: white;
            margin-right: 5px;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 p-2">
                    <div>
                        <h4 class="mb-0 font-weight-bold">Itinéraires PDP / Outstation</h4>
                        <p class="text-muted small mb-0">Lignes de transport interurbain et arrêts</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-import" data-toggle="modal" data-target="#importModal">
                            <i class="fa fa-file-code-o mr-1"></i> Import JSON
                        </button>
                        <a href="{{ route('admin.pdp-route.create') }}" class="btn btn-add">
                            <i class="fa fa-plus-circle mr-1"></i> Nouvel Itinéraire
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="table-2">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Compagnie</th>
                                <th>Type</th>
                                <th>Arrêts</th>
                                <th>Prix / Seg.</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routes as $index => $route)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $route->name }}</strong></td>
                                    <td>{{ $route->company ? $route->company->name : 'N/A' }}</td>
                                    <td><span class="badge badge-primary">{{ $route->type }}</span></td>
                                    <td>{{ $route->stops->count() }} arrêts</td>
                                    <td>{{ currency($route->base_price_per_segment) }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.pdp-route.destroy', $route->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <div class="btn-group">
                                                <a href="{{ route('admin.pdp-route.edit', $route->id) }}"
                                                    class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Supprimer cet itinéraire ?')"><i
                                                        class="fa fa-trash"></i></button>
                                            </div>
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

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.pdp.routes.import.post') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">Importer des Itinéraires</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Compagnie</label>
                            <select class="form-control" name="interurban_company_id" required>
                                @foreach(\App\Models\InterurbanCompany::all() as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fichier JSON</label>
                            <input type="file" name="json_file" class="form-control" accept=".json,.txt" required>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="update_existing" value="1"> Mettre à jour si existe déjà
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Importer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@extends('admin.layout.base')

@section('title', 'Créer un Itinéraire')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
        }

        .box {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .form-group label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
            display: block;
        }

        .btn-submit {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white p-0">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <h4 class="mb-0 font-weight-bold">Nouvel Itinéraire PDP</h4>
                        <span class="text-muted small">Définition d'une ligne de transport interurbain</span>
                    </div>
                    <a href="{{ route('admin.pdp-route.index') }}" class="btn btn-secondary">Retour</a>
                </div>

                <form action="{{ route('admin.pdp-route.store') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nom de l'Itinéraire</label>
                                <input class="form-control" type="text" name="name" required
                                    placeholder="Ex: Abidjan - Yamoussoukro">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Compagnie</label>
                                <select class="form-control" name="interurban_company_id" required>
                                    <option value="">Sélectionner une compagnie</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Type</label>
                                <select class="form-control" name="type" required>
                                    <option value="INTERURBAN" selected>Interurbain (Gare)</option>
                                    <option value="INTERREGIONAL">Interrégional (Voyage Longue Distance)</option>
                                    <option value="COMMUNAL">Communal (TDR)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Prix de Base / Segment ({{ currency() }})</label>
                                <input class="form-control" type="number" name="base_price_per_segment" required
                                    value="500">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-light border-top text-center">
                        <button type="submit" class="btn btn-submit btn-block">Créer l'Itinéraire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
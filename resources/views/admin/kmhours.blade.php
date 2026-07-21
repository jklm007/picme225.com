@extends('admin.layout.base')

@section('title', 'Gestion des Forfaits de Location')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: #f8fafc;
        }
        .premium-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .premium-header {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            padding: 30px 40px;
            color: white;
        }
        .premium-header h4 {
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .form-area {
            padding: 40px;
        }
        .form-control-premium {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 12px 18px;
            font-size: 1rem;
            transition: all 0.2s;
            height: auto;
        }
        .form-control-premium:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .btn-premium {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        .btn-premium:hover {
            background: #2563eb;
            transform: translateY(-2px);
            color: white;
        }
        .table-area {
            padding: 0 40px 40px;
        }
        .table thead th {
            background: #f1f5f9;
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
            color: #64748b;
            padding: 15px 20px;
        }
        .table tbody td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .badge-package {
            background: #eff6ff;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
    </style>
@endsection

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        
        <!-- Formulaire d'ajout -->
        <div class="premium-card">
            <div class="premium-header">
                <h4><i class="fa fa-plus-circle mr-2"></i>Nouveau Forfait Horaire</h4>
                <p class="mb-0 opacity-75">Définissez des packages Km/Heure utilisables dans vos services de location.</p>
            </div>

            <div class="form-area">
                <form action="{{route('admin.kmhour.store')}}" method="POST">
                    {{csrf_field()}}
                    <div class="row align-items-end">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold mb-2">Distance Incluse (KM)</label>
                            <input class="form-control form-control-premium" type="number" name="kilometer" required placeholder="Ex: 50">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold mb-2">Durée Incluse (Heures)</label>
                            <input class="form-control form-control-premium" type="number" name="hour" required placeholder="Ex: 4">
                        </div>
                        <div class="col-md-4 form-group">
                            <button type="submit" class="btn btn-premium btn-block">
                                <i class="fa fa-save mr-2"></i>Créer le Forfait
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des forfaits -->
        <div class="premium-card">
            <div class="premium-header" style="background: white; color: #0f172a; border-bottom: 1px solid #f1f5f9;">
                <h5 class="mb-0 font-weight-bold">Catalogue des Forfaits</h5>
            </div>
            
            <div class="table-area mt-4">
                <table class="table" id="table-2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Configuration</th>
                            <th>ID Technique</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kmhours as $index => $kmhour)
                            <tr>
                                <td><span class="text-muted">{{$index + 1}}</span></td>
                                <td>
                                    <div class="badge-package">
                                        <i class="fa fa-clock-o"></i> {{$kmhour->hour}}H
                                        <span class="text-muted opacity-50">|</span>
                                        <i class="fa fa-road"></i> {{$kmhour->kilometer}}KM
                                    </div>
                                </td>
                                <td><code>#{{$kmhour->id}}</code></td>
                                <td class="text-right">
                                    <form action="{{ route('admin.kmhour.destroy', $kmhour->id) }}" method="POST" class="d-inline">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="_method" value="DELETE">
                                        
                                        <a href="{{ route('admin.kmhour.edit', $kmhour->id) }}" class="btn btn-outline-primary btn-sm rounded-lg mr-2">
                                            <i class="fa fa-pencil"></i> @lang('admin.edit')
                                        </a>
                                        
                                        <button class="btn btn-outline-danger btn-sm rounded-lg" onclick="return confirm('Confirmer la suppression ?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
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





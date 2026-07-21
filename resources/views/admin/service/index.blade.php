@extends('admin.layout.base')

@section('title', 'Service Types')

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

        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #6c757d;
            border-top: none;
            border-bottom: 2px solid #edf2f9;
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 0.9rem;
            color: #343a40;
            padding: 15px;
            border-top: 1px solid #edf2f9;
            transition: all 0.2s ease;
        }

        .table-hover tbody tr:hover td {
            background-color: #f8faff;
        }

        .badge-soft-primary {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }
        
        .badge-soft-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-soft-secondary {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
        }

        /* Antigravity — Badges zone_coverage */
        .badge-zone {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 5px 11px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }
        .badge-zone-communal {
            background: rgba(59,130,246,0.12);
            color: #2563eb;
            border: 1px solid rgba(59,130,246,0.25);
        }
        .badge-zone-intercommunal {
            background: rgba(168,85,247,0.12);
            color: #7c3aed;
            border: 1px solid rgba(168,85,247,0.25);
        }
        .badge-zone-toute {
            background: rgba(16,185,129,0.12);
            color: #059669;
            border: 1px solid rgba(16,185,129,0.25);
        }

        .service-img-wrapper {
            height: 50px;
            width: 50px;
            border-radius: 12px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .service-img-wrapper img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .service-name {
            font-weight: 700;
            font-size: 1rem;
            color: #2b3445;
            margin-bottom: 2px;
        }

        .service-provider-name {
            font-size: 0.8rem;
            color: #7d879c;
            font-weight: 500;
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

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4);
            color: #fff;
        }

        .action-btns .btn {
            border-radius: 8px;
            padding: 6px 12px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            margin-right: 4px;
        }
        
        .action-btns .btn-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border: none;
        }
        .action-btns .btn-info:hover {
            background-color: #17a2b8;
            color: white;
        }

        .action-btns .btn-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
        }
        .action-btns .btn-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        .capacity-pill {
            display: inline-flex;
            align-items: center;
            background: #f1f3f5;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
        }
        .capacity-pill i {
            margin-left: 6px;
            color: #adb5bd;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            
            @if(Setting::get('demo_mode') == 1)
                <div class="alert alert-warning mb-4 shadow-sm border-0 rounded-lg">
                    <i class="fa fa-info-circle mr-2"></i> <strong>Mode Démo :</strong> Les modifications et suppressions sont limitées pour des raisons de sécurité.
                </div>
            @endif

            <div class="box bg-white">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark">Gestion des Services</h4>
                        <p class="text-muted small mb-0">Paramétrez les types de véhicules et les algorithmes de tarification</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.service.create') }}" class="btn btn-add">
                            <i class="fa fa-plus mr-2"></i> Ajouter un Service
                        </a>
                    </div>
                </div>

                <div class="table-responsive p-3">
                    <table class="table table-hover w-100" id="table-2">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="22%">Service &amp; Véhicule</th>
                                <th width="9%" class="text-center">Capacité</th>
                                <th width="13%">Tarification Base</th>
                                <th width="9%">Pr. / Km</th>
                                <th width="9%">Pr. / Min</th>
                                <th width="9%">Type Calcul</th>
                                {{-- Antigravity: Colonne Zone de couverture --}}
                                <th width="12%" class="text-center">Zone Couverture</th>
                                <th width="12%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $index => $service)
                                <tr>
                                    <td><span class="text-muted font-weight-bold">{{ $index + 1 }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="service-img-wrapper">
                                                @if($service->image)
                                                    @php
                                                        $imgPath = $service->image;
                                                        if (strpos($imgPath, 'http') !== 0) {
                                                            if (strpos($imgPath, 'uploads/') !== 0) {
                                                                $imgPath = 'storage/' . $imgPath;
                                                            }
                                                        }
                                                    @endphp
                                                    <img src="{{ asset($imgPath) }}" alt="{{ $service->name }}">
                                                @else
                                                    <i class="fa fa-car text-secondary fa-lg"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="service-name">{{ $service->name }}</div>
                                                <div class="service-provider-name">{{ $service->provider_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="capacity-pill">
                                            {{ $service->capacity }} <i class="fa fa-users"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge-soft-primary mb-1 d-inline-block text-center w-75">{{ currency($service->fixed) }}</span>
                                            <span class="text-muted small"><i class="fa fa-location-arrow mr-1 text-secondary"></i> {{ distance($service->distance) }} inclus</span>
                                        </div>
                                    </td>
                                    <td><span class="font-weight-bold text-dark">{{ currency($service->price) }}</span></td>
                                    <td><span class="font-weight-bold text-dark">{{ currency($service->minute) }}</span></td>
                                    <td>
                                        <span class="badge-soft-secondary text-uppercase"><i class="fa fa-calculator mr-1"></i> @lang('servicetypes.' . $service->calculator)</span>
                                    </td>
                                    {{-- Antigravity: Badge Zone de couverture --}}
                                    <td class="text-center">
                                        @php
                                            $zc = $service->zone_coverage ?? 'COMMUNAL';
                                        @endphp
                                        @if($zc === 'TOUTE_ZONE')
                                            <span class="badge-zone badge-zone-toute">
                                                <i class="fa fa-globe-americas"></i> Universel
                                            </span>
                                        @elseif($zc === 'INTERCOMMUNAL')
                                            <span class="badge-zone badge-zone-intercommunal">
                                                <i class="fa fa-route"></i> Inter-communal
                                            </span>
                                        @else
                                            <span class="badge-zone badge-zone-communal">
                                                <i class="fa fa-city"></i> Communal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns d-inline-flex">
                                            <a href="{{ route('admin.service.edit', $service->id) }}" class="btn btn-info" data-toggle="tooltip" title="Éditer">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            @if(Setting::get('demo_mode', 0) == 0)
                                            <form action="{{ route('admin.service.destroy', $service->id) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service définitivement ?')">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
                                                <button type="submit" class="btn btn-danger" data-toggle="tooltip" title="Supprimer">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
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

@section('scripts')
    <script type="text/javascript">
        // Détruire l'instance DataTable existante si elle existe
        if ($.fn.DataTable.isDataTable('#table-2')) {
            $('#table-2').DataTable().destroy();
        }

        // Initialiser DataTable avec les options personnalisées
        $('#table-2').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    </script>
@endsection
@extends('admin.layout.base')

@section('title', 'Compagnies Interurbaines')

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

        .company-logo {
            height: 50px;
            width: 50px;
            object-fit: contain;
            border-radius: 8px;
            background: #f1f1f1;
            padding: 5px;
        }

        .btn-add {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            color: white;
        }

        .btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .action-btns .btn {
            margin-bottom: 3px;
            font-weight: 600;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 p-2">
                    <div>
                        <h4 class="mb-0 font-weight-bold">Compagnies Interurbaines</h4>
                        <p class="text-muted small mb-0">Gestion des transporteurs officiels (UTB, STIF, etc.)</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.interurban-company.create') }}" class="btn btn-add">
                            <i class="fa fa-plus-circle mr-1"></i> Nouvelle Compagnie
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="table-2">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Logo & Nom</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Nb. Itinéraires</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $index => $company)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($company->logo)
                                                <img src="{{ Storage::url($company->logo) }}" class="company-logo mr-3">
                                            @else
                                                <div class="company-logo mr-3 d-flex align-items-center justify-content-center">
                                                    <i class="fa fa-building text-muted"></i>
                                                </div>
                                            @endif
                                            <strong>{{ $company->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $company->email }}</td>
                                    <td>{{ $company->contact_number }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $company->routes()->count() }}</span>
                                    </td>
                                    <td class="action-btns text-center">
                                        <form action="{{ route('admin.interurban-company.destroy', $company->id) }}"
                                            method="POST">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <div class="btn-group">
                                                <a href="{{ route('admin.interurban-company.edit', $company->id) }}"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fa fa-pencil"></i> Editer
                                                </a>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Supprimer cette compagnie ? Tous les itinéraires associés seront impactés.')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
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
@endsection

@section('scripts')
    <script type="text/javascript">
        if ($.fn.DataTable.isDataTable('#table-2')) {
            $('#table-2').DataTable().destroy();
        }
        $('#table-2').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    </script>
@endsection
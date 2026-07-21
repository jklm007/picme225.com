@extends('admin.layout.base')

@section('title', 'Modifier la Compagnie')

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

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            height: auto;
        }

        .btn-submit {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: transform 0.2s;
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white p-0 overflow-hidden">
                <!-- Header -->
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <h4 class="mb-0 font-weight-bold">
                            <i class="fa fa-pencil text-primary mr-2"></i> Modifier la Compagnie : {{ $company->name }}
                        </h4>
                        <span class="text-muted small">Mise à jour des informations du transporteur</span>
                    </div>
                    <a href="{{ route('admin.interurban-company.index') }}" class="btn btn-secondary px-4">
                        <i class="fa fa-angle-left mr-1"></i> Retour
                    </a>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.interurban-company.update', $company->id) }}" method="POST"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name">Nom de la Compagnie</label>
                                    <input class="form-control" type="text" name="name"
                                        value="{{ old('name', $company->name) }}" required id="name"
                                        placeholder="Ex: UTB, STIF, etc.">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="type">Taille de la Compagnie</label>
                                    <select class="form-control" name="type" id="type" required>
                                        <option value="BIG" {{ $company->type == 'BIG' ? 'selected' : '' }}>Grande (Big)
                                        </option>
                                        <option value="SMALL" {{ $company->type == 'SMALL' ? 'selected' : '' }}>Petite (Small)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="email">Email de Contact</label>
                                    <input class="form-control" type="email" name="email"
                                        value="{{ old('email', $company->email) }}" required id="email"
                                        placeholder="contact@compagnie.com">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="contact_number">Numéro de Téléphone</label>
                                    <input class="form-control" type="text" name="contact_number"
                                        value="{{ old('contact_number', $company->contact_number) }}" required
                                        id="contact_number" placeholder="+225 0102030405">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="logo">Logo de la Compagnie</label>
                                    @if($company->logo)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($company->logo) }}"
                                                style="height: 50px; border-radius: 5px;">
                                        </div>
                                    @endif
                                    <input type="file" accept="image/*" name="logo" class="dropify" data-height="100">
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="address">Adresse du Siège</label>
                                    <textarea class="form-control" name="address" required id="address" rows="3"
                                        placeholder="Adresse complète de la compagnie...">{{ old('address', $company->address) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-4 bg-light border-top text-center">
                        <button type="submit" class="btn btn-submit btn-block px-5">
                            <i class="fa fa-save mr-2"></i> Mettre à jour la Compagnie
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
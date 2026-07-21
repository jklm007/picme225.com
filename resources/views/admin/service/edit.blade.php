@extends('admin.layout.base')

@section('title', 'Configuration Service - ' . $service->name)

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: #f1f5f9;
            color: #1a202c;
        }

        .main-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.6);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .header-premium {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 50px 40px;
            color: white;
            position: relative;
        }

        .header-premium h4 {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .nav-tabs-premium {
            gap: 5px;
            padding: 0 30px;
            background: #fff;
            border-bottom: 2px solid #e2e8f0;
        }

        .nav-tabs-premium .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 22px 25px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            font-size: 0.95rem;
        }

        .nav-tabs-premium .nav-link:hover {
            color: #3b82f6;
        }

        .nav-tabs-premium .nav-link.active {
            color: #3b82f6 !important;
        }

        .nav-tabs-premium .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 20%;
            width: 60%;
            height: 4px;
            background: #3b82f6;
            border-radius: 4px;
        }

        .tab-content {
            padding: 50px 40px;
        }

        .form-label-custom {
            font-weight: 700;
            color: #0f172a;
            font-size: 1rem;
            margin-bottom: 12px;
            display: block;
        }

        .form-control-premium {
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            padding: 14px 20px;
            transition: all 0.2s;
            font-size: 0.95rem;
            background-color: #f8fafc;
            color: #0f172a !important;
        }

        .form-control-premium:focus {
            border-color: #2563eb;
            background-color: #ffffff;
            color: #0f172a !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        select.form-control-premium, select.form-control {
            color: #1e293b !important;
            padding: 10px 15px !important;
            min-height: 52px;
            height: auto !important;
            appearance: auto !important;
            line-height: normal !important;
        }
        
        select.form-control-premium option {
            color: #1e293b !important;
            background: #ffffff;
        }

        /* Category Selector Pills */
        .category-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-item {
            position: relative;
        }

        .category-item input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .category-pill {
            display: flex;
            align-items: center;
            padding: 10px 18px;
            background: #f1f5f9;
            border: 2px solid transparent;
            border-radius: 14px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
            gap: 8px;
        }

        .category-item input:checked + .category-pill {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
        }

        .premium-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 35px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .premium-card.active-state {
            border-left: 6px solid #3b82f6;
        }

        .category-pill {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            gap: 10px;
        }

        .logic-step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }

        .step-number {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        .step-content {
            flex: 1;
        }

        .bg-primary-light {
            background-color: rgba(59, 130, 246, 0.05) !important;
        }

        .variant-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            height: 100%;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .variant-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .variant-card.active {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.12);
        }

        .geo-card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
        }

        .geo-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .geo-card.active {
            border-color: #3b82f6;
            background: #f8fafc;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
        }

        .geo-card.active[data-geo="intercommunal"] {
            border-color: #a855f7;
            background: #faf5ff;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.1);
        }

        .variant-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
        }

        .variant-card.active .variant-badge {
            background: #3b82f6;
        }

        .variant-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .icon-prive { background: #fee2e2; color: #dc2626; }
        .icon-partage { background: #dcfce7; color: #16a34a; }
        .icon-arret { background: #fef9c3; color: #ca8a04; }

        .btn-save-premium {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            border: none;
            padding: 18px 45px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
            letter-spacing: 0.5px;
        }

        .btn-save-premium:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.3);
            color: white;
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 30px;
            height: 100%;
        }

        .section-tag {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 1.5px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-tag::after {
            content: '';
            flex: 1;
            height: 2px;
            background: #f1f5f9;
        }

        /* FORCE VISIBILITY FOR CHECKBOXES (Géo-Zones, Ambulance, etc.) */
        /* NB: Les variant-cards utilisent data-value et JS — pas de checkbox ici */
        input[type="checkbox"]:not([name="main_services[]"]) {
            opacity: 1 !important;
            visibility: visible !important;
            position: relative !important;
            width: 22px !important;
            height: 22px !important;
            display: inline-block !important;
            z-index: 999 !important;
            cursor: pointer !important;
            margin-right: 12px !important;
            accent-color: #3b82f6 !important;
            appearance: checkbox !important;
            -webkit-appearance: checkbox !important;
        }

        .custom-control-label {
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            vertical-align: middle !important;
            padding-left: 0 !important; /* Reset since we use native checkbox */
        }

        .custom-control-label::before, .custom-control-label::after {
            display: none !important; /* Hide the broken bootstrap pseudo-elements */
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="main-box">
                <!-- Header Premium -->
                <div class="header-premium">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4>Configurateur de Service</h4>
                            <p class="mb-0 opacity-75">Intelligence artificielle de dispatch & Paramétrage du catalogue</p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-light px-3 py-2" style="font-size: 0.9rem;">ID:
                                #{{ $service->id }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.service.update', $service->id) }}" method="POST" enctype="multipart/form-data"
                    id="editServiceForm">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PATCH">

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs nav-tabs-premium" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#general" role="tab"><i
                                    class="fa fa-info-circle mr-2"></i>Identité</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pricing" role="tab"><i
                                    class="fa fa-tag mr-2"></i>Tarifs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#sharing" role="tab"><i
                                    class="fa fa-brain mr-2"></i>Dispatch & Variantes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#extras" role="tab"><i
                                    class="fa fa-plus-square mr-2"></i>Extras & Location</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#geo" role="tab"><i
                                    class="fa fa-globe mr-2"></i>Géo-Zones</a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        <!-- TAB 1: IDENTITÉ -->
                        <div class="tab-pane active show" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Nom Public du Service</label>
                                            <input class="form-control form-control-premium" type="text"
                                                value="{{ $service->name }}" name="name" required
                                                placeholder="Ex: VTC Classique">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Nom du Transporteur</label>
                                            <input class="form-control form-control-premium" type="text"
                                                value="{{ $service->provider_name }}" name="provider_name" required
                                                placeholder="Ex: Sedan">
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="form-label-custom">Compagnie Associée (Optionnel)</label>
                                            <select class="form-control form-control-premium" name="interurban_company_id">
                                                <option value="">-- Aucune (Service standard) --</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ $service->interurban_company_id == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="small text-muted mt-1">Lier ce service à une compagnie pour filtrer ses gares et lignes (ex: UTB Express).</p>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="form-label-custom">Capacité Maximale</label>
                                            <div class="d-flex align-items-center bg-light p-3 rounded-xl border-dashed">
                                                <i class="fa fa-users text-primary mr-3 fa-lg"></i>
                                                <input class="form-control form-control-premium border-0 bg-transparent" type="number" 
                                                    value="{{ $service->capacity }}" name="capacity" required style="font-size: 1.5rem; font-weight: 700;">
                                                <span class="ml-2 font-weight-bold text-muted">Passagers</span>
                                            </div>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="form-label-custom">Description Marketing</label>
                                            <textarea class="form-control form-control-premium" name="description"
                                                rows="5" placeholder="Décrivez les avantages de ce service...">{{ $service->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="glass-card mb-4">
                                        <label class="form-label-custom">Visuel du Véhicule</label>
                                        @php
                                            $editImgPath = $service->image;
                                            if ($editImgPath && strpos($editImgPath, 'http') !== 0) {
                                                if (strpos($editImgPath, 'uploads/') !== 0) {
                                                    $editImgPath = 'storage/' . $editImgPath;
                                                }
                                                $editImgPath = asset($editImgPath);
                                            }
                                        @endphp
                                        <input type="file" name="image" class="dropify"
                                            data-default-file="{{ $editImgPath }}" data-height="160">

                                        <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
                                            <label style="font-weight: bold; color: #555; font-size: 0.9rem;">...OU sélectionner une image existante :</label>
                                            <select name="image_select" class="form-control form-control-premium" id="image_select" onchange="previewSelectedImage(this)">
                                                <option value="">-- Conserver l'image actuelle / Aucune sélection --</option>
                                                @foreach($images as $img)
                                                    @php $imgPath = 'service/' . basename($img); @endphp
                                                    <option value="{{ $imgPath }}" data-url="{{ \Storage::disk('s3')->url($imgPath) }}" {{ $service->image == $imgPath ? 'selected' : '' }}>
                                                        {{ basename($img) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-3 text-center" id="image_select_preview_container" style="display: none;">
                                                <img id="image_select_preview" src="" style="max-height: 100px; border-radius: 8px; border: 1px solid #ddd; padding: 5px; background: #fff;" />
                                            </div>
                                        </div>

                                        <script>
                                            function previewSelectedImage(selectElement) {
                                                var selectedOption = selectElement.options[selectElement.selectedIndex];
                                                var previewContainer = document.getElementById('image_select_preview_container');
                                                var previewImage = document.getElementById('image_select_preview');
                                                
                                                if (selectedOption.value !== "") {
                                                    previewImage.src = selectedOption.getAttribute('data-url');
                                                    previewContainer.style.display = 'block';
                                                } else {
                                                    previewContainer.style.display = 'none';
                                                }
                                            }
                                            // Initial preview if selected
                                            window.onload = function() {
                                                var select = document.getElementById('image_select');
                                                if(select.value !== "") {
                                                    previewSelectedImage(select);
                                                }
                                            }
                                        </script>
                                    </div>
                                    <div class="glass-card">
                                        <div class="section-tag">Catégories d'Affichage</div>
                                        <div class="row">
                                            @foreach($services as $main)
                                                <div class="col-md-3 mb-3">
                                                    <label class="category-item w-100 mb-0">
                                                        <input type="checkbox" name="main_services[]" value="{{$main->id}}" 
                                                            {{ $service->services->contains($main->id) ? 'checked' : '' }}>
                                                        <div class="category-pill h-100">
                                                            <i class="fa fa-tag"></i>
                                                            <span>{{$main->name}}</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="small text-muted mt-3 mb-0"><i class="fa fa-info-circle mr-1"></i> Influences la visibilité dans l'App.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: TARIFS -->
                        <div class="tab-pane fade" id="pricing" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card p-4 border-0 bg-light rounded-lg">
                                        <label class="form-label-custom">Calculateur Principal</label>
                                        <select class="form-control form-control-premium mb-4" id="calculator"
                                            name="calculator">
                                            <option value="MIN" @if($service->calculator == 'MIN') selected @endif>Temps
                                                uniquement</option>
                                            <option value="DISTANCE" @if($service->calculator == 'DISTANCE') selected @endif>
                                                Distance uniquement</option>
                                            <option value="DISTANCEMIN" @if($service->calculator == 'DISTANCEMIN') selected
                                            @endif>Distance + Temps</option>
                                            <option value="DISTANCEHOUR" @if($service->calculator == 'DISTANCEHOUR') selected
                                            @endif>Distance + Heure</option>
                                        </select>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_taxable"
                                                name="is_taxable" value="1" {{ $service->is_taxable ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="is_taxable">Soumettre
                                                à la TVA</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Prise en charge (Prix fixe)</label>
                                            <div class="input-group">
                                                <input class="form-control form-control-premium" type="text"
                                                    value="{{ $service->fixed }}" name="fixed">
                                                <div class="input-group-append"><span
                                                        class="input-group-text bg-white border-left-0">{{ currency() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Distance incluse</label>
                                            <div class="input-group">
                                                <input class="form-control form-control-premium" type="text"
                                                    value="{{ $service->distance }}" name="distance">
                                                <div class="input-group-append"><span
                                                        class="input-group-text bg-white border-left-0">{{ distance() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Prix au Kilomètre</label>
                                            <input class="form-control form-control-premium" type="text"
                                                value="{{ $service->price }}" name="price">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Prix à la Minute</label>
                                            <input class="form-control form-control-premium" type="text"
                                                value="{{ $service->minute }}" name="minute">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: DISPATCH & VARIANTES (UNIFIED) -->
                        <div class="tab-pane fade" id="sharing" role="tabpanel">
                            <div class="mb-5">
                                <div class="section-tag">Variantes de Course Actives</div>
                                <div id="variant_inputs_container"></div>
                                <div class="row">
                                    <!-- Option Privé -->
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card {{ (isset($service->allowed_variants) && in_array('prive', $service->allowed_variants)) ? 'active' : '' }}"
                                            data-variant="prive"
                                            onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-prive"><i class="fa fa-user"></i></div>
                                            <h5 class="font-weight-bold">Mode Privé</h5>
                                            <p class="small text-muted mb-0">Course directe exclusive. Idéal pour VTC Classique et Premium.</p>
                                        </div>
                                    </div>
                                    <!-- Option Partage -->
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card {{ (isset($service->allowed_variants) && in_array('partage', $service->allowed_variants)) ? 'active' : '' }}"
                                            data-variant="partage"
                                            onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-partage"><i class="fa fa-random"></i></div>
                                            <h5 class="font-weight-bold">Mode Partage (TDR)</h5>
                                            <p class="small text-muted mb-0">Covoiturage dynamique. Optimisé pour les trajets urbains denses.</p>
                                        </div>
                                    </div>
                                    <!-- Option Arrêt -->
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card {{ (isset($service->allowed_variants) && (in_array('arret', $service->allowed_variants) || in_array('arret_pdp', $service->allowed_variants))) ? 'active' : '' }}"
                                            data-variant="arret_pdp"
                                            onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-arret"><i class="fa fa-map-marker"></i></div>
                                            <h5 class="font-weight-bold">Mode Arrêt (PDP)</h5>
                                            <p class="small text-muted mb-0">Lignes avec arrêts prédéfinis. Fonctionnement de type réseau bus.</p>
                                        </div>
                                    </div>
                                    <!-- Option Livraison Privée -->
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card {{ (isset($service->allowed_variants) && in_array('prive', $service->allowed_variants)) ? 'active' : '' }}"
                                            data-variant="prive"
                                            onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-prive"><i class="fa fa-cube"></i></div>
                                            <h5 class="font-weight-bold">Livraison Privée</h5>
                                            <p class="small text-muted mb-0">Livraison directe exclusive pour un seul client.</p>
                                        </div>
                                    </div>
                                    <!-- Option Livraison Partagée -->
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card {{ (isset($service->allowed_variants) && in_array('partage', $service->allowed_variants)) ? 'active' : '' }}"
                                            data-variant="partage"
                                            onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-partage"><i class="fa fa-cubes"></i></div>
                                            <h5 class="font-weight-bold">Livraison Partagée</h5>
                                            <p class="small text-muted mb-0">Livraison mutualisée avec algorithme de détours.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="glass-card shadow-sm">
                                        <div class="section-tag">Cerveau du Service</div>
                                        
                                        <div id="smart_logic_badge" class="p-3 mb-4 rounded-xl border {{ $service->sharing_type !== 'NONE' ? 'bg-primary-light border-primary' : 'bg-light' }}">
                                            <div class="small font-weight-bold text-primary mb-1">Moteur Actif :</div>
                                            <div id="active_logic_name" class="font-weight-bold text-dark h6 mb-0">
                                                {{ $service->sharing_type == 'PDP' ? 'Réseau d\'Itinéraires (PDP)' : ($service->sharing_type == 'DYNAMIC_POOL' ? 'Optimisation de Flux (TDR)' : 'Séquentiel (Privé)') }}
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-outline-secondary btn-sm mb-4" onclick="$('#advanced_dispatch').toggle()">
                                            <i class="fa fa-terminal mr-2"></i> Paramètres Techniques
                                        </button>

                                        <div id="advanced_dispatch" style="display:none;" class="p-3 border rounded-xl bg-light mb-4">
                                            <label class="small font-weight-bold">Moteur de Dispatch (Override)</label>
                                            <select class="form-control form-control-premium mb-3" name="sharing_type" id="sharing_type">
                                                <option value="NONE" {{ $service->sharing_type == 'NONE' ? 'selected' : '' }}>Séquentiel (Privé et Standard)</option>
                                                <option value="DYNAMIC_POOL" {{ $service->sharing_type == 'DYNAMIC_POOL' ? 'selected' : '' }}>Optimisation de Flux (Partage TDR)</option>
                                                <option value="PDP" {{ $service->sharing_type == 'PDP' ? 'selected' : '' }}>Réseau d'Itinéraires Fixes (PDP)</option>
                                            </select>
                                        </div>
                                        
                                        <label class="form-label-custom">Réduction mode Arrêt (%)</label>
                                        <div class="input-group mb-3">
                                            <input class="form-control form-control-premium" type="number"
                                                name="arret_discount_percent" value="{{ $service->arret_discount_percent }}">
                                            <div class="input-group-append"><span class="input-group-text bg-white border-left-0 font-weight-bold">%</span></div>
                                        </div>

                                        <label class="form-label-custom">Km Gratuit par Passager (TDR)</label>
                                        <div class="input-group">
                                            <input class="form-control form-control-premium" type="number" step="0.1"
                                                name="free_km_per_passenger" value="{{ $service->free_km_per_passenger }}">
                                            <div class="input-group-append"><span class="input-group-text bg-white border-left-0 font-weight-bold">km</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div id="sharing_config" style="display:none;"
                                        class="glass-card shadow-lg bg-white">
                                        <div class="section-tag text-primary">Intelligence Algorithmique</div>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Détour Maximum (km)</label>
                                                <input class="form-control form-control-premium" type="number" step="0.1" name="max_detour"
                                                    value="{{ $service->max_detour }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Prix / Km Partagé</label>
                                                <input class="form-control form-control-premium" type="number" step="0.01" name="price_per_km"
                                                    value="{{ $service->price_per_km }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Prix / Segment PDP</label>
                                                <input class="form-control form-control-premium" type="number" name="price_per_segment"
                                                    value="{{ $service->price_per_segment }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">KM / Segment PDP</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="number"
                                                        step="0.1" name="km_per_segment" value="{{ $service->km_per_segment ?? 2.5 }}">
                                                    <div class="input-group-append"><span
                                                            class="input-group-text bg-light border-left-0">KM</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Attente Max (min)</label>
                                                <input class="form-control form-control-premium" type="number" name="max_waiting_time"
                                                    value="{{ $service->max_waiting_time }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: EXTRAS & LOCATION -->
                        <div class="tab-pane fade" id="extras" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="premium-card mb-4 active-state">
                                        <div class="section-tag text-primary">🌍 Mobilité Interurbaine</div>
                                        <div class="logic-step">
                                            <div class="step-number">1</div>
                                            <div class="step-content">
                                                <h6 class="font-weight-bold mb-1">Configuration Outstation</h6>
                                                <p class="small text-muted">Activez les trajets longue distance entre les villes.</p>
                                                <div class="custom-control custom-switch mb-3">
                                                    <input type="checkbox" class="custom-control-input" id="is_intercity"
                                                        name="is_intercity" value="1" {{ $service->is_intercity ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold" for="is_intercity">
                                                        Activer le Service Voyage (Inter-villes)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="logic-step">
                                            <div class="step-number">2</div>
                                            <div class="step-content">
                                                <h6 class="font-weight-bold mb-1">Prix Outstation / Km</h6>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="text"
                                                        value="{{ $service->outstation_price }}" name="outstation_price" placeholder="0.00">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white border-left-0">{{ currency() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="section-tag text-success">🔗 Système de Raccordement (Feeder)</div>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="requires_feeder_ride"
                                                        name="requires_feeder_ride" value="1" {{ $service->requires_feeder_ride ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold" for="requires_feeder_ride">Nécessite un véhicule de raccordement</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="can_act_as_feeder"
                                                        name="can_act_as_feeder" value="1" {{ $service->can_act_as_feeder ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold" for="can_act_as_feeder">Peut servir de véhicule rabatteur (Feeder)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="small font-weight-bold">Rayon de raccordement (km)</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="number" step="0.1"
                                                        name="feeder_trigger_radius" value="{{ $service->feeder_trigger_radius ?? 2 }}">
                                                    <div class="input-group-append"><span class="input-group-text bg-light border-left-0">KM</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="premium-card border-danger">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-danger text-white rounded-circle p-3 mr-3">
                                                <i class="fa fa-ambulance fa-lg"></i>
                                            </div>
                                            <div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="ambulance" name="ambulance" value="1" {{ $service->ambulance == 1 ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold text-danger h5 mb-0" for="ambulance">Mode URGENCE / AMBULANCE</label>
                                                </div>
                                                <p class="small text-muted mb-0">Priorité maximale et affichage spécifique sur la carte.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="premium-card border-info mt-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info text-white rounded-circle p-3 mr-3">
                                                <i class="fa fa-user-times fa-lg"></i>
                                            </div>
                                            <div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="allow_without_driver" name="allow_without_driver" value="1" {{ $service->allow_without_driver ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold text-info h5 mb-0" for="allow_without_driver">Autoriser SANS CHAUFFEUR</label>
                                                </div>
                                                <p class="small text-muted mb-0">Permet la location du véhicule sans conducteur si le forfait dépasse 24 heures.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div id="rental_config_container" style="display: none;">
                                        <div class="premium-card active-state" style="border-left-color: #8b5cf6;">
                                            <div class="section-tag text-purple" style="color: #8b5cf6;">🔑 Gestion de la Location</div>
                                            
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label class="form-label-custom">Tarif Journalier (Location)</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-premium" type="text"
                                                            value="{{ $service->rental_amount }}" name="rental_amount">
                                                        <div class="input-group-append"><span class="input-group-text bg-white">{{ currency() }}</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label-custom">Forfait 24h</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-premium" type="text" value="{{ $service->day }}"
                                                            name="day">
                                                        <div class="input-group-append"><span class="input-group-text bg-white">{{ currency() }}</span></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="font-weight-bold mb-3">Paliers Horaires Personnalisés</h6>
                                            <div class="row">
                                                @foreach($kmhours as $kmh)
                                                    @php
                                                        $existingPrice = $kmhours_service->where('km_hour_id', $kmh->id)->first();
                                                    @endphp
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 border rounded-xl bg-light transition-all hover-shadow">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="badge badge-primary">{{$kmh->hour}}h</span>
                                                                <span class="small font-weight-bold text-muted">{{$kmh->kilometer}}km max</span>
                                                            </div>
                                                            <input type="hidden" name="km_hour_id[]" value="{{$kmh->id}}">
                                                            <div class="input-group input-group-sm">
                                                                <input class="form-control border-0 font-weight-bold" type="text"
                                                                    value="{{ $existingPrice ? $existingPrice->ren_price : '' }}"
                                                                    name="ren_price[]" placeholder="Prix forfait">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text bg-transparent border-0">{{ currency() }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="rental_placeholder" class="premium-card text-center d-flex flex-column align-items-center justify-content-center p-5 bg-light border-dashed">
                                        <i class="fa fa-key fa-3x text-muted mb-3 opacity-25"></i>
                                        <h6 class="text-muted font-weight-bold">Module Location Désactivé</h6>
                                        <p class="small text-muted">Sélectionnez la catégorie "Location" dans l'onglet Identité pour configurer les forfaits.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: GEO-ZONES -->
                        <div class="tab-pane fade" id="geo" role="tabpanel">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="premium-card h-100 active-state">
                                        <div class="section-tag text-primary">📍 Restrictions Territoriales</div>
                                        
                                        <input type="hidden" name="is_communal" id="is_communal_input" value="{{ $service->is_communal }}">
                                        <input type="hidden" name="is_intercommunal" id="is_intercommunal_input" value="{{ $service->is_intercommunal }}">

                                        <div class="geo-card {{ $service->is_communal == 1 ? 'active' : '' }}" data-geo="communal" onclick="toggleGeo('communal')">
                                            <div class="d-flex align-items-center">
                                                <div class="geo-icon bg-primary-light text-primary rounded-circle p-3 mr-3">
                                                    <i class="fa fa-city fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h6 class="font-weight-bold mb-1">🏙️ Service Communal Uniquement</h6>
                                                    <p class="small text-muted mb-0">Le véhicule ne peut pas sortir de sa commune d'attache.</p>
                                                </div>
                                                <div class="ml-auto geo-check text-primary" style="{{ $service->is_communal == 1 ? '' : 'display:none;' }}">
                                                    <i class="fa fa-check-circle fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="geo-card {{ $service->is_intercommunal == 1 ? 'active' : '' }} mt-3" data-geo="intercommunal" onclick="toggleGeo('intercommunal')">
                                            <div class="d-flex align-items-center">
                                                <div class="geo-icon bg-purple-light text-purple rounded-circle p-3 mr-3" style="background-color: #f3e8ff; color: #a855f7;">
                                                    <i class="fa fa-map-marked-alt fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h6 class="font-weight-bold mb-1">🗺️ Service Inter-Communal / Libre</h6>
                                                    <p class="small text-muted mb-0">Autorise les trajets traversant plusieurs communes ou zones.</p>
                                                </div>
                                                <div class="ml-auto geo-check text-purple" style="color: #a855f7; {{ $service->is_intercommunal == 1 ? '' : 'display:none;' }}">
                                                    <i class="fa fa-check-circle fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="is_interregional" id="is_interregional_input" value="{{ $service->is_interregional ?? 0 }}">
                                        <div class="geo-card {{ ($service->is_interregional ?? 0) == 1 ? 'active' : '' }} mt-3" data-geo="interregional" onclick="toggleGeo('interregional')">
                                            <div class="d-flex align-items-center">
                                                <div class="geo-icon bg-warning-light text-warning rounded-circle p-3 mr-3" style="background-color: #fef3c7; color: #d97706;">
                                                    <i class="fa fa-bus fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h6 class="font-weight-bold mb-1">🚌 Service Interrégional (Voyage)</h6>
                                                    <p class="small text-muted mb-0">Ligne de transport longue distance entre villes.</p>
                                                </div>
                                                <div class="ml-auto geo-check text-warning" style="color: #d97706; {{ ($service->is_interregional ?? 0) == 1 ? '' : 'display:none;' }}">
                                                    <i class="fa fa-check-circle fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Antigravity: Widget Zone de Couverture Active --}}
                                        <div class="mt-3 p-3 rounded-xl border" id="zone_coverage_widget"
                                             style="background: #f8faff; border-color: #e0e7ff !important;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="text-xs font-weight-bold text-muted text-uppercase mb-1" style="font-size:0.7rem; letter-spacing:1px;">
                                                        🎯 Zone de Couverture Active
                                                    </div>
                                                    <div id="zone_coverage_badge_container">
                                                        @php $zc = $service->zone_coverage ?? 'COMMUNAL'; @endphp
                                                        @if($zc === 'TOUTE_ZONE')
                                                            <span class="badge badge-pill px-3 py-2" style="background:rgba(16,185,129,0.15);color:#059669;font-size:0.85rem;font-weight:700;border:1px solid rgba(16,185,129,0.3);">
                                                                🌍 TOUTE ZONE (Universel)
                                                            </span>
                                                        @elseif($zc === 'INTERCOMMUNAL')
                                                            <span class="badge badge-pill px-3 py-2" style="background:rgba(168,85,247,0.15);color:#7c3aed;font-size:0.85rem;font-weight:700;border:1px solid rgba(168,85,247,0.3);">
                                                                🗺️ INTERCOMMUNAL
                                                            </span>
                                                        @else
                                                            <span class="badge badge-pill px-3 py-2" style="background:rgba(59,130,246,0.15);color:#2563eb;font-size:0.85rem;font-weight:700;border:1px solid rgba(59,130,246,0.3);">
                                                                🏙️ COMMUNAL
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                                                        Calculée automatiquement depuis les drapeaux ci-dessus.
                                                    </small>
                                                </div>
                                                <i class="fa fa-shield-alt fa-2x" style="color:#c7d2fe;"></i>
                                            </div>
                                            {{-- Champ caché envoyé au serveur --}}
                                            <input type="hidden" name="zone_coverage" id="zone_coverage_input"
                                                   value="{{ $service->zone_coverage ?? 'COMMUNAL' }}">
                                        </div>

                                        <div class="alert alert-info mt-4 border-0 rounded-xl bg-primary-light">
                                            <i class="fa fa-info-circle mr-2"></i>
                                            <small>Ces paramètres influencent l'algorithme de dispatching "Nearby Providers" et les recherches utilisateurs.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="premium-card">
                                        <div class="section-tag">Paramètres de Zone</div>
                                        
                                        <!-- Configuration Communale -->
                                        <div id="config_communal" style="{{ $service->is_communal == 1 ? '' : 'display:none;' }}">
                                            <div class="alert alert-warning mb-4 border-0 rounded-xl">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-info-circle fa-2x text-warning mr-3"></i>
                                                    <div>
                                                        <h6 class="font-weight-bold mb-1">Commune Dynamique</h6>
                                                        <p class="small mb-0">La commune d'attache n'est pas définie ici. Elle est déterminée dynamiquement en fonction de la zone d'inscription du chauffeur (Provider).</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label-custom">Rayon d'action Opérationnel (km)</label>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-3 mr-3">
                                                        <i class="fa fa-bullseye text-primary"></i>
                                                    </div>
                                                    <div class="input-group flex-1">
                                                        <input class="form-control form-control-premium" type="number" step="0.1"
                                                            name="max_distance" id="max_distance_input" value="{{ $service->max_distance }}" placeholder="15">
                                                        <div class="input-group-append"><span class="input-group-text bg-white border-left-0 font-weight-bold">KM</span></div>
                                                    </div>
                                                </div>
                                                <small class="text-muted mt-2 d-block">Distance maximale autorisée entre le point de prise en charge et le centre de la zone.</small>
                                            </div>
                                        </div>

                                        <!-- Configuration Inter-Communale -->
                                        <div id="config_intercommunal" style="{{ $service->is_intercommunal == 1 ? '' : 'display:none;' }}">
                                            <div class="form-group">
                                                <label class="form-label-custom">Communes Autorisées (Lignes / Trajets)</label>
                                                <p class="small text-muted mb-3">Sélectionnez les communes couvertes par ce service. Laissez vide pour autoriser le "Monde Entier" (aucune limite).</p>
                                                
                                                <div class="row">
                                                    @php
                                                        $selectedCommunes = is_array($service->communes) ? $service->communes : [];
                                                    @endphp
                                                    @foreach(['Abidjan', 'Cocody', 'Plateau', 'Yopougon', 'Abobo', 'Marcory', 'Adjamé', 'Treichville', 'Koumassi', 'Port-Bouët', 'Anyama', 'Bingerville', 'Songon'] as $c)
                                                        <div class="col-md-4 mb-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="commune_{{ $loop->index }}" name="communes[]" value="{{ $c }}" {{ in_array($c, $selectedCommunes) ? 'checked' : '' }}>
                                                                <label class="custom-control-label" for="commune_{{ $loop->index }}">{{ $c }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                            </div>
                        </div>

                    </div>

                    <!-- Action Footer -->
                    <div class="p-5 border-top d-flex justify-content-between align-items-center bg-light">
                        <a href="{{ route('admin.service.index') }}" class="text-muted font-weight-bold"><i
                                class="fa fa-times mr-2"></i>Abandonner</a>
                        <button type="submit" class="btn btn-save-premium px-5 shadow">
                            Mettre à jour la Configuration
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // ─── Gestion des variantes (data-variant, sans checkbox caché) ───────
        function toggleVariant(card) {
            var $card = $(card);
            $card.toggleClass('active');
            updateLogicHierarchy();
        }

        function updateLogicHierarchy() {
            var isArret   = $('.variant-card[data-variant="arret_pdp"]').hasClass('active');
            var isPartage = $('.variant-card[data-variant="partage"]').hasClass('active');

            var sharingType = 'NONE';
            var logicName   = 'Séquentiel (Privé)';

            if (isArret && isPartage) {
                sharingType = 'PDP';
                logicName   = 'Réseau PDP + Partage TDR';
            } else if (isArret) {
                sharingType = 'PDP';
                logicName   = 'Réseau d\'Itinéraires (PDP)';
            } else if (isPartage) {
                sharingType = 'DYNAMIC_POOL';
                logicName   = 'Optimisation de Flux (TDR)';
            }

            $('#sharing_type').val(sharingType);
            $('#active_logic_name').text(logicName);

            if (isArret || isPartage) {
                $('#sharing_config').slideDown(200);
                $('#smart_logic_badge').addClass('bg-primary-light border-primary').removeClass('bg-light');
            } else {
                $('#sharing_config').slideUp(200);
                $('#smart_logic_badge').removeClass('bg-primary-light border-primary').addClass('bg-light');
            }
        }

        $(document).ready(function () {
            // Initialise l'affichage selon les cartes déjà actives au chargement
            updateLogicHierarchy();

            // Avant soumission : génère les hidden inputs allowed_variants[] + feedback visuel
            $('#editServiceForm').on('submit', function () {
                // 1. Générer les hidden inputs pour les variantes actives
                var $container = $('#variant_inputs_container');
                $container.empty();
                var addedVariants = [];
                $('.variant-card.active').each(function () {
                    var val = $(this).data('variant');
                    if (val && !addedVariants.includes(val)) {
                        $container.append('<input type="hidden" name="allowed_variants[]" value="' + val + '">');
                        addedVariants.push(val);
                    }
                });

                // 2. Feedback visuel sur le bouton submit
                var btn = $(this).find('button[type="submit"]');
                btn.html('<i class="fa fa-spinner fa-spin mr-2"></i> Application des paramètres...').attr('disabled', true);
                return true;
            });

            // Initialize dropify
            $('.dropify').dropify({
                messages: {
                    'default': 'Glissez une image ici',
                    'replace': 'Glissez pour remplacer',
                    'remove': 'Supprimer',
                    'error': 'Oups, erreur.'
                }
            });

            // Gérer la visibilité des paramètres du Forfait Location
            function checkRentalVisibility() {
                let showRental = false;
                $('input[name="main_services[]"]:checked').each(function() {
                    let categoryName = $(this).closest('label').find('span').text().toLowerCase();
                    if (categoryName.includes('location') || categoryName.includes('rental')) {
                        showRental = true;
                    }
                });

                if (showRental) {
                    $('#rental_config_container').fadeIn();
                    $('#rental_placeholder').hide();
                } else {
                    $('#rental_config_container').hide();
                    $('#rental_placeholder').fadeIn();
                }
            }

            $('input[name="main_services[]"]').on('change', function() {
                checkRentalVisibility();
            });

            // Au chargement initial
            checkRentalVisibility();

        }); // Fin du $(document).ready()

        // ─── Gestion des Géo-Zones (Scope Global pour le onclick) ───────
        function toggleGeo(type) {
            var isCurrentlyActive = $('.geo-card[data-geo="' + type + '"]').hasClass('active');

            if (isCurrentlyActive) {
                $('.geo-card[data-geo="' + type + '"]').removeClass('active border-primary');
                $('.geo-card[data-geo="' + type + '"] .geo-check').hide();
                $('#is_' + type + '_input').val('0');
                if (type === 'communal') $('#config_communal').slideUp();
                if (type === 'intercommunal') $('#config_intercommunal').slideUp();
            } else {
                $('.geo-card[data-geo="' + type + '"]').addClass('active border-primary');
                $('.geo-card[data-geo="' + type + '"] .geo-check').show();
                $('#is_' + type + '_input').val('1');
                if (type === 'communal') $('#config_communal').slideDown();
                if (type === 'intercommunal') $('#config_intercommunal').slideDown();
            }

            updateZoneCoverageBadge(); // Antigravity: mise à jour badge temps réel
        }

        /**
         * Antigravity — Met à jour le badge zone_coverage en temps réel.
         * Miroir côté JS de ZoneFilterService::inferZoneCoverage() :
         *   intercommunal=1 + interregional=1  → TOUTE_ZONE (Universel)
         *   intercommunal=1                    → INTERCOMMUNAL
         *   sinon                              → COMMUNAL
         */
        function updateZoneCoverageBadge() {
            var communal      = parseInt($('#is_communal_input').val())     === 1;
            var intercommunal = parseInt($('#is_intercommunal_input').val()) === 1;
            var interregional = parseInt($('#is_interregional_input').val()) === 1;

            var zone, html;

            if (intercommunal && interregional) {
                zone = 'TOUTE_ZONE';
                html = '<span class="badge badge-pill px-3 py-2" style="background:rgba(16,185,129,0.15);color:#059669;font-size:0.85rem;font-weight:700;border:1px solid rgba(16,185,129,0.3);">🌍 TOUTE ZONE (Universel)</span>';
            } else if (intercommunal) {
                zone = 'INTERCOMMUNAL';
                html = '<span class="badge badge-pill px-3 py-2" style="background:rgba(168,85,247,0.15);color:#7c3aed;font-size:0.85rem;font-weight:700;border:1px solid rgba(168,85,247,0.3);">🗺️ INTERCOMMUNAL</span>';
            } else {
                zone = 'COMMUNAL';
                html = '<span class="badge badge-pill px-3 py-2" style="background:rgba(59,130,246,0.15);color:#2563eb;font-size:0.85rem;font-weight:700;border:1px solid rgba(59,130,246,0.3);">🏙️ COMMUNAL</span>';
            }

            $('#zone_coverage_badge_container').html(html);
            $('#zone_coverage_input').val(zone);

            // Animation flash sur le widget
            $('#zone_coverage_widget').css('transition', 'box-shadow 0.3s ease').css('box-shadow', '0 0 0 3px rgba(99,102,241,0.25)');
            setTimeout(function() {
                $('#zone_coverage_widget').css('box-shadow', 'none');
            }, 500);
        }

    </script>
@endsection
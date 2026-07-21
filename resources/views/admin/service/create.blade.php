@extends('admin.layout.base')

@section('title', 'Nouveau Type de Service')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: #f0f2f5;
            color: #1a202c;
        }

        .main-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            overflow: hidden;
            margin-bottom: 40px;
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

        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #cbd5e1;
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

        .category-item input:checked + .category-pill {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
            transform: scale(1.05);
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
            transition: all 0.3s;
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

        .btn-create-premium {
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

        .btn-create-premium:hover {
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
            padding-left: 0 !important;
        }

        .custom-control-label::before, .custom-control-label::after {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="main-box">
                <div class="header-premium">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4>Créer un Nouveau Service</h4>
                            <p class="mb-0 opacity-75">Configurez un nouveau mode de transport pour vos utilisateurs</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.service.store') }}" method="POST" enctype="multipart/form-data"
                    id="createServiceForm">
                    {{ csrf_field() }}

                    <ul class="nav nav-tabs nav-tabs-premium" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#general" role="tab"><i
                                    class="fa fa-car mr-2"></i>Identité</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pricing" role="tab"><i
                                    class="fa fa-money mr-2"></i>Tarification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#sharing" role="tab"><i
                                    class="fa fa-share-alt mr-2"></i>Intelligence Dispatch</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#extras" role="tab"><i
                                    class="fa fa-star mr-2"></i>Extras & Location</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#geo" role="tab"><i class="fa fa-map mr-2"></i>Zone
                                Géo</a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        <div class="tab-pane active show" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Nom du Service</label>
                                            <input class="form-control form-control-premium" type="text" name="name"
                                                required placeholder="Ex: Premium Gold">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Nom du Transporteur</label>
                                            <input class="form-control form-control-premium" type="text"
                                                name="provider_name" required placeholder="Ex: Berline Luxe">
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="form-label-custom">Compagnie Associée (Optionnel)</label>
                                            <select class="form-control form-control-premium" name="interurban_company_id">
                                                <option value="">-- Aucune (Service standard) --</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                            <p class="small text-muted mt-1">Lier ce service à une compagnie pour filtrer ses gares et lignes (ex: UTB Express).</p>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="form-label-custom">Capacité Maximale</label>
                                            <div class="d-flex align-items-center bg-light p-3 rounded-xl border-dashed">
                                                <i class="fa fa-users text-primary mr-3 fa-lg"></i>
                                                <input class="form-control form-control-premium border-0 bg-transparent"
                                                    type="number" name="capacity" required value="4"
                                                    style="font-size: 1.5rem; font-weight: 700;">
                                                <span class="ml-2 font-weight-bold text-muted">Passagers</span>
                                            </div>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="form-label-custom">Description Courte</label>
                                            <textarea class="form-control form-control-premium" name="description" rows="4"
                                                required placeholder="Détails du service pour l'utilisateur..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="glass-card mb-4">
                                        <label class="form-label-custom">Visuel de Référence</label>
                                        <input type="file" name="image" class="dropify" data-height="160">
                                        
                                        <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
                                            <label style="font-weight: bold; color: #555; font-size: 0.9rem;">...OU sélectionner une image existante :</label>
                                            <select name="image_select" class="form-control form-control-premium" id="image_select" onchange="previewSelectedImage(this)">
                                                <option value="">-- Aucune sélection --</option>
                                                @foreach($images as $img)
                                                    <option value="service/{{ basename($img) }}" data-url="{{ asset('storage/service/'.basename($img)) }}">{{ basename($img) }}</option>
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
                                        </script>
                                    </div>

                                    <div class="glass-card">
                                        <div class="section-tag">Associations Catalogue</div>
                                        <div class="category-selector">
                                            @foreach($services as $s)
                                                <label class="category-item">
                                                    <input type="checkbox" name="main_services[]" value="{{ $s->id }}">
                                                    <div class="category-pill">
                                                        <i class="fa fa-folder-open category-icon"></i>
                                                        <span>{{ $s->name }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="small text-muted mt-3"><i class="fa fa-info-circle mr-1"></i> Sélectionnez
                                            les grandes sections où ce service apparaîtra.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pricing" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card p-4 border shadow-sm rounded-xl">
                                        <label class="form-label-custom">Modèle Économique</label>
                                        <select class="form-control form-control-premium mb-4" id="calculator"
                                            name="calculator">
                                            <option value="MIN">Uniquement Temps</option>
                                            <option value="DISTANCE">Uniquement Distance</option>
                                            <option value="DISTANCEMIN" selected>Standard (Distance + Temps)</option>
                                            <option value="DISTANCEHOUR">Heure de Location</option>
                                        </select>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_taxable"
                                                name="is_taxable" value="1" checked>
                                            <label class="custom-control-label font-weight-bold" for="is_taxable">Services
                                                taxables (TVA)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Prix de base (Démarrage)</label>
                                            <input class="form-control form-control-premium" type="number" step="0.01"
                                                name="fixed" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Distance Inclus (km)</label>
                                            <input class="form-control form-control-premium" type="number" step="0.1"
                                                name="distance" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Prix / KM</label>
                                            <input class="form-control form-control-premium" type="number" step="0.01"
                                                name="price" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label-custom">Prix / Minute</label>
                                            <input class="form-control form-control-premium" type="number" step="0.01"
                                                name="minute" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="sharing" role="tabpanel">
                            <div class="mb-5">
                                <div class="section-tag">Variantes de Course Disponibles</div>
                                <div id="variant_inputs_container"></div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card active" data-variant="prive" onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-prive"><i class="fa fa-user"></i></div>
                                            <h5 class="font-weight-bold">Mode Privé</h5>
                                            <p class="small text-muted mb-0">Trajet direct EXCLUSIF. Dispatch séquentiel
                                                classique vers le chauffeur le plus proche.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card" data-variant="partage" onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-partage"><i class="fa fa-random"></i></div>
                                            <h5 class="font-weight-bold">Mode Partage (TDR)</h5>
                                            <p class="small text-muted mb-0">Covoiturage intelligent. Algorithme de détour
                                                dynamique pour optimiser les flux.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card" data-variant="arret_pdp" onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-arret"><i class="fa fa-map-marker"></i></div>
                                            <h5 class="font-weight-bold">Mode Gare (PDP)</h5>
                                            <p class="small text-muted mb-0">Lignes avec arrêts fixes. Les utilisateurs
                                                rejoignent des points de ramassage prédéfinis.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card" data-variant="prive" onclick="toggleVariant(this)">
                                            <div class="variant-badge"><i class="fa fa-check"></i></div>
                                            <div class="variant-icon icon-prive"><i class="fa fa-cube"></i></div>
                                            <h5 class="font-weight-bold">Livraison Privée</h5>
                                            <p class="small text-muted mb-0">Livraison directe exclusive pour un seul client.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="variant-card" data-variant="partage" onclick="toggleVariant(this)">
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
                                    <div class="glass-card">
                                        <div class="section-tag">Logique de Dispatch</div>

                                        <div id="smart_logic_badge" class="p-3 mb-4 rounded-xl bg-light border">
                                            <div class="small font-weight-bold text-primary mb-1">Moteur Actif :</div>
                                            <div id="active_logic_name" class="font-weight-bold text-dark h6 mb-0">
                                                Séquentiel (Privé)</div>
                                        </div>

                                        <button type="button" class="btn btn-outline-secondary btn-sm mb-4"
                                            onclick="$('#advanced_dispatch').toggle()">
                                            <i class="fa fa-cog mr-2"></i> Paramètres Avancés
                                        </button>

                                        <div id="advanced_dispatch" style="display:none;"
                                            class="p-3 border rounded-xl bg-light mb-4">
                                            <label class="small font-weight-bold">Algorithme Principal (Force)</label>
                                            <select class="form-control form-control-premium mb-3" name="sharing_type"
                                                id="sharing_type">
                                                <option value="NONE" selected>Séquentiel (Privé / Standard)</option>
                                                <option value="DYNAMIC_POOL">Optimisation de Flux (Partage TDR)</option>
                                                <option value="PDP">Réseau d'Itinéraires (Lignes Fixes / PDP)</option>
                                            </select>
                                            <p class="small text-muted mb-0"><i class="fa fa-info-circle mr-1"></i> Mode
                                                technique : forcer un moteur spécifique.</p>
                                        </div>

                                        <label class="form-label-custom">Réduction mode "Arrêt" (%)</label>
                                        <div class="input-group mb-3">
                                            <input class="form-control form-control-premium" type="number"
                                                name="arret_discount_percent" value="0">
                                            <div class="input-group-append"><span
                                                    class="input-group-text bg-white border-left-0 font-weight-bold">%</span>
                                            </div>
                                        </div>

                                        <label class="form-label-custom">Km Gratuit par Passager (TDR)</label>
                                        <div class="input-group">
                                            <input class="form-control form-control-premium" type="number" step="0.1"
                                                name="free_km_per_passenger" value="0">
                                            <div class="input-group-append"><span
                                                    class="input-group-text bg-white border-left-0 font-weight-bold">km</span>
                                            </div>
                                        </div>
                                        <p class="small text-muted mt-2">Réduction appliquée si l'utilisateur marche vers un
                                            arrêt.</p>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div id="sharing_config" style="display:none;" class="glass-card shadow-lg bg-white">
                                        <div class="section-tag text-primary">Paramètres Algorithmiques</div>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Détour Maximum Admissible</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="number"
                                                        step="0.1" name="max_detour" value="2.0">
                                                    <div class="input-group-append"><span
                                                            class="input-group-text bg-light border-left-0">KM</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Attente Maximum (Pooling)</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="number"
                                                        name="max_waiting_time" value="10">
                                                    <div class="input-group-append"><span
                                                            class="input-group-text bg-light border-left-0">MIN</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Prix au KM Partagé</label>
                                                <input class="form-control form-control-premium" type="number"
                                                    name="price_per_km" value="100">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">Prix / Segment PDP</label>
                                                <input class="form-control form-control-premium" type="number"
                                                    name="price_per_segment" value="250">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="small font-weight-bold">KM / Segment PDP</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="number"
                                                        step="0.1" name="km_per_segment" value="2.5">
                                                    <div class="input-group-append"><span
                                                            class="input-group-text bg-light border-left-0">KM</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                                        name="is_intercity" value="1">
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
                                                        name="outstation_price" placeholder="0.00">
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
                                                        name="requires_feeder_ride" value="1">
                                                    <label class="custom-control-label font-weight-bold" for="requires_feeder_ride">Nécessite un véhicule de raccordement</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="can_act_as_feeder"
                                                        name="can_act_as_feeder" value="1">
                                                    <label class="custom-control-label font-weight-bold" for="can_act_as_feeder">Peut servir de véhicule rabatteur (Feeder)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="small font-weight-bold">Rayon de raccordement (km)</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-premium" type="number" step="0.1"
                                                        name="feeder_trigger_radius" value="2">
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
                                                    <input type="checkbox" class="custom-control-input" id="ambulance" name="ambulance" value="1">
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
                                                    <input type="checkbox" class="custom-control-input" id="allow_without_driver" name="allow_without_driver" value="1" checked>
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
                                                            name="rental_amount">
                                                        <div class="input-group-append"><span class="input-group-text bg-white">{{ currency() }}</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label-custom">Forfait 24h</label>
                                                    <div class="input-group">
                                                        <input class="form-control form-control-premium" type="text"
                                                            name="day">
                                                        <div class="input-group-append"><span class="input-group-text bg-white">{{ currency() }}</span></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="font-weight-bold mb-3">Paliers Horaires Personnalisés</h6>
                                            <div class="row">
                                                @foreach($kmhours as $kmh)
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 border rounded-xl bg-light transition-all hover-shadow">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="badge badge-primary">{{$kmh->hour}}h</span>
                                                                <span class="small font-weight-bold text-muted">{{$kmh->kilometer}}km max</span>
                                                            </div>
                                                            <input type="hidden" name="km_hour_id[]" value="{{$kmh->id}}">
                                                            <div class="input-group input-group-sm">
                                                                <input class="form-control border-0 font-weight-bold" type="text"
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

                        <div class="tab-pane fade" id="geo" role="tabpanel">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="premium-card h-100 active-state">
                                        <div class="section-tag text-primary">📍 Restrictions Territoriales</div>
                                        
                                        <div class="logic-step p-3 rounded-xl transition-all mb-4" id="card_communal" style="cursor: pointer;">
                                            <div class="custom-control custom-checkbox custom-control-inline w-100">
                                                <input type="checkbox" id="is_communal" name="is_communal" class="custom-control-input" value="1">
                                                <label class="custom-control-label font-weight-bold h6 mb-0 w-100" for="is_communal">
                                                    🏙️ Service Communal Uniquement
                                                    <p class="small text-muted font-weight-normal mt-1 mb-0">Le véhicule ne peut pas sortir de sa commune d'attache.</p>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="logic-step p-3 rounded-xl transition-all" id="card_intercommunal" style="cursor: pointer;">
                                            <div class="custom-control custom-checkbox custom-control-inline w-100">
                                                <input type="checkbox" id="is_intercommunal" name="is_intercommunal" class="custom-control-input" value="1">
                                                <label class="custom-control-label font-weight-bold h6 mb-0 w-100" for="is_intercommunal">
                                                    🗺️ Service Inter-Communal / Libre
                                                    <p class="small text-muted font-weight-normal mt-1 mb-0">Autorise les trajets traversant plusieurs communes ou zones.</p>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="logic-step p-3 rounded-xl transition-all mb-4" id="card_interregional" style="cursor: pointer;">
                                            <div class="custom-control custom-checkbox custom-control-inline w-100">
                                                <input type="checkbox" id="is_interregional" name="is_interregional" class="custom-control-input" value="1">
                                                <label class="custom-control-label font-weight-bold h6 mb-0 w-100" for="is_interregional">
                                                    🚌 Service Interrégional (Voyage)
                                                    <p class="small text-muted font-weight-normal mt-1 mb-0">Ligne de transport longue distance entre villes.</p>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Antigravity: Widget Zone de Couverture Active --}}
                                        <div class="mt-4 p-3 rounded-xl border" id="zone_coverage_widget"
                                             style="background: #f8faff; border-color: #e0e7ff !important;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="text-xs font-weight-bold text-muted text-uppercase mb-1" style="font-size:0.7rem; letter-spacing:1px;">
                                                        🎯 Zone de Couverture Détectée
                                                    </div>
                                                    <div id="zone_coverage_badge_container">
                                                        <span class="badge badge-pill px-3 py-2" style="background:rgba(59,130,246,0.15);color:#2563eb;font-size:0.85rem;font-weight:700;border:1px solid rgba(59,130,246,0.3);">
                                                            🏙️ COMMUNAL
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                                                        Calculée automatiquement selon vos choix.
                                                    </small>
                                                </div>
                                                <i class="fa fa-shield-alt fa-2x" style="color:#c7d2fe;"></i>
                                            </div>
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
                                        
                                        <div class="form-group mb-5" id="commune_selection_group">
                                            <label class="form-label-custom">Commune d'Attache / District Principal</label>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-3 mr-3">
                                                    <i class="fa fa-university text-primary"></i>
                                                </div>
                                                <select class="form-control form-control-premium flex-1" name="commune" id="commune_select">
                                                    <option value="">Monde Entier / Pas de limite</option>
                                                    @foreach(['Abidjan', 'Cocody', 'Plateau', 'Yopougon', 'Abobo', 'Marcory', 'Adjamé', 'Treichville', 'Koumassi', 'Port-Bouët', 'Anyama', 'Bingerville', 'Songon'] as $c)
                                                        <option value="{{ $c }}">{{ $c }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <p class="small text-primary mt-2 mb-0" id="commune_warning" style="display:none;"><i class="fa fa-exclamation-triangle mr-1"></i> Obligatoire pour un service communal.</p>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label-custom">Rayon d'action Opérationnel (km)</label>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-3 mr-3">
                                                    <i class="fa fa-bullseye text-primary"></i>
                                                </div>
                                                <div class="input-group flex-1">
                                                    <input class="form-control form-control-premium" type="number" step="0.1"
                                                        name="max_distance" value="15" placeholder="15">
                                                    <div class="input-group-append"><span class="input-group-text bg-white border-left-0 font-weight-bold">KM</span></div>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">Distance maximale autorisée entre le point de prise en charge et le centre de la zone.</small>
                                        </div>
                                    </div>
                                </div>
                                     <div class="p-5 border-top d-flex justify-content-center bg-light">
                        <button type="submit" class="btn btn-create-premium px-5 shadow-lg">
                            Enregistrer le nouveau Service
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Dropify Init
            if ($.isFunction($.fn.dropify)) {
                $('.dropify').dropify({
                    messages: {
                        'default': 'Glissez-déposez un visuel ici ou cliquez',
                        'replace': 'Glissez-déposez ou cliquez pour remplacer',
                        'remove':  'Supprimer',
                        'error':   'Désolé, le fichier est trop volumineux'
                    }
                });
            }

            // Form Submission feedback and Variant tracking
            $('#createServiceForm').on('submit', function () {
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
                
                const btn = $(this).find('button[type="submit"]');
                btn.html('<i class="fa fa-circle-o-notch fa-spin mr-2"></i> Configuration en cours...').attr('disabled', true);
                return true;
            });

            // Initial checks
            checkRentalVisibility();
            updateLogicHierarchy();
            
            // Category toggle for Rental
            $('input[name="main_services[]"]').on('change', function() {
                checkRentalVisibility();
            });

            // Géo-Zones Mutual Exclusion
            $('#is_communal').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#is_intercommunal').prop('checked', false);
                    $('#is_interregional').prop('checked', false); // Désactive interregional aussi
                    $('#commune_warning').fadeIn();
                } else {
                    $('#commune_warning').fadeOut();
                }
                updateZoneCoverageBadge();
            });

            $('#is_intercommunal').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#is_communal').prop('checked', false);
                    $('#commune_warning').fadeOut();
                }
                updateZoneCoverageBadge();
            });
            
            $('#is_interregional').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#is_communal').prop('checked', false);
                    $('#commune_warning').fadeOut();
                }
                updateZoneCoverageBadge();
            });

            // Clickable Logic Cards
            $('#card_communal').on('click', function(e) {
                if(e.target.type !== 'checkbox') $('#is_communal').click();
            });
            $('#card_intercommunal').on('click', function(e) {
                if(e.target.type !== 'checkbox') $('#is_intercommunal').click();
            });
            $('#card_interregional').on('click', function(e) {
                if(e.target.type !== 'checkbox') $('#is_interregional').click();
            });
            
            // Initiale
            updateZoneCoverageBadge();
        });

        /**
         * Antigravity — Met à jour le badge zone_coverage en temps réel dans create.blade.php
         */
        function updateZoneCoverageBadge() {
            var communal      = $('#is_communal').is(':checked');
            var intercommunal = $('#is_intercommunal').is(':checked');
            var interregional = $('#is_interregional').is(':checked');

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

            // Animation flash
            $('#zone_coverage_widget').css('transition', 'box-shadow 0.3s ease').css('box-shadow', '0 0 0 3px rgba(99,102,241,0.25)');
            setTimeout(function() {
                $('#zone_coverage_widget').css('box-shadow', 'none');
            }, 500);
        }

        function checkRentalVisibility() {
            let isRentalSelected = false;
            $('input[name="main_services[]"]:checked').each(function() {
                let label = $(this).closest('label').text().toLowerCase();
                if (label.includes('location') || label.includes('rental')) {
                    isRentalSelected = true;
                }
            });

            if (isRentalSelected) {
                $('#rental_config_container').slideDown(400);
                $('#rental_placeholder').hide();
                $('#calculator').val('DISTANCEHOUR');
            } else {
                $('#rental_config_container').slideUp(300);
                $('#rental_placeholder').show();
            }
        }

        function toggleVariant(card) {
            var $card = $(card);
            $card.toggleClass('active');
            updateLogicHierarchy();
        }

        function updateLogicHierarchy() {
            var isArret   = $('.variant-card[data-variant="arret_pdp"]').hasClass('active') || $('.variant-card[data-variant="arret"]').hasClass('active');
            var isPartage = $('.variant-card[data-variant="partage"]').hasClass('active');

            let sharingType = 'NONE';
            let logicName = 'Séquentiel (Privé)';

            if (isArret && isPartage) {
                sharingType = 'PDP';
                logicName   = 'Réseau PDP + Partage TDR';
            } else if (isArret) {
                sharingType = 'PDP';
                logicName   = 'Réseau d\'Itinéraires (Gare/PDP)';
            } else if (isPartage) {
                sharingType = 'DYNAMIC_POOL';
                logicName   = 'Optimisation Multi-stop (TDR)';
            }

            $('#sharing_type').val(sharingType);
            $('#active_logic_name').text(logicName);

            if (sharingType !== 'NONE') {
                $('#smart_logic_badge').addClass('bg-primary-light border-primary').removeClass('bg-light');
                $('#sharing_config').slideDown();
            } else {
                $('#smart_logic_badge').removeClass('bg-primary-light border-primary').addClass('bg-light');
                $('#sharing_config').slideUp();
            }
        }
    </script>
@endsection
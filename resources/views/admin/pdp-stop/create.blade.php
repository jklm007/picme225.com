@extends('admin.layout.base')

@section('title', 'Créer un arrêt')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('admin.pdp-stop.index', ['route_id' => $routeId]) }}" class="btn btn-default pull-right">
                    <i class="fa fa-angle-left"></i> Retour
                </a>

                <h5 style="margin-bottom: 2em;">
                    <i class="fa fa-map-marker"></i> Créer un nouvel arrêt PDP
                </h5>

                <form class="form-horizontal" action="{{ route('admin.pdp-stop.store') }}" method="POST" role="form">
                    {{ csrf_field() }}

                    <!-- Itinéraire -->
                    <div class="form-group row">
                        <label for="pdp_route_id" class="col-xs-12 col-form-label">
                            Itinéraire <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <select class="form-control" id="pdp_route_id" name="pdp_route_id" required>
                                <option value="">Sélectionner un itinéraire</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ old('pdp_route_id', $routeId) == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Nom de l'arrêt -->
                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">
                            Nom de l'arrêt <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name"
                                placeholder="Ex: Carrefour Vie">
                        </div>
                    </div>

                    <!-- Ordre -->
                    <div class="form-group row">
                        <label for="order" class="col-xs-12 col-form-label">
                            Ordre <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ old('order') }}" name="order" required
                                id="order" min="1" placeholder="Position dans l'itinéraire">
                            <small class="form-text text-muted">Position de l'arrêt dans la séquence de l'itinéraire</small>
                        </div>
                    </div>

                    <!-- Type d'arrêt -->
                    <div class="form-group row">
                        <label for="type" class="col-xs-12 col-form-label">
                            Type d'arrêt <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <select class="form-control" id="type" name="type" required>
                                <option value="arret" {{ old('type') == 'arret' ? 'selected' : '' }}>Arrêt simple</option>
                                <option value="gare" {{ old('type') == 'gare' ? 'selected' : '' }}>Gare (Station)</option>
                            </select>
                            <small class="form-text text-muted">Distinguez s'il s'agit d'un arrêt de rue ou d'une gare
                                routière</small>
                        </div>
                    </div>

                    <!-- Catégorie de véhicule -->
                    <div class="form-group row">
                        <label for="vehicle_category" class="col-xs-12 col-form-label">
                            Catégorie de véhicule <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <select class="form-control" id="vehicle_category" name="vehicle_category" required>
                                <option value="both" {{ old('vehicle_category') == 'both' ? 'selected' : '' }}>Tous (Car &
                                    Mini-bus)</option>
                                <option value="car" {{ old('vehicle_category') == 'car' ? 'selected' : '' }}>Car uniquement
                                </option>
                                <option value="minibus" {{ old('vehicle_category') == 'minibus' ? 'selected' : '' }}>Mini-bus
                                    uniquement</option>
                            </select>
                            <small class="form-text text-muted">Spécifiez quels types de véhicules peuvent s'arrêter
                                ici</small>
                        </div>
                    </div>

                    <!-- ========================================= -->
                    <!-- SECTION CARTE INTERACTIVE -->
                    <!-- ========================================= -->
                    <hr style="margin: 2em 0; border-top: 2px solid #007bff;">
                    <h6 class="mb-3" style="color: #007bff; font-weight: bold;">
                        <i class="fa fa-map"></i> LOCALISATION SUR LA CARTE
                    </h6>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Cliquez sur la carte</strong> pour placer l'arrêt. Les coordonnées et l'adresse seront
                        automatiquement remplies.
                    </div>

                    <!-- Carte Google Maps -->
                    <div class="form-group row">
                        <div class="col-xs-12">
                            <div id="map" style="width: 100%; height: 500px; border: 2px solid #ddd; border-radius: 8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Barre de recherche d'adresse -->
                    <div class="form-group row">
                        <label for="search_address" class="col-xs-12 col-form-label">
                            <i class="fa fa-search"></i> Rechercher une adresse
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" id="search_address"
                                placeholder="Tapez une adresse pour la rechercher sur la carte...">
                            <small class="form-text text-muted">Ou cliquez directement sur la carte pour placer le
                                marqueur</small>
                        </div>
                    </div>

                    <hr style="margin: 2em 0;">

                    <!-- Coordonnées (automatiques, en lecture seule) -->
                    <div class="form-group row">
                        <label for="latitude" class="col-xs-12 col-form-label">
                            Latitude <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="any" value="{{ old('latitude', 5.3364) }}"
                                name="latitude" required id="latitude" readonly style="background-color: #f0f0f0;">
                            <small class="form-text text-muted">Rempli automatiquement en cliquant sur la carte</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="longitude" class="col-xs-12 col-form-label">
                            Longitude <span class="text-danger">*</span>
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="any" value="{{ old('longitude', -4.0267) }}"
                                name="longitude" required id="longitude" readonly style="background-color: #f0f0f0;">
                            <small class="form-text text-muted">Rempli automatiquement en cliquant sur la carte</small>
                        </div>
                    </div>

                    <!-- Adresse (automatique) -->
                    <div class="form-group row">
                        <label for="address" class="col-xs-12 col-form-label">
                            Adresse
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('address') }}" name="address" id="address"
                                readonly style="background-color: #f0f0f0;">
                            <small class="form-text text-muted">Récupérée automatiquement via Google Maps</small>
                        </div>
                    </div>

                    <!-- Commune (automatique) -->
                    <div class="form-group row">
                        <label for="commune" class="col-xs-12 col-form-label">
                            Commune
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('commune') }}" name="commune" id="commune"
                                readonly style="background-color: #f0f0f0;">
                            <small class="form-text text-muted">Détectée automatiquement depuis l'adresse</small>
                        </div>
                    </div>

                    <hr style="margin: 2em 0;">

                    <!-- Temps d'attente max -->
                    <div class="form-group row">
                        <label for="max_waiting_time" class="col-xs-12 col-form-label">
                            Temps d'attente max (minutes)
                        </label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ old('max_waiting_time', 5) }}"
                                name="max_waiting_time" id="max_waiting_time" min="0" placeholder="5">
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="form-group row">
                        <div class="col-xs-10">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_recommended" value="1"
                                    id="is_recommended" {{ old('is_recommended') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_recommended">Arrêt Recommandé</label>
                            </div>
                            <small class="form-text text-muted">Les arrêts recommandés sont mis en avant dans
                                l'application</small>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="form-group row">
                        <div class="col-xs-10">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-3">
                                    <a href="{{ route('admin.pdp-stop.index', ['route_id' => $routeId]) }}"
                                        class="btn btn-danger btn-block">
                                        <i class="fa fa-times"></i> Annuler
                                    </a>
                                </div>
                                <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-save"></i> Créer l'arrêt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Charger Leaflet CSS et JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map;
        let marker;

        document.addEventListener('DOMContentLoaded', function () {
            // Position par défaut: Abidjan, Côte d'Ivoire
            const defaultLat = parseFloat(document.getElementById('latitude').value) || 5.3364;
            const defaultLng = parseFloat(document.getElementById('longitude').value) || -4.0267;

            // Initialiser la carte Leaflet
            map = L.map('map').setView([defaultLat, defaultLng], 13);

            // Charger les tuiles OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Créer le marqueur draggable
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            // Gérer le déplacement du marqueur
            marker.on('dragend', function (event) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // Gérer le clic sur la carte pour placer le marqueur
            map.on('click', function(event) {
                marker.setLatLng(event.latlng);
                updateCoordinates(event.latlng.lat, event.latlng.lng);
            });

            // Si des coordonnées par défaut existent, essayer de récupérer l'adresse (Reverse Geocoding)
            if (defaultLat && defaultLng) {
                reverseGeocode(defaultLat, defaultLng);
            }

            // Gérer la barre de recherche Photon (Autocomplete)
            const searchInput = document.getElementById('search_address');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value;
                
                // Ne chercher que si 3 caractères ou plus
                if (query.length < 3) {
                    closeSuggestions();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    // Bbox Côte d'Ivoire pour limiter la recherche
                    const bbox = "-9.0,4.0,-2.0,11.0";
                    fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&bbox=${bbox}&limit=5`)
                        .then(response => response.json())
                        .then(data => {
                            showSuggestions(data.features);
                        })
                        .catch(err => console.error("Erreur Photon:", err));
                }, 500);
            });

            // Validation finale du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function (e) {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;

                if (!lat || !lng || lat == 0 || lng == 0) {
                    e.preventDefault();
                    showNotification('danger', 'Veuillez placer le marqueur sur la carte pour définir la position de l\'arrêt.');
                    return false;
                }
                return true;
            });
        });

        // Afficher les suggestions Photon
        function showSuggestions(features) {
            closeSuggestions();
            if (!features || features.length === 0) return;

            const input = document.getElementById('search_address');
            const suggestionBox = document.createElement('div');
            suggestionBox.setAttribute('id', 'autocomplete-list');
            suggestionBox.setAttribute('class', 'autocomplete-items');
            
            // Positionner la boîte sous l'input
            input.parentNode.appendChild(suggestionBox);

            features.forEach(feature => {
                const props = feature.properties;
                const item = document.createElement('div');
                let addressLabel = props.name || '';
                if (props.city) addressLabel += ', ' + props.city;
                if (props.state) addressLabel += ', ' + props.state;
                
                item.innerHTML = `<strong>${props.name || 'Lieu'}</strong><br><small class="text-muted">${addressLabel}</small>`;
                
                item.addEventListener('click', function() {
                    const coords = feature.geometry.coordinates; // [lng, lat]
                    const lat = coords[1];
                    const lng = coords[0];
                    
                    input.value = props.name || '';
                    closeSuggestions();
                    
                    // Déplacer la carte et le marqueur
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                });
                suggestionBox.appendChild(item);
            });
        }

        function closeSuggestions() {
            const items = document.querySelectorAll('.autocomplete-items');
            items.forEach(item => item.remove());
        }

        // Fermer les suggestions en cliquant ailleurs
        document.addEventListener('click', function (e) {
            if (e.target.id !== 'search_address') {
                closeSuggestions();
            }
        });

        // Mettre à jour les inputs Latitude/Longitude
        function updateCoordinates(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            reverseGeocode(lat, lng);
        }

        // Reverse Geocoding avec Nominatim
        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('address').value = data.display_name;
                        
                        // Extraire la commune si possible
                        const addr = data.address;
                        const commune = addr.suburb || addr.town || addr.city || addr.village || addr.county || '';
                        if (commune) {
                            document.getElementById('commune').value = commune;
                        }
                        showNotification('success', 'Adresse et commune mises à jour !');
                    }
                })
                .catch(err => {
                    console.error("Erreur Nominatim:", err);
                    showNotification('warning', 'Impossible de récupérer le nom de la rue.');
                });
        }

        // Afficher une notification
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.innerHTML = `
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>${type === 'success' ? '✓' : type === 'warning' ? '⚠' : '✗'}</strong> ${message}
        `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    </script>

    <style>
        #map {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            z-index: 1; /* Empêcher la carte de cacher les suggestions */
        }
        
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 15px;
            right: 15px;
            background-color: #fff;
            max-height: 250px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        
        input[readonly] {
            cursor: not-allowed;
        }
        .alert {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
@endsection
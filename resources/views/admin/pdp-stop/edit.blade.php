@extends('admin.layout.base')

@section('title', 'Modifier l\'arrêt')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                @php
                    $firstRoute = $stop->routes->first();
                    $currentRouteId = request('route_id') ?? ($firstRoute ? $firstRoute->id : null);
                    $currentOrder = $firstRoute ? $firstRoute->pivot->order : 1;
                @endphp

                <a href="{{ route('admin.pdp-stop.index', ['route_id' => $currentRouteId]) }}"
                    class="btn btn-default pull-right">
                    <i class="fa fa-angle-left"></i> Retour
                </a>

                <h5 style="margin-bottom: 2em;">Modifier l'arrêt: {{ $stop->name }}</h5>

                <form class="form-horizontal" action="{{ route('admin.pdp-stop.update', $stop->id) }}" method="POST"
                    role="form">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    <div class="form-group row">
                        <label for="pdp_route_id" class="col-xs-12 col-form-label">Itinéraire *</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="pdp_route_id" name="pdp_route_id" required>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ old('pdp_route_id', $currentRouteId) == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">Nom de l'arrêt *</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('name', $stop->name) }}" name="name"
                                required id="name">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="order" class="col-xs-12 col-form-label">Ordre *</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ old('order', $currentOrder) }}" name="order"
                                required id="order" min="1">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="type" class="col-xs-12 col-form-label">Type d'arrêt *</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="type" name="type" required>
                                <option value="arret" {{ old('type', $stop->type) == 'arret' ? 'selected' : '' }}>Arrêt simple
                                </option>
                                <option value="gare" {{ old('type', $stop->type) == 'gare' ? 'selected' : '' }}>Gare (Station)
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="vehicle_category" class="col-xs-12 col-form-label">Catégorie de véhicule *</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="vehicle_category" name="vehicle_category" required>
                                <option value="both" {{ old('vehicle_category', $stop->vehicle_category) == 'both' ? 'selected' : '' }}>Tous (Car & Mini-bus)</option>
                                <option value="car" {{ old('vehicle_category', $stop->vehicle_category) == 'car' ? 'selected' : '' }}>Car uniquement</option>
                                <option value="minibus" {{ old('vehicle_category', $stop->vehicle_category) == 'minibus' ? 'selected' : '' }}>Mini-bus uniquement</option>
                            </select>
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
                        Déplacez le marqueur sur la carte pour modifier la position de l'arrêt, ou utilisez la barre de recherche.
                    </div>

                    <!-- Carte Leaflet -->
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
                        </div>
                    </div>

                    <hr style="margin: 2em 0;">

                    <div class="form-group row">
                        <label for="latitude" class="col-xs-12 col-form-label">Latitude *</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="any"
                                value="{{ old('latitude', $stop->latitude) }}" name="latitude" required id="latitude" readonly style="background-color: #f0f0f0;">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="longitude" class="col-xs-12 col-form-label">Longitude *</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="any"
                                value="{{ old('longitude', $stop->longitude) }}" name="longitude" required id="longitude" readonly style="background-color: #f0f0f0;">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="commune" class="col-xs-12 col-form-label">Commune</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('commune', $stop->commune) }}"
                                name="commune" id="commune" readonly style="background-color: #f0f0f0;">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="address" class="col-xs-12 col-form-label">Adresse</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('address', $stop->address) }}"
                                name="address" id="address" readonly style="background-color: #f0f0f0;">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="max_waiting_time" class="col-xs-12 col-form-label">Temps d'attente max (minutes)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number"
                                value="{{ old('max_waiting_time', $stop->max_waiting_time) }}" name="max_waiting_time"
                                id="max_waiting_time" min="0">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $stop->is_active) ? 'checked' : '' }}>
                            <label>Actif</label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <input type="checkbox" name="is_outstation_hub" value="1" {{ old('is_outstation_hub', $stop->is_outstation_hub) ? 'checked' : '' }}>
                            <label>Hub de sortie de ville</label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <input type="checkbox" name="is_recommended" value="1" {{ old('is_recommended', $stop->is_recommended) ? 'checked' : '' }}>
                            <label>Recommandé</label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-3">
                                    <a href="{{ route('admin.pdp-stop.index', ['route_id' => $currentRouteId]) }}"
                                        class="btn btn-danger btn-block">Annuler</a>
                                </div>
                                <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                    <button type="submit" class="btn btn-primary btn-block">Mettre à jour</button>
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
            // Position par défaut
            const defaultLat = parseFloat(document.getElementById('latitude').value) || 5.3364;
            const defaultLng = parseFloat(document.getElementById('longitude').value) || -4.0267;

            // Initialiser la carte Leaflet
            map = L.map('map').setView([defaultLat, defaultLng], 15);

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

            // Gérer la barre de recherche Photon (Autocomplete)
            const searchInput = document.getElementById('search_address');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value;
                
                if (query.length < 3) {
                    closeSuggestions();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    const bbox = "-9.0,4.0,-2.0,11.0";
                    fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&bbox=${bbox}&limit=5`)
                        .then(response => response.json())
                        .then(data => {
                            showSuggestions(data.features);
                        })
                        .catch(err => console.error("Erreur Photon:", err));
                }, 500);
            });
        });

        function showSuggestions(features) {
            closeSuggestions();
            if (!features || features.length === 0) return;

            const input = document.getElementById('search_address');
            const suggestionBox = document.createElement('div');
            suggestionBox.setAttribute('id', 'autocomplete-list');
            suggestionBox.setAttribute('class', 'autocomplete-items');
            
            input.parentNode.appendChild(suggestionBox);

            features.forEach(feature => {
                const props = feature.properties;
                const item = document.createElement('div');
                let addressLabel = props.name || '';
                if (props.city) addressLabel += ', ' + props.city;
                if (props.state) addressLabel += ', ' + props.state;
                
                item.innerHTML = `<strong>${props.name || 'Lieu'}</strong><br><small class="text-muted">${addressLabel}</small>`;
                
                item.addEventListener('click', function() {
                    const coords = feature.geometry.coordinates;
                    const lat = coords[1];
                    const lng = coords[0];
                    
                    input.value = props.name || '';
                    closeSuggestions();
                    
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

        document.addEventListener('click', function (e) {
            if (e.target.id !== 'search_address') {
                closeSuggestions();
            }
        });

        function updateCoordinates(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            reverseGeocode(lat, lng);
        }

        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('address').value = data.display_name;
                        const addr = data.address;
                        const commune = addr.suburb || addr.town || addr.city || addr.village || addr.county || '';
                        if (commune) {
                            document.getElementById('commune').value = commune;
                        }
                    }
                })
                .catch(err => console.error("Erreur Nominatim:", err));
        }
    </script>

    <style>
        #map {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            z-index: 1;
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
    </style>
@endsection
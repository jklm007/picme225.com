@extends('provider.layout.app')

@section('title', 'Tableau de bord - ')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Map Container fits exactly inside page-content-wrapper */
    #map {
        width: 100%;
        height: calc(100vh - 124px - env(safe-area-inset-top) - env(safe-area-inset-bottom));
        position: relative;
        z-index: 1;
        opacity: 0;
        transition: opacity 0.8s;
    }
    #map.ready { opacity: 1; }

    /* Floating Map Controls on the right */
    .map-controls-cluster {
        position: absolute;
        top: 20px; right: 16px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .map-ctrl-btn {
        width: 44px; height: 44px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: var(--white);
        font-size: 16px;
        cursor: pointer;
        box-shadow: var(--shadow);
        transition: background 0.2s, transform 0.2s;
    }
    .map-ctrl-btn:hover { background: var(--surface-2); transform: scale(1.05); }
    .map-ctrl-btn.accent { color: var(--green); border-color: rgba(46,204,113,0.3); }

    /* GPS status dot indicator on map */
    .map-gps-indicator {
        position: absolute;
        top: 20px; left: 16px;
        z-index: 10;
        width: 10px; height: 10px;
        background: var(--gray-3);
        border-radius: 50%;
        box-shadow: 0 0 6px currentColor;
        transition: background 0.3s, box-shadow 0.3s;
    }
    .map-gps-indicator.active {
        background: var(--green);
        box-shadow: 0 0 8px var(--green);
    }

    /* Bottom status panel */
    .status-panel-overlay {
        position: absolute;
        bottom: 16px; left: 12px; right: 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        z-index: 10;
        box-shadow: var(--shadow);
    }
    .status-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .status-panel-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-2);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .status-panel-text {
        font-size: 16px;
        font-weight: 700;
        color: var(--white);
        margin-top: 2px;
    }
    
    @keyframes pulse-green-glow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(46,204,113,0.4); }
        50% { box-shadow: 0 0 0 8px rgba(46,204,113,0); }
    }
    .status-panel-text.online {
        color: var(--green);
        animation: pulse-green-glow 2s ease-in-out infinite;
        border-radius: 6px;
        padding: 2px 6px;
        background: var(--green-dim);
        display: inline-block;
    }

    .status-panel-toggle-wrap { display: flex; align-items: center; gap: 10px; }
    .status-panel-toggle-text { font-size: 12px; font-weight: 600; color: var(--gray-2); }
    
    .status-panel-switch {
        position: relative;
        display: inline-block;
        width: 52px; height: 28px;
        flex-shrink: 0;
    }
    .status-panel-switch input { display: none; }
    .status-panel-slider {
        position: absolute; inset: 0;
        background: var(--gray-3);
        border-radius: 34px;
        cursor: pointer;
        transition: background 0.4s;
    }
    .status-panel-slider::before {
        content: '';
        position: absolute;
        width: 22px; height: 22px;
        left: 3px; top: 3px;
        background: white;
        border-radius: 50%;
        transition: transform 0.4s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .status-panel-switch input:checked + .status-panel-slider { background: var(--green); }
    .status-panel-switch input:checked + .status-panel-slider::before { transform: translateX(24px); }

    .status-panel-stats {
        display: flex;
        justify-content: space-around;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }
    .status-panel-stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        flex: 1;
    }
    .status-panel-stat-item + .status-panel-stat-item { border-left: 1px solid var(--border); }
    .status-panel-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        margin-bottom: 2px;
    }
    .status-panel-icon.gold { background: rgba(241,196,15,0.12); color: var(--gold); }
    .status-panel-icon.green { background: var(--green-dim); color: var(--green); }
    .status-panel-icon.blue { background: rgba(52,152,219,0.12); color: #3498db; }
    
    .status-panel-value { font-size: 14px; font-weight: 700; color: var(--white); }
    .status-panel-stat-label { font-size: 9px; color: var(--gray-2); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Custom Leaflet overrides */
    .leaflet-control-attribution { display: none; }
</style>
@endsection

@section('content')
<div style="position: relative; width: 100%; height: 100%; flex: 1; display: flex; flex-direction: column;">
    <!-- Map Container -->
    <div id="map"></div>

    <!-- GPS status indicator -->
    <div class="map-gps-indicator" id="map-gps-dot"></div>

    <!-- Floating Map Controls -->
    <div class="map-controls-cluster">
        <button class="map-ctrl-btn accent" id="btn-locate" aria-label="Ma position">
            <i class="fa fa-crosshairs"></i>
        </button>
        <button class="map-ctrl-btn" id="btn-zoom-in" aria-label="Zoom +">
            <i class="fa fa-plus"></i>
        </button>
        <button class="map-ctrl-btn" id="btn-zoom-out" aria-label="Zoom -">
            <i class="fa fa-minus"></i>
        </button>
    </div>

    <!-- Bottom Status Panel -->
    <div class="status-panel-overlay">
        <div class="status-panel-header">
            <div>
                <div class="status-panel-label">Statut</div>
                <div class="status-panel-text {{ Auth::guard('provider')->user()->service && Auth::guard('provider')->user()->service->status == 'active' ? 'online' : '' }}" id="status-panel-text-element">
                    {{ Auth::guard('provider')->user()->service && Auth::guard('provider')->user()->service->status == 'active' ? '🟢 En ligne' : '⚪ Hors ligne' }}
                </div>
            </div>
            <div class="status-panel-toggle-wrap">
                <span class="status-panel-toggle-text" id="status-toggle-label">{{ Auth::guard('provider')->user()->service && Auth::guard('provider')->user()->service->status == 'active' ? 'ON' : 'OFF' }}</span>
                <label class="status-panel-switch" aria-label="Changer statut">
                    <input type="checkbox" id="provider-status-toggle"
                        {{ Auth::guard('provider')->user()->service && Auth::guard('provider')->user()->service->status == 'active' ? 'checked' : '' }}>
                    <span class="status-panel-slider"></span>
                </label>
            </div>
        </div>
        <div class="status-panel-stats">
            <div class="status-panel-stat-item">
                <div class="status-panel-icon gold"><i class="fa fa-star"></i></div>
                <div class="status-panel-value">{{ number_format(Auth::guard('provider')->user()->rating ?? 0, 1) }}</div>
                <div class="status-panel-stat-label">Note</div>
            </div>
            <div class="status-panel-stat-item">
                <div class="status-panel-icon green"><i class="fa fa-car"></i></div>
                <div class="status-panel-value">{{ $today_trips }}</div>
                <div class="status-panel-stat-label">Courses</div>
            </div>
            <div class="status-panel-stat-item">
                <div class="status-panel-icon blue"><i class="fa fa-money"></i></div>
                <div class="status-panel-value">{{ currency($today_revenue) }}</div>
                <div class="status-panel-stat-label">Revenus</div>
            </div>
        </div>
    </div>

    <!-- Hidden form for status update -->
    <form id="form-offline" action="{{ url('/provider/profile/available') }}" method="POST" style="display:none;">
        {{ csrf_field() }}
        <input type="hidden" name="service_status" id="service_status_input" value="">
    </form>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    (function() {
        'use strict';

        var map = null;
        var driverMarker = null;
        var lastHeading = 0;
        var watchId = null;
        var routingControl = null;

        function makeCarIcon(heading) {
            return L.divIcon({
                html: '<div style="transform:rotate(' + heading + 'deg); width:40px; height:40px;">' +
                      '<img src="{{ asset('images/car_marker.png') }}" style="width:100%;height:100%;object-fit:contain;" />' +
                      '</div>',
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                className: ''
            });
        }

        function initMap() {
            var defaultLat = 5.3599517;
            var defaultLng = -4.0082563;

            map = L.map('map', {
                zoomControl: false,
                attributionControl: false,
                tap: false
            }).setView([defaultLat, defaultLng], 14);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(map);

            document.getElementById('map').classList.add('ready');

            startGPS();

            // Zoom controls
            document.getElementById('btn-zoom-in').addEventListener('click', function() {
                map.zoomIn();
            });
            document.getElementById('btn-zoom-out').addEventListener('click', function() {
                map.zoomOut();
            });
            document.getElementById('btn-locate').addEventListener('click', function() {
                if (driverMarker) {
                    map.setView(driverMarker.getLatLng(), 16, { animate: true });
                } else {
                    startGPS();
                }
            });
        }

        function startGPS() {
            if (!navigator.geolocation) {
                console.warn('Geolocation not supported');
                return;
            }

            navigator.geolocation.getCurrentPosition(onPosition, onGPSError, {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            });

            if (watchId) navigator.geolocation.clearWatch(watchId);
            watchId = navigator.geolocation.watchPosition(onPosition, onGPSError, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 1000
            });
        }

        function onPosition(pos) {
            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;
            var heading = pos.coords.heading || lastHeading;

            if (heading !== null && heading >= 0) {
                lastHeading = heading;
            }

            var dot = document.getElementById('map-gps-dot');
            if (dot) dot.classList.add('active');

            if (!driverMarker) {
                map.setView([lat, lng], 15, { animate: true });
                driverMarker = L.marker([lat, lng], {
                    icon: makeCarIcon(lastHeading),
                    zIndexOffset: 1000
                }).addTo(map);
            } else {
                driverMarker.setLatLng([lat, lng]);
                driverMarker.setIcon(makeCarIcon(lastHeading));
            }

            sendPositionToServer(lat, lng);
        }

        function onGPSError(err) {
            console.warn('GPS error:', err.message);
            var dot = document.getElementById('map-gps-dot');
            if (dot) dot.classList.remove('active');
        }

        function sendPositionToServer(lat, lng) {
            fetch('/api/provider/location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            }).catch(function() {});
        }

        function initStatusToggle() {
            var toggle = document.getElementById('provider-status-toggle');
            var statusText = document.getElementById('status-panel-text-element');
            var toggleLabel = document.getElementById('status-toggle-label');
            var headerDot = document.getElementById('header-status-dot');

            if (!toggle) return;

            toggle.addEventListener('change', function() {
                var isOnline = this.checked;
                var input = document.getElementById('service_status_input');
                var form = document.getElementById('form-offline');

                if (input) input.value = isOnline ? 'active' : 'offline';

                if (isOnline) {
                    statusText.textContent = '🟢 En ligne';
                    statusText.classList.add('online');
                    toggleLabel.textContent = 'ON';
                    if (headerDot) headerDot.classList.add('active');
                } else {
                    statusText.textContent = '⚪ Hors ligne';
                    statusText.classList.remove('online');
                    toggleLabel.textContent = 'OFF';
                    if (headerDot) headerDot.classList.remove('active');
                }

                if (form) form.submit();
            });
        }

        window.updateMap = function(route) {
            if (!map) return;
            if (routingControl) { map.removeControl(routingControl); routingControl = null; }
            if (route && route.destination && route.destination.lat) {
                routingControl = L.Routing.control({
                    waypoints: [
                        L.latLng(route.source.lat, route.source.lng),
                        L.latLng(route.destination.lat, route.destination.lng)
                    ],
                    router: L.Routing.osrmv1({ language: 'fr', profile: 'car' }),
                    lineOptions: { styles: [{ color: '#2ecc71', opacity: 0.85, weight: 5 }] },
                    addWaypoints: false, draggableWaypoints: false,
                    fitSelectedRoutes: true, show: false
                }).addTo(map);
            }
        };

        window.addEventListener('DOMContentLoaded', function() {
            initMap();
            initStatusToggle();
        });

    })();
</script>

<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.1/react.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.1/react-dom.js"></script>
<script src="https://unpkg.com/babel-standalone@6.15.0/babel.min.js"></script>
<script type="text/babel" src="{{ asset('asset/js/incoming.js') }}"></script>
@endsection

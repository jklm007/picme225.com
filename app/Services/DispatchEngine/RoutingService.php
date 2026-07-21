<?php

namespace App\Services\DispatchEngine;

use Illuminate\Support\Facades\Log;

/**
 * RoutingService - Moteur de routage en cascade (Phase 4)
 *
 * Rôle : Fournir un calcul de temps de trajet réel (avec trafic) et de
 * distance routière pour les modes avancés de l'IA.
 *
 * Stratégie de Cascade (Haute Disponibilité & Réduction de Coûts) :
 * 1. OSRM (Gratuit, Rapide, sans trafic précis)
 * 2. Mapbox (Trafic inclus, coût modéré, Fallback 1)
 * 3. Google Maps (Trafic inclus, très précis, très cher, Fallback 2)
 */
class RoutingService
{
    protected $osrmUrl;
    protected $mapboxKey;
    protected $googleKey;

    public function __construct()
    {
        // Si le client a son propre serveur OSRM, il sera dans le .env
        // Sinon, on utilise le serveur de démo public par défaut.
        $this->osrmUrl   = config('services.osrm.url') ?: env('OSRM_URL', 'http://router.project-osrm.org');
        $this->mapboxKey = config('services.mapbox.key') ?: env('MAPBOX_API_KEY');
        $this->googleKey = config('services.google.map_key') ?: env('GOOGLE_MAP_API_KEY_DIRECTIONS');
    }

    /**
     * Calcule le temps et la distance routière entre deux points.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return array|null ['distance_km' => float, 'duration_min' => float] ou null si tout échoue
     */
    public function getRouteEstimate(float $lat1, float $lng1, float $lat2, float $lng2): ?array
    {
        // 1. Essai via OSRM (Priorité 1)
        $osrm = $this->_getOsrmEstimate($lat1, $lng1, $lat2, $lng2);
        if ($osrm) return $osrm;

        // 2. Fallback via Mapbox (Priorité 2)
        if ($this->mapboxKey) {
            $mapbox = $this->_getMapboxEstimate($lat1, $lng1, $lat2, $lng2);
            if ($mapbox) return $mapbox;
        }

        // 3. Fallback via Google Maps (Priorité 3)
        if ($this->googleKey) {
            $google = $this->_getGoogleEstimate($lat1, $lng1, $lat2, $lng2);
            if ($google) return $google;
        }

        Log::error("[RoutingService] ÉCHEC TOTAL DE ROUTAGE (OSRM, Mapbox et Google ont tous échoué)");
        return null;
    }

    /**
     * Calcule via OSRM (Format: longitude,latitude)
     */
    private function _getOsrmEstimate($lat1, $lng1, $lat2, $lng2): ?array
    {
        try {
            // OSRM attend le format : {longitude},{latitude}
            $url = "{$this->osrmUrl}/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 2, // Timeout ultra court pour ne pas bloquer le dispatch
                CURLOPT_USERAGENT      => 'PicMe-Dispatch-Engine/2.0' // OSRM l'exige souvent
            ]);
            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['routes'][0])) {
                    $route = $data['routes'][0];
                    return [
                        'distance_km'  => $route['distance'] / 1000,
                        'duration_min' => $route['duration'] / 60
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("[RoutingService] OSRM a échoué: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Calcule via Mapbox Directions API (Trafic inclus)
     */
    private function _getMapboxEstimate($lat1, $lng1, $lat2, $lng2): ?array
    {
        try {
            $url = "https://api.mapbox.com/directions/v5/mapbox/driving-traffic/{$lng1},{$lat1};{$lng2},{$lat2}?access_token={$this->mapboxKey}";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 2
            ]);
            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['routes'][0])) {
                    $route = $data['routes'][0];
                    return [
                        'distance_km'  => $route['distance'] / 1000,
                        'duration_min' => $route['duration'] / 60
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("[RoutingService] Mapbox a échoué: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Calcule via Google Maps Directions API (Trafic inclus)
     */
    private function _getGoogleEstimate($lat1, $lng1, $lat2, $lng2): ?array
    {
        try {
            // Google Maps attend le format : {latitude},{longitude}
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$lat1},{$lng1}&destination={$lat2},{$lng2}&departure_time=now&key={$this->googleKey}";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3
            ]);
            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['routes'][0]['legs'][0])) {
                    $leg = $data['routes'][0]['legs'][0];
                    // Si on a le temps avec trafic, on le prend, sinon on prend le temps normal
                    $durationSecs = $leg['duration_in_traffic']['value'] ?? $leg['duration']['value'];
                    return [
                        'distance_km'  => $leg['distance']['value'] / 1000,
                        'duration_min' => $durationSecs / 60
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("[RoutingService] Google Maps a échoué: " . $e->getMessage());
        }
        return null;
    }
}

<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    public function reverseGeocode($latitude, $longitude)
    {
        $apiKey = config('services.google_maps.key');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}&result_type=locality|administrative_area_level_3&language=fr";

        try {
            $response = Http::get($url);
            $response->throw();
            $data = $response->json();

            if ($data['status'] === 'OK') {
                foreach ($data['results'] as $result) {
                    foreach ($result['address_components'] as $component) {
                        if (in_array('administrative_area_level_3', $component['types'])) {
                            return $component['long_name'];
                        }
                    }
                }
                foreach ($data['results'] as $result) {
                    foreach ($result['address_components'] as $component) {
                        if (in_array('locality', $component['types'])) {
                            return $component['long_name'];
                        }
                    }
                }
                return null; // Aucun type pertinent trouvé
            } else {
                \Log::error("Google Geocoding API error: " . $data['status'] . " - " . ($data['error_message'] ?? 'No message'));
                return null;
            }
        } catch (\Exception $e) {
            \Log::error("HTTP request error (Geocoding): " . $e->getMessage());
            return null;
        }
    }

    // Récupérer l'adresse exacte complète
    public function getFullAddress($latitude, $longitude)
    {
        $apiKey = config('services.google_maps.key');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}&language=fr";

        try {
            $response = Http::get($url);
            $response->throw();
            $data = $response->json();

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                // Retourne l'adresse formatée la plus précise (généralement le premier résultat)
                return $data['results'][0]['formatted_address'];
            } else {
                \Log::error("Google Geocoding API error (getFullAddress): " . $data['status'] . " - " . ($data['error_message'] ?? 'No message'));
                return null;
            }
        } catch (\Exception $e) {
            \Log::error("HTTP request error (getFullAddress): " . $e->getMessage());
            return null;
        }
    }

    // Vous pouvez ajouter d'autres méthodes liées à Google Maps ici (getRouteDuration, etc.)
}

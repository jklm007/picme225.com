<?php

namespace App\Services;

use App\Models\Commune;
use App\Models\PdpStop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhotonGeocodingService
{
    private $photonUrl = 'https://photon.komoot.io/api/';

    /**
     * Recherche un lieu via Photon et vérifie sa cohérence avec la base locale
     */
    public function searchAndValidate($query, $communeId = null)
    {
        // 1. Appel API Photon
        // Limitation à la Côte d'Ivoire (bbox ou lat/lon)
        $response = Http::get($this->photonUrl, [
            'q' => $query,
            'limit' => 5,
            'lat' => 5.345317, // Abidjan centre
            'lon' => -4.024429,
        ]);

        if (!$response->successful()) {
            return ['error' => 'Erreur API Photon'];
        }

        $features = $response->json('features');
        $results = [];

        foreach ($features as $feature) {
            $coords = $feature['geometry']['coordinates']; // [lon, lat]
            $lng = $coords[0];
            $lat = $coords[1];
            $props = $feature['properties'];

            // 2. Calcul du Score de Confiance
            $score = $this->calculateConfidenceScore($props, $lat, $lng, $query, $communeId);

            // 3. Statut de validation en fonction du score
            $statut = 'manuel';
            if ($score >= 90) {
                $statut = 'automatique';
            } elseif ($score >= 60) {
                $statut = 'en_attente';
            }

            $results[] = [
                'nom' => $props['name'] ?? $query,
                'latitude' => $lat,
                'longitude' => $lng,
                'score' => $score,
                'statut' => $statut,
                'photon_raw_data' => $feature,
                'adresse_formatee' => ($props['name'] ?? '') . ', ' . ($props['street'] ?? '') . ', ' . ($props['city'] ?? '')
            ];
        }

        return $results;
    }

    /**
     * Calcul du Score de confiance (0 à 100)
     */
    private function calculateConfidenceScore($props, $lat, $lng, $query, $communeId)
    {
        $score = 0;

        // A. Correspondance du nom (+30)
        $name = strtolower($props['name'] ?? '');
        $q = strtolower($query);
        if ($name && (strpos($name, $q) !== false || strpos($q, $name) !== false)) {
            $score += 30;
        }

        // B. Précision OSM (node vs way) (+30)
        $osmType = $props['osm_type'] ?? '';
        if ($osmType === 'node') {
            $score += 30; // Point très précis
        } elseif ($osmType === 'way') {
            $score += 20; // Bâtiment ou route
        }

        // C. Validation Spatiale (PostGIS) (+40)
        if ($communeId) {
            // Vérifier si le point est physiquement dans le polygone de la commune
            $commune = Commune::find($communeId);
            if ($commune) {
                $isInside = Commune::where('id', $communeId)
                    ->containsPoint($lat, $lng)
                    ->exists();

                if ($isInside) {
                    $score += 40;
                } else {
                    // Pénalité sévère si hors de la commune demandée
                    $score -= 50; 
                }
            }
        } else {
            // Si pas de commune spécifiée, on cherche dans quelle commune se trouve le point
            $communeMatch = Commune::containsPoint($lat, $lng)->first();
            if ($communeMatch) {
                $score += 40; // Au moins il est dans une zone couverte connue
            }
        }

        // D. Vérification de proximité avec les PDP existants (+10 bonus ou pénalité)
        $nearbyPdp = PdpStop::withinRadius($lat, $lng, 0.1)->count(); // dans les 100m
        if ($nearbyPdp > 0) {
            // Probablement un doublon, on baisse le score pour obliger une vérif manuelle
            $score -= 20; 
        }

        return max(0, min(100, $score)); // Borner entre 0 et 100
    }
}

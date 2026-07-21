<?php

namespace App\Services;

use App\Models\PdpStop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Antigravity — ZoneFilterService
 *
 * Moteur intelligent de filtrage des services selon le trajet utilisateur.
 *
 * Responsabilités :
 *  1. Résoudre la commune de départ  (GPS → PdpStop le plus proche, fallback Photon/Nominatim)
 *  2. Résoudre la commune de destination (même logique)
 *  3. Comparer les communes pour déterminer le mode de trajet
 *  4. Filtrer une collection de ServiceType selon les règles métier :
 *       - Même commune  → COMMUNAL + INTERCOMMUNAL + TOUTE_ZONE
 *       - Communes diff → INTERCOMMUNAL + TOUTE_ZONE seulement
 *
 * Règles zone_coverage :
 *  - COMMUNAL      : is_communal = 1
 *  - INTERCOMMUNAL : is_communal = 0, is_intercommunal = 1, is_interregional = 0
 *  - TOUTE_ZONE    : is_communal = 0, is_intercommunal = 1, is_interregional = 1
 */
class ZoneFilterService
{
    /**
     * Rayon de recherche en km autour des coordonnées pour trouver un arrêt PDP.
     */
    private const PDP_SEARCH_RADIUS_KM = 5.0;

    /**
     * TTL du cache de résolution de commune (86400 secondes = 24 heures).
     */
    private const COMMUNE_CACHE_TTL = 86400;

    /**
     * URL de l'API Photon (OpenStreetMap) pour le géocodage inversé.
     */
    private const PHOTON_URL = 'https://photon.komoot.io/reverse';

    /**
     * URL de l'API Nominatim en fallback.
     */
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse';

    // =========================================================================
    // 1. RÉSOLUTION DE COMMUNE
    // =========================================================================

    /**
     * Résout la commune d'une paire de coordonnées GPS.
     *
     * Stratégie (dans l'ordre) :
     *   a) PdpStop le plus proche dans un rayon de 5 km
     *   b) API Photon (OpenStreetMap) — géocodage inversé
     *   c) API Nominatim — fallback secondaire
     *
     * @param  float  $lat
     * @param  float  $lng
     * @return string|null  Nom de commune normalisé, ou null si introuvable
     */
    public function resolveCommune(float $lat, float $lng): ?string
    {
        // Clé de cache géohash
        $cacheKey = 'zone_commune_v2:' . round($lat, 3) . ':' . round($lng, 3);

        return Cache::remember($cacheKey, self::COMMUNE_CACHE_TTL, function () use ($lat, $lng) {

            // --- Stratégie A : PostGIS direct sur la table communes ---
            $communeObj = \App\Models\Commune::containsPoint($lat, $lng)->first();
            if ($communeObj) {
                Log::debug('[ZoneFilter] Commune résolue via PostGIS', ['lat' => $lat, 'lng' => $lng, 'commune' => $communeObj->commune]);
                return $this->normalizeCommune($communeObj->commune);
            }

            // --- Stratégie B : Photon (OpenStreetMap) ---
            $commune = $this->resolveFromPhoton($lat, $lng);
            if ($commune) {
                Log::debug('[ZoneFilter] Commune résolue via Photon', compact('lat', 'lng', 'commune'));
                return $commune;
            }

            // --- Stratégie C : Nominatim ---
            $commune = $this->resolveFromNominatim($lat, $lng);
            if ($commune) {
                Log::debug('[ZoneFilter] Commune résolue via Nominatim', compact('lat', 'lng', 'commune'));
                return $commune;
            }

            Log::warning('[ZoneFilter] Impossible de résoudre la commune', compact('lat', 'lng'));
            return null;
        });
    }

    /**
     * Résout la commune via l'API Photon (OpenStreetMap).
     * Photon renvoie city/town/village dans les propriétés de la feature.
     */
    private function resolveFromPhoton(float $lat, float $lng): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'PickMePro/1.0 (contact@picme225.com)'])
                ->get(self::PHOTON_URL, [
                    'lat'  => $lat,
                    'lon'  => $lng,
                    'lang' => 'fr',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            $props = $data['features'][0]['properties'] ?? null;

            if (!$props) {
                return null;
            }

            // Photon expose la commune dans : city, district, locality, county
            $commune = $props['city']
                    ?? $props['district']
                    ?? $props['locality']
                    ?? $props['county']
                    ?? null;

            return $commune ? $this->normalizeCommune($commune) : null;

        } catch (\Throwable $e) {
            Log::warning('[ZoneFilter] Photon API error: ' . $e->getMessage(), compact('lat', 'lng'));
            return null;
        }
    }

    /**
     * Résout la commune via l'API Nominatim (fallback secondaire).
     */
    private function resolveFromNominatim(float $lat, float $lng): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'PickMePro/1.0 (contact@picme225.com)'])
                ->get(self::NOMINATIM_URL, [
                    'lat'            => $lat,
                    'lon'            => $lng,
                    'format'         => 'json',
                    'accept-language' => 'fr',
                    'zoom'           => 14,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data    = $response->json();
            $address = $data['address'] ?? [];

            // Nominatim expose la commune dans : city, town, village, suburb
            $commune = $address['city']
                    ?? $address['town']
                    ?? $address['village']
                    ?? $address['suburb']
                    ?? $address['county']
                    ?? null;

            return $commune ? $this->normalizeCommune($commune) : null;

        } catch (\Throwable $e) {
            Log::warning('[ZoneFilter] Nominatim API error: ' . $e->getMessage(), compact('lat', 'lng'));
            return null;
        }
    }

    /**
     * Normalise un nom de commune : supprime les accents, met en majuscules,
     * retire les préfixes courants (Commune de, Ville de...).
     */
    private function normalizeCommune(string $commune): string
    {
        $c = trim($commune);
        // Supprime préfixes administratifs
        $c = preg_replace('/^(commune\s+de\s+|ville\s+de\s+|sous-préfecture\s+de\s+)/i', '', $c);
        // Normalise la casse (première lettre en majuscule par mot)
        return mb_convert_case(trim($c), MB_CASE_TITLE, 'UTF-8');
    }

    // =========================================================================
    // 2. CONTEXTE DE TRAJET
    // =========================================================================

    /**
     * Construit le contexte géographique complet d'un trajet.
     *
     * @param  float|null  $startLat
     * @param  float|null  $startLng
     * @param  float|null  $endLat
     * @param  float|null  $endLng
     * @return array{
     *   start_commune: string|null,
     *   end_commune: string|null,
     *   is_same_commune: bool,
     *   trip_mode: 'same_commune'|'different_communes'|'unknown',
     *   can_filter: bool
     * }
     */
    public function buildTripContext(
        ?float $startLat,
        ?float $startLng,
        ?float $endLat,
        ?float $endLng
    ): array {
        if (!$startLat || !$startLng || !$endLat || !$endLng) {
            return [
                'start_commune'  => null,
                'end_commune'    => null,
                'is_same_commune' => false,
                'trip_mode'      => 'unknown',
                'can_filter'     => false,
            ];
        }

        $startCommune = $this->resolveCommune($startLat, $startLng);
        $endCommune   = $this->resolveCommune($endLat, $endLng);

        if (!$startCommune || !$endCommune) {
            return [
                'start_commune'  => $startCommune,
                'end_commune'    => $endCommune,
                'is_same_commune' => false,
                'trip_mode'      => 'unknown',
                'can_filter'     => false,
            ];
        }

        $isSame = $this->communesMatch($startCommune, $endCommune);

        return [
            'start_commune'  => $startCommune,
            'end_commune'    => $endCommune,
            'is_same_commune' => $isSame,
            'trip_mode'      => $isSame ? 'same_commune' : 'different_communes',
            'can_filter'     => true,
        ];
    }

    /**
     * Compare deux noms de commune de façon tolérante aux variantes orthographiques.
     */
    private function communesMatch(string $a, string $b): bool
    {
        return $this->slugify($a) === $this->slugify($b);
    }

    /**
     * Slugifie un nom de commune pour la comparaison (supprime accents, casse, ponctuation).
     */
    private function slugify(string $str): string
    {
        // Translitérer les caractères accentués
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str) ?: $str;
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return $str;
    }

    // =========================================================================
    // 3. FILTRAGE DES SERVICES
    // =========================================================================

    /**
     * Filtre une collection de ServiceType selon le mode de trajet.
     *
     * Règles métier :
     *  - same_commune    → COMMUNAL + INTERCOMMUNAL + TOUTE_ZONE
     *  - different_communes → INTERCOMMUNAL + TOUTE_ZONE seulement (masquer COMMUNAL)
     *  - unknown         → tout afficher (pas assez d'info pour filtrer)
     *
     * IMPORTANT : Location et Urgence sont toujours TOUTE_ZONE → jamais filtrés.
     *
     * @param  Collection  $serviceTypes  Collection de ServiceType
     * @param  string      $tripMode      'same_commune' | 'different_communes' | 'unknown'
     * @return Collection
     */
    public function filterServiceTypes(Collection $serviceTypes, string $tripMode): Collection
    {
        if ($tripMode === 'unknown') {
            // Pas assez d'information → tout afficher par sécurité
            return $serviceTypes;
        }

        if ($tripMode === 'same_commune') {
            // Même commune → tout afficher
            return $serviceTypes;
        }

        // Communes différentes → masquer les services COMMUNAL uniquement
        return $serviceTypes->filter(function ($st) {
            $coverage = $st->zone_coverage ?? $this->inferZoneCoverage($st);
            return in_array($coverage, ['INTERCOMMUNAL', 'TOUTE_ZONE']);
        })->values();
    }

    /**
     * Infère la zone_coverage depuis les champs booléens si la colonne n'existe pas encore
     * (compatibilité ascendante avant migration).
     */
    public function inferZoneCoverage($serviceType): string
    {
        if ($serviceType->is_communal) {
            return 'COMMUNAL';
        }

        if ($serviceType->is_intercommunal && $serviceType->is_interregional) {
            return 'TOUTE_ZONE';
        }

        if ($serviceType->is_intercommunal) {
            return 'INTERCOMMUNAL';
        }

        // Par défaut, un service sans marqueur est considéré COMMUNAL
        return 'COMMUNAL';
    }

    // =========================================================================
    // 4. UTILITAIRES
    // =========================================================================

    /**
     * Invalide le cache de résolution de commune pour des coordonnées données.
     * Utile quand un arrêt PDP est mis à jour.
     */
    public function invalidateCommuneCache(float $lat, float $lng): void
    {
        $cacheKey = 'zone_commune:' . round($lat, 2) . ':' . round($lng, 2);
        Cache::forget($cacheKey);
    }

    /**
     * Construit une réponse "aucun service disponible" structurée.
     */
    public function buildEmptyResponse(array $tripContext): array
    {
        return [
            'status'       => true,
            'trip_context' => $tripContext,
            'services'     => [],
            'message'      => 'Aucun service disponible pour ce trajet.',
        ];
    }
}

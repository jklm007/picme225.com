<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : Masquage des informations sensibles
 *
 * Pour les courses non-payées, masque :
 * - Le numéro de téléphone du conducteur et des autres passagers
 * - Les coordonnées GPS précises (remplacées par une approximation à 2km)
 *
 * Ce middleware protège contre le contournement de la commission.
 */
class MaskSensitiveInfoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // On ne travaille que sur les réponses JSON
        if (!$response->headers->contains('Content-Type', 'application/json')) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);
        if (!$data) {
            return $response;
        }

        // Masquer selon le contexte de la réponse
        $data = $this->maskSensitiveData($data, $request);

        $response->setContent(json_encode($data));
        return $response;
    }

    /**
     * Parcourt la réponse et masque les données sensibles si non payé.
     */
    private function maskSensitiveData(array $data, Request $request): array
    {
        // Vérifier si la course est payée en vérifiant le flag dans la réponse
        $isPaid = $data['paid'] ?? $data['payment'] ['paid'] ?? false;

        if ($isPaid) {
            return $data; // Aucun masquage si payé
        }

        // Masquer le numéro de téléphone dans toutes les sous-clés
        array_walk_recursive($data, function (&$value, $key) {
            if (in_array($key, ['mobile', 'phone', 'telephone', 'contact'])) {
                $value = $this->maskPhone($value);
            }
            if (in_array($key, ['s_latitude', 'd_latitude', 'latitude', 'track_latitude'])) {
                $value = $this->fuzzyCoordinate((float) $value);
            }
            if (in_array($key, ['s_longitude', 'd_longitude', 'longitude', 'track_longitude'])) {
                $value = $this->fuzzyCoordinate((float) $value);
            }
        });

        // Ajouter le flag explicite pour le front
        $data['can_access_full_info'] = false;
        $data['_info_masked'] = 'Réservez et payez pour voir les coordonnées exactes.';

        return $data;
    }

    /** Masque un numéro de téléphone, ex: "07 12 34 56" → "07 *** ** 56" */
    private function maskPhone(?string $phone): string
    {
        if (empty($phone)) return '***';
        return substr($phone, 0, 3) . str_repeat('*', max(0, strlen($phone) - 5)) . substr($phone, -2);
    }

    /**
     * "Flous" une coordonnée GPS pour une précision d'environ 2km.
     * Evite la localisation exacte d'un conducteur ou passager non payé.
     */
    private function fuzzyCoordinate(float $coord): float
    {
        // Décalage aléatoire d'environ 0.02 degrés ≈ 2km
        return round($coord + (rand(-200, 200) / 10000), 4);
    }
}

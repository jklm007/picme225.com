<?php

namespace App\Services\DispatchEngine;

use App\Models\Provider;
use Illuminate\Support\Facades\Log;

/**
 * DriverLearningService - IA Adaptative (Apprentissage des chauffeurs)
 *
 * Rôle : Analyser les actions des chauffeurs (acceptation, refus, timeout)
 * et mettre à jour leur profil en temps réel pour adapter le dispatch.
 */
class DriverLearningService
{
    /**
     * Met à jour le taux d'acceptation lorsqu'un chauffeur accepte une course.
     * Appelé depuis TripController@accept.
     *
     * @param int $providerId
     */
    public function recordAcceptance(int $providerId): void
    {
        $this->_updateStats($providerId, true);
    }

    /**
     * Met à jour le taux d'acceptation lorsqu'un chauffeur refuse ou ignore une course.
     * Appelé depuis TripController@destroy (ou timeout).
     *
     * @param int $providerId
     */
    public function recordRejection(int $providerId): void
    {
        $this->_updateStats($providerId, false);
    }

    /**
     * Met à jour les compteurs et calcule le nouveau taux d'acceptation.
     *
     * @param int  $providerId
     * @param bool $accepted
     */
    private function _updateStats(int $providerId, bool $accepted): void
    {
        try {
            $provider = Provider::find($providerId);
            if (!$provider) return;

            $provider->total_offered_trips += 1;
            
            if ($accepted) {
                $provider->total_accepted_trips += 1;
            }

            // Calcul du taux d'acceptation (en pourcentage)
            if ($provider->total_offered_trips > 0) {
                $rate = ($provider->total_accepted_trips / $provider->total_offered_trips) * 100;
                $provider->acceptance_rate = (int) round($rate);
            }

            $provider->save();

            Log::info("[DriverLearning] Chauffeur #{$providerId} - Action: " . ($accepted ? 'ACCEPTE' : 'REFUSE') . " - Taux: {$provider->acceptance_rate}%");

        } catch (\Exception $e) {
            Log::error("[DriverLearning] Erreur lors de la mise à jour des stats: " . $e->getMessage());
        }
    }
}

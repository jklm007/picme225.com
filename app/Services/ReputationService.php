<?php

namespace App\Services;

use App\Models\User;
use App\Models\Provider;

class ReputationService
{
    /** Points constants (Revised for Scarcity & Quality) */
    public const POINTS_ROAD_INFO_MILESTONE = 0.50;    // +0.5 si 10 likes sur info trafic
    public const POINTS_TRIP_COMPLETION = 1.00;        // Succès d'un trajet (+1.0)
    public const POINTS_SALE_COMPLETION = 1.00;        // Succès d'une vente marketplace (+1.0)
    public const POINTS_TICKET_PURCHASE = 1.00;        // Achat de billet événementiel
    public const POINTS_REVIEW_POSITIVE = 0.50;        // Bon avis reçu
    public const POINTS_LIKE_SUCCESS_BASED = 0.05;     // Like sur post succès
    public const POINTS_HANDSHAKE = 0.50;              // Validation handshake passager
    
    public const POINTS_ROAD_INFO_CONTRIBUTION = 0.00; // Pas de points immédiat
    public const POINTS_VOTE = 0.00; 
    public const POINTS_COMMENT = 0.00; 
    public const POINTS_LIKE_RECEIVED = 0.00;

    public static function awardPoints($model, float $points)
    {
        $model->increment('social_points', $points);
        self::updateBadge($model);
    }

    public static function awardTrustScore($model, int $points)
    {
        // trust_score capped between 0 and 100
        $newScore = max(0, min(100, $model->trust_score + $points));
        $model->update(['trust_score' => $newScore]);
    }

    public static function updateBadge($model)
    {
        $points = $model->social_points;
        $badge = 'Nouveau';

        if ($points >= 1000) $badge = '👑 Légende';
        elseif ($points >= 500) $badge = '💎 Ambassadeur Elite';
        elseif ($points >= 200) $badge = '⭐ Membre Influent';
        elseif ($points >= 50) $badge = '✅ Membre Actif';

        $model->update(['user_badge' => $badge]);
    }
}

<?php

namespace App\Services;

use App\Models\RideBooking;
use App\Models\UserRequests;
use Illuminate\Support\Facades\DB;

/**
 * Service de calcul et sécurisation de la commission Picme.
 *
 * Logique :
 * - Commission Picme : 15% du montant total
 * - Part Chauffeur : 85% du montant total
 * - Les fonds sont maintenus en Escrow jusqu'à la fin de la course.
 */
class CommissionService
{
    /** Taux de commission Picme (configurable via settings) */
    private float $commissionRate;

    public function __construct()
    {
        // Lire depuis les settings ou utiliser 15% par défaut
        $this->commissionRate = (float) setting('commission_percentage', 15) / 100;
    }

    /**
     * Calcule la répartition de la commission pour un montant donné.
     *
     * @return array
     */
    public function calculate(float $totalAmount): array
    {
        $beta_mode = (bool) \Setting::get('beta_mode', '0');
        $commission_enabled = (bool) \Setting::get('commission_enabled', '1');

        $simulatedCommission   = round($totalAmount * $this->commissionRate, 2);
        $simulatedDriverAmount = round($totalAmount - $simulatedCommission, 2);

        if ($beta_mode && !$commission_enabled) {
            $commission = 0.00;
            $driverAmount = $totalAmount;
        } else {
            $commission = $simulatedCommission;
            $driverAmount = $simulatedDriverAmount;
        }

        return [
            'total'                     => $totalAmount,
            'commission'                => $commission,
            'driver_amount'             => $driverAmount,
            'rate'                      => $this->commissionRate,
            'simulated_commission'      => $simulatedCommission,
            'simulated_driver_amount'   => $simulatedDriverAmount,
        ];
    }

    /**
     * Applique la commission sur un RideBooking et marque les fonds en Escrow.
     * Appelé au moment de la réservation via le réseau social.
     */
    public function applyToBooking(RideBooking $booking): RideBooking
    {
        $breakdown = $this->calculate((float) $booking->price);

        $booking->update([
            'commission_amount' => $breakdown['commission'],
            'driver_amount'     => $breakdown['driver_amount'],
            'escrow_status'     => 'HELD',
        ]);

        $beta_mode = (bool) \Setting::get('beta_mode', '0');
        if ($beta_mode) {
            \DB::table('simulated_commission_logs')->insert([
                'ride_booking_id' => $booking->id,
                'total_amount' => $booking->price,
                'simulated_commission' => $breakdown['simulated_commission'],
                'simulated_provider_pay' => $breakdown['simulated_driver_amount'],
                'created_at' => now()
            ]);
        }

        return $booking->fresh();
    }

    /**
     * Libère les fonds de l'Escrow vers le chauffeur en fin de course.
     * Appelé après confirmation du QR Handshake de fin de trajet.
     */
    public function releaseEscrow(RideBooking $booking): bool
    {
        if ($booking->escrow_status !== 'HELD') {
            return false;
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['escrow_status' => 'RELEASED', 'paid' => true]);

            // Créditer le chauffeur en ECO
            $provider = $booking->activeRide?->provider;
            if ($provider) {
                $ecoAmount = $booking->driver_amount / 1000.0;
                $provider->increment('eco_wallet_balance', $ecoAmount);

                // Enregistrer dans ProviderWallet (historique CFA)
                \App\Models\ProviderWallet::create([
                    'provider_id' => $provider->id,
                    'amount' => $booking->driver_amount,
                    'transaction_id' => 'RIDE_' . $booking->id,
                    'transaction_desc' => 'Gain pour la course #' . $booking->booking_id,
                    'type' => 'CREDIT',
                    'balance' => $provider->eco_wallet_balance,
                ]);
                
                // Récompense Reputation & Confiance
                \App\Services\ReputationService::awardPoints($provider, \App\Services\ReputationService::POINTS_COMPLETION);
                \App\Services\ReputationService::awardTrustScore($provider, \App\Services\ReputationService::TRUST_SUCCESSFUL_TRIP);
            }

            // Récompense Passager
            if ($booking->user) {
                \App\Services\ReputationService::awardPoints($booking->user, \App\Services\ReputationService::POINTS_COMPLETION);
                \App\Services\ReputationService::awardTrustScore($booking->user, \App\Services\ReputationService::TRUST_SUCCESSFUL_TRIP);
            }
        });

        return true;
    }

    /**
     * Rembourse un passager si la course est annulée avant départ.
     */
    public function refundEscrow(RideBooking $booking): bool
    {
        if ($booking->escrow_status !== 'HELD') {
            return false;
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['escrow_status' => 'REFUNDED', 'status' => 'CANCELLED']);

            // Rembourser le passager sur son wallet
            $booking->user()->increment('wallet_balance', $booking->price);
        });

        return true;
    }
}

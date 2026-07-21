<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MarketplaceListing;
use App\Models\RentalBooking;
use App\Models\User;
use App\Services\CommissionService;

/**
 * Contrôleur de la Location de Véhicules sans Chauffeur.
 *
 * Gère :
 * - La liste des véhicules disponibles à la location (avec matching par position)
 * - La réservation avec caution Escrow
 * - La logistique de livraison (à domicile ou retrait sur place)
 * - L'état des lieux numérique (QR code + photos)
 */
class VehicleRentalController extends Controller
{
    // =========================================================================
    // SECTION 1 : LISTING DES VEHICULES
    // =========================================================================

    /**
     * Liste les véhicules disponibles à la location autour d'une position GPS.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'type'      => 'nullable|in:RENTAL,SALE',
            'city'      => 'nullable|string',
        ]);

        $query = \App\Models\MarketplaceListing::with('user:id,first_name,last_name,picture')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at');

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->city) {
            $query->where('location_city', 'LIKE', '%' . $request->city . '%');
        }

        $listings = $query->latest()->paginate(15);

        return response()->json($listings);
    }

    // =========================================================================
    // SECTION 2 : RESERVATION + CAUTION ESCROW
    // =========================================================================

    /**
     * Réserver un véhicule avec paiement de la caution en Escrow.
     *
     * La caution et le prix de location sont bloqués sur le wallet.
     * Le propriétaire ne reçoit sa part (85%) qu'à la fin de la location.
     * La caution est libérée si aucun dommage n'est signalé.
     */
    public function book(Request $request, int $listingId): JsonResponse
    {
        $request->validate([
            'start_at'         => 'required|date|after:now',
            'end_at'           => 'required|date|after:start_at',
            'pickup_type'      => 'required|in:SELF,HOME_DELIVERY',
            'delivery_address' => 'required_if:pickup_type,HOME_DELIVERY|string|nullable',
            'delivery_latitude'=> 'required_if:pickup_type,HOME_DELIVERY|numeric|nullable',
            'delivery_longitude' => 'required_if:pickup_type,HOME_DELIVERY|numeric|nullable',
        ]);

        $listing = \App\Models\MarketplaceListing::where('status', 'ACTIVE')->findOrFail($listingId);

        // Calculer la durée et le prix total
        $start = \Carbon\Carbon::parse($request->start_at);
        $end   = \Carbon\Carbon::parse($request->end_at);
        
        $durationMultiplier = 1;
        if ($listing->price_unit === 'month') {
            $durationMultiplier = max(1, $start->diffInMonths($end));
        } else {
            $durationMultiplier = max(1, $start->diffInDays($end));
        }

        $rentalPrice  = $listing->price * $durationMultiplier;
        $deliveryFee  = ($request->pickup_type === 'HOME_DELIVERY') ? $listing->delivery_price : 0;
        $totalPrice   = $rentalPrice + $deliveryFee;
        $depositAmount = $listing->deposit_amount;

        // Calcul du séquestre de 10% sur le montant total
        $escrowAmount = $totalPrice * 0.10;

        // Vérifier le solde du wallet (doit couvrir 10% du total estimé)
        $user = Auth::user();
        if ($user->wallet_balance < $escrowAmount) {
            return response()->json([
                'error' => 'Solde insuffisant. Vous devez avoir au minimum ' . $escrowAmount . ' FCFA (10% du total) pour soumettre la demande de réservation.',
            ], 422);
        }

        // Calculer la commission (15%)
        $commission = app(CommissionService::class);
        $breakdown  = $commission->calculate($rentalPrice);

        $booking = DB::transaction(function () use (
            $request, $listing, $start, $end, $totalPrice, $depositAmount,
            $deliveryFee, $breakdown, $user
        ) {
            // NOTE: On ne prélève pas encore l'argent, on crée juste la réservation en attente

            $booking = \App\Models\RentalBooking::create([
                'listing_id'        => $listing->id,
                'user_id'           => $user->id,
                'start_at'          => $start,
                'end_at'            => $end,
                'total_price'       => $totalPrice,
                'deposit_amount'    => $depositAmount, // L'acompte physique n'est pas prélevé ici
                'delivery_price'    => $deliveryFee,
                'commission_amount' => $breakdown['commission'],
                'owner_amount'      => $breakdown['driver_amount'],
                'pickup_type'       => $request->pickup_type,
                'delivery_address'  => $request->delivery_address,
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude'=> $request->delivery_longitude,
                'status'            => 'PENDING_VENDOR', // Statut "En attente d'acceptation"
                'escrow_status'     => 'PENDING',
                'deposit_status'    => 'PENDING',
                'qr_code'           => \Illuminate\Support\Str::uuid(),
            ]);

            // Envoi de la notification Push au vendeur
            try {
                $owner = $listing->user;
                if ($owner && $owner->fcm_token) {
                    \App\Services\FCMService::sendPushNotification(
                        $owner->fcm_token,
                        'Nouvelle demande de réservation',
                        $user->first_name . ' souhaite louer "' . $listing->title . '". Vérifiez vos messages pour accepter ou refuser.',
                        ['type' => 'RESERVATION_REQUEST', 'booking_id' => $booking->id]
                    );
                    \App\Models\AppNotification::create([
                        'user_id' => $owner->id,
                        'title' => 'Nouvelle demande de réservation',
                        'message' => $user->first_name . ' souhaite louer "' . $listing->title . '".',
                        'type' => 'RESERVATION_REQUEST',
                        'action_id' => (string) $booking->id
                    ]);
                }
            } catch (\Exception $e) {
                // Log and continue
                \Illuminate\Support\Facades\Log::error("Erreur push notification réservation: " . $e->getMessage());
            }

            return $booking;
        });

        return response()->json([
            'success'         => true,
            'booking'         => $booking,
            'message'         => 'Demande de réservation envoyée. Le vendeur a été notifié. ' . $escrowAmount . ' FCFA seront bloqués s\'il accepte.',
        ], 201);
    }

    /**
     * Le vendeur accepte la demande de réservation.
     */
    public function acceptBooking(Request $request, int $bookingId): JsonResponse
    {
        $booking = \App\Models\RentalBooking::with('listing')->findOrFail($bookingId);
        $listing = $booking->listing;

        // Sécurité : seul le propriétaire de l'annonce peut accepter
        if ($listing->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        if ($booking->status !== 'PENDING_VENDOR') {
            return response()->json(['error' => 'Cette réservation n\'est pas en attente d\'acceptation.'], 400);
        }

        $renter = \App\Models\User::findOrFail($booking->user_id);
        $escrowAmount = $booking->total_price * 0.10;

        // Revérification du solde du client avant de prélever
        if ($renter->wallet_balance < $escrowAmount) {
            $booking->update(['status' => 'CANCELLED']); // Annulé car solde insuffisant
            return response()->json([
                'error' => 'Le client n\'a plus les fonds nécessaires (10%). La réservation a été annulée.',
            ], 422);
        }

        DB::transaction(function () use ($booking, $listing, $renter, $escrowAmount) {
            // Prélèvement des 10% sur le wallet du client
            $renter->decrement('wallet_balance', $escrowAmount);

            // Mise à jour de la réservation
            $booking->update([
                'status' => 'CONFIRMED',
                'escrow_status' => 'HELD'
            ]);

            // Mise à jour du listing si nécessaire
            $listing->update(['status' => 'RESERVED']);

            // Notification au client
            try {
                if ($renter->fcm_token) {
                    \App\Services\FCMService::sendPushNotification(
                        $renter->fcm_token,
                        'Réservation acceptée !',
                        'Votre réservation pour "' . $listing->title . '" a été acceptée. Les 10% ont été sécurisés.',
                        ['type' => 'RESERVATION_ACCEPTED', 'booking_id' => $booking->id]
                    );
                }
            } catch (\Exception $e) {}
        });

        return response()->json([
            'success' => true,
            'message' => 'Réservation acceptée. Les fonds (10%) ont été sécurisés.',
        ]);
    }

    /**
     * Le vendeur refuse la demande de réservation.
     */
    public function rejectBooking(Request $request, int $bookingId): JsonResponse
    {
        $booking = \App\Models\RentalBooking::with('listing')->findOrFail($bookingId);
        $listing = $booking->listing;

        if ($listing->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        if ($booking->status !== 'PENDING_VENDOR') {
            return response()->json(['error' => 'Cette réservation n\'est pas en attente.'], 400);
        }

        $booking->update(['status' => 'REJECTED']);

        // Notification au client
        try {
            $renter = \App\Models\User::find($booking->user_id);
            if ($renter && $renter->fcm_token) {
                \App\Services\FCMService::sendPushNotification(
                    $renter->fcm_token,
                    'Réservation refusée',
                    'Votre réservation pour "' . $listing->title . '" a été déclinée par le propriétaire.',
                    ['type' => 'RESERVATION_REJECTED', 'booking_id' => $booking->id]
                );
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Réservation refusée.',
        ]);
    }

    // =========================================================================
    // SECTION 3 : ÉTAT DES LIEUX & FIN DE LOCATION
    // =========================================================================

    /**
     * Finalise la location et libère les fonds Escrow.
     * Si aucun dommage, la caution est remboursée.
     */
    public function complete(Request $request, int $bookingId): JsonResponse
    {
        $request->validate([
            'vehicle_condition_end' => 'nullable|string', // URL photo
            'has_damage'            => 'required|boolean',
        ]);

        $booking = \App\Models\RentalBooking::where('user_id', Auth::id())->findOrFail($bookingId);

        if ($booking->status !== 'ACTIVE' && $booking->status !== 'CONFIRMED') {
            return response()->json(['error' => 'Cette location n\'est pas en cours.'], 422);
        }

        DB::transaction(function () use ($request, $booking) {
            $booking->update([
                'status'                => 'COMPLETED',
                'vehicle_condition_end' => $request->vehicle_condition_end,
                'escrow_status'         => 'RELEASED',
                'deposit_status'        => $request->has_damage ? 'DEDUCTED' : 'RELEASED',
            ]);

            // Libérer les fonds escrow vers le propriétaire (85%)
            $owner = \App\Models\MarketplaceListing::find($booking->listing_id)?->user;
            $owner?->increment('wallet_balance', $booking->owner_amount);

            // Rembourser la caution si pas de dommage
            if (!$request->has_damage) {
                $renter = \App\Models\User::find($booking->user_id);
                $renter?->increment('wallet_balance', $booking->deposit_amount);
            }

            // Libérer le véhicule dans le listing
            \App\Models\MarketplaceListing::where('id', $booking->listing_id)->update(['status' => 'ACTIVE']);
        });

        return response()->json([
            'success'          => true,
            'caution_returned' => !$request->has_damage,
            'message'          => !$request->has_damage
                ? 'Location terminée. Caution remboursée sur votre wallet.'
                : 'Location terminée. Un signalement de dommage a été enregistré.',
        ]);
    }
}

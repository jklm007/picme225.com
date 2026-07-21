<?php

namespace App\Services;

use App\Models\MarketplaceListing;
use App\Models\EventPassType;
use App\Models\TransportTicket;
use App\Models\WalletPassbook;
use App\Models\UserRequests;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\SendPushNotification;

class TicketPurchaseService
{
    /**
     * Process the purchase of a ticket or marketplace item.
     * Extracts logic from MarketplaceBookingController@buy.
     */
    public function purchase($listingId, $buyer, $paymentMode, $passId = null, $userLat = 5.3484, $userLng = -4.0244)
    {
        $listing = MarketplaceListing::where('status', 'ACTIVE')->findOrFail($listingId);

        if ($listing->user_id === $buyer->id) {
            throw new \Exception('Vous ne pouvez pas acheter votre propre article.');
        }

        if ($paymentMode === 'WALLET' && $buyer->wallet_balance < $listing->price) {
            throw new \Exception('Solde insuffisant dans votre portefeuille.');
        }

        $result = DB::transaction(function () use ($listing, $buyer, $paymentMode, $passId, $userLat, $userLng) {
            // --- CAS BILLETTERIE (TICKETS) ---
            if ($listing->category === 'TICKETS') {
                $pass = null;
                if ($passId && $passId != -1) {
                    $pass = EventPassType::where('id', $passId)
                        ->where('listing_id', $listing->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($pass->quantity - $pass->sold_count <= 0) {
                        throw new \Exception("Désolé, ce pass est épuisé.");
                    }

                    $priceToPay = $pass->price;
                    $personsPerPass = $pass->persons_per_pass;
                    $passIdValue = $pass->id;
                } else {
                    $priceToPay = $listing->price;
                    $personsPerPass = 1;
                    $passIdValue = 0;
                }

                // Déduction si Wallet
                if ($paymentMode === 'WALLET') {
                    $buyer->decrement('wallet_balance', $priceToPay);
                    WalletPassbook::create(['user_id' => $buyer->id, 'amount' => -$priceToPay, 'status' => 'DEBITED', 'via' => 'TICKET_PURCHASE']);
                    
                    // Crédit Vendeur (Automatique car argent virtuel)
                    $commission = $priceToPay * 0.15; 
                    $sellerCredit = $priceToPay - $commission;
                    $listing->user->increment('wallet_balance', $sellerCredit);
                    WalletPassbook::create(['user_id' => $listing->user_id, 'amount' => $sellerCredit, 'status' => 'CREDITED', 'via' => 'TICKET_SALE']);
                } else {
                    // Si CASH : L'argent est physique. Le système enregistre une commission due par l'organisateur.
                    $commission = $priceToPay * 0.15;
                    if ($listing->user->wallet_balance >= $commission) {
                        $listing->user->decrement('wallet_balance', $commission);
                    } else {
                        // Sinon on crée une dette (wallet négatif)
                        $listing->user->decrement('wallet_balance', $commission);
                    }
                    WalletPassbook::create(['user_id' => $listing->user_id, 'amount' => -$commission, 'status' => 'DEBITED', 'via' => 'TICKET_COMMISSION_CASH']);
                }

                // Génération QR Code
                $ticketId = Str::random(12);
                $signature = substr(hash_hmac('sha256', $ticketId . $buyer->id, config('app.key')), 0, 8);
                $qrCode = "PKM-{$ticketId}-{$signature}";

                $ticket = TransportTicket::create([
                    'listing_id'         => $listing->id,
                    'event_pass_type_id' => $passIdValue,
                    'transport_event_id' => 0, 
                    'user_id'            => $buyer->id,
                    'qr_code'            => $qrCode,
                    'total_price'        => $priceToPay,
                    'payment_mode'       => $paymentMode,
                    'payment_status'     => in_array($paymentMode, ['WALLET', 'ADMIN_CASH']) ? 'PAID' : 'PENDING_CASH',
                    'status'             => 'BOOKED',
                    'metadata'           => json_encode([
                        'persons_per_pass' => $personsPerPass,
                    ])
                ]);

                $ticket->share_url = route('ticket.view', ['booking_id' => $qrCode]);

                if ($pass) {
                    $pass->increment('sold_count');
                }

                // Notifications Push
                try {
                    $notifier = new SendPushNotification();
                    $buyerMsg = "Achat réussi ! Retrouvez votre ticket pour '{$listing->title}' dans 'Mes Billets'. 🎫";
                    $notifier->MarketplacePaymentConfirmed($buyer->id, $buyerMsg);
                    
                    $sellerMsg = ($paymentMode === 'WALLET') 
                        ? "Nouvelle vente : " . number_format($priceToPay, 0) . " FCFA ajoutés (net) à votre solde. 💰"
                        : "Nouvelle réservation Cash pour '{$listing->title}'. Encaissez le client à son arrivée. 💵";
                    $notifier->MarketplaceOrderReceived($listing->user_id, $sellerMsg);
                } catch (\Exception $e) {
                    \Log::error("Erreur notification achat : " . $e->getMessage());
                }

                return $ticket;
            } else {
                // --- CAS STANDARD (ARTICLE/VEHICLE) ---
                if ($paymentMode === 'WALLET') {
                    $buyer->decrement('wallet_balance', $listing->price);
                    WalletPassbook::create(['user_id' => $buyer->id, 'amount'  => -$listing->price, 'status'  => 'DEBITED', 'via'     => 'MARKETPLACE_PURCHASE']);

                    $seller = $listing->user;
                    $commissionPercent = ($seller->user_badge === 'VIP') ? 0 : (($seller->user_badge === 'PREMIUM') ? 5 : 10);
                    $commissionAmount = ($listing->price * $commissionPercent) / 100;
                    $sellerCredit     = $listing->price - $commissionAmount;
                    $seller->increment('wallet_balance', $sellerCredit);
                    WalletPassbook::create(['user_id' => $seller->id, 'amount'  => $sellerCredit, 'status'  => 'CREDITED', 'via'     => 'MARKETPLACE_SALE']);
                }

                // Demande de livraison Picme
                $deliveryRequest = new UserRequests();
                $deliveryRequest->booking_id = Helper::generate_booking_id();
                $deliveryRequest->user_id    = $buyer->id;
                $deliveryRequest->service_type_id = 1;
                $deliveryRequest->status          = 'SEARCHING';
                $deliveryRequest->s_latitude      = $listing->location_latitude;
                $deliveryRequest->s_longitude     = $listing->location_longitude;
                $deliveryRequest->s_address       = "Vendeur Marketplace";
                $deliveryRequest->d_latitude      = $userLat;
                $deliveryRequest->d_longitude     = $userLng;
                $deliveryRequest->payment_mode    = $paymentMode;
                $deliveryRequest->method          = 'delivery';
                $deliveryRequest->save();

                $listing->decrement('stock_quantity');
                if ($listing->stock_quantity <= 1) {
                    $listing->update(['status' => 'SOLD']);
                }

                // --- CRÉATION DU TICKET DE SUIVI (REÇU) ---
                $ticketId = Str::random(12);
                $signature = substr(hash_hmac('sha256', $ticketId . $buyer->id, config('app.key')), 0, 8);
                $qrCode = "PKM-{$ticketId}-{$signature}";

                $ticket = TransportTicket::create([
                    'listing_id'         => $listing->id,
                    'event_pass_type_id' => 0,
                    'transport_event_id' => 0,
                    'user_id'            => $buyer->id,
                    'qr_code'            => $qrCode,
                    'total_price'        => $listing->price,
                    'payment_mode'       => $paymentMode,
                    'payment_status'     => ($paymentMode === 'WALLET') ? 'PAID' : 'PENDING_CASH',
                    'status'             => 'SOLD'
                ]);

                return $ticket;
            }
        });

        // Déclencher le Job WhatsApp si c'est un ticket payé
        if ($result && $result->payment_status !== 'PENDING_CASH' && $listing->category === 'TICKETS') {
            try {
                if (class_exists(\App\Jobs\SendWhatsAppTicketJob::class)) {
                    dispatch(new \App\Jobs\SendWhatsAppTicketJob($result, $buyer));
                }
            } catch (\Exception $e) {
                \Log::error("Erreur envoi WhatsApp : " . $e->getMessage());
            }
        }

        return $result;
    }
}

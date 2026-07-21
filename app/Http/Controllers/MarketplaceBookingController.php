<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MarketplaceListing;
use App\Models\RentalBooking;
use App\Models\UserRequests;
use App\Models\EventPassType;
use App\Models\TransportTicket;
use App\Models\WalletPassbook;
use App\Helpers\Helper;
use App\Models\ServiceType;
use App\Models\Post;
use App\Models\MarketplaceAgent;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MarketplaceBookingController extends Controller
{
        public function buy(Request $request, $id): JsonResponse
        {
            try {
                $paymentMode = $request->input('payment_mode', 'WALLET');
                $passId = $request->input('pass_type_id');
                $userLat = $request->input('user_lat', 5.3484);
                $userLng = $request->input('user_lng', -4.0244);
                
                $service = new \App\Services\TicketPurchaseService();
                $service->purchase($id, Auth::user(), $paymentMode, $passId, $userLat, $userLng);
    
                return response()->json([
                    'success' => true,
                    'message' => 'Opération validée !',
                ]);
    
            } catch (\Exception $e) {
                \Log::error("Erreur achat marketplace : " . $e->getMessage());
                return response()->json(['error' => 'Erreur technique lors de l\'achat : ' . $e->getMessage()], 500);
            }
        }

        public function rent(Request $request, $id): JsonResponse
        {
            $listing = MarketplaceListing::findOrFail($id);
            
            $user = Auth::user();
    
            // 🛡️ SÉCURITÉ : Vérification KYC pour location sans chauffeur
            if ($listing->type === 'VEHICLE' && !$listing->with_driver) {
                if ($user->kyc_status !== 'APPROVED') {
                    return response()->json([
                        'error' => 'Vérification d\'identité requise.',
                        'message' => 'Pour louer un véhicule sans chauffeur, vous devez faire vérifier votre identité dans votre profil.',
                        'action' => 'KYC_REQUIRED'
                    ], 403);
                }
            }
    
            $request->validate([
                'start_date' => 'required|date|after:today',
                'end_date'   => 'required|date|after:start_date',
            ]);
    
            $booking = RentalBooking::create([
                'listing_id' => $listing->id,
                'user_id'    => Auth::id(),
                'start_at'   => $request->start_date,
                'end_at'     => $request->end_date,
                'status'     => 'PENDING',
                'total_price' => $listing->price * (Carbon::parse($request->end_date)->diffInDays($request->start_date) ?: 1),
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Demande de location envoyée ! En attente de validation par le propriétaire.',
                'data'    => $booking
            ], 201);
        }

        public function my_bookings(): JsonResponse
        {
            $bookings = RentalBooking::with('listing.user')
                ->where('user_id', \Auth::id())
                ->latest()
                ->get();
            return response()->json([
                'success' => true,
                'data'    => $bookings
            ]);
        }

        public function received_bookings(): JsonResponse
        {
            // On récupère toutes les réservations liées aux annonces de l'utilisateur connecté
            $bookings = RentalBooking::with(['listing', 'user'])
                ->whereHas('listing', function($q) {
                    $q->where('user_id', \Auth::id());
                })
                ->latest()
                ->get();
    
            return response()->json([
                'success' => true,
                'data'    => $bookings
            ]);
        }

        public function update_booking_status(Request $request, $id): JsonResponse
        {
            // On cherche d'abord dans RentalBooking, puis dans TransportTicket
            $booking = \App\Models\RentalBooking::find($id);
            if (!$booking) {
                $booking = \App\Models\TransportTicket::find($id);
            }
    
            if (!$booking) {
                return response()->json(['error' => 'Réservation introuvable.'], 404);
            }
    
            $listing = $booking->listing;
    
            // Seul le propriétaire peut confirmer/annuler (ou le locataire peut annuler)
            if ($listing->user_id != \Auth::id() && $booking->user_id != \Auth::id()) {
                return response()->json(['error' => 'Action non autorisée.'], 403);
            }
    
            $request->validate(['status' => 'required|in:CONFIRMED,CANCELLED,COMPLETED']);
    
            // --- ALGORITHME : ANNULATEURS EN SÉRIE & SÉQUESTRE ANTI-FRAUDE ---
            if ($request->status === 'CANCELLED' && $booking->status !== 'CANCELLED') {
                $canceller = \Auth::user();
                $isMeetingConfirmed = ($booking->status === 'MEETING_CONFIRMED');
                
                // "Check-in GPS Unilatéral" : si la commande a une UserRequest associée et que le livreur/vendeur est "ARRIVED"
                $isGpsArrived = false;
                if ($booking instanceof \App\Models\TransportTicket) {
                    $meta = is_string($booking->metadata) ? json_decode($booking->metadata, true) : $booking->metadata;
                    if (is_array($meta) && isset($meta['request_id'])) {
                        $req = \App\Models\UserRequests::find($meta['request_id']);
                        if ($req && in_array($req->status, ['ARRIVED', 'PICKEDUP', 'DROPPED'])) {
                            $isGpsArrived = true;
                        }
                    }
                }
    
                $isConfiscated = $isMeetingConfirmed || $isGpsArrived;
    
                if ($isConfiscated) {
                    // Confiscation Totale : Le vendeur et le client se sont croisés (ou le vendeur est sur place).
                    // Tentative d'annulation pour éviter la commission -> L'argent est bloqué côté plateforme.
                    // On peut ajouter un strike de fraude grave.
                    $canceller->increment('cancellation_strikes', 2);
                } else {
                    // Annulation légitime (avant rencontre) : on compte 1 strike d'annulation
                    $canceller->increment('cancellation_strikes', 1);
    
                    // --- REMBOURSEMENT NORMAL ---
                    if ($booking instanceof \App\Models\TransportTicket && $booking->payment_mode === 'CASH') {
                        $commissionPercent = ($listing->user->user_badge === 'VIP') ? 0 : (($listing->user->user_badge === 'PREMIUM') ? 5 : 10);
                        $commission = ($booking->total_price * $commissionPercent) / 100;
                        
                        // On rend la commission au vendeur
                        $listing->user->increment('wallet_balance', $commission);
                        \App\Models\WalletPassbook::create([
                            'user_id' => $listing->user_id, 
                            'amount' => $commission, 
                            'status' => 'CREDITED', 
                            'via' => 'TICKET_COMMISSION_REFUND'
                        ]);
    
                        if ($booking->pass) {
                            $booking->pass->decrement('sold_count');
                        }
                    }
                }
    
                // BLOCAGE DU COMPTE ("Filtre 2026 Ultra Puissant")
                if ($canceller->cancellation_strikes >= 5) {
                    // Blocage total du compte pour annulations abusives
                    // $canceller->update(['device_token' => null]); // Déconnexion forcée éventuelle
                }
            }
    
            $booking->update(['status' => $request->status]);
    
            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour.',
                'data'    => $booking
            ]);
        }

        public function myPurchases(Request $request)
        {
            $user = $request->user();
            \Log::info("MyPurchases request for user: " . ($user ? $user->id : 'NULL'));
            
            $formatListing = function($listing) {
                if (!$listing) return null;
                if (!$listing->relationLoaded('user')) $listing->load('user');
                
                return [
                    'id'          => (int)$listing->id,
                    'user_id'     => (int)$listing->user_id,
                    'type'        => $listing->type,
                    'title'       => $listing->title,
                    'content'     => $listing->description,
                    'media_url'   => $listing->cover_image ? url('storage/' . $listing->cover_image) : null,
                    'price'       => (float)$listing->price,
                    'latitude'    => (float)$listing->location_latitude,
                    'longitude'   => (float)$listing->location_longitude,
                    'status'      => $listing->status,
                    'category'    => $listing->category,
                    'is_digital'  => (bool)$listing->is_digital,
                    'created_at'  => $listing->created_at ? $listing->created_at->toDateTimeString() : null,
                    'user'        => $listing->user
                ];
            };
    
            // 1. Récupérer les Achats fermes (Tickets & Articles vendus)
            $purchases = TransportTicket::where('user_id', $user->id)
                ->with(['listing.user', 'pass'])
                ->latest()
                ->get();
                
            $formattedPurchases = $purchases->map(function($t) use ($formatListing) {
                return [
                    'id'           => (int)$t->id,
                    'listing'      => $formatListing($t->listing),
                    'pass'         => $t->pass,
                    'price'        => (float)$t->total_price,
                    'status'       => $t->status,
                    'payment_mode' => $t->payment_mode,
                    'created_at'   => $t->created_at ? $t->created_at->toDateTimeString() : null,
                    'qr_code'      => $t->qr_code,
                    'share_url'    => route('ticket.view', ['booking_id' => $t->qr_code])
                ];
            });
    
            // 2. Récupérer les Locations (Bookings)
            $bookings = RentalBooking::where('user_id', $user->id)
                ->with(['listing.user'])
                ->latest()
                ->get();
    
            // 3. Transformer les Bookings
            $transformedBookings = $bookings->map(function($b) use ($formatListing) {
                return [
                    'id'           => (int)$b->id,
                    'listing'      => $formatListing($b->listing),
                    'price'        => (float)$b->total_price,
                    'status'       => $b->status,
                    'payment_mode' => 'RENTAL',
                    'created_at'   => $b->created_at ? $b->created_at->toDateTimeString() : null,
                    'qr_code'      => 'RENT-' . $b->id,
                    'share_url'    => '#',
                    'is_rental'    => true
                ];
            });
    
            // Fusionner les listes
            $combined = $formattedPurchases->concat($transformedBookings)->sortByDesc('created_at')->values();
    
            return response()->json([
                'success' => true,
                'data'    => $combined
            ]);
        }

        public function downloadDigitalProduct(Request $request, $id)
        {
            $ticket = \App\Models\TransportTicket::with('listing')->where('user_id', \Auth::id())->findOrFail($id);
            
            // Autoriser le téléchargement uniquement si le produit est payé
            if ($ticket->status !== 'PAID' && $ticket->status !== 'USED' && $ticket->status !== 'CONFIRMED') {
                return response()->json(['error' => 'Achat non validé'], 403);
            }
            
            $listing = $ticket->listing;
            if (!$listing || !$listing->is_digital || !$listing->digital_file_path) {
                return response()->json(['error' => 'Ce produit n\'est pas un fichier numérique disponible au téléchargement'], 400);
            }
    
            try {
                // Génère un lien S3 éphémère (expire dans 15 minutes)
                $url = \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl(
                    $listing->digital_file_path, now()->addMinutes(15)
                );
                return response()->json(['download_url' => $url]);
            } catch (\Exception $e) {
                \Log::error('S3 Download Error: ' . $e->getMessage());
                return response()->json(['error' => 'Erreur de génération du lien de téléchargement. Vérifiez la configuration S3.'], 500);
            }
        }

}
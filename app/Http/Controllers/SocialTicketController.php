<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TransportEvent;
use App\Models\TransportTicket;
use App\Models\Post;
use App\Models\WalletPassbook;
use App\Models\ServiceType;
use App\Models\ProviderWallet;
use App\Events\NewSocialTripPosted;
use App\Models\EventPassType;
use Carbon\Carbon;

class SocialTicketController extends Controller
{
    /**
     * Chauffeur/Compagnie : Créer un nouvel événement de transport et le publier sur le fil social
     */
    public function createEvent(Request $request): JsonResponse
    {
        $request->validate([
            'service_type_id' => 'required|integer|exists:service_types,id',
            'pdp_route_id' => 'nullable|integer',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            's_address' => 'required|string',
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'd_address' => 'required|string',
            'd_latitude' => 'required|numeric',
            'd_longitude' => 'required|numeric',
            'departure_time' => 'required|date|after:now',
            'price' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
        ]);

        $providerId = Auth::guard('providerapi')->id();

        try {
            DB::beginTransaction();

            $event = TransportEvent::create([
                'provider_id' => $providerId,
                'service_type_id' => $request->service_type_id,
                'pdp_route_id' => $request->pdp_route_id,
                'title' => $request->title,
                'description' => $request->description,
                's_address' => $request->s_address,
                's_latitude' => $request->s_latitude,
                's_longitude' => $request->s_longitude,
                'd_address' => $request->d_address,
                'd_latitude' => $request->d_latitude,
                'd_longitude' => $request->d_longitude,
                'departure_time' => $request->departure_time,
                'price' => $request->price,
                'total_seats' => $request->total_seats,
                'available_seats' => $request->total_seats,
                'status' => 'SCHEDULED',
            ]);

            // Gestion des Passes Personnalisés
            if ($request->has('passes')) {
                $passes = $request->input('passes');
                if (is_string($passes)) $passes = json_decode($passes, true);

                foreach ($passes as $p) {
                    EventPassType::create([
                        'event_id'    => $event->id,
                        'name'        => $p['name'],
                        'price'       => $p['price'] ?? $event->price,
                        'valid_from'  => $p['valid_from'],
                        'valid_until' => $p['valid_until'],
                        'quantity'    => $p['quantity'] ?? $event->total_seats,
                    ]);
                }
            } else {
                // Par défaut, on crée un pass basé sur l'heure de départ
                EventPassType::create([
                    'event_id'    => $event->id,
                    'name'        => 'Ticket Standard',
                    'price'       => $event->price,
                    'valid_from'  => Carbon::parse($event->departure_time)->format('H:i:s'),
                    'valid_until' => Carbon::parse($event->departure_time)->addHours(5)->format('H:i:s'),
                    'quantity'    => $event->total_seats,
                ]);
            }

            // Création du post Social pour pousser la billetterie dans le feed
            $service = ServiceType::find($request->service_type_id);
            $serviceName = $service ? $service->name : 'Véhicule';
            $time = Carbon::parse($event->departure_time)->format('d/m/Y à H:i');

            $postContent = "🎫 BILLETTERIE OUVERTE : {$event->title}\n";
            $postContent .= "📍 Lieu: {$event->s_address}\n";
            $postContent .= "📅 À partir du {$time}.\n";
            $postContent .= ($event->description) ? "📝 {$event->description}\n" : "";

            $post = Post::create([
                'user_id'      => $providerId,
                'type'         => 'SOCIAL',
                'source'       => 'INTERNAL',
                'category'     => 'TICKET',
                'trip_id'      => $event->id,
                'content'      => $postContent,
                'pdp_route_id' => $event->pdp_route_id,
                'status'       => 'ACTIVE'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Événement et passes créés avec succès !',
                'data' => $event->load('passes')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création de l\'événement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Récupérer les événements de billetterie actifs
     */
    public function getActiveEvents(Request $request): JsonResponse
    {
        $query = TransportEvent::with(['provider:id,first_name,last_name,avatar,rating', 'serviceType', 'passes'])
            ->where('status', 'SCHEDULED')
            ->where('departure_time', '>', now()->subHours(24)); // On garde visible même si commencé pour les pass "nuit"

        if ($request->has('pdp_route_id')) {
            $query->where('pdp_route_id', $request->pdp_route_id);
        }

        $events = $query->orderBy('departure_time', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Passager : Acheter un e-Billet depuis le fil social
     */
    public function buyTicket(Request $request, $eventId): JsonResponse
    {
        $request->validate([
            'seats' => 'required|integer|min:1',
            'pass_type_id' => 'required|exists:event_pass_types,id'
        ]);

        $user = Auth::user();
        $seatsToBuy = $request->seats;

        try {
            DB::beginTransaction();

            $event = TransportEvent::where('id', $eventId)->firstOrFail();
            $pass = EventPassType::where('id', $request->pass_type_id)
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pass->quantity - $pass->sold_count < $seatsToBuy) {
                return response()->json(['error' => "Désolé, il ne reste que " . ($pass->quantity - $pass->sold_count) . " place(s) pour ce pass."], 400);
            }

            $totalPrice = $pass->price * $seatsToBuy;

            if ($user->wallet_balance < $totalPrice) {
                return response()->json(['error' => "Solde insuffisant."], 402);
            }

            // Déduction
            $user->decrement('wallet_balance', $totalPrice);
            WalletPassbook::create(['user_id' => $user->id, 'amount' => -$totalPrice, 'status' => 'DEBIT', 'via' => 'TICKET_PURCHASE']);

            // Génération QR Code sécurisé
            $personsAllowed = $pass->persons_per_pass ?: 1;
            $totalSubTickets = $seatsToBuy * $personsAllowed;
            $pricePerTicket = $totalPrice / $totalSubTickets;
            $orderRef = Str::uuid()->toString();
            
            for ($i = 0; $i < $totalSubTickets; $i++) {
                $ticketId = Str::random(12);
                $signature = substr(hash_hmac('sha256', $ticketId . $user->id, config('app.key')), 0, 8);
                $qrCode = "PKM-{$ticketId}-{$signature}";
                $totpSecret = \Illuminate\Support\Str::random(32); // Clé secrète unique pour le TOTP
    
                TransportTicket::create([
                    'transport_event_id' => $event->id,
                    'event_pass_type_id' => $pass->id,
                    'user_id' => $user->id,
                    'qr_code' => $qrCode,
                    'totp_secret' => $totpSecret,
                    'seats_booked' => 1,
                    'total_price' => $pricePerTicket,
                    'payment_status' => 'PAID',
                    'status' => 'BOOKED',
                    'metadata' => json_encode([
                        'order_ref' => $orderRef,
                        'is_sub_ticket' => true,
                        'sub_ticket_index' => $i + 1,
                        'total_sub_tickets' => $totalSubTickets
                    ])
                ]);
            }

            $pass->increment('sold_count', $seatsToBuy);
            $event->decrement('available_seats', $seatsToBuy);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Billet réservé : ' . $pass->name,
                'data' => $ticket->load('pass')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Chauffeur : Scanner le e-billet du passager à l'embarquement
     */
    public function scanTicket(Request $request): JsonResponse
    {
        $request->validate(['qr_code' => 'required|string']);
        $providerId = Auth::guard('providerapi')->id();

        $ticket = TransportTicket::where('qr_code', $request->qr_code)
            ->with(['pass', 'event', 'user:id,first_name,last_name,rating'])
            ->first();

        if (!$ticket || !$ticket->event) {
            return response()->json(['error' => 'Billet invalide.'], 404);
        }

        if ($ticket->status === 'USED') {
            return response()->json(['error' => 'Billet déjà entièrement utilisé.'], 400);
        }

        $pass = $ticket->pass;
        $personsAllowed = $pass ? ($pass->persons_per_pass ?: 1) : 1;
        
        $metadata = $ticket->metadata ? json_decode($ticket->metadata, true) : [];
        $isSubTicket = isset($metadata['is_sub_ticket']) && $metadata['is_sub_ticket'];
        $scannedCount = isset($metadata['scanned_count']) ? (int)$metadata['scanned_count'] : 0;

        // --- NOUVELLE LOGIQUE DE VALIDATION HORAIRE ---
        if ($pass) {
            $now = now()->format('H:i:s');
            $isValid = false;

            if ($pass->valid_from <= $pass->valid_until) {
                $isValid = ($now >= $pass->valid_from && $now <= $pass->valid_until);
            } else {
                // Cas "Nuit/Aube"
                $isValid = ($now >= $pass->valid_from || $now <= $pass->valid_until);
            }

            if (!$isValid) {
                return response()->json([
                    'error' => 'Accès refusé : Hors plage horaire.',
                    'pass_name' => $pass->name,
                    'valid_from' => $pass->valid_from,
                    'valid_until' => $pass->valid_until,
                    'current_time' => $now
                ], 403);
            }
        }
        
        $passName = $pass ? $pass->name : 'Standard';
        
        if ($isSubTicket || $personsAllowed <= 1) {
            $ticket->update(['status' => 'USED']);
            $message = "Accès validé : {$passName}";
            if ($isSubTicket) {
                $index = $metadata['sub_ticket_index'] ?? 1;
                $total = $metadata['total_sub_tickets'] ?? 1;
                $message = "Accès validé : {$passName} (Billet {$index}/{$total})";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => $ticket->user->first_name,
                'seats' => $ticket->seats_booked
            ]);
        }
        
        if ($scannedCount >= $personsAllowed) {
            if ($ticket->status !== 'USED') {
                $ticket->update(['status' => 'USED']);
            }
            return response()->json(['error' => 'Toutes les entrées de ce pass ont déjà été utilisées.'], 400);
        }

        $scannedCount++;
        $metadata['scanned_count'] = $scannedCount;
        $remaining = $personsAllowed - $scannedCount;
        
        $statusToUpdate = ($remaining <= 0) ? 'USED' : $ticket->status;
        
        $ticket->update([
            'status' => $statusToUpdate,
            'metadata' => json_encode($metadata)
        ]);
        
        $message = ($remaining > 0) ? "Accès validé : {$passName} (Reste {$remaining} places)" : "Accès validé : {$passName} (Dernière entrée)";

        return response()->json([
            'success' => true,
            'message' => $message,
            'user' => $ticket->user->first_name,
            'seats' => $ticket->seats_booked
        ]);
    }

    /**
     * Scanneur : Synchroniser les billets pour la validation hors ligne
     */
    public function syncTickets(Request $request, $eventId): JsonResponse
    {
        $providerId = Auth::guard('providerapi')->id();
        
        $event = TransportEvent::where('id', $eventId)
            ->where('provider_id', $providerId)
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Événement non trouvé ou accès non autorisé.'], 404);
        }
        
        $tickets = TransportTicket::where('transport_event_id', $eventId)
            ->where('status', '!=', 'CANCELLED')
            ->select('id', 'qr_code', 'totp_secret', 'status', 'seats_booked', 'user_id')
            ->with('user:id,first_name,last_name')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }
}

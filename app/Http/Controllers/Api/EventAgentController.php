<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StationAgent;
use App\Models\Partner;
use App\Models\WalletPassbook;
use App\Models\TransportEvent;
use App\Models\TransportTicket;
use App\Models\EventPassType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventAgentController extends Controller
{
    /**
     * Résoudre l'agent : Partner STATION_AGENT (prioritaire) ou legacy StationAgent.
     * Retourne ['partner' => Partner|null, 'agent' => StationAgent|null]
     */
    private function getAgent()
    {
        $user = Auth::user();
        if (!$user) return null;

        // Nouveau système
        $partner = Partner::where('user_id', $user->id)
            ->where('type', 'STATION_AGENT')
            ->first();
        if ($partner) return $partner; // On réutilise le retour de getAgent() comme bool

        // Fallback legacy
        return StationAgent::where('user_id', $user->id)->first();
    }

    /**
     * Résoudre séparément Partner et Agent pour les méthodes qui ont besoin des deux.
     */
    private function resolveActors(): array
    {
        $user = Auth::user();
        $partner = $user ? Partner::where('user_id', $user->id)->where('type', 'STATION_AGENT')->first() : null;
        $agent   = $user ? StationAgent::where('user_id', $user->id)->first() : null;
        return [$partner, $agent];
    }

    /**
     * Récupérer les détails de l'événement et les Pass disponibles
     * GET /api/user/agent/event/details
     */
    public function getEventDetails(Request $request)
    {
        $agent = $this->getAgent();
        if (!$agent) return response()->json(['error' => 'Non autorisé.'], 403);

        // On suppose que l'événement assigné est passé en paramètre, 
        // ou qu'on trouve l'événement actif lié à la gare/zone de l'agent
        $eventId = $request->input('event_id');
        $userId = \Illuminate\Support\Facades\Auth::id();
        
        $assignedListingIds = \App\Models\MarketplaceAgent::where('user_id', $userId)->pluck('listing_id')->toArray();
        $ownedListingIds = \App\Models\MarketplaceListing::where('user_id', $userId)->whereIn('category', ['TICKETS', 'TRAVEL'])->pluck('id')->toArray();
        $allListingIds = array_unique(array_merge($assignedListingIds, $ownedListingIds));
        
        if (!$eventId) {
            $event = \App\Models\MarketplaceListing::whereIn('id', $allListingIds)->orderBy('created_at', 'desc')->first();
        } else {
            $event = \App\Models\MarketplaceListing::whereIn('id', $allListingIds)->where('id', $eventId)->first();
        }

        if (!$event) {
            return response()->json(['error' => 'Aucun événement actif trouvé ou accès refusé.'], 404);
        }

        $passes = \App\Models\EventPassType::where('listing_id', $event->id)->get();

        return response()->json([
            'success' => true,
            'event' => $event,
            'passes' => $passes
        ]);
    }

    /**
     * Scanner et valider un QR Code à l'entrée
     * POST /api/user/agent/event/scan
     */
    public function scanTicket(Request $request)
    {
        $agent = $this->getAgent();
        if (!$agent) return response()->json(['error' => 'Non autorisé.'], 403);

        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $ticket = TransportTicket::with('user')->where('qr_code', $request->qr_code)->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Billet invalide ou introuvable.'
            ], 404);
        }
        
        $userId = \Illuminate\Support\Facades\Auth::id();
        $isAssigned = \App\Models\MarketplaceAgent::where('listing_id', $ticket->listing_id)->where('user_id', $userId)->exists();
        $isOwner = \App\Models\MarketplaceListing::where('id', $ticket->listing_id)->where('user_id', $userId)->exists();
        
        if (!$isAssigned && !$isOwner) {
            return response()->json([
                'success' => false,
                'error' => 'Vous n\'êtes pas assigné à cet événement pour scanner les billets.'
            ], 403);
        }

        if ($ticket->status === 'USED') {
            return response()->json([
                'success' => false,
                'error' => 'Toutes les entrées de ce billet ont DÉJÀ ÉTÉ UTILISÉES !'
            ], 400);
        }

        if ($ticket->status === 'CANCELLED') {
            return response()->json([
                'success' => false,
                'error' => 'Ce billet a été annulé.'
            ], 400);
        }

        $pass = $ticket->pass;
        $personsAllowed = $pass ? ($pass->persons_per_pass ?: 1) : 1;
        
        $metadata = $ticket->metadata ? json_decode($ticket->metadata, true) : [];
        $isSubTicket = isset($metadata['is_sub_ticket']) && $metadata['is_sub_ticket'];
        $scannedCount = isset($metadata['scanned_count']) ? (int)$metadata['scanned_count'] : 0;
        
        if ($isSubTicket || $personsAllowed <= 1) {
            $ticket->status = 'USED';
            $ticket->save();
            
            $message = "Billet valide ! Accès autorisé.";
            if ($isSubTicket) {
                $index = $metadata['sub_ticket_index'] ?? 1;
                $total = $metadata['total_sub_tickets'] ?? 1;
                $message = "Billet valide ! (Billet {$index}/{$total})";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'ticket' => $ticket,
                'customer' => $ticket->user ? $ticket->user->first_name . ' ' . $ticket->user->last_name : 'Inconnu'
            ]);
        }
        
        // --- LOGIQUE FALLBACK POUR LES ANCIENS PASS DE GROUPE ---
        if ($scannedCount >= $personsAllowed) {
            if ($ticket->status !== 'USED') {
                $ticket->status = 'USED';
                $ticket->save();
            }
            return response()->json([
                'success' => false,
                'error' => 'Toutes les entrées de ce billet ont DÉJÀ ÉTÉ UTILISÉES !'
            ], 400);
        }
        
        $scannedCount++;
        $metadata['scanned_count'] = $scannedCount;
        $remaining = $personsAllowed - $scannedCount;
        
        $ticket->status = ($remaining <= 0) ? 'USED' : $ticket->status;
        $ticket->metadata = json_encode($metadata);
        $ticket->save();
        
        $message = ($remaining > 0) ? "Billet valide ! (Reste {$remaining} places)" : "Billet valide ! Dernière entrée.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'ticket' => $ticket,
            'customer' => $ticket->user ? $ticket->user->first_name . ' ' . $ticket->user->last_name : 'Inconnu'
        ]);
    }

    /**
     * Vendre un billet physique (Cash) à la porte
     * POST /api/user/agent/event/sell-cash
     */
    public function sellCashTicket(Request $request)
    {
        $agent = $this->getAgent();
        if (!$agent) return response()->json(['error' => 'Non autorisé.'], 403);

        $request->validate([
            'event_id' => 'required|integer',
            'pass_type_id' => 'required|integer',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
        ]);

        $event = \App\Models\MarketplaceListing::findOrFail($request->event_id);
        $passType = EventPassType::findOrFail($request->pass_type_id);

        DB::beginTransaction();
        try {
            // Créer un utilisateur fantôme si infos fournies
            $userId = null;
            if ($request->customer_phone) {
                $customer = User::firstOrCreate(
                    ['mobile' => $request->customer_phone],
                    [
                        'first_name' => $request->customer_name ?? 'Client',
                        'last_name' => 'Event',
                        'email' => 'event_' . time() . '@picme.local',
                        'password' => bcrypt(Str::random(10)),
                        'payment_mode' => 'CASH'
                    ]
                );
                $userId = $customer->id;
            } else {
                // Utiliser l'ID de l'agent comme acheteur par défaut si pas de client
                $userId = $agent->user_id; 
            }

            // Générer un ticket
            $personsAllowed = $passType->persons_per_pass ?: 1;
            $pricePerTicket = $passType->price / $personsAllowed;
            $orderRef = Str::uuid()->toString();
            $tickets = [];

            for ($i = 0; $i < $personsAllowed; $i++) {
                $tickets[] = TransportTicket::create([
                    'listing_id' => $event->id,
                    'event_pass_type_id' => $passType->id,
                    'user_id' => $userId,
                    'qr_code' => 'CASH-' . strtoupper(Str::random(8)),
                    'seats_booked' => 1,
                    'total_price' => $pricePerTicket,
                    'payment_status' => 'PAID',
                    'payment_mode' => 'CASH',
                    'status' => 'USED', // Déjà à la porte, donc utilisé directement
                    'metadata' => json_encode([
                        'order_ref' => $orderRef,
                        'is_sub_ticket' => true,
                        'sub_ticket_index' => $i + 1,
                        'total_sub_tickets' => $personsAllowed
                    ])
                ]);
            }
            $ticket = $tickets[0];

            // Note: AUCUN DÉBIT sur le wallet de l'agent.
            // L'organisateur devra payer la commission à PicMe plus tard.
            
            // Loguer la vente pour les rapports (double-écriture pendant la transition)
            [$partner, $agent] = $this->resolveActors();

            if ($partner && $partner->user) {
                // Nouveau système : WalletPassbook (montant 0 = simple log sans mouvement financier)
                WalletPassbook::create([
                    'user_id'      => $partner->user->id,
                    'partner_id'   => $partner->id,
                    'amount'       => 0,
                    'status'       => 'CREDITED',
                    'via'          => 'EVENT_CASH_SALE',
                    'description'  => "Vente Cash: {$passType->name} ({$passType->price} FCFA) remis à l'organisateur.",
                    'reference_id' => (string) $ticket->id,
                ]);
            }

            if ($agent) {
                // Fallback legacy
                DB::table('agent_commission_logs')->insert([
                    'station_agent_id' => $agent->id,
                    'type'             => 'EVENT_CASH_SALE',
                    'amount'           => 0,
                    'reference_id'     => $ticket->id,
                    'description'      => "Vente Cash: {$passType->name} ({$passType->price} FCFA) [legacy]",
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vente Cash enregistrée avec succès. Accès autorisé.',
                'ticket' => $ticket
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la vente: ' . $e->getMessage()], 500);
        }
    }
}

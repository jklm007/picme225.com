<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StationAgent;
use App\Models\Partner;
use App\Models\TransportEvent;
use App\Models\EventPassType;
use App\Models\TransportTicket;
use App\Models\User;
use App\Models\WalletPassbook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Partner;
use App\Models\TransportEvent;
use App\Models\EventPassType;
use App\Models\TransportTicket;
use Illuminate\Support\Facades\Auth;

class AgentWebController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Verify if the authenticated user is an agent.
     */
    private function isAgent()
    {
        $user = Auth::user();
        if (!$user) return false;

        $isPartner = Partner::where('user_id', $user->id)->where('type', 'STATION_AGENT')->exists();
        if ($isPartner) return true;

        $isStationAgent = StationAgent::where('user_id', $user->id)->exists();
        return $isStationAgent;
    }

    /**
     * Dashboard de l'agent.
     */
    public function index()
    {
        if (!$this->isAgent()) {
            return redirect('/home')->with('error', 'Accès non autorisé. Vous n\'êtes pas un agent.');
        }

        // On peut récupérer des stats du jour
        $user = Auth::user();
        
        return view('agent.dashboard', compact('user'));
    }

    /**
     * Interface de scan web (Caméra HTML5).
     */
    public function scanner()
    {
        if (!$this->isAgent()) {
            return redirect('/home')->with('error', 'Accès non autorisé.');
        }

        return view('agent.scanner');
    }

    /**
     * Interface de vente au guichet (Cash).
     */
    public function cashDesk(Request $request)
    {
        if (!$this->isAgent()) {
            return redirect('/home')->with('error', 'Accès non autorisé.');
        }

        $userId = Auth::id();
        
        // Obtenir toutes les annonces de type TICKETS/TRAVEL assignées à l'agent (directement ou via propriétaire)
        // 1. Annonces où l'agent est assigné
        $assignedListingIds = \App\Models\MarketplaceAgent::where('user_id', $userId)->pluck('listing_id')->toArray();
        
        // 2. Annonces où l'agent est le propriétaire
        $ownedListingIds = \App\Models\MarketplaceListing::where('user_id', $userId)->whereIn('category', ['TICKETS', 'TRAVEL'])->pluck('id')->toArray();
        
        $allListingIds = array_unique(array_merge($assignedListingIds, $ownedListingIds));
        
        $events = \App\Models\MarketplaceListing::whereIn('id', $allListingIds)->orderBy('created_at', 'desc')->get();

        $eventId = $request->input('event_id');
        $event = null;
        if ($eventId) {
            $event = $events->firstWhere('id', $eventId);
        } elseif ($events->count() > 0) {
            $event = $events->first();
        }

        $passes = [];
        if ($event) {
            $passes = \App\Models\EventPassType::where('listing_id', $event->id)->get();
        }

        // On passe $events pour pouvoir faire un dropdown de sélection d'événement
        return view('agent.cash_desk', compact('event', 'events', 'passes'));
    }

    /**
     * Process Scan via AJAX.
     */
    public function processScan(Request $request)
    {
        if (!$this->isAgent()) return response()->json(['error' => 'Non autorisé.'], 403);

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
        
        $userId = Auth::id();
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
     * Process Cash Sale via AJAX.
     */
    public function processSale(Request $request)
    {
        if (!$this->isAgent()) return response()->json(['error' => 'Non autorisé.'], 403);

        $request->validate([
            'event_id' => 'required|integer',
            'pass_type_id' => 'required|integer',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
        ]);

        $event = \App\Models\MarketplaceListing::findOrFail($request->event_id);
        $passType = EventPassType::findOrFail($request->pass_type_id);
        
        $user = Auth::user();
        $partner = Partner::where('user_id', $user->id)->where('type', 'STATION_AGENT')->first();
        $agent = StationAgent::where('user_id', $user->id)->first();

        DB::beginTransaction();
        try {
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
                $userId = $user->id; 
            }

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
                    'status' => 'USED',
                    'metadata' => json_encode([
                        'order_ref' => $orderRef,
                        'is_sub_ticket' => true,
                        'sub_ticket_index' => $i + 1,
                        'total_sub_tickets' => $personsAllowed
                    ])
                ]);
            }
            $ticket = $tickets[0];

            if ($partner) {
                WalletPassbook::create([
                    'user_id'      => $partner->user_id,
                    'partner_id'   => $partner->id,
                    'amount'       => 0,
                    'status'       => 'CREDITED',
                    'via'          => 'EVENT_CASH_SALE',
                    'description'  => "Vente Cash Web: {$passType->name} ({$passType->price} FCFA)",
                    'reference_id' => (string) $ticket->id,
                ]);
            }

            if ($agent) {
                DB::table('agent_commission_logs')->insert([
                    'station_agent_id' => $agent->id,
                    'type'             => 'EVENT_CASH_SALE',
                    'amount'           => 0,
                    'reference_id'     => $ticket->id,
                    'description'      => "Vente Cash Web: {$passType->name} ({$passType->price} FCFA)",
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            // Envoi WhatsApp
            $customer = isset($customer) ? $customer : $user;
            if (class_exists(\App\Jobs\SendWhatsAppTicketJob::class)) {
                try {
                    dispatch(new \App\Jobs\SendWhatsAppTicketJob($ticket, $customer));
                } catch (\Exception $e) {
                    \Log::error("Erreur dispatch WhatsApp Agent : " . $e->getMessage());
                }
            }

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

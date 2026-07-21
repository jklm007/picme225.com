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

class MarketplaceTicketController extends Controller
{
        public function scan(Request $request)
        {
            $request->validate(['qr_code' => 'required|string']);
            $ticket = TransportTicket::where('qr_code', $request->qr_code)
                ->with(['pass', 'listing', 'user:id,first_name,last_name,picture'])
                ->first();
            if (!$ticket) return response()->json(['error' => 'Billet invalide.'], 404);
            
            // 🛡️ Logique de Sécurité Unifiée
            $listing = $ticket->listing;
            if (!$listing) {
                return response()->json(['error' => 'Annonce associée introuvable.'], 404);
            }
            
            $agent = Auth::user();
            $isAuthorized = false;
            
            if ($listing->user_id === $agent->id) {
                $isAuthorized = true;
            } else {
                $isAuthorized = \App\Models\MarketplaceAgent::where('listing_id', $listing->id)
                    ->where('user_id', $agent->id)
                    ->exists();
                if (!$isAuthorized) {
                    $ownerFleet = \App\Models\Fleet::where('user_id', $listing->user_id)->first();
                    if ($ownerFleet) {
                        $isStationAgent = \App\Models\StationAgent::where('user_id', $agent->id)
                            ->whereHas('company', function($q) use($ownerFleet) {
                                $q->where('fleet_id', $ownerFleet->id);
                            })->exists();
                        if ($isStationAgent) $isAuthorized = true;
                    }
                }
            }
            
            if (!$isAuthorized) {
                return response()->json(['error' => 'Accès refusé. Vous n\'êtes pas autorisé à scanner ce billet.'], 403);
            }
    
            if ($ticket->status === 'USED') return response()->json(['error' => 'Déjà entièrement utilisé.'], 400);
            
            $pass = $ticket->pass;
            $personsAllowed = $pass ? ($pass->persons_per_pass ?: 1) : 1;
            
            $metadata = $ticket->metadata ? json_decode($ticket->metadata, true) : [];
            $isSubTicket = isset($metadata['is_sub_ticket']) && $metadata['is_sub_ticket'];
            $scannedCount = isset($metadata['scanned_count']) ? (int)$metadata['scanned_count'] : 0;
            
            if ($pass) {
                $now = Carbon::now()->format('H:i:s');
                $isValid = ($pass->valid_from <= $pass->valid_until) 
                    ? ($now >= $pass->valid_from && $now <= $pass->valid_until)
                    : ($now >= $pass->valid_from || $now <= $pass->valid_until);
                if (!$isValid) return response()->json(['error' => 'Hors plage horaire.'], 403);
            }
            
            // Si c'est un nouveau sous-billet unitaire ou un pass classique 1 place
            if ($isSubTicket || $personsAllowed <= 1) {
                $ticket->update(['status' => 'USED']);
                
                try {
                    (new SendPushNotification())->MarketplaceTicketValidated($ticket->user_id, "Ticket validé ! ✅");
                } catch (\Exception $e) {}
                
                $message = "Entrée validée !";
                if ($isSubTicket) {
                    $index = $metadata['sub_ticket_index'] ?? 1;
                    $total = $metadata['total_sub_tickets'] ?? 1;
                    $message = "Entrée validée ! (Billet {$index}/{$total})";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => ['user' => $ticket->user, 'listing' => $ticket->listing ? $ticket->listing->title : ""]
                ]);
            }
            
            // --- LOGIQUE FALLBACK POUR LES ANCIENS PASS DE GROUPE ---
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
            
            try {
                (new SendPushNotification())->MarketplaceTicketValidated($ticket->user_id, "Ticket validé ! ✅ (Reste {$remaining} places)");
            } catch (\Exception $e) {}
            
            $message = ($remaining > 0) ? "Entrée validée (Reste {$remaining} places)" : "Dernière entrée validée !";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => ['user' => $ticket->user, 'listing' => $ticket->listing ? $ticket->listing->title : ""]
            ]);
        }

        public function verifyPicmeCard(Request $request)
        {
            $request->validate([
                'picme_card_token' => 'required|string',
                'ticket_id' => 'nullable|integer'
            ]);
    
            $sellerId = Auth::id();
    
            // Trouver le client via sa carte
            $client = \App\Models\User::where('picme_card_token', $request->picme_card_token)
                ->orWhere('qr_token', $request->picme_card_token)
                ->first();
                
            // Fallback for id_email format
            if (!$client && str_contains($request->picme_card_token, '_')) {
                $parts = explode('_', $request->picme_card_token, 2);
                if (count($parts) == 2) {
                    $client = \App\Models\User::where('id', $parts[0])->where('email', $parts[1])->first();
                }
            }
                
            if (!$client) {
                return response()->json(['error' => 'Carte PicMe non reconnue ou invalide.'], 404);
            }
    
            // Trouver le ticket correspondant
            $ticketId = $request->input('ticket_id', 0);
            if ($ticketId > 0) {
                $ticket = TransportTicket::where('id', $ticketId)
                    ->where('user_id', $client->id)
                    ->first();
            } else {
                // Auto-résolution du ticket actif le plus récent de ce client pour ce vendeur
                // (Soit le vendeur est le propriétaire de l'annonce, soit un agent)
                $ticket = TransportTicket::where('user_id', $client->id)
                    ->whereIn('status', ['BOOKED', 'SOLD', 'PENDING'])
                    ->whereHas('listing', function ($q) use ($sellerId) {
                        $q->where('user_id', $sellerId);
                    })
                    ->latest()
                    ->first();
            }
    
            if (!$ticket) {
                return response()->json(['error' => 'Aucune commande en cours ou billet actif trouvé pour ce client.'], 404);
            }
    
            if ($ticket->status === 'USED' || $ticket->status === 'MEETING_CONFIRMED') {
                return response()->json(['error' => 'Rencontre déjà confirmée pour cette commande.'], 400);
            }
    
            // Bloquer la transaction (Acquisition des frais de visite)
            $ticket->update(['status' => 'MEETING_CONFIRMED']);
    
            // Mettre à jour la UserRequest liée
            $meta = is_string($ticket->metadata) ? json_decode($ticket->metadata, true) : $ticket->metadata;
            if (is_array($meta) && isset($meta['request_id'])) {
                $req = \App\Models\UserRequests::find($meta['request_id']);
                if ($req) $req->update(['status' => 'ARRIVED']);
            }
    
            try {
                (new SendPushNotification())->MarketplaceTicketValidated($client->id, "Carte PicMe scannée ! Rencontre confirmée. 🤝");
            } catch (\Exception $e) {}
    
            return response()->json([
                'success' => true,
                'message' => "Rencontre confirmée avec " . ($client->display_name ?: $client->first_name),
                'client' => ['id' => $client->id, 'name' => $client->first_name, 'picture' => $client->picture]
            ]);
        }

        public function publicTicketView($signature)
        {
            $ticket = TransportTicket::where('qr_code', $signature)->with(['listing', 'pass'])->firstOrFail();
            return view('marketplace.ticket', ['ticket' => $ticket, 'listing' => $ticket->listing, 'pass' => $ticket->pass]);
        }

        public function getListingTickets($id)
        {
            $listing = MarketplaceListing::findOrFail($id);
            $agent = Auth::user();
    
            // 🛡️ Logique de Sécurité Unifiée
            if ($listing->user_id === $agent->id) {
                $isAuthorized = true;
            } else {
                $isAuthorized = MarketplaceAgent::where('listing_id', $id)->where('user_id', $agent->id)->exists();
                if (!$isAuthorized) {
                    $ownerFleet = \App\Models\Fleet::where('user_id', $listing->user_id)->first();
                    if ($ownerFleet) {
                        $isStationAgent = \App\Models\StationAgent::where('user_id', $agent->id)
                            ->whereHas('company', function($q) use($ownerFleet) {
                                $q->where('fleet_id', $ownerFleet->id);
                            })->exists();
                        if ($isStationAgent) $isAuthorized = true;
                    }
                }
            }
    
            if (!$isAuthorized) {
                return response()->json(['error' => 'Accès refusé.'], 403);
            }
    
            $tickets = TransportTicket::where('listing_id', $id)
                ->with(['user', 'pass'])
                ->latest()
                ->get();
    
            return response()->json(['success' => true, 'data' => $tickets]);
        }

        public function manualCheckIn($ticket_id)
        {
            $ticket = TransportTicket::findOrFail($ticket_id);
            
            // 🛡️ Logique de Sécurité Unifiée
            $listing = $ticket->listing;
            if (!$listing) {
                return response()->json(['error' => 'Annonce associée introuvable.'], 404);
            }
            
            $agent = Auth::user();
            $isAuthorized = false;
            
            if ($listing->user_id === $agent->id) {
                $isAuthorized = true;
            } else {
                $isAuthorized = \App\Models\MarketplaceAgent::where('listing_id', $listing->id)
                    ->where('user_id', $agent->id)
                    ->exists();
                if (!$isAuthorized) {
                    $ownerFleet = \App\Models\Fleet::where('user_id', $listing->user_id)->first();
                    if ($ownerFleet) {
                        $isStationAgent = \App\Models\StationAgent::where('user_id', $agent->id)
                            ->whereHas('company', function($q) use($ownerFleet) {
                                $q->where('fleet_id', $ownerFleet->id);
                            })->exists();
                        if ($isStationAgent) $isAuthorized = true;
                    }
                }
            }
            
            if (!$isAuthorized) {
                return response()->json(['error' => 'Accès refusé. Vous n\'êtes pas autorisé à valider manuellement ce billet.'], 403);
            }
    
            if ($ticket->status === 'USED') return response()->json(['error' => 'Déjà entièrement utilisé.'], 400);
            
            $pass = $ticket->pass;
            $personsAllowed = $pass ? ($pass->persons_per_pass ?: 1) : 1;
            
            $metadata = $ticket->metadata ? json_decode($ticket->metadata, true) : [];
            $isSubTicket = isset($metadata['is_sub_ticket']) && $metadata['is_sub_ticket'];
            $scannedCount = isset($metadata['scanned_count']) ? (int)$metadata['scanned_count'] : 0;
            
            if ($isSubTicket || $personsAllowed <= 1) {
                $ticket->update(['status' => 'USED']);
        
                try {
                    (new SendPushNotification())->MarketplaceTicketValidated($ticket->user_id, "Ticket validé manuellement ! ✅");
                } catch (\Exception $e) {}
                
                $message = "Validé !";
                if ($isSubTicket) {
                    $index = $metadata['sub_ticket_index'] ?? 1;
                    $total = $metadata['total_sub_tickets'] ?? 1;
                    $message = "Validé ! (Billet {$index}/{$total})";
                }
        
                return response()->json(['success' => true, 'message' => $message]);
            }
            
            // --- LOGIQUE FALLBACK POUR LES ANCIENS PASS DE GROUPE ---
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
    
            try {
                (new SendPushNotification())->MarketplaceTicketValidated($ticket->user_id, "Ticket validé manuellement ! ✅ (Reste {$remaining} places)");
            } catch (\Exception $e) {}
            
            $message = ($remaining > 0) ? "Validé (Reste {$remaining} places)" : "Dernière entrée validée !";
    
            return response()->json(['success' => true, 'message' => $message]);
        }

        public function agentSellTicket(Request $request, $id)
        {
            $request->validate([
                'pass_type_id' => 'required|exists:event_pass_types,id',
                'client_name' => 'nullable|string',
                'client_phone' => 'nullable|string'
            ]);
    
            $listing = MarketplaceListing::findOrFail($id);
            $agent = Auth::user();
    
            // 1. Vérification : Est-ce le propriétaire ?
            if ($listing->user_id === $agent->id) {
                $isAuthorized = true;
            } else {
                // 2. Vérification : Est-ce un agent délégué spécifiquement pour cette annonce ?
                $isAuthorized = MarketplaceAgent::where('listing_id', $id)->where('user_id', $agent->id)->exists();
    
                if (!$isAuthorized) {
                    // 3. Vérification Organisationnelle : Est-ce un agent de la compagnie/flotte proprio ?
                    // On cherche si le propriétaire de l'annonce a une flotte
                    $ownerFleet = \App\Models\Fleet::where('user_id', $listing->user_id)->first();
                    if ($ownerFleet) {
                        // On vérifie si l'agent actuel est un StationAgent lié à cette flotte (via ses compagnies)
                        $isStationAgent = \App\Models\StationAgent::where('user_id', $agent->id)
                            ->whereHas('company', function($q) use($ownerFleet) {
                                $q->where('fleet_id', $ownerFleet->id);
                            })->exists();
                        
                        if ($isStationAgent) $isAuthorized = true;
                    }
                }
            }
    
            if (!$isAuthorized) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à vendre pour cet événement.'], 403);
            }
    
            // 🛡️ Vérification : L'agent doit être autorisé (soit le proprio, soit un agent délégué)
            // Pour l'instant, on autorise le proprio ou on peut ajouter une logique de rôle "AGENT"
            
            $pass = EventPassType::where('id', $request->pass_type_id)
                ->where('listing_id', $listing->id)
                ->lockForUpdate()
                ->firstOrFail();
    
            if ($pass->quantity - $pass->sold_count <= 0) {
                return response()->json(['error' => 'Ce pass est épuisé.'], 400);
            }
    
            $priceToPay = $pass->price;
    
            DB::transaction(function () use ($listing, $pass, $agent, $request, $priceToPay) {
                $personsAllowed = $pass->persons_per_pass ?: 1;
                $pricePerTicket = $priceToPay / $personsAllowed;
                $orderRef = Str::uuid()->toString();
                
                for ($i = 0; $i < $personsAllowed; $i++) {
                    // Création du ticket (Déjà PAYÉ car cash encaissé par l'agent)
                    $ticketId = Str::random(12);
                    $signature = substr(hash_hmac('sha256', $ticketId . $agent->id, config('app.key')), 0, 8);
                    $qrCode = "PKM-{$ticketId}-{$signature}";
        
                    $ticket = TransportTicket::create([
                        'listing_id'         => $listing->id,
                        'event_pass_type_id' => $pass->id,
                        'user_id'            => $agent->id, // On lie à l'agent pour le suivi, ou on crée un user "Guest"
                        'qr_code'            => $qrCode,
                        'total_price'        => $pricePerTicket,
                        'payment_mode'       => 'CASH',
                        'payment_status'     => 'PAID',
                        'status'             => 'BOOKED',
                        'metadata'           => json_encode([
                            'pass_name' => $pass->name,
                            'listing_title' => $listing->title,
                            'client_name' => $request->client_name ?: 'Client Guichet',
                            'client_phone' => $request->client_phone ?: '',
                            'order_ref' => $orderRef,
                            'is_sub_ticket' => true,
                            'sub_ticket_index' => $i + 1,
                            'total_sub_tickets' => $personsAllowed
                        ])
                    ]);
                }
    
                $pass->increment('sold_count');
    
                // Enregistrer la commission due à la plateforme (Débit organisteur)
                $commission = $priceToPay * 0.15; 
                $listing->user->decrement('wallet_balance', $commission);
                WalletPassbook::create([
                    'user_id' => $listing->user_id, 
                    'amount' => -$commission, 
                    'status' => 'DEBITED', 
                    'via' => 'AGENT_SALE_COMMISSION',
                    'description' => "Commission vente guichet par " . $agent->first_name
                ]);
            });
    
            $ticket = TransportTicket::where('listing_id', $listing->id)->latest()->first();
            $shareUrl = route('ticket.view', ['booking_id' => $ticket->qr_code]);
    
            return response()->json([
                'success' => true,
                'message' => 'Vente réussie !',
                'share_url' => $shareUrl,
                'ticket_id' => $ticket->id
            ]);
        }

}
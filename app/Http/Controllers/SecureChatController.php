<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SecureMessage;
use App\Models\ChatQuote;
use App\Models\WalletPassbook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\PicmeAiService;

class SecureChatController extends Controller
{
    private PicmeAiService $ai;

    public function __construct()
    {
        $this->ai = new PicmeAiService();
    }
    /**
     * Obtenir la liste des contacts avec qui l'utilisateur a récemment discuté.
     */
    public function contacts(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        
        $messages = \App\Models\SecureMessage::where(function($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->where('sender_id', '>', 0)
            ->where('receiver_id', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [];
        foreach ($messages as $msg) {
            $otherId = ($msg->sender_id == $userId) ? $msg->receiver_id : $msg->sender_id;
            $listingId = $msg->announcement_id ?? 0;
            $key = $otherId . '_' . $listingId;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'contact_id'   => $otherId,
                    'listing_id'   => $listingId,
                    'last_message' => $msg->message,
                    'created_at'   => $msg->created_at->format('Y-m-d H:i:s'),
                    'is_mine'      => ($msg->sender_id == $userId),
                    'unread_count' => 0,
                ];
            }
            // Count unread: messages FROM other person not yet read
            if ($msg->sender_id != $userId && !$msg->is_read) {
                $grouped[$key]['unread_count']++;
            }
        }
        
        $result = [];
        foreach ($grouped as $g) {
            // --- Contact info ---
            $contact = \App\Models\User::find($g['contact_id']);
            $name    = 'Utilisateur inconnu';
            $picture = asset('img/logo.png');

            if ($contact) {
                $name = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
                if (empty($name)) $name = $contact->display_name ?? 'Utilisateur';
                if ($contact->picture) {
                    $picture = str_starts_with($contact->picture, 'http')
                        ? $contact->picture
                        : \Storage::disk('s3')->url( $contact->picture);
                }
            } else {
                $provider = \App\Models\Provider::find($g['contact_id']);
                if ($provider) {
                    $name = trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? ''));
                    if ($provider->avatar) {
                        $picture = str_starts_with($provider->avatar, 'http')
                            ? $provider->avatar
                            : \Storage::disk('s3')->url( $provider->avatar);
                    }
                }
            }

            // --- Listing/article info ---
            $listingTitle    = null;
            $listingImage    = null;
            $listingCategory = null;

            if ($g['listing_id'] > 0) {
                $listing = \DB::table('marketplace_listings')
                    ->select('title', 'cover_image', 'category', 'type')
                    ->where('id', $g['listing_id'])
                    ->first();

                if ($listing) {
                    $listingTitle    = $listing->title;
                    $listingCategory = $listing->category ?? $listing->type;
                    if (!empty($listing->cover_image)) {
                        $listingImage = str_starts_with($listing->cover_image, 'http')
                            ? $listing->cover_image
                            : \Storage::disk('s3')->url( $listing->cover_image);
                    }
                }
            }

            // --- Relative time ---
            $relativeTime = \Carbon\Carbon::parse($g['created_at'])->diffForHumans(null, true, true);

            $result[] = [
                'contact_id'       => (string) $g['contact_id'],
                'listing_id'       => $g['listing_id'],
                'contact_name'     => $name,
                'contact_picture'  => $picture,
                'last_message'     => $g['last_message'],
                'is_mine'          => $g['is_mine'],
                'created_at'       => $g['created_at'],
                'relative_time'    => $relativeTime,
                'unread_count'     => $g['unread_count'],
                'listing_title'    => $listingTitle,
                'listing_image'    => $listingImage,
                'listing_category' => $listingCategory,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => array_values($result)
        ]);
    }

    public function thread(Request $request, $recipientId)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        
        $listingId = $request->query('listing_id');
        
        $query = SecureMessage::where(function($q) use ($userId, $recipientId) {
            $q->where(function($sub) use ($userId, $recipientId) {
                $sub->where('sender_id', $userId)->where('receiver_id', $recipientId);
            })->orWhere(function($sub) use ($userId, $recipientId) {
                $sub->where('sender_id', $recipientId)->where('receiver_id', $userId);
            });
        });

        // Filtrer par annonce si un ID d'annonce est fourni
        if ($listingId !== null) {
            $query->where('announcement_id', $listingId);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        // ✅ Auto-mark as read: tous les messages reçus de cet interlocuteur
        SecureMessage::where('sender_id', $recipientId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->when($listingId !== null, fn($q) => $q->where('announcement_id', $listingId))
            ->update(['is_read' => true]);

        $recipientName = 'Utilisateur';
        $recipientPicture = asset('img/logo.png');

        $contact = \App\Models\User::find($recipientId);
        if ($contact) {
            $recipientName = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
            if (empty($recipientName)) $recipientName = $contact->display_name ?? 'Utilisateur';
            if ($contact->picture) {
                $recipientPicture = str_starts_with($contact->picture, 'http')
                    ? $contact->picture
                    : \Storage::disk('s3')->url( $contact->picture);
            }
        } else {
            $provider = \App\Models\Provider::find($recipientId);
            if ($provider) {
                $recipientName = trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? ''));
                if ($provider->avatar) {
                    $recipientPicture = str_starts_with($provider->avatar, 'http')
                        ? $provider->avatar
                        : \Storage::disk('s3')->url( $provider->avatar);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $messages,
            'recipient' => [
                'name' => $recipientName,
                'picture' => $recipientPicture
            ]
        ]);
    }

    /**
     * Marquer explicitement tous les messages d'un contact comme lus.
     * POST /api/user/social/secure-chat/{recipientId}/mark-read
     */
    public function markRead(Request $request, $recipientId)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $listingId = $request->input('listing_id');

        $updated = SecureMessage::where('sender_id', $recipientId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->when($listingId, fn($q) => $q->where('announcement_id', $listingId))
            ->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'updated' => $updated,
        ]);
    }

    /**
     * Envoyer un message P2P sécurisé et modéré.
     */
    public function sendMessage(Request $request, $recipientId)
    {
        $request->validate([
            'message'           => 'required|string|max:1000',
            'announcement_id'   => 'nullable|exists:marketplace_listings,id',
            'receiver_type'     => 'required|in:user,provider',
            'listing_id'        => 'nullable|integer',
            'client_message_id' => 'nullable|string|max:50',
        ]);

        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        // Check for idempotency to prevent duplicates
        if ($request->client_message_id) {
            $existingMsg = SecureMessage::where('client_message_id', $request->client_message_id)->first();
            if ($existingMsg) {
                return response()->json([
                    'status'      => 'success',
                    'message'     => 'Message dǸj envoyǸ.',
                    'data'        => $existingMsg,
                    'lead_score'  => $existingMsg->lead_score,
                    'ai_mode'     => $this->ai->getMode(),
                ], 200);
            }
        }

        $recipientId = (int) $recipientId;
        if ($recipientId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Destinataire invalide.'], 400);
        }

        if ($request->receiver_type === 'user') {
            $recipientExists = \App\Models\User::where('id', $recipientId)->exists();
        } else {
            $recipientExists = \App\Models\Provider::where('id', $recipientId)->exists();
        }

        if (!$recipientExists) {
            return response()->json(['status' => 'error', 'message' => 'Le destinataire n\'existe pas.'], 404);
        }

        $sender  = Auth::user();
        $rawMessage = $request->message;

        // Étape 1 : Filtre Regex Synchrone (Ultra-rapide)
        $regexAnalysis = $this->ai->applyRegexFilter($rawMessage);
        
        $content   = $regexAnalysis['content'];
        $isFlagged = $regexAnalysis['is_flagged'];
        $isBlocked = $regexAnalysis['is_blocked'];
        $leadScore = $regexAnalysis['lead_score'] ?? 'WARM';

        // Étape 2 : Enregistrer le message principal 
        $msg = new SecureMessage();
        $msg->client_message_id = $request->client_message_id;
        $msg->sender_type    = 'user';
        $msg->sender_id      = $userId;
        $msg->receiver_type  = $request->receiver_type;
        $msg->receiver_id    = $recipientId;
        $msg->announcement_id = $request->listing_id ?? $request->announcement_id;
        $msg->message        = $content;
        $msg->is_flagged     = $isFlagged;
        $msg->is_blocked     = $isBlocked;
        $msg->lead_score     = $leadScore;
        $msg->ai_used        = $regexAnalysis['ai_used'] ?? false;
        $msg->save();

        // Dispatch background AI moderation if not blocked by synchronous Regex shield
        if (!$isBlocked) {
            try {
                \App\Jobs\ModerateChatMessageJob::dispatch(
                    $msg->id,
                    $rawMessage,
                    $userId,
                    $recipientId,
                    $request->listing_id ?? $request->announcement_id,
                    $sender->cancellation_strikes ?? 0
                );
            } catch (\Exception $e) {
                \Log::error("Failed to dispatch ModerateChatMessageJob: " . $e->getMessage());
            }
        }

        // 🔔 Diffusion Temps Réel (WebSockets / Soketi)
        try {
            broadcast(new \App\Events\NewSecureMessage($msg))->toOthers();
        } catch (\Exception $e) { /* Soketi down → silencieux */ }

        // 📲 Notification Push (FCM) & In-App Notification (Centre d'Activités)
        try {
            $senderName = $sender->display_name ?? $sender->first_name ?? 'Utilisateur';
            
            // 1. In-App Notification pour le Centre d'Activités
            // On sauvegarde la notification avec l'ID de l'expéditeur et le listing_id
            $actionId = $userId . '_' . ($request->listing_id ?? 0);
            \App\Models\AppNotification::send($recipientId, "Nouveau message de " . $senderName, $content, "NEW_CHAT_MESSAGE", $actionId);
            
            // 2. FCM Push
            $pushData = [
                'type' => 'NEW_CHAT_MESSAGE',
                'title' => 'Nouveau message de ' . $senderName,
                'message' => $content,
                'recipient_id' => $userId,
                'listing_id' => $request->listing_id ?? 0
            ];
            $pushService = new \App\Http\Controllers\SendPushNotification();
            if ($request->receiver_type === 'user') {
                $pushService->sendPushToUser($recipientId, $pushData);
            } else {
                $pushService->sendPushToProvider($recipientId, $pushData);
            }
        } catch (\Exception $e) {
            \Log::error("Chat Push Error: " . $e->getMessage());
        }

        // ─── Réponse finale ───────────────────────────────────────────────────
        if ($isBlocked) {
            return response()->json([
                'status'      => 'warning',
                'message'     => '🛡️ PicMe-Shield a modéré ce message pour votre sécurité.',
                'data'        => $msg,
                'lead_score'  => $leadScore,
                'ai_mode'     => $this->ai->getMode(),
            ], 200);
        }

        return response()->json([
            'status'       => 'success',
            'data'         => $msg,
            'lead_score'   => $leadScore,
            'ai_mode'      => $this->ai->getMode(),
        ]);
    }

    /**
     * Le prestataire génère un devis dans le chat.
     */
    public function sendQuote(Request $request, $recipientId)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:100',
            'listing_id'      => 'nullable|integer',
            'receiver_type'   => 'required|in:user,provider',
        ]);

        $userId = Auth::id(); // Typically the provider
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $recipientId = (int) $recipientId;
        if ($recipientId <= 0) return response()->json(['status' => 'error', 'message' => 'Destinataire invalide.'], 400);

        // 1. Save main message representing the quote
        $content = "Devis proposé : " . $request->amount . " FCFA";
        
        $msg = new SecureMessage();
        $msg->sender_type    = 'provider'; // Typically quotes are sent by providers
        $msg->sender_id      = $userId;
        $msg->receiver_type  = $request->receiver_type;
        $msg->receiver_id    = $recipientId;
        $msg->announcement_id = $request->listing_id;
        $msg->message        = $content;
        $msg->save();

        // 2. Save quote data
        $quote = new ChatQuote();
        $quote->message_id  = $msg->id;
        $quote->provider_id = $userId;
        $quote->user_id     = $recipientId;
        $quote->listing_id  = $request->listing_id;
        $quote->amount      = $request->amount;
        $quote->status      = 'PENDING';
        $quote->save();

        // Broadcast / Push
        try {
            broadcast(new \App\Events\NewSecureMessage($msg))->toOthers();
            
            $pushData = [
                'type' => 'NEW_CHAT_QUOTE',
                'title' => 'Nouveau devis reçu',
                'message' => $content,
                'recipient_id' => $recipientId,
                'quote_id' => $quote->id,
                'listing_id' => $request->listing_id ?? 0
            ];
            $pushService = new \App\Http\Controllers\SendPushNotification();
            if ($request->receiver_type === 'user') {
                $pushService->sendPushToUser($recipientId, $pushData);
            } else {
                $pushService->sendPushToProvider($recipientId, $pushData);
            }
        } catch (\Exception $e) {}

        // Add quote to response
        $msg->quote = $quote;

        return response()->json([
            'status' => 'success',
            'data'   => $msg,
        ]);
    }

    /**
     * Le client accepte et paie le devis via son Wallet.
     */
    public function acceptQuote(Request $request, $id)
    {
        $userId = Auth::id(); // The client
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $quote = ChatQuote::findOrFail($id);

        if ($quote->status !== 'PENDING') {
            return response()->json(['status' => 'error', 'message' => 'Ce devis n\'est plus valide ou a déjà été traité.'], 400);
        }

        if ($quote->user_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Vous n\'êtes pas autorisé à payer ce devis.'], 403);
        }

        $buyer = Auth::user();

        if ($buyer->wallet_balance < $quote->amount) {
            return response()->json(['status' => 'error', 'message' => 'Solde insuffisant dans votre portefeuille.'], 402);
        }

        try {
            DB::transaction(function () use ($quote, $buyer) {
                // 1. Débiter l'acheteur
                $buyer->decrement('wallet_balance', $quote->amount);
                WalletPassbook::create([
                    'user_id' => $buyer->id, 
                    'amount'  => -$quote->amount, 
                    'status'  => 'DEBITED', 
                    'via'     => 'CHAT_QUOTE_ESCROW_HELD'
                ]);

                // 2. Mark quote as FUNDS_HELD (Séquestre)
                $quote->status = 'FUNDS_HELD';
                $quote->save();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Devis accepté. Fonds sécurisés sous séquestre.',
                'quote'   => $quote
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur de paiement devis: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Erreur technique lors du paiement.'], 500);
        }
    }

    /**
     * Prestataire marque le devis comme terminé avec des preuves photos.
     */
    public function markQuoteCompleted(Request $request, $id)
    {
        $userId = Auth::id(); // The provider
        $quote = ChatQuote::findOrFail($id);

        if ($quote->provider_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé.'], 403);
        }

        if ($quote->status !== 'FUNDS_HELD') {
            return response()->json(['status' => 'error', 'message' => 'Statut invalide.'], 400);
        }

        $request->validate([
            'images'   => 'required|array|min:1', // Proof of work
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('proofs/completion', 's3');
                $imagePaths[] = '/storage/' . $path;
            }
        }

        $quote->completion_images = $imagePaths;
        $quote->status = 'AWAITING_CLIENT_CONFIRMATION';
        $quote->save();

        // Push notification to user
        try {
            $pushData = [
                'type' => 'QUOTE_AWAITING_CONFIRMATION',
                'title' => 'Prestation terminée',
                'message' => 'Le prestataire a terminé. Veuillez valider.',
                'quote_id' => $quote->id
            ];
            (new \App\Http\Controllers\SendPushNotification())->sendPushToUser($quote->user_id, $pushData);
        } catch (\Exception $e) {}

        return response()->json([
            'status' => 'success',
            'message' => 'Prestation marquée comme terminée.',
            'quote' => $quote
        ]);
    }

    /**
     * Client valide la prestation. Libération du séquestre.
     */
    public function confirmQuote(Request $request, $id)
    {
        $userId = Auth::id();
        $quote = ChatQuote::findOrFail($id);

        if ($quote->user_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé.'], 403);
        }

        if ($quote->status !== 'AWAITING_CLIENT_CONFIRMATION') {
            return response()->json(['status' => 'error', 'message' => 'Statut invalide.'], 400);
        }

        try {
            DB::transaction(function () use ($quote) {
                $commissionPercent = 15;
                $commissionAmount = ($quote->amount * $commissionPercent) / 100;
                $sellerCredit     = $quote->amount - $commissionAmount;

                $seller = \App\Models\Provider::find($quote->provider_id);
                if ($seller) {
                    $seller->increment('wallet_balance', $sellerCredit);
                }

                $quote->status = 'COMPLETED';
                $quote->save();

                // TODO: Enqueue Gateway Payout Robot Task here in the future
            });

            // Push notification to provider
            try {
                $pushData = [
                    'type' => 'QUOTE_COMPLETED',
                    'title' => 'Paiement libéré',
                    'message' => 'Le client a validé. Les fonds sont ajoutés à votre portefeuille.',
                    'quote_id' => $quote->id
                ];
                (new \App\Http\Controllers\SendPushNotification())->sendPushToProvider($quote->provider_id, $pushData);
            } catch (\Exception $e) {}

            return response()->json([
                'status' => 'success',
                'message' => 'Paiement libéré avec succès.',
                'quote' => $quote
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la libération.'], 500);
        }
    }

    /**
     * Client ouvre un litige.
     */
    public function openDispute(Request $request, $id)
    {
        $userId = Auth::id();
        $quote = ChatQuote::findOrFail($id);

        if ($quote->user_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé.'], 403);
        }

        if ($quote->status !== 'AWAITING_CLIENT_CONFIRMATION') {
            return response()->json(['status' => 'error', 'message' => 'Vous ne pouvez ouvrir un litige que lorsque le prestataire a marqué terminé.'], 400);
        }

        $request->validate([
            'reason'   => 'required|string',
            'images'   => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('proofs/dispute', 's3');
                $imagePaths[] = '/storage/' . $path;
            }
        }

        $quote->dispute_reason = $request->reason;
        $quote->dispute_images = $imagePaths;
        $quote->status = 'DISPUTED';
        $quote->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Litige ouvert. Les fonds sont gelés.',
            'quote' => $quote
        ]);
    }

    /**
     * Prestataire annule et rembourse.
     */
    public function cancelQuoteByProvider(Request $request, $id)
    {
        $userId = Auth::id();
        $quote = ChatQuote::findOrFail($id);

        if ($quote->provider_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé.'], 403);
        }

        if (!in_array($quote->status, ['FUNDS_HELD', 'AWAITING_CLIENT_CONFIRMATION'])) {
            return response()->json(['status' => 'error', 'message' => 'Statut invalide.'], 400);
        }

        try {
            DB::transaction(function () use ($quote) {
                $buyer = \App\Models\User::find($quote->user_id);
                if ($buyer) {
                    $buyer->increment('wallet_balance', $quote->amount);
                    WalletPassbook::create([
                        'user_id' => $buyer->id, 
                        'amount'  => $quote->amount, 
                        'status'  => 'CREDITED', 
                        'via'     => 'CHAT_QUOTE_REFUND_PROVIDER'
                    ]);
                }
                $quote->status = 'CANCELLED_BY_PROVIDER';
                $quote->save();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Devis annulé. Le client a été remboursé.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur technique.'], 500);
        }
    }

    /**
     * Client annule (avant exécution).
     */
    public function cancelQuoteByClient(Request $request, $id)
    {
        $userId = Auth::id();
        $quote = ChatQuote::findOrFail($id);

        if ($quote->user_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé.'], 403);
        }

        if ($quote->status !== 'FUNDS_HELD') {
            return response()->json(['status' => 'error', 'message' => 'Vous ne pouvez plus annuler à ce stade.'], 400);
        }

        try {
            DB::transaction(function () use ($quote, $userId) {
                $buyer = \App\Models\User::find($userId);
                if ($buyer) {
                    $buyer->increment('wallet_balance', $quote->amount);
                    WalletPassbook::create([
                        'user_id' => $buyer->id, 
                        'amount'  => $quote->amount, 
                        'status'  => 'CREDITED', 
                        'via'     => 'CHAT_QUOTE_REFUND_CLIENT'
                    ]);
                }
                $quote->status = 'CANCELLED_BY_CLIENT';
                $quote->save();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Annulation confirmée. Vous avez été remboursé.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur technique.'], 500);
        }
    }

    /**
     * Statut de PicMe AI (pour le panneau admin).
     */
    public function aiStatus(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'ai_mode' => $this->ai->getMode(),
            'enabled' => $this->ai->isEnabled(),
        ]);
    }

    /** Afficher les détails d'un message spécifique */
    public function showMessage($id): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $msg = SecureMessage::find($id);
        if (!$msg) {
            return response()->json(['status' => 'error', 'message' => 'Message non trouvé.'], 404);
        }

        // Vérifier l'autorisation : l'utilisateur connecté doit être l'expéditeur ou le destinataire
        if ($msg->sender_id != $userId && $msg->receiver_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $msg
        ]);
    }

    /** Mettre à jour (modifier) le texte d'un message spécifique */
    public function updateMessage(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $msg = SecureMessage::find($id);
        if (!$msg) {
            return response()->json(['status' => 'error', 'message' => 'Message non trouvé.'], 404);
        }

        // Seul l'expéditeur peut modifier son message
        if ($msg->sender_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $msg->message = $request->message;
        $msg->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Message mis à jour avec succès.',
            'data'    => $msg
        ]);
    }

    /** Supprimer un message spécifique */
    public function destroyMessage($id): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $msg = SecureMessage::find($id);
        if (!$msg) {
            return response()->json(['status' => 'error', 'message' => 'Message non trouvé.'], 404);
        }

        if ($msg->sender_id != $userId && $msg->receiver_id != $userId) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée.'], 403);
        }

        $msg->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Message supprimé avec succès.'
        ]);
    }

    /** Supprimer une conversation complète avec un contact */
    public function destroyThread($recipientId): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $recipientId = (int) $recipientId;
        if ($recipientId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Destinataire invalide.'], 400);
        }

        // Supprimer tous les messages entre l'utilisateur connecté et le destinataire
        $deleted = SecureMessage::where(function($q) use ($userId, $recipientId) {
                $q->where('sender_id', $userId)->where('receiver_id', $recipientId);
            })
            ->orWhere(function($q) use ($userId, $recipientId) {
                $q->where('sender_id', $recipientId)->where('receiver_id', $userId);
            })
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Conversation supprimée avec succès.',
            'deleted_count' => $deleted
        ]);
    }

    public function bulkDestroyThread(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $recipientIds = $request->input('recipientIds');
        if (!is_array($recipientIds) || empty($recipientIds)) {
            return response()->json(['status' => 'error', 'message' => 'Destinataires invalides.'], 400);
        }

        $deleted = SecureMessage::where(function($query) use ($userId, $recipientIds) {
            $query->where('sender_id', $userId)->whereIn('receiver_id', $recipientIds);
        })->orWhere(function($query) use ($userId, $recipientIds) {
            $query->where('receiver_id', $userId)->whereIn('sender_id', $recipientIds);
        })->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Conversations supprimées avec succès.',
            'deleted_count' => $deleted
        ]);
    }
}


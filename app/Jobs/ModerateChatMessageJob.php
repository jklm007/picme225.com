<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SecureMessage;
use App\Services\PicmeAiService;

class ModerateChatMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $messageId;
    public $rawMessage;
    public $senderId;
    public $recipientId;
    public $listingId;
    public $senderStrikes;

    /**
     * Create a new job instance.
     */
    public function __construct($messageId, $rawMessage, $senderId, $recipientId, $listingId, $senderStrikes)
    {
        $this->messageId = $messageId;
        $this->rawMessage = $rawMessage;
        $this->senderId = $senderId;
        $this->recipientId = $recipientId;
        $this->listingId = $listingId;
        $this->senderStrikes = $senderStrikes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $msg = SecureMessage::find($this->messageId);
        if (!$msg || $msg->is_blocked) {
            return; // Déjà bloqué par le Regex ou supprimé
        }

        // Construire le contexte
        $context = [
            'sender_strikes'  => $this->senderStrikes,
            'seller_name'     => 'le vendeur',
            'recent_messages' => SecureMessage::where(function ($q) {
                $q->where('sender_id', $this->senderId)->where('receiver_id', $this->recipientId);
            })->orWhere(function ($q) {
                $q->where('sender_id', $this->recipientId)->where('receiver_id', $this->senderId);
            })->orderBy('created_at', 'desc')->limit(5)->get()
              ->map(fn($m) => ['sender' => $m->sender_id == $this->senderId ? 'acheteur' : 'vendeur', 'message' => $m->message])
              ->reverse()->values()->toArray(),
        ];

        if ($this->listingId) {
            $listing = \App\Models\MarketplaceListing::find($this->listingId);
            if ($listing) {
                $context['listing']     = ['title' => $listing->title, 'price' => $listing->price, 'category' => $listing->category];
                $context['seller_name'] = $listing->user->display_name ?? $listing->user->first_name ?? 'le vendeur';
            }
        }

        $ai = new PicmeAiService();
        $analysis = $ai->analyzeMessageForJob($this->rawMessage, $this->senderId, $this->recipientId, $context);

        if (empty($analysis) || empty($analysis['ai_used'])) {
            return; // IA désactivée ou erreur
        }

        $isBlocked = $analysis['is_blocked'] ?? false;
        $isFlagged = $analysis['is_flagged'] ?? false;
        $leadScore = $analysis['lead_score'] ?? 'WARM';
        $sellerSuggestion = $analysis['seller_suggestion'] ?? null;

        // Mettre à jour le message si nécessaire
        $updated = false;
        if ($isBlocked) {
            $msg->is_blocked = true;
            $msg->is_flagged = true;
            $msg->message = $analysis['content'] ?? "⚠️ [PicMe AI] Message modéré pour sécurité.";
            $updated = true;
        } elseif ($isFlagged) {
            $msg->is_flagged = true;
            $updated = true;
        }

        if ($msg->lead_score !== $leadScore) {
            $msg->lead_score = $leadScore;
            $updated = true;
        }

        if ($updated) {
            $msg->save();
            // Diffuser le message modifié pour "censurer" côté client si bloqué
            try {
                broadcast(new \App\Events\NewSecureMessage($msg))->toOthers();
            } catch (\Exception $e) {}
        }

        // Réponse automatique du vendeur si absent
        if ($sellerSuggestion && !$isBlocked) {
            $lastSellerMsg = SecureMessage::where('sender_id', $this->recipientId)
                ->where('receiver_id', $this->senderId)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$lastSellerMsg) {
                $aiReply = new SecureMessage();
                $aiReply->sender_type   = $msg->receiver_type; // the original message's receiver type
                $aiReply->sender_id     = (int) $this->recipientId;
                $aiReply->receiver_type = $msg->sender_type;
                $aiReply->receiver_id   = $this->senderId;
                $aiReply->message       = $sellerSuggestion;
                $aiReply->is_ai_reply   = true;
                $aiReply->ai_used       = true;
                $aiReply->save();

                try {
                    broadcast(new \App\Events\NewSecureMessage($aiReply))->toOthers();
                } catch (\Exception $e) {}
            }
        }
    }
}

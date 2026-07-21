<?php

namespace App\Services;

use App\Models\SecureMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ╔═══════════════════════════════════════════════════════════════╗
 * ║              PICME AI SERVICE - MOTEUR INTELLIGENT            ║
 * ║  Modèle : Llama 3 (8B) via API Groq (100% Gratuit)           ║
 * ║  Auteur : PicMe 225 - Anti-Fraud Intelligence Layer          ║
 * ╚═══════════════════════════════════════════════════════════════╝
 *
 * Architecture "Double Protection avec Fallback" :
 *   - Niveau 1 : Filtre Regex (toujours actif, ultra-rapide)
 *   - Niveau 2 : PicMe AI (Groq / Llama 3) pour l'analyse sémantique
 *   - Si l'IA bug → on utilise le Niveau 1 automatiquement (Fail-Safe)
 */
class PicmeAiService
{
    private string $groqApiKey;
    private string $groqModel    = 'llama-3.1-8b-instant';
    private string $groqEndpoint = 'https://api.groq.com/openai/v1/chat/completions';
    private bool   $aiEnabled;
    private int    $timeoutSeconds = 5; // Si l'IA met +5s → Fallback automatique

    public function __construct()
    {
        $this->groqApiKey = config('services.groq.api_key', '');
        $this->aiEnabled  = config('services.groq.enabled', true);
    }

    // ══════════════════════════════════════════════════════════════
    //  🛡️  POINT D'ENTRÉE PRINCIPAL : Analyser un message du Chat
    // ══════════════════════════════════════════════════════════════

    /**
     * Analyse un message entrant et retourne un résultat structuré.
     * Utilise l'IA si disponible, sinon bascule sur les Regex (Fail-Safe).
     *
     * @param string $message    Le message brut de l'utilisateur
     * @param int    $senderId   ID de l'expéditeur
     * @param int    $receiverId ID du destinataire
     * @param array  $context    Données de contexte (annonce, historique)
     * @return array  { content, is_blocked, is_flagged, ai_used, lead_score, action_link, seller_suggestion, summary }
     */
    public function analyzeMessage(string $message, int $senderId, int $receiverId, array $context = []): array
    {
        // ── Niveau 1 : Filtre Regex (Rapide, toujours actif) ─────────────────
        $regexResult = $this->applyRegexFilter($message);

        // Si le Regex a déjà bloqué le message, inutile d'appeler l'IA
        if ($regexResult['is_blocked']) {
            Log::info('[PicMe Regex] Message bloqué par le filtre statique.', ['sender' => $senderId]);
            return array_merge($regexResult, ['ai_used' => false]);
        }

        // ── Niveau 2 : PicMe AI (Groq) ───────────────────────────────────────
        if ($this->aiEnabled && !empty($this->groqApiKey)) {
            try {
                $aiResult = $this->callGroqApi($message, $senderId, $receiverId, $context);
                return array_merge($aiResult, ['ai_used' => true]);
            } catch (\Throwable $e) {
                // ── Fail-Safe : L'IA a bugué → on retourne le résultat Regex ──
                Log::warning('[PicMe AI] Fallback vers Regex. Raison : ' . $e->getMessage());
                return array_merge($regexResult, ['ai_used' => false, 'fallback' => true]);
            }
        }

        // IA désactivée → on retourne le résultat Regex propre
        return array_merge($regexResult, ['ai_used' => false]);
    }

    /**
     * Analyse un message uniquement via l'IA (utilisé en arrière-plan par le Job).
     */
    public function analyzeMessageForJob(string $message, int $senderId, int $receiverId, array $context = []): array
    {
        if ($this->aiEnabled && !empty($this->groqApiKey)) {
            try {
                $aiResult = $this->callGroqApi($message, $senderId, $receiverId, $context);
                return array_merge($aiResult, ['ai_used' => true]);
            } catch (\Throwable $e) {
                Log::warning('[PicMe AI Async] Error in background job: ' . $e->getMessage());
                return ['ai_used' => false, 'error' => true];
            }
        }
        return ['ai_used' => false];
    }

    // ══════════════════════════════════════════════════════════════
    //  🤖  APPEL API GROQ (Llama 3 - 8B)
    // ══════════════════════════════════════════════════════════════

    private function callGroqApi(string $message, int $senderId, int $receiverId, array $context): array
    {
        $listing    = $context['listing'] ?? null;
        $sellerName = $context['seller_name'] ?? 'le vendeur';
        $strikes    = $context['sender_strikes'] ?? 0;

        $systemPrompt = $this->buildSystemPrompt($listing, $sellerName, $strikes);

        $response = Http::withToken($this->groqApiKey)
            ->timeout($this->timeoutSeconds)
            ->post($this->groqEndpoint, [
                'model'       => $this->groqModel,
                'temperature' => 0.3,
                'max_tokens'  => 300,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $this->buildUserPrompt($message, $context)],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Groq API error: ' . $response->status());
        }

        $raw  = $response->json('choices.0.message.content', '{}');
        $data = json_decode($raw, true) ?? [];

        return $this->parseGroqResponse($data, $message);
    }

    // ══════════════════════════════════════════════════════════════
    //  📝  CONSTRUCTION DES PROMPTS (La "Personnalité" de PicMe AI)
    // ══════════════════════════════════════════════════════════════

    private function buildSystemPrompt(?array $listing, string $sellerName, int $strikes): string
    {
        $listingInfo = '';
        if ($listing) {
            $listingInfo = "Produit en vente : \"{$listing['title']}\" à {$listing['price']} FCFA. Catégorie : {$listing['category']}.";
        }

        return <<<PROMPT
Tu es **PicMe AI**, l'assistant intelligent de la plateforme de commerce PicMe 225 en Côte d'Ivoire.
Tu parles français, tu comprends le nouchi et l'argot local ivoirien.
Tu joues le rôle de {$sellerName} quand il est absent, et tu surveilles les échanges pour protéger la plateforme.

CONTEXTE : {$listingInfo}
STRIKES D'ANNULATION DE L'ACHETEUR : {$strikes} (Max = 5 avant blocage)

TES MISSIONS (répondre en JSON strict) :
1. **is_blocked** (bool) : Le message tente-t-il d'échanger un contact, un numéro de téléphone, un email, un réseau social, ou de contourner le paiement PicMe ? (ex: "appelle moi", "on fait ça en dehors", "cash direct")
2. **is_flagged** (bool) : Message suspect mais pas encore bloqué (ton agressif, hors sujet, demande anormale).
3. **content** (string) : Si bloqué, remplace le message par un avertissement professionnel. Sinon, renvoie le message original.
4. **seller_suggestion** (string|null) : Si l'acheteur pose une vraie question (état du produit, disponibilité, lieu de rencontre), génère UNE réponse courte et naturelle que le vendeur pourrait envoyer.
5. **action_link** (string|null) : Si l'acheteur semble prêt à acheter ou à confirmer un RDV, suggère d'activer le paiement séquestre (retourne "TRIGGER_PAYMENT").
6. **lead_score** (string) : Évalue le sérieux de l'acheteur : "HOT" (très intéressé), "WARM" (curieux), "COLD" (indécis), "RISKY" (suspect).
7. **summary** (string|null) : En moins de 20 mots, résume l'intention de l'acheteur pour le vendeur.

RÈGLES ABSOLUES :
- Ne jamais laisser passer un numéro de téléphone, même écrit en lettres ou avec des espaces.
- Ne jamais laisser passer une demande de contact hors plateforme.
- Toujours répondre en JSON valide.
PROMPT;
    }

    private function buildUserPrompt(string $message, array $context): string
    {
        $history = '';
        if (!empty($context['recent_messages'])) {
            $history = "Historique récent :\n";
            foreach (array_slice($context['recent_messages'], -5) as $msg) {
                $history .= "[{$msg['sender']}] : {$msg['message']}\n";
            }
        }

        return <<<PROMPT
{$history}
Nouveau message de l'acheteur :
"{$message}"

Analyse ce message selon tes missions et réponds en JSON.
PROMPT;
    }

    // ══════════════════════════════════════════════════════════════
    //  🔍  NIVEAU 1 : FILTRE REGEX (Fail-Safe ultra-rapide)
    // ══════════════════════════════════════════════════════════════

    public function applyRegexFilter(string $message): array
    {
        $content    = $message;
        $isFlagged  = false;
        $isBlocked  = false;

        // 1. Numéros écrits en chiffres (avec ou sans espaces/tirets)
        $hasDirectNumber = (bool) preg_match(
            '/(?:(?:\+|00)\d{1,3}[\s\-\.]?)?(\d[\s\-\.]?){8,}/',
            $message
        );

        // 2. Nombres épelés en lettres (zéro, sept, quarante...)
        preg_match_all(
            '/(zéro|zero|un|deux|trois|quatre|cinq|six|sept|huit|neuf|dix|onze|douze|vingt|trente|quarante|cinquante|soixante|\d)/i',
            $message, $matches
        );
        $hasHiddenNumber = count($matches[0]) >= 8;

        // 3. Adresses email
        $hasEmail = (bool) preg_match(
            '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
            $message
        );

        // 4. Mots-clés de fraude et contournement
        $hasFraudKeyword = (bool) preg_match(
            '/(num[eé]ro|contact|tel\b|t[eé]l[eé]phone|whatsapp|wa\s|watsap|whtsp|viber|telegram|insta|ig\b|facebook|fb\b|snap|snapchat|cash|esp[eé]ce|liquide|main [aà] main|hors appli|en direct|arr[aâ]nge|sans passer par|on annule|tu annules|appelle.?moi|appele.?moi|mon contact)/i',
            $message
        );

        if ($hasDirectNumber || $hasHiddenNumber || $hasEmail || $hasFraudKeyword) {
            $isFlagged = true;
            $isBlocked = true;
            $content   = "⚠️ [PICME-SHIELD 2026] Message modéré.\nL'échange de coordonnées ou de contacts hors plateforme est interdit pour protéger votre transaction. Votre paiement est sécurisé par PicMe 225.";
        }

        return [
            'content'          => $content,
            'is_blocked'       => $isBlocked,
            'is_flagged'       => $isFlagged,
            'lead_score'       => $isBlocked ? 'RISKY' : 'WARM',
            'seller_suggestion'=> null,
            'action_link'      => null,
            'summary'          => $isBlocked ? 'Tentative de contournement détectée.' : null,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  ✅  PARSER : Résultat Groq → Tableau Normalisé
    // ══════════════════════════════════════════════════════════════

    private function parseGroqResponse(array $data, string $originalMessage): array
    {
        $isBlocked = (bool) ($data['is_blocked'] ?? false);
        $isFlagged = (bool) ($data['is_flagged'] ?? false);

        // Si l'IA dit que le message est propre, on retourne le message original (jamais vide)
        $content = $isBlocked
            ? ($data['content'] ?? "⚠️ [PicMe AI] Message modéré pour sécurité.")
            : $originalMessage;

        return [
            'content'           => $content,
            'is_blocked'        => $isBlocked,
            'is_flagged'        => $isFlagged,
            'lead_score'        => $data['lead_score'] ?? 'WARM',
            'seller_suggestion' => $data['seller_suggestion'] ?? null,
            'action_link'       => $data['action_link'] ?? null,
            'summary'           => $data['summary'] ?? null,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  💬  RÉPONSE AUTOMATIQUE : PicMe AI répond à la place du vendeur
    // ══════════════════════════════════════════════════════════════

    /**
     * Génère une réponse complète au nom du vendeur si celui-ci est absent.
     * Appelé depuis SecureChatController si le vendeur n'a pas répondu depuis X minutes.
     */
    public function generateSellerReply(int $sellerId, int $buyerId, string $buyerMessage, array $listingContext = []): ?string
    {
        if (!$this->aiEnabled || empty($this->groqApiKey)) {
            return null;
        }

        try {
            $seller = \App\Models\User::find($sellerId);
            $sellerName = $seller ? ($seller->display_name ?: $seller->first_name) : 'le vendeur';

            $prompt = "Tu joues le rôle de {$sellerName}, vendeur sur PicMe 225. ";
            if (!empty($listingContext['title'])) {
                $prompt .= "Tu vends : \"{$listingContext['title']}\" à {$listingContext['price']} FCFA. ";
            }
            $prompt .= "Un acheteur t'écrit : \"{$buyerMessage}\". Réponds de manière naturelle, courte (max 2 phrases), amicale mais professionnelle. Ne partage jamais de numéro de téléphone. Encourage à passer commande sur PicMe.";

            $response = Http::withToken($this->groqApiKey)
                ->timeout($this->timeoutSeconds)
                ->post($this->groqEndpoint, [
                    'model'       => $this->groqModel,
                    'temperature' => 0.7,
                    'max_tokens'  => 150,
                    'messages'    => [
                        ['role' => 'system', 'content' => 'Tu es un vendeur ivoirien sympathique sur PicMe 225. Tu réponds en français ou en nouchi selon le ton de l\'acheteur. Sois bref et efficace.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                $reply = $response->json('choices.0.message.content', '');
                return !empty($reply) ? '🤖 ' . trim($reply) : null;
            }
        } catch (\Throwable $e) {
            Log::warning('[PicMe AI] generateSellerReply failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Génère une réponse d'assistance client au nom du support PicMe.
     */
    public function generateSupportReply(string $driverMessage, string $driverName): ?string
    {
        if (!$this->aiEnabled || empty($this->groqApiKey)) {
            return "🤖 [PicMe AI] Mode d'entraînement actif. Nous transmettons votre message à un conseiller humain.";
        }

        try {
            $systemPrompt = <<<PROMPT
Tu es **PicMe Support Agent**, le conseiller virtuel officiel d'assistance de la plateforme PicMe 225 en Côte d'Ivoire.
Tu aides les chauffeurs (comme {$driverName}) qui rencontrent des problèmes ou se posent des questions.
Tu parles français, tu es extrêmement poli, chaleureux et professionnel. Tu comprends le nouchi ivoirien.

Directives pour tes réponses :
- Sois bref et précis (max 3 phrases).
- Donne des conseils pratiques sur le portefeuille (recharges, retraits), l'activation du compte, les courses, ou le planning.
- Si le chauffeur dit bonjour, réponds chaleureusement en disant "Bonjour {$driverName}".
- Si tu ne sais pas résoudre le problème technique ou si le chauffeur est bloqué, dis-lui de contacter le support humain par email à support@picme225.ci ou par WhatsApp au +2250759747444.
- Ne propose jamais de faux rendez-vous physiques.
PROMPT;

            $response = Http::withToken($this->groqApiKey)
                ->timeout($this->timeoutSeconds)
                ->post($this->groqEndpoint, [
                    'model'       => $this->groqModel,
                    'temperature' => 0.7,
                    'max_tokens'  => 200,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $driverMessage],
                    ],
                ]);

            if ($response->successful()) {
                $reply = $response->json('choices.0.message.content', '');
                return !empty($reply) ? trim($reply) : null;
            }
        } catch (\Throwable $e) {
            Log::warning('[PicMe AI Support] generateSupportReply failed: ' . $e->getMessage());
        }

        return "🤖 Bonjour {$driverName}, notre équipe d'assistance PicMe a bien reçu votre demande et un conseiller humain va vous répondre sous peu. Pour toute urgence, contactez-nous sur WhatsApp au +2250759747444.";
    }

    // ══════════════════════════════════════════════════════════════
    //  📊  LEAD SCORING : Évaluer le sérieux d'un acheteur
    // ══════════════════════════════════════════════════════════════

    /**
     * Calcule le "Lead Score" d'un acheteur basé sur son historique de messages.
     * Retourne : HOT / WARM / COLD / RISKY
     */
    public function computeLeadScore(int $buyerId, int $sellerId): string
    {
        // Score basé sur les données de base sans IA (fallback immédiat)
        $buyer = \App\Models\User::find($buyerId);
        if (!$buyer) return 'COLD';

        $strikes = $buyer->cancellation_strikes ?? 0;

        if ($strikes >= 3) return 'RISKY';

        $msgCount = SecureMessage::where('sender_id', $buyerId)
            ->where('receiver_id', $sellerId)
            ->count();

        if ($msgCount >= 5) return 'HOT';
        if ($msgCount >= 2) return 'WARM';
        return 'COLD';
    }

    // ══════════════════════════════════════════════════════════════
    //  🌍  TRADUCTION (Transfrontalière - Bonus Futur)
    // ══════════════════════════════════════════════════════════════

    /**
     * Traduit un message en temps réel (FR <-> EN ou nouchi).
     * Désactivé par défaut pour économiser le quota Groq.
     */
    public function translate(string $message, string $targetLang = 'fr'): string
    {
        if (!$this->aiEnabled || empty($this->groqApiKey)) return $message;

        try {
            $response = Http::withToken($this->groqApiKey)
                ->timeout(3)
                ->post($this->groqEndpoint, [
                    'model'       => $this->groqModel,
                    'temperature' => 0.1,
                    'max_tokens'  => 200,
                    'messages'    => [
                        ['role' => 'system', 'content' => "Tu es un traducteur. Traduis le message suivant en {$targetLang} en gardant le même ton. Retourne seulement la traduction, rien d'autre."],
                        ['role' => 'user',   'content' => $message],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', $message);
            }
        } catch (\Throwable $e) {
            // Silencieux : on retourne le message original
        }

        return $message;
    }

    // ══════════════════════════════════════════════════════════════
    //  ℹ️  STATUT : L'IA est-elle active ?
    // ══════════════════════════════════════════════════════════════

    public function isEnabled(): bool
    {
        return $this->aiEnabled && !empty($this->groqApiKey);
    }

    public function getMode(): string
    {
        return $this->isEnabled() ? 'AI_ACTIVE (Groq/Llama3)' : 'REGEX_FALLBACK';
    }
}

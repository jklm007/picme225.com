<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Models\MarketplaceListing;
use App\Models\WhatsappGroup;
use App\Models\WhatsappUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessWhatsappBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $groupId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $groupId)
    {
        $this->userId = $userId;
        $this->groupId = $groupId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lockKey = "process_whatsapp_batch_{$this->groupId}_{$this->userId}";
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 120);

        if (!$lock->get()) {
            \Illuminate\Support\Facades\Log::warning("Batch job for user {$this->userId} in group {$this->groupId} is already running. Skipping.");
            return;
        }

        try {
            // Retrieve all unprocessed messages for this user in this group
            $messages = WhatsappMessage::where('whatsapp_user_id', $this->userId)
                ->where('group_id', $this->groupId)
                ->where('batch_processed', false)
                ->get();

            if ($messages->isEmpty()) {
                return;
            }

            // Check Idempotency: Prevent duplicating the same listing
            $primaryMessageId = $messages->first()->id;
            $existingListing = MarketplaceListing::where('whatsapp_message_id', $primaryMessageId)->exists();
            if ($existingListing) {
                \Illuminate\Support\Facades\Log::info("Idempotency triggered: Listing already exists for message ID {$primaryMessageId}");
                WhatsappMessage::whereIn('id', $messages->pluck('id'))->update([
                    'batch_processed' => true,
                    'status' => 'ignored',
                    'error_log' => 'Duplicate prevented by idempotency check'
                ]);
                return;
            }

            // Mark as processed immediately to avoid concurrent job collision
            WhatsappMessage::whereIn('id', $messages->pluck('id'))->update([
                'batch_processed' => true,
                'status' => 'processing'
            ]);

            $group = WhatsappGroup::where('group_id', $this->groupId)->first();
            $user = WhatsappUser::find($this->userId);

            $combinedText = '';
            $allMedias = [];
            $messageIds = [];

            foreach ($messages as $msg) {
                $combinedText .= $msg->content . "\n";
                if (!empty($msg->medias)) {
                    $allMedias = array_merge($allMedias, $msg->medias);
                }
                $messageIds[] = $msg->id;
            }

            // Validation: ONLY process messages that contain at least one photo.
            // Text-only messages are ignored — a photo is mandatory for a listing.
            $hasMeaningfulText = strlen(trim(str_replace(['[Média]', '[Message non texte]'], '', $combinedText))) > 5;
            $hasMedia = count($allMedias) > 0;

            if (!$hasMedia) {
                WhatsappMessage::whereIn('id', $messageIds)->update([
                    'status'    => 'ignored',
                    'error_log' => 'Ignoré: Aucune photo. Seules les annonces avec photo sont traitées.'
                ]);
                return;
            }

            // If images are present but text is short/empty, let the AI generate from images alone
            if (!$hasMeaningfulText) {
                $combinedText = '[Annonce sans texte - analyse uniquement les images pour créer une annonce complète]';
            }

            // AI Processing
            // Use AiFailoverService to handle models, cooldowns, and admin alerts
            $aiService = app(\App\Services\AiFailoverService::class);

            // Load real categories from DB for the AI prompt
            $parentCats = \App\Models\MarketplaceCategory::whereNull('parent_id')
                ->with('children')
                ->orderBy('order_index')
                ->get();

            $categoryList = '';
            $subCategoryList = '';
            foreach ($parentCats as $pCat) {
                $categoryList .= "  - {$pCat->name} ({$pCat->label})\n";
                foreach ($pCat->children as $child) {
                    $subCategoryList .= "  - {$child->name} ({$child->label}) → parent: {$pCat->name}\n";
                }
            }

            $systemPrompt = "Tu es un assistant spécialisé dans l'extraction de données pour une marketplace. Ton rôle est d'analyser un ensemble de textes et de photos envoyés par un utilisateur sur WhatsApp.
Tu dois déterminer s'il s'agit d'une annonce commerciale (vente, location, services).
L'utilisateur peut envoyer plusieurs messages et photos, mais tu dois OBLIGATOIREMENT créer UNE SEULE ANNONCE qui représente l'article principal. N'invente jamais d'autres annonces ou de démonstrations.

RÈGLE ABSOLUE ANTI-SPAM (TRÈS IMPORTANT) :
Si l'utilisateur envoie une ou plusieurs photos sans texte (ou avec le texte '[Annonce sans texte - analyse uniquement les images pour créer une annonce complète]'), tu dois vérifier s'il y a une INTENTION CLAIRE DE VENTE sur l'image (ex: un prix écrit, un texte promotionnel, une affiche publicitaire).
S'il s'agit juste d'une photo d'un objet ordinaire sans contexte (ex: un téléphone posé sur une table, des chaussures), tu DOIS répondre avec \"is_commercial\": false. N'invente JAMAIS un prix ou une annonce si l'image n'est pas explicitement une publicité ou une offre de vente.

RÈGLE SUR LES PRIX :
N'invente JAMAIS un prix. Si l'annonce est légitime mais qu'aucun prix n'est mentionné (ni dans le texte ni sur l'image), fixe la valeur de \"price\" à 0 (zéro).

TRADUCTION OBLIGATOIRE : Si le texte original est en anglais, dioula, nouchi, le titre et la description doivent être en bon français.

Tu dois évaluer TOUTES les photos. Chaque image t'est passée avec un index [Image 1], [Image 2], etc.
Indique dans `image_indices` la liste des numéros d'images qui montrent l'article. Si une image est floue ou inutile, ne l'inclus pas.
Si tu détectes une contradiction (ex: texte dit 'Toyota' mais l'image montre 'Mercedes'), corrige la marque et ajoute une alerte dans `brand_mismatch_alert`.

Catégories disponibles (utilise EXACTEMENT ces valeurs) :
{$categoryList}

Sous-catégories disponibles (utilise EXACTEMENT ces valeurs) :
{$subCategoryList}

Tu dois renvoyer UNIQUEMENT UN SEUL OBJET JSON (pas de tableau). N'ajoute AUCUN texte avant ou après.
Format JSON attendu :
{
  \"is_commercial\": true,
  \"category\": \"string\",
  \"sub_category\": \"string\",
  \"type\": \"string (SALE, RENT, SEARCH, SERVICE)\",
  \"title\": \"string\",
  \"description\": \"string\",
  \"price\": 0,
  \"price_unit\": \"FCFA\",
  \"brand\": \"string\",
  \"model\": \"string\",
  \"year\": \"string\",
  \"location_city\": \"string\",
  \"owner_name\": \"string\",
  \"confidence_score\": 80,
  \"image_indices\": [1, 2],
  \"brand_mismatch_alert\": null
}";

            $contentString = $systemPrompt . "

TEXTE DE L'UTILISATEUR:
" . $combinedText;
            
            $payload = [
                'messages' => [
                    ['role' => 'user', 'content' => $contentString]
                ],
                'temperature' => 0.0,
                'response_format' => ['type' => 'json_object']
            ];

            // Make the unified API call with failover
            $responseArray = $aiService->executeWithFailover($payload, 'ProcessWhatsappBatchJob (User: ' . $this->userId . ')');

            $aiContent = $responseArray['choices'][0]['message']['content'] ?? '{}';
            $aiContent = trim(preg_replace('/^```json\s*(.*?)\s*```$/is', '$1', $aiContent));
            
            $data = json_decode($aiContent, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                throw new \Exception('Invalid JSON from AI (expected Object): ' . json_last_error_msg() . " -> " . $aiContent);
            }

            // Unwrap if Groq wrapped it inside an array or nested object
            if (isset($data[0])) {
                $data = $data[0];
            } else {
                foreach ($data as $key => $val) {
                    if (is_array($val) && isset($val[0]) && is_array($val[0])) {
                        $data = $val[0];
                        break;
                    }
                }
            }

            if (!isset($data['is_commercial']) || $data['is_commercial'] === false) {
                Log::info("Message ignoré, jugé non-commercial par l'IA", ['userId' => $this->userId, 'groupId' => $this->groupId]);
                WhatsappMessage::whereIn('id', $messageIds)->update([
                    'status' => 'ignored',
                    'error_log' => 'Message non commercial selon l\'IA',
                ]);
                return;
            }

            // Mode insertion configuré dans le groupe
            $insertMode = $group ? $group->insert_mode : 'ACTIVE';
            $listingStatus = ($insertMode === 'PENDING_VALIDATION') ? 'PENDING_VALIDATION' : 'ACTIVE';

            // Upload ALL images to the web pod via internal HTTP endpoint
            $finalMediasUrls = [];
            $webPodUrl    = env('INTERNAL_WEB_URL', 'http://laravel-service');
            $internalSecret = env('INTERNAL_API_SECRET', 'picme225-internal-secret');
            foreach ($allMedias as $index => $mediaBase64) {
                if (!empty($mediaBase64)) {
                    try {
                        $uploadResp = Http::withHeaders([
                            'X-Internal-Secret' => $internalSecret,
                        ])->timeout(30)->post("{$webPodUrl}/api/user/internal/upload-image", [
                            'data' => $mediaBase64,
                        ]);
                        if ($uploadResp->successful()) {
                            $finalMediasUrls[$index + 1] = $uploadResp->json('url');
                        } else {
                            Log::warning('Image upload to web pod failed', [
                                'index' => $index,
                                'status' => $uploadResp->status(),
                                'body' => substr($uploadResp->body(), 0, 200),
                            ]);
                        }
                    } catch (\Exception $uploadEx) {
                        Log::warning('Image upload exception: ' . $uploadEx->getMessage());
                    }
                }
            }

            $confidence = $data['confidence_score'] ?? 0;
            $adminAlert = $data['brand_mismatch_alert'] ?? null;
            
            // Assign mapped images
            $listingMedias = [];
            if (isset($data['image_indices']) && is_array($data['image_indices'])) {
                foreach ($data['image_indices'] as $imgIndex) {
                    if (isset($finalMediasUrls[$imgIndex])) {
                        $listingMedias[] = $finalMediasUrls[$imgIndex];
                    }
                }
            }
            
            // Fallback: If AI gave no valid indices, attach ALL images (better than no images)
            if (empty($listingMedias)) {
                $listingMedias = array_values($finalMediasUrls);
            }
            
            $coverImage = !empty($listingMedias) ? $listingMedias[0] : null;
            
            // Determine Category
            $category = $data['category'] ?? ($group ? $group->default_category : 'AUTRE');
            $subCategory = $data['sub_category'] ?? null;
            
            $catModel = \App\Models\MarketplaceCategory::where('name', $category)
                ->orWhere('label', $category)
                ->first();
            if (!$catModel) {
                $catModel = \App\Models\MarketplaceCategory::where('name', 'AUTRE')->first();
            }
            $categoryId = $catModel ? $catModel->id : null;

            $listing = new MarketplaceListing();
            $listing->user_id = $user->id;
            $listing->owner_phone = $user->phone_number;
            $listing->owner_name = $data['owner_name'] ?? $user->name;
            $listing->whatsapp_message_id = !empty($messageIds) ? $messageIds[0] : null;
            $listing->type = $data['type'] ?? 'SALE';
            $listing->category = $category;
            $listing->sub_category = $subCategory;
            $listing->title = $data['title'] ?? 'Annonce';
            $listing->description = $data['description'] ?? '';
            $listing->price = (float) ($data['price'] ?? 0);
            $listing->price_unit = $data['price_unit'] ?? 'FCFA';
            $listing->brand = $data['brand'] ?? null;
            $listing->model = $data['model'] ?? null;
            $listing->year = $data['year'] ?? null;
            $listing->location_city = $data['location_city'] ?? null;
            $listing->status = $listingStatus;
            $listing->source = 'whatsapp';
            $listing->ai_confidence_score = $confidence;
            $listing->cover_image = $coverImage;
            $listing->images = $listingMedias;
            
            $metadata = $data['metadata'] ?? [];
            if (!is_array($metadata)) {
                $metadata = [];
            }
            if ($adminAlert) {
                $metadata['ai_brand_mismatch_alert'] = $adminAlert;
            }
            $listing->metadata = $metadata;

            $listing->save();

            // Notify User via WhatsApp (per listing)
            $this->notifyUser($user->phone_number, $listing, $user);

            // Notify Admin for 1-click validation (only if listing requires validation)
            if ($listing->status === 'PENDING_VALIDATION') {
                $this->notifyAdminForValidation($listing);
            }

            // Auto-post to Social Media if listing goes directly ACTIVE
            if ($listing->status === 'ACTIVE') {
                \App\Jobs\PostToSocialMediaJob::dispatch($listing->id);
            }

            // Log for metrics
            \Illuminate\Support\Facades\Log::info("Nouvelle annonce créée depuis WhatsApp", [
                'listing_id' => $listing->id,
                'user_phone' => $user->phone_number
            ]);

            WhatsappMessage::whereIn('id', $messageIds)->update([
                'status' => 'success',
                'error_log' => null,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp Batch Job Error: ' . $e->getMessage());
            WhatsappMessage::whereIn('id', $messageIds ?? [])->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);
        } finally {
            $lock->release();
        }
    }

    /**
     * Resolve Evolution API connection settings.
     * Falls back through config → env → hardcoded defaults so the job always works
    /**
     * Resolve Evolution API connection settings.
     * Uses env() directly (with hardcoded fallbacks) because config() is unreliable
     * inside the K8s worker pod when the config cache has not been built.
     */
    private function getEvoConfig(): array
    {
        // Read ENV directly — DO NOT use config() here; the worker pod has no compiled config cache.
        $url  = env('EVOLUTION_API_URL',  'http://evolution-api-service:8080');
        $key  = env('EVOLUTION_API_KEY',  'picme225-evolution-secret-key');
        $inst = env('EVOLUTION_INSTANCE', 'picme_whatsapp');
        return [$url, $key, $inst];
    }

    /**
     * Resolve the best WhatsApp JID to use for sending a message to a user.
     * WhatsApp's new LID (Linked Identity) protocol requires using the @lid identifier
     * for contacts who use recent versions of WhatsApp. We look up recent messages
     * from this user in our group chats to find their LID, which actually delivers.
     * Falls back to standard @s.whatsapp.net format if LID is not found.
     */
    private function resolveWhatsappJid(string $phoneNumber): string
    {
        // Strip any existing suffixes
        $rawPhone = str_replace(['@s.whatsapp.net', '@lid', '@g.us'], '', $phoneNumber);

        // Fallback: standard phone format
        if (strlen($rawPhone) >= 14) {
            return $rawPhone . '@lid';
        }
        return $rawPhone . '@s.whatsapp.net';
    }

    private function notifyUser($whatsappId, $listing, $user)
    {
        [$evoApiUrl, $evoApiKey, $instanceName] = $this->getEvoConfig();

        if (!$evoApiUrl || !$evoApiKey) {
            Log::warning('Evolution API not configured for notifications.');
            return;
        }

        $appPublicUrl = rtrim(env('APP_PUBLIC_URL', 'https://www.picme225.site'), '/');
        $listingUrl   = $appPublicUrl . '/marketplace/' . $listing->id;

        if ($listing->status === 'PENDING_VALIDATION') {
            $messageText = "🆕 *Nouvelle annonce en cours de validation*\n\n";
            $messageText .= "Votre annonce a bien été reçue et est actuellement en cours de validation par notre équipe. Elle sera publiée dès qu'elle aura été approuvée.\n\n";
            $messageText .= "📦 *{$listing->title}*\n";
            $messageText .= "🏷️ Catégorie : {$listing->category}\n";
            $messageText .= "💰 Prix : " . number_format((float)$listing->price, 0, ',', ' ') . " {$listing->price_unit}\n";
            if ($listing->location_city) {
                $messageText .= "📍 Lieu : {$listing->location_city}\n";
            }
        } else {
            $messageText = "✅ *Votre annonce a été validée*\n\n";
            $messageText .= "Félicitations ! Votre annonce a été validée et est maintenant visible par tous les utilisateurs sur PickMe225.\n\n";
            $messageText .= "🔗 Voir mon annonce : {$listingUrl}\n";
            $messageText .= "🔄 Partager mon annonce : {$listingUrl}";
        }

        // Always use phone_number@s.whatsapp.net for private messages — this format works reliably.
        // LID (@lid) stored in whatsapp_id causes ERROR on private sends.
        $rawPhone = '';
        if ($user && $user->phone_number) {
            $rawPhone = preg_replace('/[^0-9]/', '', $user->phone_number);
        } else {
            $rawPhone = preg_replace('/[^0-9]/', '', str_replace(['@s.whatsapp.net', '@lid', '@g.us'], '', $whatsappId));
        }
        $sendToJid = $rawPhone . '@s.whatsapp.net';

        try {
            $resp = Http::withHeaders(['apikey' => $evoApiKey])
                ->timeout(15)
                ->post("{$evoApiUrl}/message/sendText/{$instanceName}", [
                    'number'  => $sendToJid,
                    'text'    => $messageText,
                    'options' => [
                        'delay'    => 1200,
                        'presence' => 'composing',
                    ],
                ]);

            if ($resp->successful()) {
                Log::info('notifyUser: message envoyé avec succès', [
                    'listing_id' => $listing->id,
                    'to'         => $sendToJid,
                    'status'     => $resp->status(),
                ]);
            } else {
                Log::warning('notifyUser: échec envoi message', [
                    'listing_id' => $listing->id,
                    'to'         => $sendToJid,
                    'status'     => $resp->status(),
                    'body'       => substr($resp->body(), 0, 500),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('notifyUser exception: ' . $e->getMessage());
        }
    }

    private function notifyAdminForValidation($listing)
    {
        [$evoApiUrl, $evoApiKey, $instanceName] = $this->getEvoConfig();

        // Admin phone: 22559747444 (format validé par WhatsApp)
        $adminPhone = '22559747444@s.whatsapp.net';

        if (!$evoApiUrl || !$evoApiKey) return;

        $logoPath = public_path('logo.png');
        $base64Logo = '';
        if (file_exists($logoPath)) {
            $base64Logo = base64_encode(file_get_contents($logoPath));
        }

        $msg = "🛡️ *Nouvelle annonce à valider* 🛡️\n\n";
        $msg .= "Une nouvelle annonce a été soumise par un utilisateur et est en attente de validation :\n\n";
        $msg .= "📦 *{$listing->title}*\n";
        $msg .= "👤 Vendeur: {$listing->owner_name} ({$listing->owner_phone})\n";
        $msg .= "🏷️ Catégorie: {$listing->category}\n";
        $msg .= "💰 Prix: " . number_format((float)$listing->price, 0, ',', ' ') . " {$listing->price_unit}\n\n";
        $msg .= "➡️ Répondez *OUI {$listing->id}* pour publier.\n";
        $msg .= "➡️ Répondez *NON {$listing->id}* pour rejeter.";

        try {
            $resp = Http::withHeaders(['apikey' => $evoApiKey])
                ->timeout(15)
                ->post("{$evoApiUrl}/message/sendText/{$instanceName}", [
                    'number' => $adminPhone,
                    'options' => [
                        'delay' => 1200,
                        'presence' => 'composing'
                    ],
                    'text' => $msg,
                ]);
            
            Log::info('notifyAdmin: résultat envoi', [
                'listing_id' => $listing->id,
                'to'         => $adminPhone,
                'status'     => $resp->status(),
            ]);
        } catch (\Exception $e) {
            Log::error('notifyAdmin exception: ' . $e->getMessage());
        }
    }
}

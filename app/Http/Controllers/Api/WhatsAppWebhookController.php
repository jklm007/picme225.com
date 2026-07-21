<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsappUser;
use App\Models\WhatsappMessage;
use App\Models\WhatsappGroup;
use App\Jobs\ProcessWhatsappBatchJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\MarketplaceListing;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Evolution API (WhatsApp)
     */
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info("WEBHOOK RECEIVED", $payload);
            
            // We only care about new messages
            if (($payload['event'] ?? '') !== 'messages.upsert') {
                return response()->json(['status' => 'ignored', 'reason' => 'not a message event']);
            }

            $messageData = $payload['data']['message'] ?? [];
            if (empty($messageData)) {
                return response()->json(['status' => 'ignored', 'reason' => 'no message data']);
            }
            
            $key = $payload['data']['key'] ?? [];
            if ($key['fromMe'] ?? false) {
                return response()->json(['status' => 'ignored', 'reason' => 'from me']);
            }

            $remoteJid = $key['remoteJid'] ?? '';
            $isGroup = strpos($remoteJid, '@g.us') !== false;

            // Extract Text Content early to check for commands
            $textContent = '';
            if (isset($messageData['conversation'])) {
                $textContent = $messageData['conversation'];
            } elseif (isset($messageData['extendedTextMessage']['text'])) {
                $textContent = $messageData['extendedTextMessage']['text'];
            } elseif (isset($messageData['imageMessage']['caption'])) {
                $textContent = $messageData['imageMessage']['caption'];
            } elseif (isset($messageData['videoMessage']['caption'])) {
                $textContent = $messageData['videoMessage']['caption'];
            }

            $participant = $payload['data']['participant'] ?? $key['participant'] ?? null;
            $participantAlt = $payload['data']['participantAlt'] ?? $key['participantAlt'] ?? null;

            $senderJid = $participantAlt ?? $participant;
            if (empty($senderJid)) {
                $pushName = $payload['data']['pushName'] ?? '';
                if (strpos($pushName, '@s.whatsapp.net') !== false || strpos($pushName, '@lid') !== false) {
                    $senderJid = $pushName;
                }
            }
            if (empty($senderJid)) {
                $senderJid = $remoteJid;
            }

            if ($isGroup && (strpos($senderJid, '@s.whatsapp.net') === false && strpos($senderJid, '@lid') === false)) {
                return response()->json(['status' => 'ignored', 'reason' => 'group message without valid participant sender']);
            }

            $phoneNumber = null;
            if ($participantAlt) {
                $phoneNumber = str_replace(['@s.whatsapp.net', '@lid'], '', $participantAlt);
            } elseif ($participant && strpos($participant, '@s.whatsapp.net') !== false) {
                $phoneNumber = str_replace(['@s.whatsapp.net', '@lid'], '', $participant);
            } else {
                // Nous n'avons qu'un LID (ex: 124086621593622@lid). Il faut retrouver le vrai numéro.
                $lid = $participant ?? $senderJid;
                try {
                    $pdo = new \PDO(
                        'pgsql:host=postgres-service;port=5432;dbname=picme_db',
                        'picme_user',
                        'secret_password',
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                    );
                    $stmt = $pdo->prepare(
                        "SELECT key->>'participantAlt' as phone 
                         FROM evolution.\"Message\"
                         WHERE key->>'participant' = :lid
                           AND key->>'participantAlt' IS NOT NULL
                         ORDER BY \"messageTimestamp\" DESC
                         LIMIT 1"
                    );
                    $stmt->execute([':lid' => $lid]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($row && !empty($row['phone'])) {
                        $phoneNumber = str_replace(['@s.whatsapp.net', '@lid'], '', $row['phone']);
                        Log::info("Webhook: LID {$lid} résolu en numéro {$phoneNumber}");
                    }
                } catch (\Exception $ex) {
                    Log::warning('Webhook LID resolve error: ' . $ex->getMessage());
                }

                // Fallback si on ne trouve rien dans la BD
                if (!$phoneNumber) {
                    $phoneNumber = str_replace(['@s.whatsapp.net', '@lid'], '', $senderJid);
                    Log::warning("Webhook: Impossible de résoudre le LID {$lid}, utilisation du LID comme numéro: {$phoneNumber}");
                }
            }
            $pushName = $payload['data']['pushName'] ?? 'Utilisateur WhatsApp';

            // Check if user is blacklisted
            $whatsappUser = WhatsappUser::where('phone_number', $phoneNumber)->first();
            if ($whatsappUser && $whatsappUser->is_blacklisted) {
                return response()->json(['status' => 'ignored', 'reason' => 'user is blacklisted']);
            }

            // Check for bot commands (private messages only — strict matching)
            // Only exact valid commands trigger an automated reply:
            // - "MES ANNONCES"
            // - "RENOUVELER 123" (number required)
            // - "STOP 123" / "SUPPRIMER 123" (number required)
            // - "OUI 123" / "NON 123" (admin only, number required)
            $commandMatch = strtoupper(trim($textContent));
            $isValidCommand = (
                $commandMatch === 'MES ANNONCES' ||
                preg_match('/^RENOUVELER\s+\d+$/i', $commandMatch) ||
                preg_match('/^(STOP|SUPPRIMER)\s+\d+$/i', $commandMatch) ||
                preg_match('/^(OUI|NON)\s+\d+$/i', $commandMatch)
            );
            if ($isValidCommand && !$isGroup) {
                return $this->handleCommand($commandMatch, $phoneNumber, $senderJid);
            }
            
            if (!$isGroup) {
                return response()->json(['status' => 'ignored', 'reason' => 'not a group message and not a command']);
            }
            
            // 1. Vérification Whitelist (Groupes Autorisés)
            $monitoredGroup = WhatsappGroup::where('group_id', $remoteJid)->where('is_active', true)->first();
            if (!$monitoredGroup) {
                return response()->json(['status' => 'ignored', 'reason' => 'group not in whitelist']);
            }

            // Extract Media

            // Extract the message ID to prevent duplicate processing
            $messageId = $key['id'] ?? null;
            if ($messageId) {
                $exists = WhatsappMessage::where('message_id', $messageId)->exists();
                if ($exists) {
                    Log::info("Webhook ignored: Duplicate message ID {$messageId}");
                    return response()->json(['status' => 'ignored', 'reason' => 'duplicate message']);
                }
            }

            // Extract Media
            $medias = [];
            if (isset($payload['data']['messageType']) && in_array($payload['data']['messageType'], ['imageMessage', 'videoMessage', 'documentMessage'])) {
                $base64 = $payload['data']['message']['base64'] ?? $payload['data']['base64'] ?? $payload['base64'] ?? null;
                
                if (!$base64) {
                    try {
                        $instance = $payload['instance'] ?? 'picme_whatsapp';
                        // Use internal Kubernetes service URL because the pod cannot resolve the external domain
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'apikey' => config('services.evolution.key', env('EVOLUTION_API_KEY'))
                        ])->post("http://evolution-api-service:8080/chat/getBase64FromMediaMessage/{$instance}", [
                            'message' => $payload['data']
                        ]);
                        
                        if ($response->successful()) {
                            $base64 = $response->json('base64');
                        } else {
                            \Illuminate\Support\Facades\Log::error("Failed to fetch base64 internally. Status: " . $response->status() . " Body: " . $response->body());
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Exception fetching base64: " . $e->getMessage());
                    }
                }

                if ($base64) {
                    $msgType = $payload['data']['messageType'] ?? 'imageMessage';
                    $mime = $payload['data']['message'][$msgType]['mimetype'] ?? ($msgType === 'videoMessage' ? 'video/mp4' : 'image/jpeg');
                    $medias[] = "data:{$mime};base64," . $base64;
                }
            }

            if (empty(trim($textContent)) && !empty($medias)) {
                $textContent = '[Média]';
            } elseif (empty(trim($textContent))) {
                $textContent = '[Message non texte]';
            }

            // 1. Find or create the WhatsappUser
            $whatsappUser = WhatsappUser::firstOrCreate(
                ['phone_number' => $phoneNumber],
                [
                    'whatsapp_id' => $senderJid,
                    'name' => $pushName,
                ]
            );

            // 2. Save the WhatsappMessage with batch_processed = false
            $whatsappMessage = WhatsappMessage::create([
                'whatsapp_user_id' => $whatsappUser->id,
                'group_id' => $remoteJid,
                'message_id' => $messageId,
                'content' => $textContent,
                'medias' => $medias,
                'status' => 'pending',
                'batch_processed' => false,
            ]);

            // 3. Dispatch the Batch Job with a 60-second delay to group burst messages and multiple photos
            // Lock key prevents dispatching hundreds of jobs if user sends multiple messages quickly
            $lockKey = "whatsapp_batch_{$remoteJid}_{$whatsappUser->id}";
            if (!Cache::has($lockKey)) {
                ProcessWhatsappBatchJob::dispatch($whatsappUser->id, $remoteJid)->onConnection('redis')->delay(now()->addSeconds(60));
                Cache::put($lockKey, true, now()->addSeconds(60));
            }

            return response()->json(['status' => 'success', 'message_id' => $whatsappMessage->id, 'batch_job' => 'dispatched']);

        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error: ' . $e->getMessage(), ['payload' => $request->all()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function handleCommand($command, $phoneNumber, $senderJid)
    {
        $command = strtoupper(trim($command));
        $replyText = "";

        if ($command === 'MES ANNONCES') {
            $listings = MarketplaceListing::where('owner_phone', $phoneNumber)
                ->whereIn('status', ['ACTIVE', 'PENDING_VALIDATION'])
                ->get();

            if ($listings->isEmpty()) {
                $replyText = "Vous n'avez aucune annonce active pour le moment.";
            } else {
                $replyText = "Voici vos annonces actives :\n\n";
                foreach ($listings as $listing) {
                    $url = url('/marketplace/' . $listing->id);
                    $replyText .= "- {$listing->title} ({$listing->status})\n  Lien: {$url}\n  (Expire le: {$listing->created_at->addDays(90)->format('d/m/Y')})\n\n";
                }
            }
        } elseif (preg_match('/^RENOUVELER\s+(\d+)$/i', $command, $matches)) {
            $id = $matches[1];
            $listing = MarketplaceListing::where('id', $id)->where('owner_phone', $phoneNumber)->first();
            if ($listing) {
                // Renouveler : on met à jour la date de création pour redonner 90 jours
                $listing->created_at = now();
                $listing->status = 'ACTIVE';
                $listing->save();
                $replyText = "✅ Votre annonce '{$listing->title}' a été renouvelée pour 90 jours !";
            } else {
                $replyText = "❌ Annonce introuvable ou vous n'êtes pas le propriétaire.";
            }
        } elseif (preg_match('/^(STOP|SUPPRIMER)\s+(\d+)$/i', $command, $matches)) {
            $id = $matches[2];
            $listing = MarketplaceListing::where('id', $id)->where('owner_phone', $phoneNumber)->first();
            if ($listing) {
                $listing->status = 'INACTIVE';
                $listing->save();
                $replyText = "✅ Votre annonce '{$listing->title}' a été désactivée et retirée de la plateforme.";
            } else {
                $replyText = "❌ Annonce introuvable ou vous n'êtes pas le propriétaire.";
            }
        } elseif (preg_match('/^(OUI|NON)\s+(\d+)$/i', $command, $matches)) {
            // ADMIN 1-CLICK VALIDATION
            if ($phoneNumber === '2250759747444') {
                $action = strtoupper($matches[1]);
                $id = $matches[2];
                $listing = MarketplaceListing::find($id);
                
                if ($listing) {
                    if ($action === 'OUI') {
                        $listing->status = 'ACTIVE';
                        $listing->save();
                        $replyText = "✅ Annonce #{$id} ('{$listing->title}') validée et publiée !";

                        // Notify User that the listing is published
                        $appPublicUrl = rtrim(env('APP_PUBLIC_URL', 'https://www.picme225.site'), '/');
                        $listingUrl   = $appPublicUrl . '/marketplace/' . $listing->id;
                        $ownerName = $listing->owner_name ?? 'Utilisateur';

                        $messageText = "✅ *Votre annonce a été validée*\n\n";
                        $messageText .= "Félicitations ! Votre annonce a été validée et est maintenant visible par tous les utilisateurs sur PickMe225.\n\n";
                        $messageText .= "🔗 Voir mon annonce : {$listingUrl}\n";
                        $messageText .= "🔄 Partager mon annonce : {$listingUrl}";

                        $whatsappUser = \App\Models\WhatsappUser::where('phone_number', $listing->owner_phone)->first();
                        $rawPhone = preg_replace('/[^0-9]/', '', $whatsappUser ? $whatsappUser->phone_number : $listing->owner_phone);
                        $userJid = $rawPhone . '@s.whatsapp.net';

                        $evoApiUrl    = config('services.evolution.url') ?: env('EVOLUTION_API_URL', 'http://evolution-api-service:8080');
                        $evoApiKey    = config('services.evolution.key') ?: env('EVOLUTION_API_KEY', 'picme225-evolution-secret-key');
                        $instanceName = config('services.evolution.instance', 'picme_whatsapp');

                        if ($evoApiUrl && $evoApiKey) {
                            $resp = \Illuminate\Support\Facades\Http::withHeaders(['apikey' => $evoApiKey])
                                ->timeout(15)
                                ->post("{$evoApiUrl}/message/sendText/{$instanceName}", [
                                    'number'  => $userJid,
                                    'text'    => $messageText,
                                    'options' => [
                                        'delay'    => 1200,
                                        'presence' => 'composing',
                                    ],
                                ]);
                            Log::info('notifyUser (OUI command) envoyé', [
                                'listing_id' => $listing->id,
                                'to'         => $userJid,
                                'status'     => $resp->status(),
                            ]);
                        }

                    } else {
                        $listing->status = 'REJECTED';
                        $listing->save();
                        $replyText = "❌ Annonce #{$id} ('{$listing->title}') rejetée.";
                    }
                } else {
                    $replyText = "⚠️ Annonce #{$id} introuvable.";
                }
            } else {
                $replyText = "❌ Commande non autorisée.";
            }
        } else {
            // This branch should never be reached with the strict command detection above.
            // No reply sent — just log it silently.
            Log::warning('handleCommand: unrecognized command format', ['command' => $command, 'phone' => $phoneNumber]);
            return response()->json(['status' => 'success', 'command_handled' => false]);
        }

        // Send reply via Evolution API
        $evoApiUrl = config('services.evolution.url');
        $evoApiKey = config('services.evolution.key');
        $instanceName = config('services.evolution.instance', 'picme225');

        if ($evoApiUrl && $evoApiKey) {
            Http::withHeaders(['apikey' => $evoApiKey])
                ->post("{$evoApiUrl}/message/sendText/{$instanceName}", [
                    'number' => $senderJid,
                    'text' => $replyText,
                ]);
        }

        return response()->json(['status' => 'success', 'command_handled' => true]);
    }
}

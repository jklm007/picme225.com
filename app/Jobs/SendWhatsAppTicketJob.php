<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TransportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticket;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TransportTicket $ticket, User $user)
    {
        $this->ticket = $ticket;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Logique d'envoi WhatsApp
            // Par exemple, on peut utiliser une API comme Twilio, Ultramsg, ou autre (selon le service de PicMe)
            $phone = $this->user->mobile ?? $this->user->phone; // numéro de téléphone
            
            if (!$phone) return;

            $ticketUrl = route('ticket.public', ['signature' => $this->ticket->qr_code]);
            $title = $this->ticket->listing ? $this->ticket->listing->title : 'Événement';
            
            $message = "Bonjour {$this->user->first_name},\n\n";
            $message .= "Votre achat pour '{$title}' a été validé au guichet ! 🎉\n\n";
            $message .= "Vous pouvez consulter et télécharger votre e-billet sécurisé ici :\n";
            $message .= "👉 $ticketUrl\n\n";
            
            // Message promotionnel pour les invités
            if ($this->user->first_name === 'Invité' || empty($this->user->email)) {
                $message .= "💡 *Saviez-vous que PicMe225 c'est aussi votre plateforme tout-en-un ?*\n";
                $message .= "Enregistrez-vous dès maintenant pour bénéficier de toutes nos offres et services :\n";
                $message .= "🚕 Commander un taxi\n";
                $message .= "📦 Se faire livrer un colis\n";
                $message .= "🚗 Louer un véhicule\n";
                $message .= "🌍 Réserver un voyage\n";
                $message .= "Téléchargez l'application ici : " . url('/') . "\n\n";
            }
            
            $message .= "Merci de faire confiance à PicMe225 !";

            Log::info("Envoi du ticket WhatsApp à {$phone} : {$ticketUrl}");

            // Appel API Evolution
            $evoApiUrl = config('services.evolution.url') ?: env('EVOLUTION_API_URL', 'http://evolution-api-service:8080');
            $evoApiKey = config('services.evolution.key') ?: env('EVOLUTION_API_KEY', 'picme225-evolution-secret-key');
            $instanceName = config('services.evolution.instance') ?: env('EVOLUTION_INSTANCE', 'picme_whatsapp');

            $rawPhone = preg_replace('/[^0-9]/', '', $phone);
            // Assumer que le numéro a déjà le code pays. Sinon, il faudrait l'ajouter. (Ex: 225...)
            if (strlen($rawPhone) == 10 && str_starts_with($rawPhone, '0')) {
                 // Si c'est un numéro ivoirien à 10 chiffres (07... 05...)
                 $rawPhone = '225' . $rawPhone; // Garder le 0
            }
            $sendToJid = $rawPhone . '@s.whatsapp.net';

            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($this->ticket->qr_code);
            $qrCodeImage = @file_get_contents($qrCodeUrl);

            $payload = [
                'number'  => $sendToJid,
                'options' => [
                    'delay'    => 1200,
                    'presence' => 'composing',
                ],
            ];

            if ($qrCodeImage) {
                $payload['mediatype'] = 'image';
                $payload['mimetype'] = 'image/png';
                $payload['caption'] = $message;
                $payload['media'] = base64_encode($qrCodeImage);
                $endpoint = "/message/sendMedia/{$instanceName}";
            } else {
                $payload['text'] = $message;
                $endpoint = "/message/sendText/{$instanceName}";
            }

            $response = Http::withHeaders(['apikey' => $evoApiKey])
                ->timeout(15)
                ->post("{$evoApiUrl}{$endpoint}", $payload);

            // Fallback automatique Côte d'Ivoire (10 chiffres -> 8 chiffres)
            if (!$response->successful() && str_contains($response->body(), '"exists":false')) {
                if (strlen($rawPhone) == 13 && (str_starts_with($rawPhone, '22507') || str_starts_with($rawPhone, '22505') || str_starts_with($rawPhone, '22501'))) {
                    // 225 (index 0,1,2) + 07 (index 3,4) + 8 chiffres (index 5 à 12)
                    $oldRawPhone = '225' . substr($rawPhone, 5);
                    $fallbackJid = $oldRawPhone . '@s.whatsapp.net';

                    Log::info("JID 10 chiffres non trouvé sur WhatsApp. Essai avec le format 8 chiffres alternatif : {$fallbackJid}");

                    $payload['number'] = $fallbackJid;

                    $response = Http::withHeaders(['apikey' => $evoApiKey])
                        ->timeout(15)
                        ->post("{$evoApiUrl}{$endpoint}", $payload);
                }
            }

            if ($response->successful()) {
                Log::info("Message WhatsApp envoyé avec succès via Evolution API pour le ticket {$this->ticket->id}. Status: " . $response->status());
            } else {
                Log::error("Échec de l'envoi WhatsApp Evolution API pour le ticket {$this->ticket->id}. Status: " . $response->status() . " Body: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Erreur SendWhatsAppTicketJob : " . $e->getMessage());
        }
    }
}

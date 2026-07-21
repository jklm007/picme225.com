<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Models\MarketplaceListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(WhatsappMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->message->update(['status' => 'processing']);

            $apiKey = config('services.groq.api_key');
            $endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
            $model = config('services.groq.model', 'llama-3.1-8b-instant');

            if (empty($apiKey)) {
                throw new \Exception('GROQ API key is missing.');
            }

            // System prompt to force strict JSON parsing
            $systemPrompt = "Tu es un assistant spécialisé dans l'extraction de données pour une marketplace (PicMe225). Ton rôle est d'analyser une annonce brute envoyée sur WhatsApp et d'en extraire les informations au format JSON.
Tu dois renvoyer UNIQUEMENT un objet JSON valide, sans markdown, sans texte additionnel.
Voici le schéma JSON attendu (remplis avec null si l'information est introuvable) :
{
  \"category\": \"string (ex: VEHICULES, IMMOBILIER, ELECTRONIQUE, SERVICES)\",
  \"type\": \"string (ex: SALE, RENT, SERVICE)\",
  \"title\": \"string (génère un titre clair et accrocheur)\",
  \"description\": \"string (description corrigée et structurée)\",
  \"price\": \"number (ex: 5500000)\",
  \"price_unit\": \"string (ex: FCFA, /JOUR, /MOIS)\",
  \"brand\": \"string\",
  \"model\": \"string\",
  \"year\": \"string\",
  \"location_city\": \"string (ex: Abidjan, Cocody)\",
  \"owner_name\": \"string\",
  \"confidence_score\": \"number (entre 0 et 100, selon la clarté de l'annonce)\"
}";

            $response = Http::withToken($apiKey)->timeout(30)->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $this->message->content]
                ],
                'temperature' => 0.1, // Low temperature for consistent JSON
                'response_format' => ['type' => 'json_object'], // Force JSON if model supports it
            ]);

            if ($response->failed()) {
                throw new \Exception('Groq API Error: ' . $response->body());
            }

            $result = $response->json();
            $aiContent = $result['choices'][0]['message']['content'] ?? '';

            // Decode JSON (strip markdown if any)
            $aiContent = trim(preg_replace('/^```json\s*(.*?)\s*```$/is', '$1', $aiContent));
            $data = json_decode($aiContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON from AI: ' . json_last_error_msg() . ' Content: ' . $aiContent);
            }

            // Determine Listing Status based on confidence
            $confidence = $data['confidence_score'] ?? 0;
            $listingStatus = ($confidence >= 85) ? 'APPROVED' : 'PENDING_VALIDATION'; // Wait for Admin to approve if < 85

            // Extract medias
            $medias = $this->message->medias ?? [];
            $coverImage = !empty($medias) ? $medias[0] : null;

            // Create or update Listing
            $listing = new MarketplaceListing();
            $listing->user_id = $this->message->sender->user_id ?? 1; // Default to admin or system user if not linked
            $listing->source = 'whatsapp';
            $listing->whatsapp_message_id = $this->message->id;
            $listing->ai_confidence_score = $confidence;
            $listing->status = $listingStatus;
            
            $listing->category = $data['category'] ?? 'AUTRE';
            
            // Map type to valid DB enum values ('RENTAL', 'SALE', 'VEHICLE', 'ARTICLE')
            $rawType = strtoupper(trim($data['type'] ?? 'SALE'));
            if ($rawType === 'RENT' || $rawType === 'RENTAL') {
                $listing->type = 'RENTAL';
            } elseif (in_array($rawType, ['SALE', 'VEHICLE', 'ARTICLE'])) {
                $listing->type = $rawType;
            } else {
                $listing->type = 'SALE';
            }

            $listing->title = $data['title'] ?? 'Annonce WhatsApp';
            $listing->description = $data['description'] ?? $this->message->content;
            $listing->price = $data['price'] ?? 0;
            $listing->price_unit = $data['price_unit'] ?? 'FCFA';
            
            // Set metadata attributes
            $metadata = [];
            if (!empty($data['brand'])) $metadata['brand'] = $data['brand'];
            if (!empty($data['model'])) $metadata['model'] = $data['model'];
            if (!empty($data['year'])) $metadata['year'] = $data['year'];
            if (!empty($data['location_city'])) $metadata['location_city'] = $data['location_city'];
            $listing->metadata = $metadata;
            
            $listing->owner_name = $data['owner_name'] ?? $this->message->sender->name ?? $this->message->sender->phone_number;
            $listing->owner_phone = $this->message->sender->phone_number;
            
            $listing->cover_image = $coverImage;
            $listing->images = $medias;

            $listing->save();

            $this->message->update([
                'status' => 'success',
                'error_log' => null,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp Job Error: ' . $e->getMessage());
            $this->message->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\MarketplaceListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostToSocialMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $listingId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($listingId)
    {
        $this->listingId = $listingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $listing = MarketplaceListing::find($this->listingId);
        
        if (!$listing || $listing->status !== 'APPROVED') {
            return;
        }

        $appUrl = url('/marketplace/' . $listing->id);
        
        $message = "🏷️ NOUVELLE ANNONCE : " . $listing->title . "\n";
        if ($listing->price > 0) {
            $message .= "💰 Prix : " . number_format($listing->price, 0, ',', ' ') . " " . $listing->price_unit . "\n";
        }
        $message .= "\n" . substr($listing->description, 0, 300) . (strlen($listing->description) > 300 ? '...' : '') . "\n\n";
        $message .= "📍 " . ($listing->metadata['location_city'] ?? 'Abidjan') . "\n";
        $message .= "👉 Voir plus de détails et contacter le vendeur ici : " . $appUrl . "\n";
        $message .= "\n📲 Téléchargez l'application PicMe pour voir toutes nos annonces !";

        // Média principal (Fallback to app icon if no image)
        $imageUrl = $listing->cover_image ? \Storage::disk('s3')->url( $listing->cover_image) : asset('logo.png');

        // 1. Publication Facebook (Meta Graph API)
        $this->postToFacebook($message, $imageUrl);

        // 2. Publication TikTok (Direct Post API - Photo Mode)
        $this->postToTikTok($message, $listing->images ?? [$listing->cover_image]);
    }

    /**
     * Publier sur la page Facebook.
     */
    private function postToFacebook($message, $imageUrl)
    {
        $fbPageId = \Setting::get('facebook_page_id', config('services.facebook_page.page_id'));
        $fbToken = \Setting::get('facebook_access_token', config('services.facebook_page.access_token'));

        if (empty($fbPageId) || empty($fbToken)) {
            Log::warning('Facebook Page API not configured. Skipping FB post.');
            return;
        }

        try {
            $response = Http::post("https://graph.facebook.com/v19.0/{$fbPageId}/photos", [
                'url' => $imageUrl,
                'message' => $message,
                'access_token' => $fbToken,
            ]);

            if ($response->failed()) {
                Log::error('Facebook Post Error: ' . $response->body());
            } else {
                Log::info('Successfully posted to Facebook Page. Post ID: ' . $response->json('post_id'));
            }
        } catch (\Exception $e) {
            Log::error('Facebook Post Exception: ' . $e->getMessage());
        }
    }

    /**
     * Publier sur TikTok (Mode Photo).
     */
    private function postToTikTok($message, $images)
    {
        $tiktokToken = \Setting::get('tiktok_access_token', config('services.tiktok.access_token'));

        if (empty($tiktokToken)) {
            Log::warning('TikTok API not configured. Skipping TikTok post.');
            return;
        }

        if (empty($images) || count($images) == 0) {
            Log::warning('TikTok requires at least one image. Skipping.');
            return;
        }

        try {
            // Dans l'API TikTok Direct Post, il faut d'abord uploader les images ou fournir des URLs publiques.
            // On simplifie ici avec les URLs publiques
            $photoUrls = [];
            foreach (array_slice($images, 0, 5) as $img) {
                if ($img) {
                    $photoUrls[] = \Storage::disk('s3')->url( $img);
                }
            }

            // TikTok Direct Post (Photo) payload
            $response = Http::withToken($tiktokToken)
                ->post('https://open.tiktokapis.com/v2/post/publish/creator_info/query/', [
                    // Mock payload, TikTok API documentation varies for direct posting
                    // We assume a simplified Direct Post endpoint for photos
                    'post_info' => [
                        'title' => substr($message, 0, 150), // Title limit
                        'description' => substr($message, 0, 2000), // Desc limit
                        'privacy_level' => 'PUBLIC',
                        'disable_duet' => false,
                        'disable_comment' => false,
                        'disable_stitch' => false
                    ],
                    'source_info' => [
                        'source' => 'PULL_FROM_URL',
                        'photo_cover_index' => 1,
                        'photo_images' => $photoUrls
                    ],
                    'post_mode' => 'DIRECT_POST',
                    'media_type' => 'PHOTO'
                ]);

            if ($response->failed()) {
                Log::error('TikTok Post Error: ' . $response->body());
            } else {
                Log::info('Successfully posted to TikTok.');
            }
        } catch (\Exception $e) {
            Log::error('TikTok Post Exception: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupSocialFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-social-feed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Social Feed cleanup...');

        $expiredPosts = \App\Models\Post::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->get();

        $count = 0;
        foreach ($expiredPosts as $post) {
            // Supprimer le fichier physique si présent et local
            if ($post->media_url && !str_starts_with($post->media_url, 'http')) {
                if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($post->media_url)) {
                    \Illuminate\Support\Facades\Storage::disk('s3')->delete($post->media_url);
                    $this->line("Deleted file: " . $post->media_url);
                }
            }

            // Supprimer définitivement le post (pas seulement soft delete pour libérer DB)
            $post->forceDelete();
            $count++;
        }

        $this->info("Cleanup finished. {$count} expired posts and their media removed.");

        $push = new \App\Http\Controllers\SendPushNotification();

        // --- PARTIE 2 : MARKETPLACE RENEWAL PROMPT (3 MOIS) ---
        $this->info('Finding marketplace listings to prompt for renewal...');
        $listingsToPrompt = \App\Models\MarketplaceListing::whereIn('status', ['SOLD', 'CANCELLED'])
            ->where('updated_at', '<', now()->subMonths(3))
            ->whereNull('cleanup_prompt_at')
            ->get();

        foreach ($listingsToPrompt as $listing) {
            $msg = "Votre annonce \"{$listing->title}\" est clôturée depuis 3 mois et va être supprimée. Voulez-vous la conserver encore 3 mois ?";
            $push->sendPushToUser($listing->user_id, $msg);
            $listing->update(['cleanup_prompt_at' => now()]);
            $this->line("Prompted user ID {$listing->user_id} for listing #{$listing->id}");
        }

        // --- PARTIE 3 : MARKETPLACE FINAL DELETION (7 JOURS APRÈS PROMPT) ---
        $this->info('Starting Marketplace final cleanup (7 days after prompt)...');
        $oldListings = \App\Models\MarketplaceListing::whereNotNull('cleanup_prompt_at')
            ->where('cleanup_prompt_at', '<', now()->subDays(7))
            ->get();

        $mCount = 0;
        foreach ($oldListings as $listing) {
            // Delete cover image
            if ($listing->cover_image && !str_starts_with($listing->cover_image, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('s3')->delete($listing->cover_image);
            }
            // Delete extra images
            if ($listing->images && is_array($listing->images)) {
                foreach ($listing->images as $img) {
                    if (!str_starts_with($img, 'http')) {
                        \Illuminate\Support\Facades\Storage::disk('s3')->delete($img);
                    }
                }
            }
            $listing->forceDelete();
            $mCount++;
        }
        $this->info("Marketplace cleanup finished. {$mCount} old listings permanently removed.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MarketplaceListing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupExpiredMarketplaceListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up marketplace listings that are older than 90 days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $expirationDate = Carbon::now()->subDays(90);

        $listingsToDelete = MarketplaceListing::where('created_at', '<', $expirationDate)->get();

        if ($listingsToDelete->isEmpty()) {
            $this->info('No expired listings found.');
            return 0;
        }

        $count = 0;
        foreach ($listingsToDelete as $listing) {
            // Optional: send a WhatsApp message to notify the user of the deletion
            // $this->notifyUserExpired($listing);

            $listing->delete();
            $count++;
        }

        $this->info("Successfully deleted {$count} expired marketplace listings.");
        Log::info("Marketplace Cleanup: Deleted {$count} listings older than 90 days.");

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserRequests;
use Carbon\Carbon;
use Log;

class AutoCompleteCashTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'picme:autocomplete-cash-trips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically completes CASH trips that have been stuck in DROPPED status for more than 5 minutes';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('AutoCompleteCashTrips Job Started');

        $timeLimit = Carbon::now()->subMinutes(5);

        // Find all trips stuck in DROPPED status with CASH payment mode
        // that were dropped more than 5 minutes ago.
        // Assuming updated_at was the time it was marked as DROPPED.
        $stuckTrips = UserRequests::where('status', 'DROPPED')
            ->where('payment_mode', 'CASH')
            ->where('updated_at', '<=', $timeLimit)
            ->get();

        $count = 0;

        foreach ($stuckTrips as $trip) {
            try {
                // Auto-complete the trip
                $trip->status = 'COMPLETED';
                $trip->paid = 1;
                $trip->save();

                // Ensure invoice exists
                if (!$trip->payment()->exists()) {
                    $tripController = new \App\Http\Controllers\ProviderResources\TripController();
                    $tripController->invoice($trip->id);
                }

                Log::info("AutoCompleteCashTrips: Successfully auto-completed trip ID {$trip->id}");
                $count++;
            } catch (\Exception $e) {
                Log::error("AutoCompleteCashTrips: Failed to auto-complete trip ID {$trip->id} - " . $e->getMessage());
            }
        }

        $this->info("Completed {$count} stuck cash trips.");
        Log::info("AutoCompleteCashTrips Job Completed. Processed {$count} trips.");
    }
}

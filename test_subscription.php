<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserSubscriptionSchedule;
use App\Models\UserRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

try {
    echo "Running migration...\n";
    \Artisan::call('migrate');
    echo \Artisan::output();

    echo "Creating dummy schedule...\n";
    // Find or create a user
    $user = \App\Models\User::first();
    if (!$user) {
        $user = new \App\Models\User();
        $user->first_name = 'Test';
        $user->last_name = 'User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->mobile = '1234567890';
        $user->save();
    }

    // Prepare times (pickup in 50 minutes so it triggers the T-60 logic which looks ahead 45 to 60 minutes)
    $now = Carbon::now();
    $pickupTime = $now->copy()->addMinutes(50)->format('H:i');
    $currentDay = strtoupper($now->format('D'));

    $schedule = new UserSubscriptionSchedule();
    $schedule->user_id = $user->id;
    $schedule->service_id = 1;
    $schedule->s_address = 'Point A';
    $schedule->s_lat = 5.3;
    $schedule->s_lng = -4.0;
    $schedule->d_address = 'Point B';
    $schedule->d_lat = 5.35;
    $schedule->d_lng = -4.05;
    $schedule->pickup_time = $pickupTime;
    $schedule->active_days = [$currentDay];
    $schedule->status = 'ACTIVE';
    $schedule->save();

    echo "Schedule created for User #{\$user->id} with Pickup Time {\$pickupTime}\n";

    echo "Running GenerateSubscriptionRides...\n";
    \Artisan::call('rides:generate-subscription');
    
    // Check if the request was created
    $request = UserRequests::where('user_id', $user->id)
        ->where('is_subscription_trip', 1)
        ->orderBy('id', 'desc')
        ->first();

    if ($request) {
        echo "SUCCESS: Ride created! Request ID: {\$request->id}, Status: {\$request->status}, Scheduled At: {\$request->schedule_at}\n";
    } else {
        echo "FAILED: No ride was created.\n";
    }

    // Cleanup
    $schedule->delete();
    if ($request) $request->delete();

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

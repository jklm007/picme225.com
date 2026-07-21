<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$app->make(Kernel::class)->bootstrap();

use App\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

try {
    // Update GOLD Plan
    SubscriptionPlan::where('name', 'GOLD')->update([
        'price' => 50000,
        'commission_type' => 'fixed',
        'commission_value' => 0
    ]);
    echo "GOLD Plan updated to 50,000 CFA / 0 commission.\n";

    // Update Platform Booking Fee directly in DB to avoid facade issues
    DB::table('settings')->updateOrInsert(
        ['key' => 'platform_booking_fee'],
        ['value' => '100']
    );
    echo "Platform booking fee set to 100 CFA.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

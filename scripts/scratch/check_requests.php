<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\UserRequests;
use App\RequestFilter;

$requests = UserRequests::orderBy('created_at', 'desc')->take(5)->get();
echo "=== LATEST USER REQUESTS ===\n";
foreach ($requests as $r) {
    echo "ID: {$r->id} | Booking ID: {$r->booking_id} | Status: {$r->status} | User: {$r->user_id} | Provider: {$r->provider_id} | Reason: {$r->cancel_reason}\n";
    $filters = RequestFilter::where('request_id', $r->id)->get();
    echo "   Filters match ID: ";
    foreach ($filters as $f) {
        echo "{$f->provider_id} ";
    }
    echo "\n";
}

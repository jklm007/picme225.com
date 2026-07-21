<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo view('user.dashboard', [
        'user' => null,
        'recentTrips' => collect(),
        'totalTrips' => 0,
        'upcomingTrips' => collect(),
        'categories' => \App\Models\Service::with('serviceTypes')->get(),
        'package' => \App\Models\KmHour::all()
    ])->render();
    echo "

SUCCESS
";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "
";
}

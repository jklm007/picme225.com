<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;

$numbers = ['01010101', '+22501010101', '22501010101'];

foreach ($numbers as $num) {
    $users = User::where('mobile', 'like', '%' . preg_replace('/^\+/', '', $num) . '%')->get();
    echo "Searching for variations of: $num\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}, Mobile: {$user->mobile}, FleetID: {$user->fleet_id}, AgentID: {$user->station_agent_id}, Type: {$user->user_type}\n";
    }
    echo "-------------------\n";
}

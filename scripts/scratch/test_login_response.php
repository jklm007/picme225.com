<?php
require __DIR__ . '/bootstrap/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Http\Controllers\UnifiedAuthController;
use Illuminate\Http\Request;

$user = User::where('mobile', '+22502020202')->first();
if ($user) {
    echo "USER ID: " . $user->id . "\n";
    echo "FLEET ID: " . ($user->fleet_id ?? 'NULL') . "\n";
    echo "AGENT ID: " . ($user->station_agent_id ?? 'NULL') . "\n";

    $availableRoles = ['USER'];
    $primaryType = 'USER';
    if ($user->fleet_id) {
        $availableRoles[] = 'FLEET_OWNER';
        $primaryType = 'FLEET_OWNER';
    } elseif ($user->station_agent_id) {
        $availableRoles[] = 'STATION_AGENT';
        $primaryType = 'STATION_AGENT';
    }

    echo "PRIMARY TYPE: $primaryType\n";
    echo "AVAILABLE ROLES: " . json_encode($availableRoles) . "\n";
} else {
    echo "USER NOT FOUND\n";
}

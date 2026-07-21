<?php
require __DIR__ . '/bootstrap/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;

$mobiles = ['+22502020202', '02020202', '22502020202'];
$user = User::whereIn('mobile', $mobiles)->first();
if ($user) {
    echo "ID: " . $user->id . "\n";
    echo "MOBILE: " . $user->mobile . "\n";
    echo "USER_TYPE: " . $user->user_type . "\n";
    echo "FLEET_ID: " . ($user->fleet_id ?? 'NULL') . "\n";
} else {
    echo "USER NOT FOUND. All users:\n";
    foreach (User::all() as $u) {
        echo " - " . $u->mobile . " (" . $u->user_type . ")\n";
    }
}

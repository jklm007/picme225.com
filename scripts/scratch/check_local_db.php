<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Fleet;
use App\User;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING DB: " . DB::connection()->getDatabaseName() . " ===\n";

$mobile = '+22501010101';
echo "Searching for $mobile in FLEETS...\n";
$fleet = Fleet::where('mobile', $mobile)->first();
if ($fleet) {
    echo "FOUND Fleet ID: " . $fleet->id . " | Name: " . $fleet->name . "\n";
} else {
    echo "NOT FOUND in fleets.\n";
    $all = Fleet::all();
    foreach ($all as $f) {
        echo "ID: {$f->id} | Mob: [{$f->mobile}]\n";
    }
}

echo "\nSearching for $mobile in USERS...\n";
$user = User::where('mobile', $mobile)->first();
if ($user) {
    echo "FOUND User ID: " . $user->id . " | Name: " . $user->first_name . "\n";
} else {
    echo "NOT FOUND in users.\n";
}

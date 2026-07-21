<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\User::whereNotNull('display_name')->where('display_name', '!=', '')->get();
echo "Found " . $users->count() . " users with pseudos.\n";

foreach ($users as $u) {
    $updated = App\Provider::where('mobile', $u->mobile)
        ->orWhere('email', $u->email)
        ->update(['display_name' => $u->display_name]);
    if ($updated) {
        echo "Updated provider for user: " . $u->first_name . " -> " . $u->display_name . "\n";
    }
}

// Also sync the other way just in case
$providers = App\Provider::whereNotNull('display_name')->where('display_name', '!=', '')->get();
foreach ($providers as $p) {
    $updated = App\User::where('mobile', $p->mobile)
        ->orWhere('email', $p->email)
        ->where(function($q) { $q->whereNull('display_name')->orWhere('display_name', ''); })
        ->update(['display_name' => $p->display_name]);
    if ($updated) {
        echo "Updated user for provider: " . $p->first_name . " -> " . $p->display_name . "\n";
    }
}

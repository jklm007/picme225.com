<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\User::where('mobile', '0759747444')->first();
if ($u) {
    $u->display_name = 'Jklm';
    $u->save();
    echo "✅ display_name mis à jour : [" . $u->display_name . "]\n";
} else {
    echo "❌ Utilisateur non trouvé\n";
}

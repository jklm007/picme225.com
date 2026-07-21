<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$mobile = '0759747444';
$newPassword = Hash::make('123456');

echo "--- Réinitialisation du mot de passe pour: $mobile ---" . PHP_EOL;

$userUpdated = DB::table('users')->where('mobile', $mobile)->update(['password' => $newPassword]);
if ($userUpdated) {
    echo "Mot de passe USER mis à jour (123456)." . PHP_EOL;
} else {
    echo "USER non mis à jour (déjà à jour ou inexistant)." . PHP_EOL;
}

$providerUpdated = DB::table('providers')->where('mobile', $mobile)->update(['password' => $newPassword]);
if ($providerUpdated) {
    echo "Mot de passe PROVIDER mis à jour (123456)." . PHP_EOL;
} else {
    echo "PROVIDER non mis à jour (déjà à jour ou inexistant)." . PHP_EOL;
}
?>
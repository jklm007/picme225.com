<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use Carbon\Carbon;

$now = Carbon::now();

// ---- Simulation Filtre Variante + Abonnement ----
$variantColumnMap = [
    'prive'         => 'opt_private_ride',
    'partage'       => 'opt_share_ride',
    'arret_pdp'     => 'opt_arret_ride',
    'arret_hybride' => 'opt_arret_ride',
    'multi_stop'    => 'opt_multi_stop',
];
$premiumVariants = ['partage', 'arret_pdp', 'arret_hybride', 'multi_stop'];

$variants = ['prive', 'partage', 'arret_pdp', 'arret_hybride', 'multi_stop'];

foreach ($variants as $variant) {
    $col = $variantColumnMap[$variant];
    $isPremium = in_array($variant, $premiumVariants);
    
    $query = Provider::where($col, 1);
    if ($isPremium) {
        $query->where('subscription_expires_at', '>', $now)
              ->whereNotNull('subscription_expires_at');
    }
    
    $count = $query->count();
    echo "[Variante: $variant] -> $count chauffeur(s) eligible(s)" . ($isPremium ? " [PREMIUM: abonnement requis]" : " [STANDARD]") . "\n";
}

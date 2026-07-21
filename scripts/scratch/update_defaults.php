<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
$providers = Provider::with('service.service_type')->get();
$count = 0;
foreach ($providers as $p) {
    if ($p->service && $p->service->service_type) {
        $st = $p->service->service_type;
        $isCommunal = $st->is_communal;
        $name = strtolower($st->name);
        
        if ($isCommunal || in_array($name, ['woro-woro', 'gbaka', 'massa'])) {
            $p->opt_share_ride = 1;
            $p->opt_arret_ride = 1;
        } else {
            // Taxi / Standard / Livraison : activer toutes les variantes autorisées
            $p->opt_private_ride = 1;
            $p->opt_share_ride = 1;
            $p->opt_arret_ride = 1;
            $p->opt_multi_stop = 1;
            if (empty($p->subscription_expires_at) || \Carbon\Carbon::parse($p->subscription_expires_at)->isPast()) {
                $p->subscription_expires_at = \Carbon\Carbon::now()->addYear();
            }
        }
        
        $p->save();
        $count++;
    }
}
echo "Updated $count providers with default variants!\n";

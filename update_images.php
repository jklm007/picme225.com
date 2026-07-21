<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$types = \App\Models\ServiceType::all();
foreach($types as $t) {
    if ($t->name == 'Moto') {
        $t->image = 'service/moto.webp';
        $t->save();
        echo "Updated Moto\n";
    }
    if ($t->name == 'Cargo') {
        $t->image = 'service/cargo.webp';
        $t->save();
        echo "Updated Cargo\n";
    }
    if ($t->name == 'Livraison Communal') {
        $t->image = 'service/moto.webp';
        $t->save();
        echo "Updated Livraison Communal\n";
    }
}
echo "Done.\n";

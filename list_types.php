<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$types = \App\ServiceType::select('id', 'name', 'type')->orderBy('type')->orderBy('id')->get();
foreach ($types as $t) {
    echo "[{$t->type}] ID {$t->id} : {$t->name}\n";
}

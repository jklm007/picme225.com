<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sources_config = [
    ['name' => 'Abidjan.net'],
    ['name' => 'KOACI'],
    ['name' => 'FratMat'],
    ['name' => 'L\'Infodrome'],
    ['name' => 'AIP'],
    ['name' => 'RTI'],
];

$activeSources = \App\Post::whereIn('type', ['NEWS', 'RSS_NEWS'])
                     ->where('status', 'ACTIVE')
                     ->distinct()
                     ->pluck('source')
                     ->filter()
                     ->map(function($src) { return strtolower(trim($src)); })
                     ->toArray();

echo "Active sources in DB (normalized): \n";
print_r($activeSources);

$list = [];
foreach ($sources_config as $source) {
    $normalizedName = strtolower(trim($source['name']));
    if (in_array($normalizedName, $activeSources)) {
        $list[] = $source;
    }
}

echo "\nFinal dynamic sources list: \n";
print_r($list);

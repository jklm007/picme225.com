<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\PdpStop;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Mise à jour de PdpStopsSeeder.php...\n";

$stops = PdpStop::whereNull('pdp_route_id')->get();

$content = "<?php\n\nnamespace Database\Seeders;\n\nuse App\PdpStop;\nuse Illuminate\Database\Seeder;\n\nclass PdpStopsSeeder extends Seeder\n{\n    /**\n     * Run the database seeds.\n     */\n    public function run(): void\n    {\n        \$stops = [\n";

foreach ($stops as $s) {
    $content .= "            [\n";
    $content .= "                'name' => '" . addslashes($s->name) . "',\n";
    $content .= "                'latitude' => {$s->latitude},\n";
    $content .= "                'longitude' => {$s->longitude},\n";
    $content .= "                'commune' => '" . addslashes($s->commune ?: 'Abidjan') . "',\n";
    $content .= "                'is_active' => true,\n";
    $content .= "            ],\n";
}

$content .= "        ];\n\n";
$content .= "        foreach (\$stops as \$stop) {\n";
$content .= "            PdpStop::updateOrCreate(\n";
$content .= "                ['name' => \$stop['name'], 'pdp_route_id' => null],\n";
$content .= "                \$stop\n";
$content .= "            );\n";
$content .= "        }\n";
$content .= "    }\n}\n";

file_put_contents(__DIR__ . '/database/seeders/PdpStopsSeeder.php', $content);

echo "Fichier PdpStopsSeeder.php mis à jour avec succès.\n";

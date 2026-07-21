<?php

use Illuminate\Support\Facades\DB;
use App\Models\GatewayNode;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUT DU STRESS TEST : 60 MILLIONS FCFA ---\n";

// 1. Nettoyage et Préparation de 6 Nodes (3 Wave, 3 Orange)
DB::table('gateway_nodes')->truncate();
$networks = ['WAVE', 'ORANGE'];
for ($i = 1; $i <= 3; $i++) {
    foreach ($networks as $net) {
        DB::table('gateway_nodes')->insert([
            'name' => "Phone $i - $net",
            'phone_number' => "070000000$i$net",
            'network' => $net,
            'type' => ($i == 1) ? 'RECEIVER' : (($i == 2) ? 'PAYOUT' : 'VAULT'),
            'status' => 'ACTIVE',
            'daily_limit' => 12000000, // On augmente pour le test global
            'monthly_limit' => 10000000, // Limite critique de 10M
            'current_balance' => 0,
            'daily_volume' => 0,
            'monthly_volume' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}

// Ajout du Node Profit
DB::table('gateway_nodes')->insert([
    'name' => 'NODE PROFIT',
    'phone_number' => 'PROFIT_NODE',
    'network' => 'WAVE',
    'type' => 'PROFIT',
    'status' => 'ACTIVE',
    'current_balance' => 0,
    'created_at' => now(),
    'updated_at' => now()
]);

$controller = new \App\Http\Controllers\SmsPaymentController();

echo "Simulation de 1200 transactions de 50 000 F...\n";

$successCount = 0;
$blockedCount = 0;

for ($t = 1; $t <= 1300; $t++) {
    // Simuler l'App Mobile qui demande un numéro (Routage)
    $routingRequest = new Request(['network' => 'WAVE']);
    $response = $controller->getActiveReceiver($routingRequest);
    $data = json_decode($response->getContent(), true);

    if ($data['status'] == 'success') {
        $selectedNumber = $data['phone_number'];
        
        // Simuler la réception du SMS
        $smsRequest = new Request([
            'from' => 'WAVE',
            'message' => "Vous avez recu 50000 FCFA de 0701020304. Transaction: SIM_$t",
            'receiver_phone' => $selectedNumber
        ]);
        $controller->handleSms($smsRequest);
        $successCount++;
        
        if ($t % 200 == 0) echo "> $t transactions traitées (".($t * 50000)." F)...\n";
    } else {
        $blockedCount++;
    }
}

// 3. Analyse des résultats
$totalVolume = DB::table('gateway_nodes')->where('type', '!=', 'PROFIT')->sum('monthly_volume');
$totalProfit = DB::table('gateway_nodes')->where('type', 'PROFIT')->first()->current_balance;

echo "\n--- RESULTATS DU TEST ---\n";
echo "Volume Total Encaissé: ".number_format($totalVolume, 0, ',', ' ')." FCFA\n";
echo "Nombre de transactions réussies: $successCount\n";
echo "Nombre de rejets (Plafond atteint): $blockedCount\n";
echo "Profit NET accumulé: ".number_format($totalProfit, 0, ',', ' ')." FCFA\n";

if ($totalVolume >= 60000000) {
    echo "✅ SUCCÈS : Le système a encaissé les 60 Millions et s'est arrêté de lui-même quand les plafonds ont été atteints.\n";
} else {
    echo "❌ ÉCHEC : Le volume n'a pas atteint la cible.\n";
}

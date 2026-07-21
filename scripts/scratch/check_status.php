<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

# Test: 2 articles en location dans le panier
$user = App\User::find(1);

// Vider et ajouter 2 locations différentes
$cart = [
    ['cart_item_id' => (string) Illuminate\Support\Str::uuid(), 'listing_id' => 32, 'quantity' => 1],
    ['cart_item_id' => (string) Illuminate\Support\Str::uuid(), 'listing_id' => 36, 'quantity' => 1],
];
$user->update(['cart_data' => json_encode($cart)]);

// Vérifier
$fresh = App\User::find(1);
$stored = json_decode($fresh->cart_data, true);
echo "=== PANIER SAUVEGARDÉ (" . count($stored) . " articles) ===\n";
foreach ($stored as $item) {
    $l = DB::table('marketplace_listings')->find($item['listing_id'], ['id', 'title', 'type', 'status', 'price']);
    echo "  - ID {$item['listing_id']}: [{$l->status}] {$l->title} (type={$l->type}, price={$l->price})\n";
}





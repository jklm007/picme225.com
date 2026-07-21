<?php
// Test de l'API Photon pour les arrêts clés
$tests = [
    "Commissariat 35ème Palmeraie Cocody Abidjan",
    "Riviera Palmeraie Abidjan",
    "Carrefour 9 Kilos Abidjan",
    "CHU de Cocody Abidjan",
    "Abobo Gare SOTRA Abidjan",
    "Adjamé Liberté Abidjan",
];

foreach ($tests as $query) {
    $url = 'https://photon.komoot.io/api/?q=' . urlencode($query) . '&lat=5.35&lon=-4.00&location_bias_scale=0.6&limit=1';
    $r = @file_get_contents($url);
    if ($r) {
        $d = json_decode($r, true);
        $features = $d['features'] ?? [];
        if (!empty($features)) {
            $c = $features[0]['geometry']['coordinates'];
            $p = $features[0]['properties'];
            $country = $p['country'] ?? 'N/A';
            echo "✅ '$query'\n   → " . ($p['name'] ?? '?') . " | lat:{$c[1]} | lon:{$c[0]} | pays:{$country}\n";
        } else {
            echo "❌ Aucun résultat pour '$query'\n";
        }
    } else {
        echo "❌ Erreur réseau pour '$query'\n";
    }
    sleep(1);
}

<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Activation du moteur IA de dispatch
\Setting::set('use_ai_dispatch', true);

$val = \Setting::get('use_ai_dispatch', false);
echo "Feature Flag 'use_ai_dispatch' = " . ($val ? 'ACTIVÉ ✓' : 'DÉSACTIVÉ ✗') . "\n";
echo "Le moteur IA est maintenant actif sur tous les dispatches.\n";
echo "Pour désactiver (rollback): Setting::set('use_ai_dispatch', false)\n";

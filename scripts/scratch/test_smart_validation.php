<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST VALIDATION UPDATE_SMART_MODE ===\n\n";

// Simuler exactement ce que l'application Android envoie
$testCases = [
    [
        'label' => 'Mode HOME (basique)',
        'data' => ['is_smart_mode' => '1', 'smart_mode_type' => 'HOME', 'smart_dest_lat' => '5.36', 'smart_dest_lng' => '-4.02', 'smart_dest_address' => 'Cocody', 'smart_zone_radius' => '5', 'smart_communes' => '[]'],
    ],
    [
        'label' => 'Mode ZONE',
        'data' => ['is_smart_mode' => '1', 'smart_mode_type' => 'ZONE', 'smart_dest_lat' => '5.36', 'smart_dest_lng' => '-4.02', 'smart_dest_address' => '', 'smart_zone_radius' => '5', 'smart_communes' => '[]'],
    ],
    [
        'label' => 'Mode COMMUNE',
        'data' => ['is_smart_mode' => '1', 'smart_mode_type' => 'COMMUNE', 'smart_dest_lat' => '', 'smart_dest_lng' => '', 'smart_dest_address' => '', 'smart_zone_radius' => '5', 'smart_communes' => '["Cocody","Marcory"]'],
    ],
    [
        'label' => 'Mode STATION',
        'data' => ['is_smart_mode' => '1', 'smart_mode_type' => 'STATION', 'smart_dest_lat' => '5.36', 'smart_dest_lng' => '-4.02', 'smart_dest_address' => 'Gare Adjamé', 'smart_zone_radius' => '5', 'smart_communes' => '[]'],
    ],
    [
        'label' => 'Mode WORO_FREE',
        'data' => ['is_smart_mode' => '1', 'smart_mode_type' => 'WORO_FREE', 'smart_dest_lat' => '', 'smart_dest_lng' => '', 'smart_dest_address' => '', 'smart_zone_radius' => '5', 'smart_communes' => '[]'],
    ],
    [
        'label' => 'Mode WORO_FIXED',
        'data' => ['is_smart_mode' => '1', 'smart_mode_type' => 'WORO_FIXED', 'smart_dest_lat' => '', 'smart_dest_lng' => '', 'smart_dest_address' => '', 'smart_zone_radius' => '5', 'smart_communes' => '[]'],
    ],
    [
        'label' => 'Désactivation (is_smart_mode=0)',
        'data' => ['is_smart_mode' => '0', 'smart_mode_type' => 'HOME'],
    ],
];

$rules = [
    'is_smart_mode'    => 'required|boolean',
    'smart_mode_type'  => 'required_if:is_smart_mode,true|in:HOME,ZONE,COMMUNE,STATION,WORO_FREE,WORO_FIXED',
    'smart_dest_lat'   => 'nullable|numeric',
    'smart_dest_lng'   => 'nullable|numeric',
    'smart_dest_address' => 'nullable|string',
    'smart_zone_radius'  => 'nullable|numeric|min:1|max:50',
    'smart_communes'     => 'nullable',
];

foreach ($testCases as $tc) {
    echo "--- {$tc['label']} ---\n";
    $request = \Illuminate\Http\Request::create('/test', 'POST', $tc['data']);
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        echo "❌ VALIDATION FAILED:\n";
        foreach ($validator->errors()->all() as $err) {
            echo "   → $err\n";
        }
    } else {
        echo "✅ Validation passed\n";
        // Check the boolean cast problem
        $isSmart = $request->boolean('is_smart_mode');
        echo "   is_smart_mode (boolean): " . ($isSmart ? 'TRUE' : 'FALSE') . "\n";
    }
    echo "\n";
}

echo "=== TEST BOOLEAN PARSING ===\n";
echo "filter_var('1', FILTER_VALIDATE_BOOLEAN): " . var_export(filter_var('1', FILTER_VALIDATE_BOOLEAN), true) . "\n";
echo "filter_var('true', FILTER_VALIDATE_BOOLEAN): " . var_export(filter_var('true', FILTER_VALIDATE_BOOLEAN), true) . "\n";
echo "filter_var('false', FILTER_VALIDATE_BOOLEAN): " . var_export(filter_var('false', FILTER_VALIDATE_BOOLEAN), true) . "\n";
echo "filter_var('0', FILTER_VALIDATE_BOOLEAN): " . var_export(filter_var('0', FILTER_VALIDATE_BOOLEAN), true) . "\n";

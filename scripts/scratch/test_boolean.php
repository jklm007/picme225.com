<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$testCases = [
    [
        'label' => 'String "true"',
        'data' => ['is_smart_mode' => 'true', 'smart_mode_type' => 'HOME'],
    ],
    [
        'label' => 'String "false"',
        'data' => ['is_smart_mode' => 'false', 'smart_mode_type' => 'HOME'],
    ],
    [
        'label' => 'Boolean true',
        'data' => ['is_smart_mode' => true, 'smart_mode_type' => 'HOME'],
    ],
    [
        'label' => 'Integer 1',
        'data' => ['is_smart_mode' => 1, 'smart_mode_type' => 'HOME'],
    ]
];

$rules = [
    'is_smart_mode' => 'required|boolean'
];

foreach ($testCases as $tc) {
    echo "--- {$tc['label']} ---\n";
    $validator = \Illuminate\Support\Facades\Validator::make($tc['data'], $rules);
    if ($validator->fails()) {
        echo "❌ FAILED: " . $validator->errors()->first('is_smart_mode') . "\n";
    } else {
        echo "✅ Passed\n";
    }
}

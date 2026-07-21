<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0 && strpos($class, 'App\\Models\\') !== 0) {
        $parts = explode('\\', $class);
        if (count($parts) === 2) {
            $modelName = $parts[1];
            $targetClass = "App\\Models\\{$modelName}";
            if (class_exists($targetClass)) {
                class_alias($targetClass, $class);
            }
        }
    }
});

// Test 1: App\Service (used by UserServiceController)
try {
    $s = \App\Service::all();
    echo "App\\Service OK: count=" . $s->count() . "\n";
} catch(\Throwable $e) {
    echo "App\\Service ERROR: " . $e->getMessage() . "\n";
}

// Test 2: App\Models\Service (used by seeders)
try {
    $s = \App\Models\Service::all();
    echo "App\\Models\\Service OK: count=" . $s->count() . "\n";
} catch(\Throwable $e) {
    echo "App\\Models\\Service ERROR: " . $e->getMessage() . "\n";
}

// Test 3: App\ServiceType
try {
    $s = \App\ServiceType::all();
    echo "App\\ServiceType OK: count=" . $s->count() . "\n";
} catch(\Throwable $e) {
    echo "App\\ServiceType ERROR: " . $e->getMessage() . "\n";
}

// Test 4: App\Models\ServiceType
try {
    $s = \App\Models\ServiceType::all();
    echo "App\\Models\\ServiceType OK: count=" . $s->count() . "\n";
} catch(\Throwable $e) {
    echo "App\\Models\\ServiceType ERROR: " . $e->getMessage() . "\n";
}

// Test 5: Check all services
try {
    $result = \Illuminate\Support\Facades\Cache::forget('services:all');
    echo "Cache cleared: services:all\n";

    echo "\n=== DIRECT CONTROLLER TESTING ===\n";
    \App::setLocale('fr');
    echo "Current Locale: " . \App::getLocale() . "\n";
    echo "Direct trans lookup: " . trans('servicetypes.Taxi Vtc') . "\n";
    
    $controller = new \App\Http\Controllers\UserServiceController();
    $respObj = $controller->services();
    echo "services() response code: " . $respObj->getStatusCode() . "\n";
    echo "services() response content: " . $respObj->getContent() . "\n";

    // Test getServiceTypes
    \App::setLocale('fr');
    $req = new \Illuminate\Http\Request();
    $req->replace(['service_name' => 'Taxi', 'ride_variant' => 'prive']);
    $respTypes = $controller->getServiceTypes($req);
    echo "getServiceTypes() response code: " . $respTypes->getStatusCode() . "\n";
    echo "getServiceTypes() response content: " . $respTypes->getContent() . "\n";

} catch(\Throwable $e) {
    echo "SERVICES TESTING ERROR: " . $e->getMessage() . "\n";
}

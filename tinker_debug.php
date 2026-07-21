<?php
define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// Handle a simple root request to boot the application
$kernel->handle(Illuminate\Http\Request::create('/', 'GET'));

try {
    // Find an admin user
    $admin = \App\Models\Admin::first();
    if (!$admin) {
        throw new \Exception("No admin user found in database!");
    }
    
    // Set the authenticated admin user
    \Auth::guard('admin')->setUser($admin);
    \Auth::setUser($admin);
    
    echo "Authenticated as admin ID: " . $admin->id . "\n";
    echo "Calling MarketplaceListingController->index() directly...\n";
    
    $controller = app(\App\Http\Controllers\Admin\MarketplaceListingController::class);
    $response = $controller->index();
    
    if ($response instanceof \Illuminate\View\View) {
        echo "Rendering view...\n";
        $response->render();
    }
    echo "SUCCESS! No error thrown.\n";
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}

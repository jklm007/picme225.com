<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/marketplace-listings', 'GET');
try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 500) {
        if ($response->exception) {
            echo "Exception: " . $response->exception->getMessage() . "\n";
            echo "File: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
            echo $response->exception->getTraceAsString();
        } else {
            echo "Content: " . substr($response->getContent(), 0, 500) . "...\n";
        }
    }
} catch (\Throwable $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}

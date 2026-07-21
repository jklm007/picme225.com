<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$user = \App\User::first();
echo "User: " . ($user ? $user->email . ' / ' . $user->mobile : 'None') . "\n";

$provider = \App\Provider::first();
echo "Provider: " . ($provider ? $provider->email . ' / ' . $provider->mobile : 'None') . "\n";

// Let's test a fake login request logic
$login = 'test@example.com';
$field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
echo "Test email: field=$field\n";

$login = '+22501020304';
$field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
echo "Test mobile: field=$field\n";


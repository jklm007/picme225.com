<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tokens = DB::table('oauth_access_tokens')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($tokens as $token) {
    echo "User ID: " . $token->user_id . " | Created At: " . $token->created_at . "\n";
}

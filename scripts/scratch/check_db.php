<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userExists = \App\User::where('id', 1)->exists();
echo "User 1 exists: " . ($userExists ? "YES" : "NO") . "\n";

$annExists = \Illuminate\Support\Facades\DB::table('announcements')->where('id', 2)->exists();
echo "Announcement 2 exists: " . ($annExists ? "YES" : "NO") . "\n";

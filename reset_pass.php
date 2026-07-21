<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$password = bcrypt('123456');

App\Models\User::where('mobile', 'like', '%0759747444%')->update(['password' => $password]);
App\Models\Provider::where('mobile', 'like', '%0759747444%')->update(['password' => $password]);
echo "Passwords reset to 123456\n";

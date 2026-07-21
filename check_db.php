<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo App\Models\User::count() . " users\n";
echo App\Models\Provider::count() . " drivers\n";
echo App\Models\Admin::count() . " admins\n";

if ($admin = App\Models\Admin::first()) {
    echo "Admin email: " . $admin->email . "\n";
}
if ($user = App\Models\User::first()) {
    echo "User email: " . $user->email . "\n";
}

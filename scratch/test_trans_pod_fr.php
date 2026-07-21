<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
app()->setLocale('fr');
echo 'locale: ' . app()->getLocale() . "\n";
echo 'admin.include.dashboard: ' . trans('admin.include.dashboard') . "\n";
echo 'admin.include.settings: ' . trans('admin.include.settings') . "\n";

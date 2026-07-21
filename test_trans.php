<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo 'Locale: ' . app()->getLocale() . ' | Location: ' . trans('home.location') . ' | Drive: ' . trans('home.drive') . "\n";

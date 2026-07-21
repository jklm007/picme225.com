<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$locales = ['fr', 'en', 'fr-FR', 'fr_FR', 'French', 'french', 'null', ''];
foreach ($locales as $loc) {
    if ($loc === 'null') {
        $lang = app()->getLocale();
    } else {
        app()->setLocale($loc);
        $lang = $loc;
    }
    echo "Locale set: '$lang' | Result: '" . trans('admin.include.dashboard') . "'\n";
}

<?php
// Correct diagnostic script
chdir('/app');
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LOCALE DIAGNOSTIC ===\n";
echo "Default locale: " . $app->getLocale() . "\n";
echo "Config locale: " . config('app.locale') . "\n";

// Check if translation files exist
$langs = ['fr', 'en'];
foreach ($langs as $lang) {
    $path = '/app/resources/lang/' . $lang . '/home.php';
    if (file_exists($path)) {
        $arr = include($path);
        echo "[$lang/home.php] OK - keys count: " . count($arr) . "\n";
    } else {
        echo "[$lang/home.php] MISSING\n";
    }
}

// Test translation with each locale
foreach ($langs as $lang) {
    app()->setLocale($lang);
    echo "[$lang] home.location = " . trans('home.location') . "\n";
    echo "[$lang] home.drive = " . trans('home.drive') . "\n";
}

// Check if translation loader database contains keys
try {
    $dbTranslations = \DB::table('language_lines')->get();
    echo "Total db translation lines: " . $dbTranslations->count() . "\n";
    foreach ($dbTranslations as $line) {
        echo " - group: {$line->group}, key: {$line->key}\n";
    }
} catch (\Exception $e) {
    echo "DB Translation Loader Error: " . $e->getMessage() . "\n";
}

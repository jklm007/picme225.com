<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

$tables = ['posts', 'social_likes', 'social_comments', 'likes', 'comments', 'author_favorites'];

foreach ($tables as $table) {
    try {
        \DB::table($table)->truncate();
        echo "Table '$table' videe.\n";
    } catch (\Exception $e) {
        // Ignorer si la table n'existe pas
    }
}

\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

\Cache::forget('last_news_sync_at');
\Cache::forget('active_news_sources_data');
\Artisan::call('cache:clear');

echo "Nettoyage termine.\n";

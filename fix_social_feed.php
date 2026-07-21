<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Post;
use Illuminate\Support\Facades\DB;

// 1. Supprimer les vieux posts RSS_NEWS et NEWS expirés (plus de 72h)
$deleted = Post::whereIn('type', ['RSS_NEWS', 'NEWS'])
    ->where(function($q) {
        $q->where('expires_at', '<', now())
          ->orWhere(function($sq) {
              $sq->whereNotNull('published_at')
                 ->where('published_at', '<', now()->subHours(72));
          })
          ->orWhere(function($sq) {
              $sq->whereNull('published_at')
                 ->where('created_at', '<', now()->subHours(72));
          });
    })
    ->delete();

echo "Deleted old expired posts: " . $deleted . "\n";

// 2. Vérifier ce qui reste
$remaining = Post::where('status', 'ACTIVE')
    ->whereNull('deleted_at')
    ->where(function($q) {
        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
    })
    ->count();
echo "Remaining visible posts: " . $remaining . "\n";

// 3. Effacer le cache du fil social
\Illuminate\Support\Facades\Cache::flush();
echo "Cache cleared.\n";

echo "Done! Now rerun: php artisan news:fetch\n";

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Post;

$count = Post::where('trip_type', 'shared')->update(['author_type' => 'PROVIDER']);
echo "Mise à jour de $count publications chauffeurs existantes.\n";

// Optionnel: Si vous savez que certains IDs appartiennent à des chauffeurs
// Post::whereIn('user_id', [ID_CHAUFFEUR])->update(['author_type' => 'PROVIDER']);

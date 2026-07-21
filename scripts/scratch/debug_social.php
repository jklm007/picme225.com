<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNOSTIC SOCIAL HUB ===\n\n";

// 1. Vérifier les utilisateurs et leur display_name
echo "--- UTILISATEURS ---\n";
$users = App\User::select('id','first_name','last_name','display_name','user_badge','social_points')
    ->limit(10)->get();
foreach ($users as $u) {
    echo "ID:{$u->id} | {$u->first_name} {$u->last_name} | pseudo=[" . ($u->display_name ?: 'VIDE/NULL') . "] | badge={$u->user_badge}\n";
}

// 2. Vérifier les posts récents
echo "\n--- POSTS RECENTS (Stories/Info Trafic) ---\n";
$posts = App\Post::with('user:id,first_name,last_name,display_name')
    ->whereIn('type', ['SOCIAL_PIC','SOCIAL_VID','ROAD_INFO'])
    ->latest()
    ->limit(10)
    ->get();
foreach ($posts as $p) {
    $userName = $p->user ? ($p->user->display_name ?: $p->user->first_name.' '.$p->user->last_name) : 'NO USER';
    $pseudo = $p->user ? ($p->user->display_name ?: 'VIDE') : 'N/A';
    echo "PostID:{$p->id} | type={$p->type} | user_id={$p->user_id} | pseudo=[{$pseudo}] | nom=[{$userName}] | status={$p->status}\n";
}

// 3. Vérifier la table posts - colonnes
echo "\n--- STRUCTURE TABLE POSTS ---\n";
$columns = Illuminate\Support\Facades\Schema::getColumnListing('posts');
echo "Colonnes: " . implode(', ', $columns) . "\n";

// 4. Vérifier la table users - display_name
echo "\n--- STRUCTURE TABLE USERS (display_name) ---\n";
$hasColumn = Illuminate\Support\Facades\Schema::hasColumn('users', 'display_name');
echo "Colonne display_name existe: " . ($hasColumn ? 'OUI' : 'NON') . "\n";

// 5. Tester la route DELETE
echo "\n--- TEST ROUTE DELETE ---\n";
$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    if (strpos($route->uri(), 'social/posts') !== false) {
        echo $route->methods()[0] . " " . $route->uri() . " -> " . $route->getActionName() . "\n";
    }
}

// 6. Vérifier si le Post model utilise SoftDeletes
echo "\n--- SOFT DELETES ---\n";
$postModel = new App\Post();
$traits = class_uses_recursive($postModel);
echo "SoftDeletes actif: " . (in_array('Illuminate\Database\Eloquent\SoftDeletes', $traits) ? 'OUI' : 'NON') . "\n";

echo "\n=== FIN DIAGNOSTIC ===\n";

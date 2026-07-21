<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
DB::table('provider_documents')->update(['url' => 'https://kissing-planners-vids-sorts.trycloudflare.com/dummy.png']);
DB::table('providers')->update(['avatar' => 'https://kissing-planners-vids-sorts.trycloudflare.com/dummy.png']);
echo "Documents and Avatars updated\n";

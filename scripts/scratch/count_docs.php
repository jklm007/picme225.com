<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$total = App\Document::count();
$submitted = App\ProviderDocument::where('provider_id', 1)->count();
echo "Total Docs: $total, Submitted: $submitted\n";

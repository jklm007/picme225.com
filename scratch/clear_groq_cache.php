<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\Cache;
Cache::forget('groq_available_vision_models');
Cache::forget('groq_unavailable_models');
Cache::forget('groq_last_working_model');
echo "Groq cache cleared!\n";

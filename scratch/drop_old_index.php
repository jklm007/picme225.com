<?php
require 'bootstrap/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Dropping unique_proposal_user_vote...\n";
    Schema::table('dao_votes', function (Blueprint $table) {
        $table->dropUnique('unique_proposal_user_vote');
    });
    echo "✅ Success: Dropped unique_proposal_user_vote!\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

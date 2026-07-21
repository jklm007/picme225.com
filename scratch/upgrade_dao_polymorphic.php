<?php
require 'bootstrap/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "==================================================\n";
echo "    UPGRADING DAO SCHEMAS TO POLYMORPHIC (V2.3)    \n";
echo "==================================================\n\n";

// 1. Upgrade dao_proposals
try {
    echo "1. Modifying 'dao_proposals' table...\n";
    
    // Drop foreign key
    Schema::table('dao_proposals', function (Blueprint $table) {
        $table->dropForeign('dao_proposals_user_id_foreign');
    });
    echo "   - Dropped foreign key 'dao_proposals_user_id_foreign'.\n";
} catch (\Exception $e) {
    echo "   - Already dropped or error on foreign key: " . $e->getMessage() . "\n";
}

try {
    // Add creator_type
    Schema::table('dao_proposals', function (Blueprint $table) {
        if (!Schema::hasColumn('dao_proposals', 'creator_type')) {
            $table->string('creator_type')->default('USER')->after('user_id');
        }
    });
    echo "   - Added 'creator_type' column to 'dao_proposals'.\n";
} catch (\Exception $e) {
    echo "   - creator_type error: " . $e->getMessage() . "\n";
}

// 2. Upgrade dao_votes
try {
    echo "\n2. Modifying 'dao_votes' table (dropping FK)...\n";
    Schema::table('dao_votes', function (Blueprint $table) {
        $table->dropForeign('dao_votes_user_id_foreign');
    });
    echo "   - Dropped foreign key from 'dao_votes'.\n";
} catch (\Exception $e) {
    echo "   - Already dropped or error on FK: " . $e->getMessage() . "\n";
}

try {
    echo "3. Dropping unique constraint from 'dao_votes'...\n";
    Schema::table('dao_votes', function (Blueprint $table) {
        $table->dropUnique('unique_proposal_user_vote');
    });
    echo "   - Dropped unique index 'unique_proposal_user_vote'.\n";
} catch (\Exception $e) {
    echo "   - Error dropping unique constraint: " . $e->getMessage() . "\n";
}

try {
    echo "4. Adding voter_type to 'dao_votes'...\n";
    Schema::table('dao_votes', function (Blueprint $table) {
        if (!Schema::hasColumn('dao_votes', 'voter_type')) {
            $table->string('voter_type')->default('USER')->after('user_id');
        }
    });
    echo "   - Added 'voter_type' column.\n";
} catch (\Exception $e) {
    echo "   - Error adding voter_type: " . $e->getMessage() . "\n";
}

try {
    echo "5. Adding new unique constraint to 'dao_votes'...\n";
    Schema::table('dao_votes', function (Blueprint $table) {
        $table->unique(['proposal_id', 'user_id', 'voter_type'], 'unique_proposal_voter_vote');
    });
    echo "   - Added 'unique_proposal_voter_vote' constraint.\n";
} catch (\Exception $e) {
    echo "   - Error adding unique constraint: " . $e->getMessage() . "\n";
}

echo "\n✅ DAO schema upgrade complete!\n";

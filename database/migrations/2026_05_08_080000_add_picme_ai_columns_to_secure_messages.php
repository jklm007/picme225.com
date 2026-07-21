<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Colonnes PicMe AI sur la table secure_messages
 * - lead_score    : Score de sérieux de l'acheteur (HOT/WARM/COLD/RISKY)
 * - ai_used       : L'IA a-t-elle analysé ce message ?
 * - is_ai_reply   : Ce message a-t-il été généré automatiquement par PicMe AI ?
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secure_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('secure_messages', 'lead_score')) {
                $table->string('lead_score', 10)->default('WARM')->after('is_blocked');
            }
            if (!Schema::hasColumn('secure_messages', 'ai_used')) {
                $table->boolean('ai_used')->default(false)->after('lead_score');
            }
            if (!Schema::hasColumn('secure_messages', 'is_ai_reply')) {
                $table->boolean('is_ai_reply')->default(false)->after('ai_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('secure_messages', function (Blueprint $table) {
            $table->dropColumnIfExists('lead_score');
            $table->dropColumnIfExists('ai_used');
            $table->dropColumnIfExists('is_ai_reply');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSharingFieldsToUserRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            // On ajoute les colonnes après 'service_type_id' qui existe bien
            $table->integer('total_capacity')->nullable()->after('service_type_id');
            $table->integer('seats_booked')->default(0)->after('total_capacity');
            $table->boolean('is_pool_dynamic')->default(false)->after('seats_booked');
            $table->boolean('is_pdp_route')->default(false)->after('is_pool_dynamic');

            // --- CORRECTION POUR LA RELATION AUTO-RÉFÉRENCÉE ---
            // 1. Définir la colonne avec le bon type : INTEGER UNSIGNED
            $table->unsignedInteger('linked_request_id')->nullable()->after('is_pdp_route');

            // 2. Définir la contrainte de clé étrangère sur cette colonne
            $table->foreign('linked_request_id')
                  ->references('id')->on('user_requests') // Elle pointe vers la même table 'user_requests'
                  ->onDelete('set null'); // Si la course principale est supprimée, on met juste null ici

            
            // Champs pour la livraison (leur positionnement n'est pas critique)
            $table->string('sender_name')->nullable()->after('linked_request_id');
            $table->string('sender_phone')->nullable()->after('sender_name');
            $table->string('recipient_name')->nullable()->after('sender_phone');
            $table->string('recipient_phone')->nullable()->after('recipient_name');
            $table->text('package_description')->nullable()->after('recipient_phone');
        });
    }

    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            // Il est important de supprimer la clé étrangère AVANT les colonnes
            $table->dropForeign(['linked_request_id']);
            
            // Supprimer toutes les colonnes ajoutées dans cette migration
            $table->dropColumn([
                'total_capacity',
                'seats_booked',
                'is_pool_dynamic',
                'is_pdp_route',
                'linked_request_id',
                'sender_name',
                'sender_phone',
                'recipient_name',
                'recipient_phone',
                'package_description'
            ]);
        });
    }
}

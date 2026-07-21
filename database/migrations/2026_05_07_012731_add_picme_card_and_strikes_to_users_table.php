<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPicmeCardAndStrikesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('picme_card_token')->nullable()->unique()->after('id')->comment('Jeton unique pour la Carte PicMe (NFC/QR)');
            $table->integer('cancellation_strikes')->default(0)->after('picme_card_token')->comment('Compteur d\'annulations suspectes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('picme_card_token');
            $table->dropColumn('cancellation_strikes');
        });
    }
}

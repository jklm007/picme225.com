<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutstationFieldsToUserRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->integer('luggage_count')->default(0)->after('distance');
            $table->string('selected_seats')->nullable()->after('luggage_count');
            $table->unsignedBigInteger('interurban_company_id')->nullable()->after('selected_seats');
            $table->foreign('interurban_company_id')->references('id')->on('interurban_companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropForeign(['interurban_company_id']);
            $table->dropColumn(['luggage_count', 'selected_seats', 'interurban_company_id']);
        });
    }
}

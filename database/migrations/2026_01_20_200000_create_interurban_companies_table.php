<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterurbanCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interurban_companies', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('logo')->nullable();
            $row->string('contact_phone')->nullable();
            $row->string('contact_email')->nullable();
            $row->text('address')->nullable();
            $row->boolean('is_active')->default(true);
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interurban_companies');
    }
}

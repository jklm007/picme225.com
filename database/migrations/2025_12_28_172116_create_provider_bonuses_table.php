<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->enum('bonus_type', [
                'RATING_EXCELLENCE',
                'QUICK_ACCEPTANCE',
                'PUNCTUALITY',
                'PEAK_HOURS',
                'ZERO_CANCELLATION',
                'SENIORITY',
                'MILESTONE',
                'STREAK',
                'GROWTH',
                'REFERRAL_DRIVER',
                'REFERRAL_PASSENGER',
                'TOP_PERFORMER'
            ]);
            $table->decimal('amount', 10, 4)->comment('Montant en ECO');
            $table->string('trigger')->nullable()->comment('Ex: rating_4.9, 100_rides');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('related_id')->nullable()->comment('ID de la course ou du filleul');
            $table->string('related_type')->nullable()->comment('UserRequest, Provider');
            $table->enum('status', ['PENDING', 'APPROVED', 'PAID'])->default('APPROVED');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->index(['provider_id', 'bonus_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_bonuses');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pdp_route_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_route_id');
            $table->unsignedBigInteger('pdp_stop_id');
            $table->integer('order')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('pdp_route_id')->references('id')->on('pdp_routes')->onDelete('cascade');
            $table->foreign('pdp_stop_id')->references('id')->on('pdp_stops')->onDelete('cascade');
        });

        // Migrate existing data
        $stops = \DB::table('pdp_stops')->whereNotNull('pdp_route_id')->get();
        foreach ($stops as $stop) {
            \DB::table('pdp_route_stops')->insert([
                'pdp_route_id' => $stop->pdp_route_id,
                'pdp_stop_id' => $stop->id,
                'order' => $stop->order ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // We can safely drop pdp_route_id and order from pdp_stops now
        Schema::table('pdp_stops', function (Blueprint $table) {
            try {
                $table->dropForeign('pdp_stops_pdp_route_id_foreign');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('pdp_stops_pdp_route_id_order_index');
            } catch (\Exception $e) {}
            
            $table->dropColumn(['pdp_route_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->unsignedBigInteger('pdp_route_id')->nullable();
            $table->integer('order')->nullable();
        });
        Schema::dropIfExists('pdp_route_stops');
    }
};

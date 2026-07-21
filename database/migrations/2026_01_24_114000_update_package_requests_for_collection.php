<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePackageRequestsForCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('package_requests', 'pdp_route_id')) {
                $table->unsignedBigInteger('pdp_route_id')->nullable()->after('interurban_company_id');
            }
            if (!Schema::hasColumn('package_requests', 'needs_collection')) {
                $table->boolean('needs_collection')->default(false)->after('pdp_route_id');
            }
            if (!Schema::hasColumn('package_requests', 'collection_request_id')) {
                $table->unsignedInteger('collection_request_id')->nullable()->after('needs_collection');
            }

            // Add foreign key for collection_request_id if it doesn't exist
            // Assuming user_requests table uses increments('id') which is unsignedInteger
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_requests', function (Blueprint $table) {
            $table->dropColumn(['pdp_route_id', 'needs_collection', 'collection_request_id']);
        });
    }
}

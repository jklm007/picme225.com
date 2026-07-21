<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Drop old status check constraint
            DB::statement("ALTER TABLE marketplace_listings DROP CONSTRAINT IF EXISTS marketplace_listings_status_check");
            
            // Add updated status check constraint to include WhatsApp statuses
            DB::statement("ALTER TABLE marketplace_listings ADD CONSTRAINT marketplace_listings_status_check CHECK (status::text = ANY (ARRAY[
                'ACTIVE'::text, 
                'RESERVED'::text, 
                'SOLD'::text, 
                'PAUSED'::text,
                'PENDING_VALIDATION'::text,
                'APPROVED'::text,
                'REJECTED'::text
            ]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE marketplace_listings DROP CONSTRAINT IF EXISTS marketplace_listings_status_check");
            DB::statement("ALTER TABLE marketplace_listings ADD CONSTRAINT marketplace_listings_status_check CHECK (status::text = ANY (ARRAY[
                'ACTIVE'::text, 
                'RESERVED'::text, 
                'SOLD'::text, 
                'PAUSED'::text
            ]))");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the product_type enum to allow additional values: consumable, equipment, other
        // MySQL allows ENUM modifications using MODIFY or CHANGE, so execute a raw SQL statement
        // This is safe for migrations where the column already exists.
        DB::statement("ALTER TABLE `products` MODIFY `product_type` ENUM('raw_material','semi_finished','finished_good','consumable','equipment','other') NOT NULL DEFAULT 'raw_material'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original three values
        DB::statement("ALTER TABLE `products` MODIFY `product_type` ENUM('raw_material','semi_finished','finished_good') NOT NULL DEFAULT 'raw_material'");
    }
};

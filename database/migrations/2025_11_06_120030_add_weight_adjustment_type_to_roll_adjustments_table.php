<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE roll_adjustments MODIFY COLUMN adjustment_type ENUM('ADD', 'REMOVE', 'DAMAGE', 'RESTORE', 'WEIGHT_ADJUST') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE roll_adjustments MODIFY COLUMN adjustment_type ENUM('ADD', 'REMOVE', 'DAMAGE', 'RESTORE') NOT NULL");
    }
};

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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE bon_transferts MODIFY COLUMN status ENUM('draft', 'in_transit', 'received', 'confirmed', 'cancelled', 'archived') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE bon_transferts MODIFY COLUMN status ENUM('draft', 'in_transit', 'received', 'confirmed', 'archived') DEFAULT 'draft'");
    }
};

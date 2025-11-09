<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN status ENUM('draft', 'pending', 'confirmed', 'cancelled') DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN status ENUM('draft', 'confirmed', 'cancelled') DEFAULT 'draft'");
    }
};

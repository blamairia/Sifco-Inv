<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_quantities', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_quantities', 'last_movement_id')) {
                $table->foreignId('last_movement_id')
                    ->nullable()
                    ->after('cump_snapshot')
                    ->constrained('stock_movements')
                    ->noActionOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_quantities', function (Blueprint $table) {
            if (Schema::hasColumn('stock_quantities', 'last_movement_id')) {
                $table->dropForeign(['last_movement_id']);
                $table->dropColumn('last_movement_id');
            }
        });
    }
};

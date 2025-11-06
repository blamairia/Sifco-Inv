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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('roll_weight_before_kg', 12, 3)->nullable()->after('qty_moved');
            $table->decimal('roll_weight_after_kg', 12, 3)->nullable()->after('roll_weight_before_kg');
            $table->decimal('roll_weight_delta_kg', 12, 3)->nullable()->after('roll_weight_after_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn([
                'roll_weight_before_kg',
                'roll_weight_after_kg',
                'roll_weight_delta_kg',
            ]);
        });
    }
};

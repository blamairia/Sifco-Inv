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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->decimal('weight_before_kg', 15, 3)->nullable()->after('qty_change');
            $table->decimal('weight_after_kg', 15, 3)->nullable()->after('weight_before_kg');
            $table->decimal('weight_change_kg', 15, 3)->nullable()->after('weight_after_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn([
                'weight_before_kg',
                'weight_after_kg',
                'weight_change_kg',
            ]);
        });
    }
};

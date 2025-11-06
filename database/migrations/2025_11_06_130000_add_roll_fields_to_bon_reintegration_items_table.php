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
        Schema::table('bon_reintegration_items', function (Blueprint $table) {
            $table->enum('item_type', ['product', 'roll'])->default('product')->after('bon_reintegration_id');
            $table->foreignId('roll_id')->nullable()->after('product_id')->constrained('rolls')->nullOnDelete();
            $table->decimal('previous_weight_kg', 12, 3)->nullable()->after('qty_returned');
            $table->decimal('returned_weight_kg', 12, 3)->nullable()->after('previous_weight_kg');
            $table->decimal('weight_delta_kg', 12, 3)->nullable()->after('returned_weight_kg');
            $table->decimal('cump_at_return', 12, 2)->nullable()->after('weight_delta_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_reintegration_items', function (Blueprint $table) {
            $table->dropForeign(['roll_id']);
            $table->dropColumn([
                'item_type',
                'roll_id',
                'previous_weight_kg',
                'returned_weight_kg',
                'weight_delta_kg',
                'cump_at_return',
            ]);
        });
    }
};

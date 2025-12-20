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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique()->comment('ADJ-YYYYMMDD-####');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->noActionOnDelete();
            $table->decimal('qty_before', 15, 2)->comment('Quantity before adjustment');
            $table->decimal('qty_after', 15, 2)->comment('Quantity after adjustment');
            $table->decimal('qty_change', 15, 2)->comment('Positive or negative change');
            $table->enum('adjustment_type', ['INCREASE', 'DECREASE', 'CORRECTION'])->default('CORRECTION');
            $table->text('reason')->comment('Required explanation for adjustment');
            $table->foreignId('adjusted_by')->constrained('users')->noActionOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'warehouse_id']);
            $table->index('adjustment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};

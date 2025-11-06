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
        Schema::create('roll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('roll_id')->constrained('rolls')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->enum('adjustment_type', ['ADD', 'REMOVE', 'DAMAGE', 'RESTORE']);
            $table->enum('previous_status', ['in_stock', 'reserved', 'consumed', 'damaged', 'archived'])->nullable();
            $table->enum('new_status', ['in_stock', 'reserved', 'consumed', 'damaged', 'archived']);
            $table->text('reason');
            $table->foreignId('adjusted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'adjustment_type']);
            $table->index(['product_id', 'adjustment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roll_adjustments');
    }
};

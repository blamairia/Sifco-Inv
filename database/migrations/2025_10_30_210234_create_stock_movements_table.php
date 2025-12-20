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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique()->comment('BON-MOV-2025-0001');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_from_id')->nullable()->constrained('warehouses')->noActionOnDelete();
            $table->foreignId('warehouse_to_id')->nullable()->constrained('warehouses')->noActionOnDelete();
            $table->enum('movement_type', ['RECEPTION', 'ISSUE', 'TRANSFER', 'RETURN']);
            $table->decimal('qty_moved', 15, 2);
            $table->decimal('cump_at_movement', 12, 2)->comment('CUMP snapshot');
            $table->decimal('value_moved', 15, 2)->storedAs('qty_moved * cump_at_movement');
            $table->enum('status', ['draft', 'pending', 'confirmed', 'cancelled'])->default('draft');
            $table->string('reference_number')->nullable()->comment('Links to bon tables');
            $table->foreignId('user_id')->constrained('users')->noActionOnDelete();
            $table->timestamp('performed_at');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'status']);
            $table->index(['warehouse_from_id', 'warehouse_to_id']);
            $table->index('movement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

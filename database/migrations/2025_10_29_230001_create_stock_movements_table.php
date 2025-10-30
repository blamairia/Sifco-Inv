<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Complete audit trail for all stock movements.
     * Every entry, issue, transfer, return, adjustment creates a movement.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number', 50)->unique()->comment('BON-MOV-2025-0001');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_from_id')->nullable()->constrained('warehouses')->comment('NULL for RECEPTION');
            $table->foreignId('warehouse_to_id')->nullable()->constrained('warehouses')->comment('NULL for ISSUE');
            
            $table->enum('movement_type', [
                'RECEPTION',      // Supplier → Warehouse
                'ISSUE',          // Warehouse → NULL (Production)
                'TRANSFER',       // Warehouse → Warehouse
                'RETURN',         // NULL → Warehouse (Reintegration)
                'ADJUSTMENT',     // Manual adjustment
            ])->comment('Type of movement');
            
            $table->decimal('qty_moved', 15, 2)->comment('Quantity moved');
            $table->decimal('cump_at_movement', 12, 2)->comment('CUMP snapshot at time of movement');
            $table->decimal('value_moved', 15, 2)->comment('qty_moved × cump_at_movement');
            
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->string('reference_number', 100)->nullable()->comment('Links to bon_* table');
            
            $table->foreignId('user_id')->constrained('users')->comment('Who performed');
            $table->timestamp('performed_at');
            
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->comment('Manager approval');
            $table->timestamp('approved_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('movement_number');
            $table->index('product_id');
            $table->index('status');
            $table->index('movement_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

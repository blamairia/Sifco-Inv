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
        Schema::create('stock_quantities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('total_qty', 15, 2)->default(0);
            $table->decimal('reserved_qty', 15, 2)->default(0)->comment('For future use');
            $table->decimal('available_qty', 15, 2)->storedAs('total_qty - reserved_qty');
            $table->decimal('cump_snapshot', 12, 2)->default(0)->comment('Last known CUMP');
            $table->timestamp('updated_at');
            $table->unique(['product_id', 'warehouse_id']);
            
            $table->index(['warehouse_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_quantities');
    }
};

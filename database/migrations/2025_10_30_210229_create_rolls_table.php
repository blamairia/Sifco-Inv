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
        Schema::create('rolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('ean_13', 13)->unique()->comment('Unique barcode for this roll');
            $table->string('batch_number')->nullable()->comment('Supplier batch');
            $table->date('received_date');
            $table->unsignedBigInteger('received_from_movement_id')->nullable()->comment('FK to stock_movements - added later');
            $table->enum('status', ['in_stock', 'reserved', 'consumed', 'damaged', 'archived'])->default('in_stock');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['warehouse_id', 'status']);
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rolls');
    }
};

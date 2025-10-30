<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock Adjustments – Manual Inventory Corrections
     */
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique()->comment('ADJ-{YMMDD}-{seq}');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            
            $table->decimal('qty_adjustment', 15, 2)->comment('Positive or negative adjustment');
            $table->enum('reason', [
                'inventory_count',
                'damage',
                'loss',
                'correction',
                'other',
            ])->comment('Reason for adjustment');
            
            $table->date('adjustment_date');
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'archived',
            ])->default('draft');
            
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->comment('Manager');
            $table->timestamp('approved_at')->nullable();
            
            $table->text('notes')->comment('Description of adjustment');
            $table->timestamps();
            
            $table->index('adjustment_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};

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
        Schema::create('bon_transfert_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_transfert_id')->constrained('bon_transferts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_transferred', 15, 2);
            $table->decimal('cump_at_transfer', 12, 2)->comment('Transfer at original cost');
            $table->decimal('value_transferred', 15, 2)->storedAs('qty_transferred * cump_at_transfer');
            $table->timestamps();
            
            $table->index('bon_transfert_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_transfert_items');
    }
};

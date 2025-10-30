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
        Schema::create('bon_entree_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_entree_id')->constrained('bon_entrees')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_entered', 15, 2);
            $table->decimal('price_ht', 12, 2)->comment('Unit price before fees');
            $table->decimal('price_ttc', 12, 2)->comment('Unit price after fees distribution');
            $table->decimal('line_total_ttc', 15, 2)->storedAs('qty_entered * price_ttc');
            $table->timestamps();
            
            $table->index('bon_entree_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_entree_items');
    }
};

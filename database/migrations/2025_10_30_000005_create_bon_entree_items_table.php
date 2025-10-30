<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon d'Entrée Items – Line items for stock entry
     */
    public function up(): void
    {
        Schema::create('bon_entree_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_entree_id')->constrained();
            $table->foreignId('product_id')->constrained();
            
            $table->decimal('qty_entered', 15, 2)->comment('Quantity received');
            $table->decimal('price_ht', 12, 2)->comment('Unit price before frais d\'approche');
            $table->decimal('price_ttc', 12, 2)->comment('Unit price after frais allocation');
            $table->decimal('line_total_ttc', 15, 2)->comment('qty_entered × price_ttc');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_entree_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon de Réintégration Items – Line items for returns
     */
    public function up(): void
    {
        Schema::create('bon_reintegration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_reintegration_id')->constrained();
            $table->foreignId('product_id')->constrained();
            
            $table->decimal('qty_returned', 15, 2)->comment('Quantity returned');
            $table->decimal('cump_at_return', 12, 2)->comment('CUMP from original issue date');
            $table->decimal('value_returned', 15, 2)->comment('qty_returned × cump_at_return');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_reintegration_items');
    }
};

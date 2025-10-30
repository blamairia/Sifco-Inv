<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon de Sortie Items – Line items for stock issues
     */
    public function up(): void
    {
        Schema::create('bon_sortie_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_sortie_id')->constrained();
            $table->foreignId('product_id')->constrained();
            
            $table->decimal('qty_issued', 15, 2)->comment('Quantity issued');
            $table->decimal('cump_at_issue', 12, 2)->comment('CUMP snapshot for valuation');
            $table->decimal('value_issued', 15, 2)->comment('qty_issued × cump_at_issue');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_sortie_items');
    }
};

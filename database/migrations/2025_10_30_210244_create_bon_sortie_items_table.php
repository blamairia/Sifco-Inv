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
        Schema::create('bon_sortie_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_sortie_id')->constrained('bon_sorties')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_issued', 15, 2);
            $table->decimal('cump_at_issue', 12, 2)->comment('CUMP snapshot for valuation');
            $table->decimal('value_issued', 15, 2)->storedAs('qty_issued * cump_at_issue');
            $table->timestamps();
            
            $table->index('bon_sortie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_sortie_items');
    }
};

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
        Schema::create('bon_reintegration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_reintegration_id')->constrained('bon_reintegrations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_returned', 15, 2);
            $table->decimal('value_returned', 15, 2)->comment('qty * cump_at_return');
            $table->timestamps();
            
            $table->index('bon_reintegration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_reintegration_items');
    }
};

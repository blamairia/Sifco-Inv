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
        Schema::create('receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained();
            $table->foreignId('roll_specification_id')->constrained();
            $table->integer('qty_received')->comment('Number of rolls received');
            $table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['receipt_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
    }
};

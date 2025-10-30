<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon de Transfert Items – Line items for transfers
     */
    public function up(): void
    {
        Schema::create('bon_transfert_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_transfert_id')->constrained();
            $table->foreignId('product_id')->constrained();
            
            $table->decimal('qty_transferred', 15, 2)->comment('Quantity transferred');
            $table->decimal('cump_at_transfer', 12, 2)->comment('CUMP preserved during transfer');
            $table->decimal('value_transferred', 15, 2)->comment('qty_transferred × cump_at_transfer');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_transfert_items');
    }
};

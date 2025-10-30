<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon de Transfert – Inter-warehouse Transfer
     */
    public function up(): void
    {
        Schema::create('bon_transferts', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number', 50)->unique()->comment('BTRN-{YMMDD}-{seq}');
            $table->foreignId('warehouse_from_id')->constrained('warehouses');
            $table->foreignId('warehouse_to_id')->constrained('warehouses');
            $table->date('transfer_date');
            
            $table->enum('status', [
                'draft',
                'in_transit',
                'received',
                'confirmed',
                'archived',
            ])->default('draft');
            
            $table->foreignId('requested_by_id')->constrained('users')->comment('Demandeur');
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by_id')->nullable()->constrained('users')->comment('Magasinier receiver');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('bon_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_transferts');
    }
};

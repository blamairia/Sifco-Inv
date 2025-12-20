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
        Schema::create('bon_transferts', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number')->unique();
            $table->foreignId('warehouse_from_id')->constrained('warehouses')->noActionOnDelete();
            $table->foreignId('warehouse_to_id')->constrained('warehouses')->noActionOnDelete();
            $table->date('transfer_date');
            $table->enum('status', ['draft', 'in_transit', 'received', 'confirmed', 'cancelled', 'archived'])->default('draft');
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['warehouse_from_id', 'warehouse_to_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_transferts');
    }
};

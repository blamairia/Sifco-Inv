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
        Schema::create('bon_entrees', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number')->unique();
            $table->foreignId('bon_reception_id')->constrained('bon_receptions')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('receipt_date');
            $table->enum('status', ['draft', 'entered', 'confirmed', 'archived'])->default('draft');
            $table->foreignId('entered_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->decimal('total_amount_ttc', 15, 2)->default(0)->comment('Including frais d\'approche');
            $table->decimal('total_amount_ht', 15, 2)->default(0)->comment('Before frais');
            $table->decimal('frais_approche', 15, 2)->default(0)->comment('Transport, D3, transitaire');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['warehouse_id', 'receipt_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_entrees');
    }
};

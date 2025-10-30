<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon d'Entrée – Stock Entry to Warehouse
     * 
     * SIFCO Procedure Reference:
     * "Le gestionnaire des stocks enregistre le bon d'entrée dans le logiciel de gestion
     * sur la base du bon de réception... La valorisation s'effectue au coût d'achat"
     */
    public function up(): void
    {
        Schema::create('bon_entrees', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number', 50)->unique()->comment('BENT-{YMMDD}-{seq}');
            $table->foreignId('bon_reception_id')->constrained('bon_receptions');
            $table->foreignId('warehouse_id')->constrained();
            $table->date('receipt_date');
            
            $table->enum('status', [
                'draft',
                'entered',
                'confirmed',
                'archived',
            ])->default('draft')->comment('Entry status');
            
            $table->foreignId('entered_by_id')->constrained('users')->comment('Gestionnaire des stocks');
            $table->timestamp('entered_at')->nullable();
            
            $table->decimal('total_amount_ht', 15, 2)->default(0)->comment('Total before frais d\'approche');
            $table->decimal('frais_approche', 15, 2)->default(0)->comment('Transport, D3, transitaire fees');
            $table->decimal('total_amount_ttc', 15, 2)->default(0)->comment('Total TTC (including VAT)');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('bon_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_entrees');
    }
};

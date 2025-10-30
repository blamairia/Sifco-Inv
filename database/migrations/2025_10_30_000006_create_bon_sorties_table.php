<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon de Sortie – Stock Issue to Production
     * 
     * SIFCO Procedure Reference:
     * "Le demandeur transmet un bon d'approvisionnement signé au magasinier,
     * ce dernier vérifie le stock, prépare la commande et procède à la mise à disposition"
     */
    public function up(): void
    {
        Schema::create('bon_sorties', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number', 50)->unique()->comment('BSRT-{YMMDD}-{seq}');
            $table->foreignId('warehouse_id')->constrained();
            $table->date('issued_date');
            $table->string('destination', 255)->comment('Production, department, etc.');
            
            $table->enum('status', [
                'draft',
                'issued',
                'confirmed',
                'archived',
            ])->default('draft');
            
            $table->foreignId('issued_by_id')->constrained('users')->comment('Magasinier');
            $table->timestamp('issued_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('bon_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_sorties');
    }
};

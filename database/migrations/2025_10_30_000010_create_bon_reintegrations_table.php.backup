<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bon de Réintégration – Return to Stock
     * 
     * SIFCO Procedure Reference:
     * "L'utilisateur présente le bon d'approvisionnement de l'article retourné au magasinier.
     * Le gestionnaire des stocks valorise la réintégration sur la base du CUMP de la date de sortie."
     */
    public function up(): void
    {
        Schema::create('bon_reintegrations', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number', 50)->unique()->comment('BRIN-{YMMDD}-{seq}');
            $table->foreignId('bon_sortie_id')->constrained('bon_sorties')->comment('Original issue');
            $table->foreignId('warehouse_id')->constrained()->comment('Return destination');
            $table->date('return_date');
            
            $table->enum('status', [
                'draft',
                'received',
                'verified',
                'confirmed',
                'archived',
            ])->default('draft');
            
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->comment('Magasinier');
            $table->timestamp('verified_at')->nullable();
            
            $table->string('physical_condition', 100)->nullable()->comment('unopened, slight_damage, major_damage');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('bon_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_reintegrations');
    }
};

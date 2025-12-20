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
        Schema::create('bon_reintegrations', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number')->unique();
            $table->foreignId('bon_sortie_id')->constrained('bon_sorties')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->noActionOnDelete();
            $table->date('return_date');
            $table->enum('status', ['draft', 'received', 'verified', 'confirmed', 'archived'])->default('draft');
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->decimal('cump_at_return', 12, 2)->comment('CUMP from original issue date');
            $table->text('notes')->nullable();
            $table->string('physical_condition')->nullable()->comment('unopened, slight_damage');
            $table->timestamps();
            
            $table->index(['warehouse_id', 'return_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_reintegrations');
    }
};

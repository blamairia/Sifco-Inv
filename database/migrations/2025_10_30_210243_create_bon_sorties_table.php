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
        Schema::create('bon_sorties', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->noActionOnDelete();
            $table->date('issued_date');
            $table->enum('status', ['draft', 'issued', 'confirmed', 'archived'])->default('draft');
            $table->foreignId('issued_by_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->string('destination')->comment('Production, Client, department');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['warehouse_id', 'issued_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_sorties');
    }
};

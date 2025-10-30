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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->enum('type', ['papier_roll', 'consommable', 'fini']);
            $table->text('description')->nullable();
            
            // Key attributes for paper rolls (filterable/indexable)
            $table->integer('grammage')->nullable()->comment('GSM - grammes par mètre carré (only for papier_roll)');
            $table->integer('laize')->nullable()->comment('Width in mm (only for papier_roll)');
            $table->string('flute', 10)->nullable()->comment('Flute type: E, B, C, etc. (only for papier_roll)');
            $table->string('type_papier', 50)->nullable()->comment('Kraft, Test, etc. (only for papier_roll)');
            
            // Flexible JSON for other attributes
            $table->json('extra_attributes')->nullable()->comment('Flexible storage for other specs');
            
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->decimal('min_stock', 15, 2)->default(0);
            $table->decimal('safety_stock', 15, 2)->default(0);
            $table->timestamps();
            
            // Indexes for filtering
            $table->index(['type', 'grammage']);
            $table->index(['type', 'laize']);
            $table->index(['type', 'flute']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roll_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roll_id')->constrained('rolls')->cascadeOnDelete();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->string('event_type')->comment('RECEPTION, TRANSFER, SORTIE, REINTEGRATION, ADJUSTMENT');
            $table->string('reference_number')->nullable()->comment('Related bon number');
            
            // Physical metrics
            $table->decimal('weight_before_kg', 12, 3);
            $table->decimal('weight_after_kg', 12, 3);
            $table->decimal('weight_delta_kg', 12, 3);
            $table->decimal('length_before_m', 12, 3);
            $table->decimal('length_after_m', 12, 3);
            $table->decimal('length_delta_m', 12, 3);
            
            // Waste tracking
            $table->boolean('has_waste')->default(false);
            $table->decimal('waste_weight_kg', 12, 3)->default(0);
            $table->decimal('waste_length_m', 12, 3)->default(0);
            $table->string('waste_reason')->nullable();
            
            // Source/destination
            $table->foreignId('warehouse_from_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('warehouse_to_id')->nullable()->constrained('warehouses')->nullOnDelete();
            
            // Event metadata
            $table->foreignId('triggered_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable()->comment('Event-specific data like roll_spec changes');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexing
            $table->index(['roll_id', 'event_type']);
            $table->index(['event_type', 'reference_number']);
            $table->index('has_waste');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roll_lifecycle_events');
    }
};
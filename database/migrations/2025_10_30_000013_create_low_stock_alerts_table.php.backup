<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Low Stock Alerts – Avis de Rupture
     * 
     * SIFCO Procedure Reference (Annexe 2):
     * "Nous vous informons que le stock minimum/stock de sécurité est atteint pour les articles"
     * 
     * Auto-generated when qty < min_stock or qty < safety_stock
     */
    public function up(): void
    {
        Schema::create('low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_number', 50)->unique()->comment('ALERT-{YMMDD}-{seq}');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained()->comment('NULL = all warehouses');
            
            $table->decimal('current_qty', 15, 2)->comment('Current quantity at alert time');
            $table->decimal('min_stock', 15, 2)->comment('Minimum stock threshold');
            $table->decimal('safety_stock', 15, 2)->comment('Safety stock threshold');
            
            $table->enum('alert_type', [
                'min_stock_reached',
                'safety_stock_reached',
            ])->comment('Which threshold was crossed');
            
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by_id')->nullable()->constrained('users')->comment('Gestionnaire');
            $table->timestamp('acknowledged_at')->nullable();
            
            $table->boolean('reorder_requested')->default(false);
            $table->decimal('reorder_qty', 15, 2)->nullable()->comment('Quantity to reorder');
            
            $table->timestamps();
            
            $table->index('alert_number');
            $table->index(['product_id', 'is_acknowledged']);
            $table->index('alert_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('low_stock_alerts');
    }
};

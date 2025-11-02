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
        Schema::table('bon_entrees', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['bon_reception_id']);
            $table->dropForeign(['entered_by_id']);
        });
        
        Schema::table('bon_entrees', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['bon_reception_id', 'entered_by_id', 'entered_at', 'receipt_date', 'status']);
            
            // Add new fields
            $table->foreignId('supplier_id')->after('bon_number')->constrained('suppliers')->cascadeOnDelete();
            $table->string('document_number')->nullable()->after('supplier_id')->comment('Supplier invoice/delivery number');
            $table->date('expected_date')->nullable()->after('warehouse_id')->comment('Expected arrival date');
            $table->date('received_date')->nullable()->after('expected_date')->comment('Actual received date');
            $table->enum('status', ['draft', 'pending', 'validated', 'received', 'cancelled'])
                ->default('draft')
                ->after('received_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_entrees', function (Blueprint $table) {
            // Drop new foreign key
            $table->dropForeign(['supplier_id']);
        });
        
        Schema::table('bon_entrees', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['supplier_id', 'document_number', 'expected_date', 'received_date', 'status']);
            
            // Restore old fields
            $table->foreignId('bon_reception_id')->after('bon_number')->constrained('bon_receptions')->cascadeOnDelete();
            $table->date('receipt_date')->after('warehouse_id');
            $table->enum('status', ['draft', 'entered', 'confirmed', 'archived'])
                ->default('draft')
                ->after('receipt_date');
            $table->foreignId('entered_by_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable()->after('entered_by_id');
        });
    }
};

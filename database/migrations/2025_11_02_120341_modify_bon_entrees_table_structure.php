<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS "bon_entrees_warehouse_id_receipt_date_index"');
                DB::statement('DROP INDEX IF EXISTS "bon_entrees_status_index"');
            }

            if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
                // Drop indices explicitly for SQL Server
                DB::statement("IF EXISTS (SELECT * FROM sys.indexes WHERE name = 'bon_entrees_warehouse_id_receipt_date_index' AND object_id = OBJECT_ID('bon_entrees')) DROP INDEX bon_entrees.bon_entrees_warehouse_id_receipt_date_index");
                DB::statement("IF EXISTS (SELECT * FROM sys.indexes WHERE name = 'bon_entrees_status_index' AND object_id = OBJECT_ID('bon_entrees')) DROP INDEX bon_entrees.bon_entrees_status_index");
                
                // Drop check constraints on status column
                DB::statement("
                    DECLARE @sql NVARCHAR(MAX) = N'';
                    SELECT @sql += N'ALTER TABLE bon_entrees DROP CONSTRAINT ' + name + N';'
                    FROM sys.check_constraints
                    WHERE parent_object_id = OBJECT_ID('bon_entrees')
                    AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('bon_entrees'), 'status', 'ColumnId');
                    IF @sql IS NOT NULL EXEC sp_executesql @sql;
                ");
            }

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

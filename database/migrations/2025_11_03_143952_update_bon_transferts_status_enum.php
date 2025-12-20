<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = N'';
                SELECT @sql += N'ALTER TABLE bon_transferts DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('bon_transferts')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('bon_transferts'), 'status', 'ColumnId');
                EXEC sp_executesql @sql;
            ");
            DB::statement("ALTER TABLE bon_transferts ADD CONSTRAINT bon_transferts_status_check CHECK (status IN ('draft', 'in_transit', 'received', 'confirmed', 'cancelled', 'archived'))");
        } else {
            DB::statement("ALTER TABLE bon_transferts MODIFY COLUMN status ENUM('draft', 'in_transit', 'received', 'confirmed', 'cancelled', 'archived') DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE bon_transferts MODIFY COLUMN status ENUM('draft', 'in_transit', 'received', 'confirmed', 'archived') DEFAULT 'draft'");
    }
};

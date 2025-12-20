<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = N'';
                SELECT @sql += N'ALTER TABLE stock_movements DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('stock_movements')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('stock_movements'), 'status', 'ColumnId');
                EXEC sp_executesql @sql;
            ");
            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_status_check CHECK (status IN ('draft', 'pending', 'confirmed', 'cancelled'))");
        } else {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN status ENUM('draft', 'pending', 'confirmed', 'cancelled') DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN status ENUM('draft', 'confirmed', 'cancelled') DEFAULT 'draft'");
    }
};

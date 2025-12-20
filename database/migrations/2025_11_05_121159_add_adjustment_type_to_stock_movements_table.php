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
                SELECT @sql += N'ALTER TABLE stock_movements DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('stock_movements')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('stock_movements'), 'movement_type', 'ColumnId');
                EXEC sp_executesql @sql;
            ");
            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_movement_type_check CHECK (movement_type IN ('RECEPTION', 'ISSUE', 'TRANSFER', 'RETURN', 'ADJUSTMENT'))");
        } else {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN movement_type ENUM('RECEPTION', 'ISSUE', 'TRANSFER', 'RETURN', 'ADJUSTMENT') NOT NULL");
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

        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN movement_type ENUM('RECEPTION', 'ISSUE', 'TRANSFER', 'RETURN') NOT NULL");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                SELECT @sql += N'ALTER TABLE roll_adjustments DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('roll_adjustments')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('roll_adjustments'), 'adjustment_type', 'ColumnId');
                EXEC sp_executesql @sql;
            ");
            DB::statement("ALTER TABLE roll_adjustments ADD CONSTRAINT roll_adjustments_adjustment_type_check CHECK (adjustment_type IN ('ADD', 'REMOVE', 'DAMAGE', 'RESTORE', 'WEIGHT_ADJUST'))");
        } else {
            DB::statement("ALTER TABLE roll_adjustments MODIFY COLUMN adjustment_type ENUM('ADD', 'REMOVE', 'DAMAGE', 'RESTORE', 'WEIGHT_ADJUST') NOT NULL");
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

        DB::statement("ALTER TABLE roll_adjustments MODIFY COLUMN adjustment_type ENUM('ADD', 'REMOVE', 'DAMAGE', 'RESTORE') NOT NULL");
    }
};

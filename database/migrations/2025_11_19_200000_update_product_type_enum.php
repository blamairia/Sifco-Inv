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
        // Modify the product_type enum to allow additional values: consumable, equipment, other
        // MySQL allows ENUM modifications using MODIFY or CHANGE, so execute a raw SQL statement
        // This is safe for migrations where the column already exists.
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = N'';
                SELECT @sql += N'ALTER TABLE products DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('products')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('products'), 'product_type', 'ColumnId');
                EXEC sp_executesql @sql;
            ");
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_product_type_check CHECK (product_type IN ('raw_material','semi_finished','finished_good','consumable','equipment','other'))");
        } else {
            DB::statement("ALTER TABLE `products` MODIFY `product_type` ENUM('raw_material','semi_finished','finished_good','consumable','equipment','other') NOT NULL DEFAULT 'raw_material'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original three values
        DB::statement("ALTER TABLE `products` MODIFY `product_type` ENUM('raw_material','semi_finished','finished_good') NOT NULL DEFAULT 'raw_material'");
    }
};

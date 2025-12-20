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
        // Step 1: Drop indexes that depend on type column
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['type', 'grammage']); // products_type_grammage_index
            $table->dropIndex(['type', 'laize']); // products_type_laize_index
            $table->dropIndex(['type', 'flute']); // products_type_flute_index
        });

        // Step 2: Add new form_type column
        Schema::table('products', function (Blueprint $table) {
            $table->enum('form_type', ['roll', 'sheet', 'other'])
                ->nullable()
                ->after('product_type')
                ->comment('Physical form: roll (bobine), sheet (feuille), other');
        });

        // Step 3: Migrate existing data from type + is_roll to form_type
        DB::table('products')->update([
            'form_type' => DB::raw("
                CASE
                    WHEN is_roll = 1 OR type = 'papier_roll' THEN 'roll'
                    WHEN type = 'consommable' THEN 'consumable'
                    WHEN type = 'fini' THEN 'sheet'
                    ELSE 'other'
                END
            ")
        ]);

        // Step 4: Make form_type non-nullable after migration
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("ALTER TABLE products ALTER COLUMN form_type NVARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_form_type_check CHECK (form_type IN ('roll', 'sheet', 'consumable', 'other'))");
        } else {
            Schema::table('products', function (Blueprint $table) {
                // Keep the old enum change for other drivers if needed, or use DB::statement for generic
                $table->enum('form_type', ['roll', 'sheet', 'consumable', 'other'])
                    ->default('other')
                    ->nullable(false)
                    ->change();
            });
        }

        // Step 5: Drop old redundant columns
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = N'';
                SELECT @sql += N'ALTER TABLE products DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('products')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('products'), 'type', 'ColumnId');
                IF @sql IS NOT NULL EXEC sp_executesql @sql;
            ");
        }
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_roll']);
        });

        // Step 6: Update product_type to be enum for consistency
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = N'';
                
                -- Drop check constraints
                SELECT @sql += N'ALTER TABLE products DROP CONSTRAINT ' + name + N';'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('products')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('products'), 'product_type', 'ColumnId');
                
                -- Drop default constraints
                SELECT @sql += N'ALTER TABLE products DROP CONSTRAINT ' + name + N';'
                FROM sys.default_constraints
                WHERE parent_object_id = OBJECT_ID('products')
                AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('products'), 'product_type', 'ColumnId');

                IF @sql IS NOT NULL EXEC sp_executesql @sql;
            ");
            DB::statement("ALTER TABLE products ALTER COLUMN product_type NVARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_product_type_check CHECK (product_type IN ('raw_material', 'semi_finished', 'finished_good', 'consumable', 'equipment', 'other'))");
            
            DB::statement("
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.default_constraints
                    WHERE parent_object_id = OBJECT_ID('products')
                    AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('products'), 'product_type', 'ColumnId')
                )
                BEGIN
                    ALTER TABLE products ADD DEFAULT 'raw_material' FOR product_type
                END
            ");
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('product_type', ['raw_material', 'semi_finished', 'finished_good', 'consumable', 'equipment', 'other'])
                    ->default('raw_material')
                    ->change()
                    ->comment('Manufacturing stage/logical type: raw material, semi-finished, finished good, consumable, equipment, other');
            });
        }

        // Step 7: Add new indexes for form_type
        Schema::table('products', function (Blueprint $table) {
            $table->index(['form_type', 'grammage']);
            $table->index(['form_type', 'laize']);
            $table->index(['form_type', 'flute']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['form_type', 'grammage']);
            $table->dropIndex(['form_type', 'laize']);
            $table->dropIndex(['form_type', 'flute']);
        });

        // Restore old structure
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['papier_roll', 'consommable', 'fini'])
                ->nullable()
                ->after('name');
            $table->boolean('is_roll')->default(false)->after('type');
        });

        // Migrate form_type back to type + is_roll
        DB::table('products')->update([
            'type' => DB::raw("
                CASE
                    WHEN form_type = 'roll' THEN 'papier_roll'
                    WHEN form_type = 'consumable' THEN 'consommable'
                    ELSE 'fini'
                END
            "),
            'is_roll' => DB::raw("IF(form_type = 'roll', 1, 0)")
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['papier_roll', 'consommable', 'fini'])
                ->nullable(false)
                ->change();
        });

        // Drop new column
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('form_type');
        });

        // Restore product_type to string
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type')->default('raw_material')->change();
        });

        // Restore old indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index(['type', 'grammage']);
            $table->index(['type', 'laize']);
            $table->index(['type', 'flute']);
        });
    }
};

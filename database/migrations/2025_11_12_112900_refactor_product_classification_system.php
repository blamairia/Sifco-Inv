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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('form_type', ['roll', 'sheet', 'consumable', 'other'])
                ->nullable(false)
                ->change();
        });

        // Step 5: Drop old redundant columns
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_roll']);
        });

        // Step 6: Update product_type to be enum for consistency
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_type', ['raw_material', 'semi_finished', 'finished_good', 'consumable', 'equipment', 'other'])
                ->default('raw_material')
                ->change()
                ->comment('Manufacturing stage/logical type: raw material, semi-finished, finished good, consumable, equipment, other');
        });

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

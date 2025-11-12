<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bon_entree_items MODIFY item_type ENUM('bobine','product','pallet') DEFAULT 'product'");
            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('bon_entree_items', function (Blueprint $table): void {
                $table->dropIndex('bon_entree_items_item_type_bon_entree_id_index');
            });

            DB::statement('ALTER TABLE bon_entree_items RENAME COLUMN item_type TO item_type_old');

            Schema::table('bon_entree_items', function (Blueprint $table): void {
                $table->string('item_type')->default('product');
            });

            DB::statement("UPDATE bon_entree_items SET item_type = CASE WHEN item_type_old IN ('bobine','product','pallet') THEN item_type_old ELSE 'product' END");

            DB::statement('ALTER TABLE bon_entree_items DROP COLUMN item_type_old');

            Schema::table('bon_entree_items', function (Blueprint $table): void {
                $table->index(['item_type', 'bon_entree_id']);
            });

            return;
        }

        // For other drivers (e.g. pgsql) we assume enum alteration is handled manually.
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bon_entree_items MODIFY item_type ENUM('bobine','product') DEFAULT 'product'");
            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('bon_entree_items', function (Blueprint $table): void {
                $table->dropIndex('bon_entree_items_item_type_bon_entree_id_index');
            });

            DB::statement('ALTER TABLE bon_entree_items RENAME COLUMN item_type TO item_type_old');

            Schema::table('bon_entree_items', function (Blueprint $table): void {
                $table->string('item_type')->default('product');
            });

            DB::statement("UPDATE bon_entree_items SET item_type = CASE WHEN item_type_old IN ('bobine','product') THEN item_type_old ELSE 'product' END");

            DB::statement('ALTER TABLE bon_entree_items DROP COLUMN item_type_old');

            Schema::table('bon_entree_items', function (Blueprint $table): void {
                $table->index(['item_type', 'bon_entree_id']);
            });

            return;
        }

        // For other drivers (e.g. pgsql) we assume enum alteration is handled manually.
    }
};

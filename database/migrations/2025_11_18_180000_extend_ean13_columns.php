<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Alter bon_entree_items.ean_13 to allow longer values
            try {
                DB::statement("ALTER TABLE `bon_entree_items` DROP INDEX `bon_entree_items_ean_13_unique`");
            } catch (\Throwable $e) {
                // ignore if index doesn't exist
            }
            DB::statement("ALTER TABLE `bon_entree_items` MODIFY `ean_13` VARCHAR(64) NULL COMMENT 'For bobines only'");
            try {
                DB::statement("ALTER TABLE `bon_entree_items` ADD UNIQUE INDEX `bon_entree_items_ean_13_unique` (`ean_13`)");
            } catch (\Throwable $e) {
                // ignore if index exists
            }

            // Alter rolls.ean_13 to allow longer values
            try {
                DB::statement("ALTER TABLE `rolls` DROP INDEX `rolls_ean_13_unique`");
            } catch (\Throwable $e) {
                // ignore if index doesn't exist
            }
            DB::statement("ALTER TABLE `rolls` MODIFY `ean_13` VARCHAR(64) NOT NULL COMMENT 'Unique barcode for this roll'");
            try {
                DB::statement("ALTER TABLE `rolls` ADD UNIQUE INDEX `rolls_ean_13_unique` (`ean_13`)");
            } catch (\Throwable $e) {
                // ignore if index exists
            }
        } elseif ($driver === 'sqlite') {
            // SQLite has limited ALTER support; skip type changes in testing environments.
            DB::statement("DROP INDEX IF EXISTS bon_entree_items_ean_13_unique");
            DB::statement("DROP INDEX IF EXISTS rolls_ean_13_unique");
        } else {
            // Try a safe schema change if possible (requires doctrine/dbal)
            try {
                Schema::table('bon_entree_items', function (Blueprint $table) {
                    $table->string('ean_13', 64)->nullable()->change();
                });
                Schema::table('rolls', function (Blueprint $table) {
                    $table->string('ean_13', 64)->change();
                });
            } catch (\Throwable $e) {
                // Best-effort: don't fail the migration due to driver limitations.
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE `bon_entree_items` DROP INDEX `bon_entree_items_ean_13_unique`");
            } catch (\Throwable $e) {}
            DB::statement("ALTER TABLE `bon_entree_items` MODIFY `ean_13` VARCHAR(13) NULL COMMENT 'For bobines only'");
            try {
                DB::statement("ALTER TABLE `bon_entree_items` ADD UNIQUE INDEX `bon_entree_items_ean_13_unique` (`ean_13`)");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `rolls` DROP INDEX `rolls_ean_13_unique`");
            } catch (\Throwable $e) {}
            DB::statement("ALTER TABLE `rolls` MODIFY `ean_13` VARCHAR(13) NOT NULL COMMENT 'Unique barcode for this roll'");
            try {
                DB::statement("ALTER TABLE `rolls` ADD UNIQUE INDEX `rolls_ean_13_unique` (`ean_13`)");
            } catch (\Throwable $e) {}
        } elseif ($driver === 'sqlite') {
            DB::statement("DROP INDEX IF EXISTS bon_entree_items_ean_13_unique");
            DB::statement("DROP INDEX IF EXISTS rolls_ean_13_unique");
        } else {
            try {
                Schema::table('bon_entree_items', function (Blueprint $table) {
                    $table->string('ean_13', 13)->nullable()->change();
                });
                Schema::table('rolls', function (Blueprint $table) {
                    $table->string('ean_13', 13)->change();
                });
            } catch (\Throwable $e) {
            }
        }
    }
};

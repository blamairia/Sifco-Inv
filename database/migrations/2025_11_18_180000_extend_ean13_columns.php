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
            DB::statement("DROP INDEX IF EXISTS `bon_entree_items_ean_13_unique` ON `bon_entree_items`");
            DB::statement("ALTER TABLE `bon_entree_items` MODIFY `ean_13` VARCHAR(64) NULL COMMENT 'For bobines only'");
            DB::statement("CREATE UNIQUE INDEX `bon_entree_items_ean_13_unique` ON `bon_entree_items` (`ean_13`)");

            // Alter rolls.ean_13 to allow longer values
            DB::statement("DROP INDEX IF EXISTS `rolls_ean_13_unique` ON `rolls`");
            DB::statement("ALTER TABLE `rolls` MODIFY `ean_13` VARCHAR(64) NOT NULL COMMENT 'Unique barcode for this roll'");
            DB::statement("CREATE UNIQUE INDEX `rolls_ean_13_unique` ON `rolls` (`ean_13`)");
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
            DB::statement("DROP INDEX IF EXISTS `bon_entree_items_ean_13_unique` ON `bon_entree_items`");
            DB::statement("ALTER TABLE `bon_entree_items` MODIFY `ean_13` VARCHAR(13) NULL COMMENT 'For bobines only'");
            DB::statement("CREATE UNIQUE INDEX `bon_entree_items_ean_13_unique` ON `bon_entree_items` (`ean_13`)");

            DB::statement("DROP INDEX IF EXISTS `rolls_ean_13_unique` ON `rolls`");
            DB::statement("ALTER TABLE `rolls` MODIFY `ean_13` VARCHAR(13) NOT NULL COMMENT 'Unique barcode for this roll'");
            DB::statement("CREATE UNIQUE INDEX `rolls_ean_13_unique` ON `rolls` (`ean_13`)");
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

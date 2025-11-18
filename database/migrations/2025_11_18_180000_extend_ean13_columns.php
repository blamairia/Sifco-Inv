<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter bon_entree_items.ean_13 to allow longer values
        DB::statement("DROP INDEX IF EXISTS `bon_entree_items_ean_13_unique` ON `bon_entree_items`");
        DB::statement("ALTER TABLE `bon_entree_items` MODIFY `ean_13` VARCHAR(64) NULL COMMENT 'For bobines only'");
        DB::statement("CREATE UNIQUE INDEX `bon_entree_items_ean_13_unique` ON `bon_entree_items` (`ean_13`)");

        // Alter rolls.ean_13 to allow longer values
        DB::statement("DROP INDEX IF EXISTS `rolls_ean_13_unique` ON `rolls`");
        DB::statement("ALTER TABLE `rolls` MODIFY `ean_13` VARCHAR(64) NOT NULL COMMENT 'Unique barcode for this roll'");
        DB::statement("CREATE UNIQUE INDEX `rolls_ean_13_unique` ON `rolls` (`ean_13`)");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS `bon_entree_items_ean_13_unique` ON `bon_entree_items`");
        DB::statement("ALTER TABLE `bon_entree_items` MODIFY `ean_13` VARCHAR(13) NULL COMMENT 'For bobines only'");
        DB::statement("CREATE UNIQUE INDEX `bon_entree_items_ean_13_unique` ON `bon_entree_items` (`ean_13`)");

        DB::statement("DROP INDEX IF EXISTS `rolls_ean_13_unique` ON `rolls`");
        DB::statement("ALTER TABLE `rolls` MODIFY `ean_13` VARCHAR(13) NOT NULL COMMENT 'Unique barcode for this roll'");
        DB::statement("CREATE UNIQUE INDEX `rolls_ean_13_unique` ON `rolls` (`ean_13`)");
    }
};

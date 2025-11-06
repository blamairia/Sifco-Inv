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
        Schema::table('bon_entree_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_entree_items', 'weight_kg')) {
                $table->decimal('weight_kg', 12, 3)->nullable()->after('qty_entered');
            }
        });

        Schema::table('bon_sortie_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_sortie_items', 'weight_kg')) {
                $table->decimal('weight_kg', 12, 3)->nullable()->after('qty_issued');
            }
        });

        // Migrate existing data: move weight from qty columns for bobines/rolls
        DB::statement(
            "UPDATE bon_entree_items SET weight_kg = qty_entered WHERE item_type = 'bobine' AND weight_kg IS NULL"
        );
        DB::statement(
            "UPDATE bon_entree_items SET qty_entered = 1 WHERE item_type = 'bobine' AND qty_entered <> 0"
        );

        DB::statement(
            "UPDATE bon_sortie_items SET weight_kg = qty_issued WHERE item_type = 'roll' AND weight_kg IS NULL"
        );
        DB::statement(
            "UPDATE bon_sortie_items SET qty_issued = 1 WHERE item_type = 'roll' AND qty_issued <> 0"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Attempt to restore previous qty values before dropping the columns
        DB::statement(
            "UPDATE bon_entree_items SET qty_entered = weight_kg WHERE item_type = 'bobine' AND weight_kg IS NOT NULL"
        );
        DB::statement(
            "UPDATE bon_sortie_items SET qty_issued = weight_kg WHERE item_type = 'roll' AND weight_kg IS NOT NULL"
        );

        Schema::table('bon_sortie_items', function (Blueprint $table) {
            if (Schema::hasColumn('bon_sortie_items', 'weight_kg')) {
                $table->dropColumn('weight_kg');
            }
        });

        Schema::table('bon_entree_items', function (Blueprint $table) {
            if (Schema::hasColumn('bon_entree_items', 'weight_kg')) {
                $table->dropColumn('weight_kg');
            }
        });
    }
};

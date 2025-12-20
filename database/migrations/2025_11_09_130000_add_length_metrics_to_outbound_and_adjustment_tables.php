<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_sortie_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_sortie_items', 'length_m')) {
                $table->decimal('length_m', 12, 3)->nullable()->after('weight_kg');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::table('bon_sortie_items')
                ->where('item_type', 'roll')
                ->whereNotNull('roll_id')
                ->where(fn ($query) => $query->whereNull('length_m')->orWhere('length_m', 0))
                ->update([
                    'length_m' => DB::raw("(
                        SELECT COALESCE(r.length_m, 0)
                        FROM rolls r
                        WHERE r.id = bon_sortie_items.roll_id
                    )"),
                ]);
        } elseif (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement(<<<SQL
                UPDATE bsi
                SET bsi.length_m = COALESCE(r.length_m, 0)
                FROM bon_sortie_items bsi
                JOIN rolls r ON r.id = bsi.roll_id
                WHERE bsi.item_type = 'roll' AND bsi.roll_id IS NOT NULL AND (bsi.length_m IS NULL OR bsi.length_m = 0)
            SQL);
        } else {
            DB::statement(<<<SQL
                UPDATE bon_sortie_items bsi
                JOIN rolls r ON r.id = bsi.roll_id
                SET bsi.length_m = COALESCE(r.length_m, 0)
                WHERE bsi.item_type = 'roll' AND bsi.roll_id IS NOT NULL AND (bsi.length_m IS NULL OR bsi.length_m = 0)
            SQL);
        }

        Schema::table('bon_reintegration_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_reintegration_items', 'previous_length_m')) {
                $table->decimal('previous_length_m', 12, 3)->nullable()->after('previous_weight_kg');
            }

            if (! Schema::hasColumn('bon_reintegration_items', 'returned_length_m')) {
                $table->decimal('returned_length_m', 12, 3)->nullable()->after('returned_weight_kg');
            }

            if (! Schema::hasColumn('bon_reintegration_items', 'length_delta_m')) {
                $table->decimal('length_delta_m', 12, 3)->nullable()->after('weight_delta_kg');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::table('bon_reintegration_items')
                ->where('item_type', 'roll')
                ->update([
                    'previous_length_m' => DB::raw("COALESCE(previous_length_m, (
                        SELECT r.length_m FROM rolls r WHERE r.id = bon_reintegration_items.roll_id
                    ))"),
                    'returned_length_m' => DB::raw("COALESCE(returned_length_m, (
                        SELECT r.length_m FROM rolls r WHERE r.id = bon_reintegration_items.roll_id
                    ))"),
                    'length_delta_m' => DB::raw('COALESCE(length_delta_m, 0)'),
                ]);
        } elseif (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement(<<<SQL
                UPDATE bri
                SET bri.previous_length_m = COALESCE(bri.previous_length_m, r.length_m),
                    bri.returned_length_m = COALESCE(bri.returned_length_m, r.length_m),
                    bri.length_delta_m = COALESCE(bri.length_delta_m, 0)
                FROM bon_reintegration_items bri
                LEFT JOIN rolls r ON r.id = bri.roll_id
                WHERE bri.item_type = 'roll'
            SQL);
        } else {
            DB::statement(<<<SQL
                UPDATE bon_reintegration_items bri
                LEFT JOIN rolls r ON r.id = bri.roll_id
                SET bri.previous_length_m = COALESCE(bri.previous_length_m, r.length_m),
                    bri.returned_length_m = COALESCE(bri.returned_length_m, r.length_m),
                    bri.length_delta_m = COALESCE(bri.length_delta_m, 0)
                WHERE bri.item_type = 'roll'
            SQL);
        }

        Schema::table('roll_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('roll_adjustments', 'previous_length_m')) {
                $table->decimal('previous_length_m', 12, 3)->nullable()->after('previous_weight_kg');
            }

            if (! Schema::hasColumn('roll_adjustments', 'new_length_m')) {
                $table->decimal('new_length_m', 12, 3)->nullable()->after('new_weight_kg');
            }

            if (! Schema::hasColumn('roll_adjustments', 'length_delta_m')) {
                $table->decimal('length_delta_m', 12, 3)->nullable()->after('weight_delta_kg');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::table('roll_adjustments')
                ->whereNull('previous_length_m')
                ->whereNull('new_length_m')
                ->update([
                    'previous_length_m' => DB::raw("(
                        SELECT r.length_m FROM rolls r WHERE r.id = roll_adjustments.roll_id
                    )"),
                    'new_length_m' => DB::raw("(
                        SELECT r.length_m FROM rolls r WHERE r.id = roll_adjustments.roll_id
                    )"),
                    'length_delta_m' => DB::raw('COALESCE(length_delta_m, 0)'),
                ]);
        } elseif (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement(<<<SQL
                UPDATE ra
                SET ra.previous_length_m = COALESCE(ra.previous_length_m, r.length_m),
                    ra.new_length_m = COALESCE(ra.new_length_m, r.length_m),
                    ra.length_delta_m = COALESCE(ra.length_delta_m, 0)
                FROM roll_adjustments ra
                JOIN rolls r ON r.id = ra.roll_id
                WHERE ra.previous_length_m IS NULL AND ra.new_length_m IS NULL
            SQL);
        } else {
            DB::statement(<<<SQL
                UPDATE roll_adjustments ra
                JOIN rolls r ON r.id = ra.roll_id
                SET ra.previous_length_m = COALESCE(ra.previous_length_m, r.length_m),
                    ra.new_length_m = COALESCE(ra.new_length_m, r.length_m),
                    ra.length_delta_m = COALESCE(ra.length_delta_m, 0)
                WHERE ra.previous_length_m IS NULL AND ra.new_length_m IS NULL
            SQL);
        }
    }

    public function down(): void
    {
        Schema::table('roll_adjustments', function (Blueprint $table) {
            foreach (['length_delta_m', 'new_length_m', 'previous_length_m'] as $column) {
                if (Schema::hasColumn('roll_adjustments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bon_reintegration_items', function (Blueprint $table) {
            foreach (['length_delta_m', 'returned_length_m', 'previous_length_m'] as $column) {
                if (Schema::hasColumn('bon_reintegration_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bon_sortie_items', function (Blueprint $table) {
            if (Schema::hasColumn('bon_sortie_items', 'length_m')) {
                $table->dropColumn('length_m');
            }
        });
    }
};

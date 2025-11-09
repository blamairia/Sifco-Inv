<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_entree_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_entree_items', 'length_m')) {
                $table->decimal('length_m', 12, 3)->nullable()->after('weight_kg');
            }
        });

        Schema::table('bon_transfert_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_transfert_items', 'length_transferred_m')) {
                $table->decimal('length_transferred_m', 12, 3)->nullable()->after('weight_transferred_kg');
            }
        });

        Schema::table('rolls', function (Blueprint $table) {
            if (! Schema::hasColumn('rolls', 'length_m')) {
                $table->decimal('length_m', 12, 3)->nullable()->after('weight_kg');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'roll_length_before_m')) {
                $table->decimal('roll_length_before_m', 12, 3)->nullable()->after('roll_weight_delta_kg');
            }

            if (! Schema::hasColumn('stock_movements', 'roll_length_after_m')) {
                $table->decimal('roll_length_after_m', 12, 3)->nullable()->after('roll_length_before_m');
            }

            if (! Schema::hasColumn('stock_movements', 'roll_length_delta_m')) {
                $table->decimal('roll_length_delta_m', 12, 3)->nullable()->after('roll_length_after_m');
            }
        });

        Schema::table('stock_quantities', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_quantities', 'total_length_m')) {
                $table->decimal('total_length_m', 15, 3)->default(0)->after('total_weight_kg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_quantities', function (Blueprint $table) {
            if (Schema::hasColumn('stock_quantities', 'total_length_m')) {
                $table->dropColumn('total_length_m');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $columns = [
                'roll_length_before_m',
                'roll_length_after_m',
                'roll_length_delta_m',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('stock_movements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('rolls', function (Blueprint $table) {
            if (Schema::hasColumn('rolls', 'length_m')) {
                $table->dropColumn('length_m');
            }
        });

        Schema::table('bon_transfert_items', function (Blueprint $table) {
            if (Schema::hasColumn('bon_transfert_items', 'length_transferred_m')) {
                $table->dropColumn('length_transferred_m');
            }
        });

        Schema::table('bon_entree_items', function (Blueprint $table) {
            if (Schema::hasColumn('bon_entree_items', 'length_m')) {
                $table->dropColumn('length_m');
            }
        });
    }
};

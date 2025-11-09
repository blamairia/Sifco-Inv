<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_transfert_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bon_transfert_items', 'movement_out_id')) {
                $table->foreignId('movement_out_id')
                    ->nullable()
                    ->after('roll_id')
                    ->constrained('stock_movements')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('bon_transfert_items', 'movement_in_id')) {
                $table->foreignId('movement_in_id')
                    ->nullable()
                    ->after('movement_out_id')
                    ->constrained('stock_movements')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('bon_transfert_items', 'weight_transferred_kg')) {
                $table->decimal('weight_transferred_kg', 12, 3)
                    ->nullable()
                    ->after('qty_transferred');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bon_transfert_items', function (Blueprint $table) {
            if (Schema::hasColumn('bon_transfert_items', 'movement_out_id')) {
                $table->dropForeign(['movement_out_id']);
                $table->dropColumn('movement_out_id');
            }

            if (Schema::hasColumn('bon_transfert_items', 'movement_in_id')) {
                $table->dropForeign(['movement_in_id']);
                $table->dropColumn('movement_in_id');
            }

            if (Schema::hasColumn('bon_transfert_items', 'weight_transferred_kg')) {
                $table->dropColumn('weight_transferred_kg');
            }
        });
    }
};

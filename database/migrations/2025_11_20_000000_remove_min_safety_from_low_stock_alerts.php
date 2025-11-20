<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('low_stock_alerts')) {
            Schema::table('low_stock_alerts', function (Blueprint $table) {
                if (Schema::hasColumn('low_stock_alerts', 'min_stock')) {
                    try {
                        $table->dropColumn('min_stock');
                    } catch (\Throwable $e) {
                        // Some DB drivers (e.g., older SQLite) cannot drop columns easily; ignore.
                    }
                }

                if (Schema::hasColumn('low_stock_alerts', 'safety_stock')) {
                    try {
                        $table->dropColumn('safety_stock');
                    } catch (\Throwable $e) {
                        // ignore migration error for unsupported drivers.
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('low_stock_alerts')) {
            Schema::table('low_stock_alerts', function (Blueprint $table) {
                if (! Schema::hasColumn('low_stock_alerts', 'min_stock')) {
                    $table->decimal('min_stock', 15, 2)->default(0)->after('current_qty');
                }
                if (! Schema::hasColumn('low_stock_alerts', 'safety_stock')) {
                    $table->decimal('safety_stock', 15, 2)->nullable()->after('min_stock');
                }
            });
        }
    }
};

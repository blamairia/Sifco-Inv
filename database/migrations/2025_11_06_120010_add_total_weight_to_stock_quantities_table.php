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
        Schema::table('stock_quantities', function (Blueprint $table) {
            $table->decimal('total_weight_kg', 15, 3)->default(0)->after('total_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_quantities', function (Blueprint $table) {
            $table->dropColumn('total_weight_kg');
        });
    }
};

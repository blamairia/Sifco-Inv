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
        Schema::table('roll_adjustments', function (Blueprint $table) {
            $table->decimal('previous_weight_kg', 12, 3)->nullable()->after('new_status');
            $table->decimal('new_weight_kg', 12, 3)->nullable()->after('previous_weight_kg');
            $table->decimal('weight_delta_kg', 12, 3)->nullable()->after('new_weight_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roll_adjustments', function (Blueprint $table) {
            $table->dropColumn([
                'previous_weight_kg',
                'new_weight_kg',
                'weight_delta_kg',
            ]);
        });
    }
};

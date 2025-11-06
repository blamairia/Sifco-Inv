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
        Schema::table('rolls', function (Blueprint $table) {
            $table->decimal('weight_kg', 10, 3)->nullable()->after('status');
            $table->decimal('cump_value', 15, 4)->nullable()->after('weight_kg');
            $table->boolean('is_manual_entry')->default(false)->after('cump_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rolls', function (Blueprint $table) {
            $table->dropColumn(['weight_kg', 'cump_value', 'is_manual_entry']);
        });
    }
};

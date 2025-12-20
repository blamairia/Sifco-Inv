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
        Schema::table('bon_sortie_items', function (Blueprint $table) {
            $table->foreignId('roll_id')->nullable()->after('product_id')->constrained('rolls')->noActionOnDelete();
            $table->decimal('qty_issued', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_sortie_items', function (Blueprint $table) {
            $table->dropForeign(['roll_id']);
            $table->dropColumn('roll_id');
            $table->decimal('qty_issued', 15, 2)->nullable(false)->change();
        });
    }
};

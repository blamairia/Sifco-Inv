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
        Schema::table('bon_transfert_items', function (Blueprint $table) {
            $table->string('item_type', 20)->default('product')->after('id')->comment('roll or product');
            $table->foreignId('roll_id')->nullable()->after('product_id')->constrained('rolls')->nullOnDelete()->comment('If item_type=roll, reference to the roll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_transfert_items', function (Blueprint $table) {
            $table->dropForeign(['roll_id']);
            $table->dropColumn(['item_type', 'roll_id']);
        });
    }
};

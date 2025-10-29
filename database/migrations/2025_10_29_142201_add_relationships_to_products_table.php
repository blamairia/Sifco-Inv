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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('subcategory_id')->nullable()->constrained();
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->foreignId('paper_roll_type_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('products_category_id_foreign');
            $table->dropForeignKeyIfExists('products_subcategory_id_foreign');
            $table->dropForeignKeyIfExists('products_unit_id_foreign');
            $table->dropForeignKeyIfExists('products_paper_roll_type_id_foreign');
            $table->dropColumn(['category_id', 'subcategory_id', 'unit_id', 'paper_roll_type_id']);
        });
    }
};

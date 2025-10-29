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
            $table->dropForeignKeyIfExists(['category_id']);
            $table->dropForeignKeyIfExists(['subcategory_id']);
            $table->dropForeignKeyIfExists(['unit_id']);
            $table->dropForeignKeyIfExists(['paper_roll_type_id']);
            $table->dropColumn(['category_id', 'subcategory_id', 'unit_id', 'paper_roll_type_id']);
        });
    }
};

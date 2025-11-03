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
        Schema::table('bon_entree_items', function (Blueprint $table) {
            $table->enum('item_type', ['bobine', 'product'])->default('product')->after('bon_entree_id');
            $table->string('ean_13', 13)->nullable()->after('product_id')->comment('For bobines only');
            $table->string('batch_number', 100)->nullable()->after('ean_13')->comment('Supplier batch number');
            $table->foreignId('roll_id')->nullable()->after('batch_number')->constrained('rolls')->nullOnDelete()->comment('Link to created roll after receiving');
            
            $table->index(['item_type', 'bon_entree_id']);
            $table->unique('ean_13');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_entree_items', function (Blueprint $table) {
            $table->dropForeign(['roll_id']);
            $table->dropUnique(['ean_13']);
            $table->dropIndex(['item_type', 'bon_entree_id']);
            $table->dropColumn(['item_type', 'ean_13', 'batch_number', 'roll_id']);
        });
    }
};

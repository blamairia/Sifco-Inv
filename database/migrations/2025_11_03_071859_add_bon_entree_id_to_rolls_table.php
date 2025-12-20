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
            $table->foreignId('bon_entree_item_id')->nullable()->after('id')->constrained('bon_entree_items')->noActionOnDelete()->comment('Link to source bon entree item');
            
            $table->index('bon_entree_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rolls', function (Blueprint $table) {
            $table->dropForeign(['bon_entree_item_id']);
            $table->dropIndex(['bon_entree_item_id']);
            $table->dropColumn('bon_entree_item_id');
        });
    }
};

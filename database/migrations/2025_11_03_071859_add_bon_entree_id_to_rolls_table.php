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
            $table->foreignId('bon_entree_id')->nullable()->after('id')->constrained('bon_entrees')->onDelete('cascade');
            $table->string('ean_13', 13)->nullable()->change(); // Make ean_13 nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rolls', function (Blueprint $table) {
            $table->dropForeign(['bon_entree_id']);
            $table->dropColumn('bon_entree_id');
        });
    }
};

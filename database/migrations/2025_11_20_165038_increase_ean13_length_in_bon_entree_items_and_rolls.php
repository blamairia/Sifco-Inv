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
            $table->string('ean_13', 64)->change();
        });

        Schema::table('rolls', function (Blueprint $table) {
            $table->string('ean_13', 64)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_entree_items', function (Blueprint $table) {
            $table->string('ean_13', 13)->change();
        });

        Schema::table('rolls', function (Blueprint $table) {
            $table->string('ean_13', 13)->change();
        });
    }
};

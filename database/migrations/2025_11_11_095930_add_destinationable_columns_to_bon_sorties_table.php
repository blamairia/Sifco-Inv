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
        Schema::table('bon_sorties', function (Blueprint $table) {
            $table->string('destinationable_type')->nullable()->after('destination');
            $table->unsignedBigInteger('destinationable_id')->nullable()->after('destinationable_type');
            $table->index(['destinationable_type', 'destinationable_id'], 'bon_sorties_destinationable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_sorties', function (Blueprint $table) {
            $table->dropIndex('bon_sorties_destinationable_index');
            $table->dropColumn(['destinationable_type', 'destinationable_id']);
        });
    }
};

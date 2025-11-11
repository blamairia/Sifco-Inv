<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bon_entrees', function (Blueprint $table) {
            $table->string('sourceable_type')->nullable()->after('bon_number');
            $table->unsignedBigInteger('sourceable_id')->nullable()->after('sourceable_type');
            $table->index(['sourceable_type', 'sourceable_id'], 'bon_entrees_sourceable_index');
        });

        DB::table('bon_entrees')
            ->whereNotNull('supplier_id')
            ->orderBy('id')
            ->lazyById()
            ->each(function ($entree) {
                DB::table('bon_entrees')
                    ->where('id', $entree->id)
                    ->update([
                        'sourceable_type' => 'App\\Models\\Supplier',
                        'sourceable_id' => $entree->supplier_id,
                    ]);
            });

        Schema::table('bon_entrees', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_entrees', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('bon_number')->constrained('suppliers')->cascadeOnDelete();
        });

        DB::table('bon_entrees')
            ->where('sourceable_type', 'App\\Models\\Supplier')
            ->orderBy('id')
            ->lazyById()
            ->each(function ($entree) {
                DB::table('bon_entrees')
                    ->where('id', $entree->id)
                    ->update([
                        'supplier_id' => $entree->sourceable_id,
                    ]);
            });

        Schema::table('bon_entrees', function (Blueprint $table) {
            $table->dropIndex('bon_entrees_sourceable_index');
            $table->dropColumn(['sourceable_type', 'sourceable_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_category', function (Blueprint $table): void {
            $table->timestamps();
        });

        DB::table('product_category')->update([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('product_category', function (Blueprint $table): void {
            $table->dropTimestamps();
        });
    }
};

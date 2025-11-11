<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sheet_width_mm', 10, 2)->nullable()->after('laize');
            $table->decimal('sheet_length_mm', 10, 2)->nullable()->after('sheet_width_mm');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sheet_width_mm', 'sheet_length_mm']);
        });
    }
};

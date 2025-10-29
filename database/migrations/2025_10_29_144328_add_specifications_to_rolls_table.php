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
            $table->foreignId('roll_specification_id')->nullable()->after('product_id')->constrained();
            $table->string('batch_number')->nullable()->after('roll_specification_id');
            $table->date('received_date')->nullable()->after('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rolls', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('rolls_roll_specification_id_foreign');
            $table->dropColumn(['roll_specification_id', 'batch_number', 'received_date']);
        });
    }
};

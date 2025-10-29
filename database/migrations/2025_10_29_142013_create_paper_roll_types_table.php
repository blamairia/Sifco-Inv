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
        Schema::create('paper_roll_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_code')->unique();
            $table->string('name');
            $table->integer('grammage');
            $table->integer('laise');
            $table->decimal('weight', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_roll_types');
    }
};

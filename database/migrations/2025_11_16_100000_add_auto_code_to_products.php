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
		if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'auto_code')) {
			Schema::table('products', function (Blueprint $table) {
				$table->boolean('auto_code')->default(true)->after('code');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (Schema::hasTable('products') && Schema::hasColumn('products', 'auto_code')) {
			Schema::table('products', function (Blueprint $table) {
				$table->dropColumn('auto_code');
			});
		}
	}
};


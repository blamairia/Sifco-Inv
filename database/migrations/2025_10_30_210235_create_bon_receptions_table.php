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
        Schema::create('bon_receptions', function (Blueprint $table) {
            $table->id();
            $table->string('bon_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->date('receipt_date');
            $table->string('delivery_note_ref')->nullable()->comment('Bon de livraison fournisseur');
            $table->string('purchase_order_ref')->nullable()->comment('Bon de commande');
            $table->enum('status', ['received', 'verified', 'conformity_issue', 'rejected'])->default('received');
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('conformity_issues')->nullable()->comment('{missing, surplus, damaged}');
            $table->timestamps();
            
            $table->index(['supplier_id', 'receipt_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_receptions');
    }
};

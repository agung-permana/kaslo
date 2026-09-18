<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Siapa yang mencatat (suami / istri)
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete(); // Dompet sumber
            $table->foreignId('destination_wallet_id')->nullable()->constrained('wallets')->nullOnDelete(); // Dompet tujuan (transfer)
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // expense, income, transfer
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable(); // Upload nota/bukti transaksi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

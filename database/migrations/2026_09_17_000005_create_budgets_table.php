<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('month_year', 7); // Format: YYYY-MM e.g. 2026-09
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['household_id', 'category_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

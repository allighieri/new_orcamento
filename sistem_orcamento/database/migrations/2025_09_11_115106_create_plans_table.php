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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Prata, Ouro
            $table->string('slug')->unique(); // bronze, prata, ouro
            $table->text('description')->nullable();
            $table->integer('budget_limit')->nullable(); // null = ilimitado
            $table->decimal('monthly_price', 8, 2); // Preço mensal
            $table->decimal('annual_price', 8, 2); // Preço anual (por mês)
            $table->json('features')->nullable(); // Recursos do plano em JSON
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

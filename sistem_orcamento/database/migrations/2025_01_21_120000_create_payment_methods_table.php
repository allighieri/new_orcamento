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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // PIX, Cartão de Crédito, Cartão de Débito, etc.
            $table->string('slug')->unique(); // pix, credit_card, debit_card, etc.
            $table->boolean('allows_installments')->default(false); // Se permite parcelamento
            $table->integer('max_installments')->nullable(); // Máximo de parcelas permitidas
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
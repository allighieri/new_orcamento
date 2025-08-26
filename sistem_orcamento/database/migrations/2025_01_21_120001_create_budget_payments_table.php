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
        Schema::create('budget_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 10, 2); // Valor desta forma de pagamento
            $table->integer('installments')->default(1); // Número de parcelas
            $table->enum('payment_moment', [
                'approval', // Na aprovação do orçamento
                'pickup', // Na retirada do produto
                'custom' // Data customizada
            ]);
            $table->date('custom_date')->nullable(); // Data customizada se payment_moment = 'custom'
            $table->integer('days_after_pickup')->nullable(); // Dias após retirada (para boletos)
            $table->text('notes')->nullable(); // Observações específicas desta forma de pagamento
            $table->integer('order')->default(1); // Ordem de exibição
            $table->timestamps();
            
            $table->foreign('budget_id')
                  ->references('id')
                  ->on('budgets')
                  ->onDelete('cascade');
                  
            $table->foreign('payment_method_id')
                  ->references('id')
                  ->on('payment_methods')
                  ->onDelete('restrict');
                  
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_payments');
    }
};
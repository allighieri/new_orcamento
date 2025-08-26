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
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_payment_id');
            $table->integer('installment_number'); // Número da parcela (1, 2, 3...)
            $table->decimal('amount', 10, 2); // Valor da parcela
            $table->date('due_date')->nullable(); // Data de vencimento da parcela
            $table->enum('status', [
                'pending', // Pendente
                'paid', // Paga
                'overdue', // Vencida
                'cancelled' // Cancelada
            ])->default('pending');
            $table->date('paid_date')->nullable(); // Data do pagamento
            $table->decimal('paid_amount', 10, 2)->nullable(); // Valor pago (pode ser diferente do valor da parcela)
            $table->text('notes')->nullable(); // Observações da parcela
            $table->timestamps();
            
            $table->foreign('budget_payment_id')
                  ->references('id')
                  ->on('budget_payments')
                  ->onDelete('cascade');
                  
            $table->unique(['budget_payment_id', 'installment_number']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};
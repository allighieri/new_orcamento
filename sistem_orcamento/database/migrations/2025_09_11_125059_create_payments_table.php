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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('asaas_payment_id')->nullable(); // ID do pagamento no Asaas
            $table->string('asaas_customer_id')->nullable(); // ID do cliente no Asaas
            $table->decimal('amount', 10, 2); // Valor do pagamento
            $table->string('billing_type'); // PIX, CREDIT_CARD, etc
            $table->enum('status', ['PENDING', 'RECEIVED', 'CONFIRMED', 'OVERDUE', 'REFUNDED', 'RECEIVED_IN_CASH', 'REFUND_REQUESTED', 'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE', 'AWAITING_CHARGEBACK_REVERSAL', 'DUNNING_REQUESTED', 'DUNNING_RECEIVED', 'AWAITING_RISK_ANALYSIS'])->default('PENDING');
            $table->date('due_date'); // Data de vencimento
            $table->text('description')->nullable(); // Descrição do pagamento
            $table->json('payment_data')->nullable(); // Dados adicionais do pagamento (QR Code PIX, etc)
            $table->json('webhook_data')->nullable(); // Dados recebidos via webhook
            $table->timestamp('paid_at')->nullable(); // Data do pagamento
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index('asaas_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

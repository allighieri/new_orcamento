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
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade'); // Relaciona com subscription
            $table->string('asaas_payment_id')->unique(); // ID do pagamento no Asaas
            $table->decimal('amount', 10, 2); // Valor do pagamento
            $table->enum('payment_method', ['pix', 'credit_card', 'bank_slip']); // Método de pagamento
            $table->enum('status', ['pending', 'confirmed', 'received', 'overdue', 'refunded'])->default('pending'); // Status do pagamento
            $table->string('pix_qr_code')->nullable(); // QR Code PIX
            $table->string('pix_copy_paste')->nullable(); // Código PIX copia e cola
            $table->string('bank_slip_url')->nullable(); // URL do boleto
            $table->json('credit_card_info')->nullable(); // Informações do cartão (parcelas, etc)
            $table->datetime('due_date'); // Data de vencimento
            $table->datetime('confirmed_at')->nullable(); // Data de confirmação do pagamento
            $table->json('asaas_response')->nullable(); // Resposta completa da API Asaas
            $table->timestamps();
            
            // Índices para performance
            $table->index(['subscription_id', 'status']);
            $table->index('asaas_payment_id');
            $table->index('status');
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

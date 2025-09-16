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
        Schema::create('usage_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // Empresa
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade'); // Assinatura ativa
            $table->integer('year'); // Ano de referência
            $table->integer('month'); // Mês de referência (1-12)
            $table->integer('budgets_used')->default(0); // Orçamentos utilizados no mês
            $table->integer('extra_budgets_purchased')->default(0); // Orçamentos extras comprados
            $table->integer('extra_budgets_used')->default(0); // Orçamentos extras utilizados
            $table->integer('inherited_budgets')->default(0); // Orçamentos herdados de upgrade
            $table->integer('inherited_budgets_used')->default(0); // Orçamentos herdados utilizados
            $table->timestamps();
            
            // Índices para performance e unicidade
            $table->unique(['company_id', 'year', 'month']); // Um registro por empresa/mês
            $table->index(['subscription_id', 'year', 'month']);
            $table->index(['company_id', 'subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_controls');
    }
};

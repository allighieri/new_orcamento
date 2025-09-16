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
        Schema::table('payments', function (Blueprint $table) {
            // Remover campos desnecessários
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_phone',
                'customer_cpf_cnpj',
                'description',
                'external_reference',
                'discount_value',
                'interest_value',
                'fine_value',
                'webhook_data',
                'processed_at',
                'failure_reason',
                'installment_number',
                'total_installments',
                'pix_qr_code',
                'pix_copy_paste',
                'bank_slip_url',
                'credit_card_info'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Recriar campos removidos
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_cpf_cnpj')->nullable();
            $table->text('description')->nullable();
            $table->string('external_reference')->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('interest_value', 10, 2)->nullable();
            $table->decimal('fine_value', 10, 2)->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('installment_number')->nullable();
            $table->integer('total_installments')->nullable();
            $table->text('pix_qr_code')->nullable();
            $table->text('pix_copy_paste')->nullable();
            $table->string('bank_slip_url')->nullable();
            $table->json('credit_card_info')->nullable();
        });
    }
};

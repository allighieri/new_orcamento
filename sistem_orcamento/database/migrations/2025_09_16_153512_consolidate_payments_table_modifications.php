<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Adicionar campos faltantes
            $table->string('customer_name')->nullable()->after('subscription_id');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->string('customer_cpf_cnpj')->nullable()->after('customer_phone');
            $table->text('description')->nullable()->after('customer_cpf_cnpj');
            $table->string('external_reference')->nullable()->after('description');
            $table->decimal('discount_value', 10, 2)->nullable()->after('external_reference');
            $table->decimal('interest_value', 10, 2)->nullable()->after('discount_value');
            $table->decimal('fine_value', 10, 2)->nullable()->after('interest_value');
            $table->json('webhook_data')->nullable()->after('fine_value');
            $table->datetime('processed_at')->nullable()->after('webhook_data');
            $table->string('failure_reason')->nullable()->after('processed_at');
            $table->integer('installment_number')->nullable()->after('failure_reason');
            $table->integer('total_installments')->nullable()->after('installment_number');
            $table->foreignId('plan_id')->nullable()->after('subscription_id')->constrained()->onDelete('set null');
        });

        // Modificar colunas PIX para TEXT
        Schema::table('payments', function (Blueprint $table) {
            $table->text('pix_qr_code')->nullable()->change();
            $table->text('pix_copy_paste')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remover foreign key constraint do plan_id
            $table->dropForeign(['plan_id']);
            
            // Remover campos adicionados
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
                'plan_id'
            ]);
        });

        // Reverter colunas PIX para STRING
        Schema::table('payments', function (Blueprint $table) {
            $table->string('pix_qr_code')->nullable()->change();
            $table->string('pix_copy_paste')->nullable()->change();
        });
    }
};

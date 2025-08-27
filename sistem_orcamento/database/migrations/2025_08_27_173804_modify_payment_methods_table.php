<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;
use App\Models\PaymentOptionMethod;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a coluna payment_option_method_id já existe
        if (!Schema::hasColumn('payment_methods', 'payment_option_method_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->unsignedBigInteger('payment_option_method_id')->nullable()->after('company_id');
            });
        }

        // Mapeia os nomes existentes para os novos IDs (se a coluna name ainda existir)
        if (Schema::hasColumn('payment_methods', 'name')) {
            $this->migrateExistingData();
        }

        // Remove a coluna name se ainda existir e adiciona a foreign key
        Schema::table('payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('payment_methods', 'name')) {
                $table->dropColumn('name');
            }
        });

        // Adiciona a foreign key em uma operação separada
        try {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->unsignedBigInteger('payment_option_method_id')->nullable(false)->change();
                $table->foreign('payment_option_method_id')->references('id')->on('payment_option_methods');
            });
        } catch (\Exception $e) {
            // Foreign key já existe, ignora o erro
        }
    }

    private function migrateExistingData()
    {
        // Mapeamento dos nomes antigos para os novos métodos
        $mapping = [
            'PIX' => 'PIX',
            'Cartão de Crédito' => 'Cartão de Crédito',
            'Cartão de Débito' => 'Cartão de Débito',
            'Transferência Bancária' => 'Transferência Bancária TED',
            'Dinheiro' => 'PIX', // Mapear para PIX como padrão
            'Boleto' => 'PIX', // Mapear para PIX como padrão
            'Cheque' => 'PIX', // Mapear para PIX como padrão
            'Outros' => 'PIX' // Mapear para PIX como padrão
        ];

        foreach ($mapping as $oldName => $newMethod) {
            $paymentOptionMethod = PaymentOptionMethod::where('method', $newMethod)->first();
            if ($paymentOptionMethod) {
                PaymentMethod::where('name', $oldName)
                    ->update(['payment_option_method_id' => $paymentOptionMethod->id]);
            }
        }

        // Para qualquer registro que ainda não foi mapeado, usar PIX como padrão
        $pixMethod = PaymentOptionMethod::where('method', 'PIX')->first();
        if ($pixMethod) {
            PaymentMethod::whereNull('payment_option_method_id')
                ->update(['payment_option_method_id' => $pixMethod->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tentar remover a foreign key usando SQL direto
        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE payment_methods DROP FOREIGN KEY payment_methods_payment_option_method_id_foreign');
        } catch (\Exception $e) {
            // Foreign key não existe, continua
        }
        
        Schema::table('payment_methods', function (Blueprint $table) {
            // Remover a coluna se existir
            if (Schema::hasColumn('payment_methods', 'payment_option_method_id')) {
                $table->dropColumn('payment_option_method_id');
            }
            
            // Adicionar a coluna name se não existir
            if (!Schema::hasColumn('payment_methods', 'name')) {
                $table->string('name');
            }
        });
    }
};

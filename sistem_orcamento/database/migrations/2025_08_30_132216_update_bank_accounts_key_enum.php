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
        // Alterar o enum da coluna 'key' para incluir 'CNPJ'
        DB::statement("ALTER TABLE bank_accounts MODIFY COLUMN `key` ENUM('CPF', 'CNPJ', 'email', 'telefone') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primeiro, atualizar registros com 'CNPJ' para 'CPF' antes de alterar o ENUM
        DB::table('bank_accounts')->where('key', 'CNPJ')->update(['key' => 'CPF']);
        
        // Reverter para o enum original
        DB::statement("ALTER TABLE bank_accounts MODIFY COLUMN `key` ENUM('CPF', 'email', 'telefone') NULL");
    }
};

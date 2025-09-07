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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');
                  
            // Remover a constraint unique do slug para permitir duplicatas entre empresas
            $table->dropUnique(['slug']);
            
            // Criar nova constraint unique composta (company_id, slug)
            $table->unique(['company_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Remover constraint unique composta
            $table->dropUnique(['company_id', 'slug']);
            
            // Recriar constraint unique simples do slug
            $table->unique('slug');
            
            // Remover foreign key e coluna
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
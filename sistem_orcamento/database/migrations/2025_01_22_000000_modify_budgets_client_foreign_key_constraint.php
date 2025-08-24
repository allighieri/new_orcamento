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
        Schema::table('budgets', function (Blueprint $table) {
            // Remove a constraint existente
            $table->dropForeign(['client_id']);
            
            // Adiciona a nova constraint com CASCADE
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            // Remove a constraint modificada
            $table->dropForeign(['client_id']);
            
            // Restaura a constraint original com RESTRICT
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('restrict');
        });
    }
};
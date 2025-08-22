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
        Schema::table('contacts', function (Blueprint $table) {
            // Remove a constraint existente
            $table->dropForeign(['company_id']);
            
            // Adiciona a nova constraint com SET NULL
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Remove a constraint modificada
            $table->dropForeign(['company_id']);
            
            // Restaura a constraint original com CASCADE
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');
        });
    }
};

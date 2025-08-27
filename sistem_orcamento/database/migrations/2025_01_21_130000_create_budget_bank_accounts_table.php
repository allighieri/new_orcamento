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
        Schema::create('budget_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('bank_account_id');
            $table->integer('order')->default(1); // Ordem de exibição
            $table->timestamps();
            
            $table->foreign('budget_id')
                  ->references('id')
                  ->on('budgets')
                  ->onDelete('cascade');
                  
            $table->foreign('bank_account_id')
                  ->references('id')
                  ->on('bank_accounts')
                  ->onDelete('cascade');
                  
            $table->unique(['budget_id', 'bank_account_id']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_bank_accounts');
    }
};
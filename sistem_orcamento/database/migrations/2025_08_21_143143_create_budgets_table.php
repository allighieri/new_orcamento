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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('number'); // Formato 0000/YYYY
            $table->unsignedBigInteger('client_id');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->date('issue_date');
            $table->timestamps();
            
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('restrict');
                  
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

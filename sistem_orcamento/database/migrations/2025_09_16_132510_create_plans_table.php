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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Prata, Ouro
            $table->string('slug')->unique(); // bronze, prata, ouro
            $table->integer('budget_limit')->nullable(); // 5, 50, null (ilimitado)
            $table->decimal('monthly_price', 8, 2); // 30.00, 40.00, 50.00
            $table->decimal('yearly_price', 8, 2); // 300.00, 420.00, 540.00
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

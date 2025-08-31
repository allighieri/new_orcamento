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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('budget_validity_days')->default(30);
            $table->integer('budget_delivery_days')->default(30);
            $table->boolean('enable_pdf_watermark')->default(true);
            $table->boolean('show_validity_as_text')->default(false);
            $table->integer('border')->nullable()->default(0);
            $table->timestamps();
            
            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};

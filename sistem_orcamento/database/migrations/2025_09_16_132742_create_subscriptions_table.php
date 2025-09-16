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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->datetime('grace_period_ends_at')->nullable(); // 3 dias após término
            $table->integer('remaining_budgets')->nullable(); // orçamentos restantes do plano anterior
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

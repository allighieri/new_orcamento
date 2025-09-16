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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('asaas_subscription_id')->nullable()->after('plan_id');
            $table->datetime('start_date')->nullable()->after('status');
            $table->datetime('end_date')->nullable()->after('start_date');
            $table->datetime('next_billing_date')->nullable()->after('end_date');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('next_billing_date');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null')->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn([
                'asaas_subscription_id',
                'start_date',
                'end_date',
                'next_billing_date',
                'amount_paid',
                'payment_id'
            ]);
        });
    }
};

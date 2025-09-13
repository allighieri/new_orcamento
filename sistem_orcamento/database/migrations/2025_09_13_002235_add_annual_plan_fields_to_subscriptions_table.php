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
            $table->string('asaas_subscription_id')->nullable()->after('in_grace_period');
            $table->boolean('can_downgrade_to_monthly')->default(true)->after('asaas_subscription_id');
            $table->boolean('cancellation_fee_paid')->default(false)->after('can_downgrade_to_monthly');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_fee_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_subscription_id',
                'can_downgrade_to_monthly',
                'cancellation_fee_paid',
                'cancelled_at'
            ]);
        });
    }
};

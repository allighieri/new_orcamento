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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('asaas_customer_id')->nullable()->after('asaas_payment_id');
            $table->string('billing_type')->nullable()->after('payment_method');
            $table->string('billing_cycle')->nullable()->after('billing_type');
            $table->string('type')->nullable()->after('billing_cycle');
            $table->text('description')->nullable()->after('confirmed_at');
            $table->integer('extra_budgets_quantity')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_customer_id',
                'billing_type',
                'billing_cycle',
                'type',
                'description',
                'extra_budgets_quantity'
            ]);
        });
    }
};

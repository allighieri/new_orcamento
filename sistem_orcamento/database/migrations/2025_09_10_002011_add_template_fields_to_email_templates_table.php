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
        Schema::table('email_templates', function (Blueprint $table) {
            // Campos de conteúdo do template
            $table->text('header_text')->nullable()->after('html_content');
            $table->text('header2_text')->nullable()->after('header_text');
            $table->text('initial_message')->nullable()->after('header2_text');
            $table->text('final_message')->nullable()->after('initial_message');
            $table->text('footer_text')->nullable()->after('final_message');
            
            // Campos booleanos para detalhes do orçamento
            $table->boolean('show_budget_number')->default(true)->after('footer_text');
            $table->boolean('show_budget_value')->default(true)->after('show_budget_number');
            $table->boolean('show_budget_date')->default(true)->after('show_budget_value');
            $table->boolean('show_budget_validity')->default(true)->after('show_budget_date');
            $table->boolean('show_delivery_date')->default(true)->after('show_budget_validity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn([
                'header_text',
                'header2_text', 
                'initial_message',
                'final_message',
                'footer_text',
                'show_budget_number',
                'show_budget_value',
                'show_budget_date',
                'show_budget_validity',
                'show_delivery_date'
            ]);
        });
    }
};

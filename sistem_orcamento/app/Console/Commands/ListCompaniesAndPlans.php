<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;

class ListCompaniesAndPlans extends Command
{
    protected $signature = 'subscription:list-data {--companies : Listar empresas} {--plans : Listar planos} {--payments : Listar pagamentos} {--subscriptions : Listar assinaturas} {--all : Listar tudo}';
    protected $description = 'Listar empresas, planos, pagamentos e assinaturas disponíveis';

    public function handle()
    {
        $showCompanies = $this->option('companies') || $this->option('all');
        $showPlans = $this->option('plans') || $this->option('all');
        $showPayments = $this->option('payments') || $this->option('all');
        $showSubscriptions = $this->option('subscriptions') || $this->option('all');
        
        // Se nenhuma opção foi especificada, mostrar tudo
        if (!$showCompanies && !$showPlans && !$showPayments && !$showSubscriptions) {
            $showCompanies = true;
            $showPlans = true;
        }

        if ($showCompanies) {
            $this->listCompanies();
        }

        if ($showPlans) {
            if ($showCompanies) {
                $this->newLine();
            }
            $this->listPlans();
        }

        if ($showPayments) {
            if ($showCompanies || $showPlans) {
                $this->newLine();
            }
            $this->listPayments();
        }

        if ($showSubscriptions) {
            if ($showCompanies || $showPlans || $showPayments) {
                $this->newLine();
            }
            $this->listSubscriptions();
        }

        $this->newLine();
        $this->info("💡 Exemplo de uso:");
        $this->line("   php artisan subscription:create-manual {company_id} {plan_id}");
        $this->line("   php artisan subscription:create-manual 1 2 --cycle=yearly --status=active");

        return 0;
    }

    private function listCompanies()
    {
        $this->info("📋 Empresas Cadastradas:");
        
        $companies = Company::with(['subscriptions' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('fantasy_name')->get();

        if ($companies->isEmpty()) {
            $this->warn("Nenhuma empresa encontrada.");
            return;
        }

        $data = [];
        foreach ($companies as $company) {
            $activeSubscription = $company->subscriptions->first();
            $status = $activeSubscription ? 'Ativa' : 'Sem assinatura';
            $planName = $activeSubscription ? $activeSubscription->plan->name : '-';
            
            $data[] = [
                $company->id,
                $company->fantasy_name ?? $company->corporate_name ?? 'N/A',
                $company->document_number ?? 'N/A',
                $company->email ?? 'N/A',
                $status,
                $planName
            ];
        }

        $this->table(
            ['ID', 'Nome', 'CNPJ', 'Email', 'Status', 'Plano Atual'],
            $data
        );
    }

    private function listPlans()
    {
        $this->info("📦 Planos Disponíveis:");
        
        $plans = Plan::where('active', true)->orderBy('monthly_price')->get();

        if ($plans->isEmpty()) {
            $this->warn("Nenhum plano ativo encontrado.");
            return;
        }

        $data = [];
        foreach ($plans as $plan) {
            $budgetLimit = $plan->isUnlimited() ? 'Ilimitado' : $plan->budget_limit;
            
            $data[] = [
                $plan->id,
                $plan->name,
                $plan->slug,
                $budgetLimit,
                'R$ ' . number_format($plan->monthly_price, 2, ',', '.'),
                'R$ ' . number_format($plan->yearly_price, 2, ',', '.'),
                $plan->active ? 'Sim' : 'Não'
            ];
        }

        $this->table(
            ['ID', 'Nome', 'Slug', 'Limite Orçamentos', 'Preço Mensal', 'Preço Anual', 'Ativo'],
            $data
        );

        $this->newLine();
        $this->info("💰 Economia anual por plano:");
        foreach ($plans as $plan) {
            $monthlyTotal = $plan->monthly_price * 12;
            $savings = $monthlyTotal - $plan->yearly_price;
            $savingsPercent = ($savings / $monthlyTotal) * 100;
            
            $this->line(sprintf(
                "   %s: R$ %.2f (%.1f%% de desconto)",
                $plan->name,
                $savings,
                $savingsPercent
            ));
        }
    }

    private function listPayments()
    {
        $this->info("💳 Pagamentos Cadastrados:");
        
        $payments = \App\Models\Payment::with(['subscription', 'plan', 'company'])->orderBy('created_at', 'desc')->get();
        
        if ($payments->isEmpty()) {
            $this->warn("Nenhum pagamento encontrado.");
            return;
        }

        $data = [];
        foreach ($payments as $payment) {
            $company = $payment->company ? $payment->company->fantasy_name : 'N/A';
            $plan = $payment->plan ? $payment->plan->name : 'N/A';
            $amount = 'R$ ' . number_format($payment->amount, 2, ',', '.');
            
            $data[] = [
                $payment->id,
                $payment->subscription_id ?? 'N/A',
                $company,
                $plan,
                $amount,
                $payment->status,
                $payment->billing_type ?? 'N/A',
                $payment->created_at->format('d/m/Y H:i')
            ];
        }

        $this->table(
            ['ID', 'Subscription', 'Empresa', 'Plano', 'Valor', 'Status', 'Tipo', 'Criado'],
            $data
        );
    }

    private function listSubscriptions()
    {
        $this->info("📝 Assinaturas Cadastradas:");
        
        $subscriptions = \App\Models\Subscription::with(['payment', 'plan', 'company'])->orderBy('created_at', 'desc')->get();
        
        if ($subscriptions->isEmpty()) {
            $this->warn("Nenhuma assinatura encontrada.");
            return;
        }

        $data = [];
        foreach ($subscriptions as $subscription) {
            $company = $subscription->company ? $subscription->company->fantasy_name : 'N/A';
            $plan = $subscription->plan ? $subscription->plan->name : 'N/A';
            $paymentStatus = $subscription->payment ? $subscription->payment->status : 'N/A';
            
            $data[] = [
                $subscription->id,
                $company,
                $plan,
                $subscription->status,
                $subscription->payment_id ?? 'N/A',
                $paymentStatus,
                $subscription->billing_cycle,
                $subscription->starts_at ? $subscription->starts_at->format('d/m/Y') : 'N/A',
                $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : 'N/A'
            ];
        }

        $this->table(
            ['ID', 'Empresa', 'Plano', 'Status', 'Payment ID', 'Payment Status', 'Ciclo', 'Início', 'Término'],
            $data
        );
    }
}
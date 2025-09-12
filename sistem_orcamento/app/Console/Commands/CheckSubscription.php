<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Payment;

class CheckSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:subscription {company_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar subscription ativa de uma empresa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        $this->info("Verificando subscription da empresa ID: {$companyId}");
        
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Empresa não encontrada!");
            return 1;
        }
        
        $this->info("Empresa: {$company->name}");
        
        $subscription = $company->activeSubscription();
        if ($subscription) {
            $this->info("✅ Subscription ativa encontrada:");
            $this->info("   ID: {$subscription->id}");
            $this->info("   Plano: {$subscription->plan->name}");
            $this->info("   Status: {$subscription->status}");
            $this->info("   Início: {$subscription->start_date}");
            $this->info("   Fim: {$subscription->end_date}");
        } else {
            $this->error("❌ Nenhuma subscription ativa encontrada!");
            
            // Verificar último pagamento
            $lastPayment = Payment::where('company_id', $companyId)->latest()->first();
            if ($lastPayment) {
                $this->info("Último pagamento:");
                $this->info("   ID: {$lastPayment->id}");
                $this->info("   Status: {$lastPayment->status}");
                $this->info("   Asaas ID: {$lastPayment->asaas_payment_id}");
            }
        }
        
        return 0;
    }
}

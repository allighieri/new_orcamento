<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;

class CheckSubscriptions extends Command
{
    protected $signature = 'check:subscriptions {company_id?}';
    protected $description = 'Verificar subscriptions na tabela';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        if ($companyId) {
            $subscriptions = Subscription::where('company_id', $companyId)->get();
            
            if ($subscriptions->count() > 0) {
                $this->info('Subscriptions encontradas para empresa ID ' . $companyId . ':');
                $data = [];
                foreach ($subscriptions as $subscription) {
                    $data[] = [
                        $subscription->id,
                        $subscription->company_id,
                        $subscription->plan_id,
                        $subscription->status,
                        $subscription->billing_cycle,
                        $subscription->start_date ? $subscription->start_date->format('d/m/Y') : 'N/A',
                        $subscription->end_date ? $subscription->end_date->format('d/m/Y') : 'N/A',
                        $subscription->created_at->format('d/m/Y H:i')
                    ];
                }
                
                $this->table(
                    ['ID', 'Company ID', 'Plan ID', 'Status', 'Billing Cycle', 'Start Date', 'End Date', 'Created At'],
                    $data
                );
            } else {
                $this->error('Nenhuma subscription encontrada para empresa ID: ' . $companyId);
            }
        } else {
            $subscriptions = Subscription::orderBy('created_at', 'desc')->limit(10)->get();
            
            if ($subscriptions->count() > 0) {
                $this->info('Últimas 10 subscriptions:');
                $data = [];
                foreach ($subscriptions as $subscription) {
                    $data[] = [
                        $subscription->id,
                        $subscription->company_id,
                        $subscription->plan_id,
                        $subscription->status,
                        $subscription->billing_cycle,
                        $subscription->start_date ? $subscription->start_date->format('d/m/Y') : 'N/A',
                        $subscription->end_date ? $subscription->end_date->format('d/m/Y') : 'N/A',
                        $subscription->created_at->format('d/m/Y H:i')
                    ];
                }
                
                $this->table(
                    ['ID', 'Company ID', 'Plan ID', 'Status', 'Billing Cycle', 'Start Date', 'End Date', 'Created At'],
                    $data
                );
            } else {
                $this->info('Nenhuma subscription encontrada na tabela.');
            }
        }
        
        return 0;
    }
}
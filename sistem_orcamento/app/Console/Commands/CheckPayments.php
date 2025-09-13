<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;

class CheckPayments extends Command
{
    protected $signature = 'check:payments {asaas_id?}';
    protected $description = 'Verificar pagamentos na tabela';

    public function handle()
    {
        $asaasId = $this->argument('asaas_id');
        
        if ($asaasId) {
            $payment = Payment::where('asaas_payment_id', $asaasId)->first();
            
            if ($payment) {
                $this->info('Pagamento encontrado:');
                $this->table(
                    ['ID', 'Company ID', 'Plan ID', 'Asaas Payment ID', 'Status', 'Amount'],
                    [[
                        $payment->id,
                        $payment->company_id,
                        $payment->plan_id,
                        $payment->asaas_payment_id,
                        $payment->status,
                        $payment->amount
                    ]]
                );
            } else {
                $this->error('Pagamento não encontrado com asaas_payment_id: ' . $asaasId);
            }
        } else {
            $payments = Payment::orderBy('created_at', 'desc')->limit(10)->get();
            
            if ($payments->count() > 0) {
                $this->info('Últimos 10 pagamentos:');
                $data = [];
                foreach ($payments as $payment) {
                    $data[] = [
                        $payment->id,
                        $payment->company_id,
                        $payment->plan_id,
                        $payment->asaas_payment_id ?? 'N/A',
                        $payment->status,
                        $payment->amount,
                        $payment->created_at->format('d/m/Y H:i')
                    ];
                }
                
                $this->table(
                    ['ID', 'Company ID', 'Plan ID', 'Asaas Payment ID', 'Status', 'Amount', 'Created At'],
                    $data
                );
            } else {
                $this->info('Nenhum pagamento encontrado na tabela.');
            }
        }
        
        return 0;
    }
}
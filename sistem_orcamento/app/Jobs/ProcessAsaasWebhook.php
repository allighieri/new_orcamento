<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\UsageControl;
use App\Services\AsaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessAsaasWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;
    public $tries = 3; // Número de tentativas em caso de falha
    public $timeout = 120; // Timeout em segundos

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(AsaasService $asaasService)
    {
        try {
            Log::info('Processando webhook Asaas via fila', [
                'job_id' => $this->job->getJobId(),
                'payload' => $this->payload
            ]);

            // Validar se é um evento válido
            if (!isset($this->payload['event'])) {
                Log::warning('Webhook inválido - evento ausente', [
                    'payload' => $this->payload
                ]);
                return;
            }

            $event = $this->payload['event'];

            // Processar diferentes tipos de eventos
            if (str_starts_with($event, 'PAYMENT_')) {
                $this->handlePaymentEvent();
            } elseif (str_starts_with($event, 'SUBSCRIPTION_')) {
                $this->handleSubscriptionEvent();
            } else {
                Log::info('Evento não processado', [
                    'event' => $event,
                    'payload' => $this->payload
                ]);
            }

            Log::info('Webhook processado com sucesso via fila', [
                'event' => $event
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar webhook via fila', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $this->payload
            ]);
            
            // Re-lançar a exceção para que o Laravel tente novamente
            throw $e;
        }
    }

    /**
     * Processar eventos de pagamento
     */
    private function handlePaymentEvent()
    {
        if (!isset($this->payload['payment'])) {
            Log::warning('Evento de pagamento sem dados de pagamento', [
                'event' => $this->payload['event'],
                'payload' => $this->payload
            ]);
            return;
        }

        $event = $this->payload['event'];
        $paymentData = $this->payload['payment'];
        $asaasPaymentId = $paymentData['id'];

        // Buscar pagamento no banco de dados
        $payment = Payment::where('asaas_payment_id', $asaasPaymentId)->first();

        // Se não encontrou por payment_id, tentar por subscription_id (para assinaturas)
        if (!$payment && isset($paymentData['subscription'])) {
            $asaasSubscriptionId = $paymentData['subscription'];
            $payment = Payment::where('asaas_subscription_id', $asaasSubscriptionId)->first();
            
            Log::info('Buscando pagamento por subscription_id', [
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_subscription_id' => $asaasSubscriptionId,
                'payment_found' => $payment ? 'sim' : 'não'
            ]);
        }

        if (!$payment) {
            Log::warning('Pagamento não encontrado no banco', [
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_subscription_id' => $paymentData['subscription'] ?? null
            ]);
            return;
        }

        DB::beginTransaction();

        // Processar diferentes tipos de eventos de pagamento
        switch ($event) {
            case 'PAYMENT_RECEIVED':
            case 'PAYMENT_CONFIRMED':
                $this->handlePaymentApproved($payment, $paymentData);
                break;
                
            case 'PAYMENT_OVERDUE':
                $this->handlePaymentOverdue($payment, $paymentData);
                break;
                
            case 'PAYMENT_DELETED':
            case 'PAYMENT_REFUNDED':
                $this->handlePaymentCancelled($payment, $paymentData);
                break;
                
            default:
                // Atualizar apenas o status para outros eventos
                $payment->updateStatus($paymentData['status'], $this->payload);
                Log::info('Status do pagamento atualizado via fila', [
                    'payment_id' => $payment->id,
                    'event' => $event,
                    'status' => $paymentData['status']
                ]);
        }

        DB::commit();
    }

    /**
     * Processar eventos de assinatura
     */
    private function handleSubscriptionEvent()
    {
        if (!isset($this->payload['subscription'])) {
            Log::warning('Evento de assinatura sem dados de assinatura', [
                'event' => $this->payload['event'],
                'payload' => $this->payload
            ]);
            return;
        }

        $event = $this->payload['event'];
        $subscriptionData = $this->payload['subscription'];
        $asaasSubscriptionId = $subscriptionData['id'];

        Log::info('Processando evento de assinatura via fila', [
            'event' => $event,
            'asaas_subscription_id' => $asaasSubscriptionId,
            'subscription_data' => $subscriptionData
        ]);

        DB::beginTransaction();

        switch ($event) {
            case 'SUBSCRIPTION_CREATED':
                $this->handleSubscriptionCreated($subscriptionData);
                break;
                
            case 'SUBSCRIPTION_UPDATED':
                $this->handleSubscriptionUpdated($subscriptionData);
                break;
                
            case 'SUBSCRIPTION_DELETED':
            case 'SUBSCRIPTION_CANCELLED':
                $this->handleSubscriptionCancelled($subscriptionData);
                break;
                
            default:
                Log::info('Evento de assinatura não processado', [
                    'event' => $event,
                    'asaas_subscription_id' => $asaasSubscriptionId
                ]);
        }

        DB::commit();
    }

    /**
     * Processar criação de assinatura
     */
    private function handleSubscriptionCreated($subscriptionData)
    {
        $asaasSubscriptionId = $subscriptionData['id'];
        
        // Verificar se já existe uma assinatura com este ID
        $existingSubscription = Subscription::where('asaas_subscription_id', $asaasSubscriptionId)->first();
        
        if ($existingSubscription) {
            Log::info('Assinatura já existe no banco', [
                'asaas_subscription_id' => $asaasSubscriptionId,
                'subscription_id' => $existingSubscription->id
            ]);
            return;
        }

        Log::info('Nova assinatura criada no Asaas - aguardando primeiro pagamento', [
            'asaas_subscription_id' => $asaasSubscriptionId,
            'customer' => $subscriptionData['customer'] ?? null,
            'value' => $subscriptionData['value'] ?? null,
            'cycle' => $subscriptionData['cycle'] ?? null,
            'status' => $subscriptionData['status'] ?? null
        ]);
    }

    /**
     * Processar atualização de assinatura
     */
    private function handleSubscriptionUpdated($subscriptionData)
    {
        $asaasSubscriptionId = $subscriptionData['id'];
        
        $subscription = Subscription::where('asaas_subscription_id', $asaasSubscriptionId)->first();
        
        if ($subscription) {
            Log::info('Assinatura atualizada no Asaas', [
                'asaas_subscription_id' => $asaasSubscriptionId,
                'subscription_id' => $subscription->id,
                'new_status' => $subscriptionData['status'] ?? null
            ]);
        } else {
            Log::warning('Assinatura para atualização não encontrada', [
                'asaas_subscription_id' => $asaasSubscriptionId
            ]);
        }
    }

    /**
     * Processar cancelamento de assinatura
     */
    private function handleSubscriptionCancelled($subscriptionData)
    {
        $asaasSubscriptionId = $subscriptionData['id'];
        
        $subscription = Subscription::where('asaas_subscription_id', $asaasSubscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
            
            Log::info('Assinatura cancelada via webhook', [
                'asaas_subscription_id' => $asaasSubscriptionId,
                'subscription_id' => $subscription->id
            ]);
        } else {
            Log::warning('Assinatura para cancelamento não encontrada', [
                'asaas_subscription_id' => $asaasSubscriptionId
            ]);
        }


    }

    /**
     * Processar pagamento aprovado
     */
    private function handlePaymentApproved(Payment $payment, array $paymentData)
    {
        // Atualizar status do pagamento
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        // Verificar tipo de pagamento
        if ($payment->type === 'cancellation_fee') {
            $this->handleCancellationFeePayment($payment);
        } elseif ($payment->type === 'plan_change') {
            $this->handlePlanChangePayment($payment);
        } elseif ($payment->type === 'plan_change_annual') {
            $this->handleAnnualPlanChangePayment($payment, $paymentData);
        } elseif ($payment->extra_budgets_quantity && $payment->extra_budgets_quantity > 0) {
            $this->handleExtraBudgetsPayment($payment);
        } else {
            $this->handleSubscriptionPayment($payment, $paymentData);
        }
    }

    /**
     * Processar pagamento de orçamentos extras
     */
    private function handleExtraBudgetsPayment(Payment $payment)
    {
        // Buscar assinatura ativa da empresa
        $subscription = Subscription::where('company_id', $payment->company_id)
            ->where('status', 'active')
            ->first();
            
        if (!$subscription) {
            Log::error('Assinatura ativa não encontrada para pagamento de orçamentos extras', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id
            ]);
            return;
        }
        
        // Buscar controle de uso atual
        $usageControl = UsageControl::getOrCreateForCurrentMonth(
            $payment->company_id,
            $subscription->id,
            $subscription->plan->budget_limit
        );
        
        // Adicionar orçamentos extras ao limite atual (mesmo valor do plano)
        $extraBudgets = $subscription->plan->budget_limit;
        $usageControl->addExtraBudgets($extraBudgets, $payment->amount);
        
        Log::info('Orçamentos extras adicionados via pagamento (fila)', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'subscription_id' => $subscription->id,
            'extra_budgets_added' => $extraBudgets,
            'total_extra_budgets' => $usageControl->extra_budgets_purchased,
            'budgets_limit' => $usageControl->budgets_limit,
            'amount_paid' => $payment->amount
        ]);
    }

    /**
     * Processar pagamento de assinatura
     */
    private function handleSubscriptionPayment(Payment $payment, array $paymentData = [])
    {
        // Se o pagamento tem asaas_subscription_id, é uma cobrança recorrente (apenas para planos mensais)
        if ($payment->asaas_subscription_id) {
            // Buscar assinatura existente com o mesmo asaas_subscription_id
            $subscriptionByAsaasId = Subscription::where('asaas_subscription_id', $payment->asaas_subscription_id)
                ->where('status', 'active')
                ->first();
                
            if ($subscriptionByAsaasId) {
                Log::info('Pagamento recorrente de assinatura confirmado (fila)', [
                    'payment_id' => $payment->id,
                    'subscription_id' => $subscriptionByAsaasId->id,
                    'asaas_subscription_id' => $payment->asaas_subscription_id,
                    'amount' => $payment->amount
                ]);
                return; // Não precisa criar nova assinatura
            }
            
            // Primeiro pagamento da assinatura - criar assinatura
            Log::info('Primeiro pagamento de assinatura - criando assinatura (fila)', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'billing_cycle' => $payment->billing_cycle,
                'asaas_subscription_id' => $payment->asaas_subscription_id
            ]);
        } else if ($payment->billing_cycle === 'annual') {
            // Para planos anuais, é um pagamento único - criar assinatura diretamente
            Log::info('Pagamento único anual confirmado - criando assinatura (fila)', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'billing_cycle' => $payment->billing_cycle,
                'amount' => $payment->amount
            ]);
        }
        
        // Buscar e cancelar TODAS as assinaturas ativas da empresa antes de criar nova
        // Usar lockForUpdate para evitar condições de corrida
        $activeSubscriptions = Subscription::where('company_id', $payment->company_id)
            ->where('status', 'active')
            ->lockForUpdate()
            ->get();
        
        // Cancelar todas as assinaturas ativas encontradas
        foreach ($activeSubscriptions as $activeSubscription) {
            Log::info('Cancelando assinatura anterior (fila)', [
                'payment_id' => $payment->id,
                'old_subscription_id' => $activeSubscription->id,
                'old_plan_id' => $activeSubscription->plan_id,
                'new_plan_id' => $payment->plan_id,
                'old_billing_cycle' => $activeSubscription->billing_cycle,
                'new_billing_cycle' => $payment->billing_cycle
            ]);
            
            // Cancelar no Asaas se tiver subscription_id
            if ($activeSubscription->asaas_subscription_id) {
                try {
                    $asaasService = app(\App\Services\AsaasService::class);
                    $asaasService->cancelSubscription($activeSubscription->asaas_subscription_id);
                } catch (\Exception $e) {
                    Log::warning('Erro ao cancelar assinatura no Asaas via webhook', [
                        'subscription_id' => $activeSubscription->asaas_subscription_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'end_date' => now()
            ]);
        }
        
        // Log do número de assinaturas canceladas
        if ($activeSubscriptions->count() > 0) {
            Log::info('Total de assinaturas canceladas', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'cancelled_count' => $activeSubscriptions->count()
            ]);
        }
        
        // Calcular datas baseado no ciclo de cobrança
        $startDate = now();
        $endDate = $payment->billing_cycle === 'annual' 
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();
            
        // Criar nova assinatura
        $newSubscription = Subscription::create([
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id,
            'billing_cycle' => $payment->billing_cycle,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_billing_date' => $endDate,
            'amount_paid' => $payment->amount,
            'asaas_subscription_id' => $payment->asaas_subscription_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Processar controle de uso para o novo plano
        $plan = \App\Models\Plan::find($payment->plan_id);
        
        if ($plan) {
            // Para planos anuais, definir orçamentos ilimitados
            if ($payment->billing_cycle === 'annual') {
                // Criar controle de uso com orçamentos ilimitados (budget_limit = valor alto)
                $usageControl = UsageControl::getOrCreateForCurrentMonth(
                    $payment->company_id,
                    $newSubscription->id,
                    999999 // Orçamentos ilimitados para planos anuais
                );
                
                // Resetar budgets_used para 0
                $usageControl->budgets_used = 0;
                $usageControl->extra_budgets_purchased = 0;
                $usageControl->save();
                
                Log::info('Plano anual ativado com orçamentos ilimitados (fila)', [
                    'company_id' => $payment->company_id,
                    'subscription_id' => $newSubscription->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => 'annual',
                    'budgets_limit' => 'unlimited'
                ]);
            } else {
                // Para planos mensais, manter lógica atual
                // Buscar controle de uso atual para calcular orçamentos restantes
                $currentUsageControl = null;
                if (isset($subscription)) {
                    $currentUsageControl = UsageControl::where('company_id', $payment->company_id)
                        ->where('year', now()->year)
                        ->where('month', now()->month)
                        ->first();
                }
                
                // Calcular todos os orçamentos restantes do plano anterior
                $remainingBudgetsToMigrate = 0;
                if ($currentUsageControl) {
                    $oldPlanLimit = $currentUsageControl->budgets_limit;
                    $extraBudgetsPurchased = $currentUsageControl->extra_budgets_purchased;
                    $usedBudgets = $currentUsageControl->budgets_used;
                    $totalOldBudgets = $oldPlanLimit + $extraBudgetsPurchased;
                    
                    // Calcular todos os orçamentos restantes (base + extras não utilizados)
                    if ($usedBudgets < $totalOldBudgets) {
                        $remainingBudgetsToMigrate = $totalOldBudgets - $usedBudgets;
                    }
                    
                    Log::info('Calculando orçamentos restantes na mudança de plano (fila)', [
                        'company_id' => $payment->company_id,
                        'old_plan_limit' => $oldPlanLimit,
                        'old_extra_purchased' => $extraBudgetsPurchased,
                        'used_budgets' => $usedBudgets,
                        'total_old_budgets' => $totalOldBudgets,
                        'remaining_budgets_to_migrate' => $remainingBudgetsToMigrate,
                        'new_plan_limit' => $plan->budget_limit
                    ]);
                }
                
                // Criar ou atualizar controle de uso com o novo plano
                $usageControl = UsageControl::getOrCreateForCurrentMonth(
                    $payment->company_id,
                    $newSubscription->id,
                    $plan->budget_limit
                );
                
                // Resetar budgets_used para 0 e migrar orçamentos restantes como extras
                $usageControl->budgets_used = 0;
                $usageControl->extra_budgets_purchased = $remainingBudgetsToMigrate;
                $usageControl->save();
                
                if ($remainingBudgetsToMigrate > 0) {
                    Log::info('Orçamentos restantes migrados para novo plano (fila)', [
                        'company_id' => $payment->company_id,
                        'subscription_id' => $newSubscription->id,
                        'migrated_budgets' => $remainingBudgetsToMigrate,
                        'new_plan_limit' => $plan->budget_limit,
                        'total_available' => $plan->budget_limit + $remainingBudgetsToMigrate
                    ]);
                }
            }
        }
    }

    /**
     * Processar pagamento vencido
     */
    private function handlePaymentOverdue(Payment $payment, array $paymentData)
    {
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        Log::info('Pagamento vencido processado via fila', [
            'payment_id' => $payment->id,
            'status' => $paymentData['status']
        ]);
    }

    /**
     * Processar pagamento cancelado/estornado
     */
    private function handlePaymentCancelled(Payment $payment, array $paymentData)
    {
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        Log::info('Pagamento cancelado/estornado processado via fila', [
            'payment_id' => $payment->id,
            'status' => $paymentData['status']
        ]);
    }

    /**
     * Processar pagamento de taxa de cancelamento
     */
    private function handleCancellationFeePayment(Payment $payment)
    {
        Log::info('Taxa de cancelamento paga via fila', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'amount' => $payment->amount
        ]);
        
        // Aqui você pode implementar a lógica específica para taxa de cancelamento
        // Por exemplo, reativar a empresa ou permitir nova assinatura
    }
    
    /**
     * Processar pagamento de mudança entre planos anuais
     */
    private function handleAnnualPlanChangePayment(Payment $payment, array $paymentData)
    {
        Log::info('Pagamento de mudança entre planos anuais confirmado via fila', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'new_plan_id' => $payment->plan_id,
            'amount' => $payment->amount
        ]);
        
        try {
            $company = \App\Models\Company::find($payment->company_id);
            $newPlan = \App\Models\Plan::find($payment->plan_id);
            
            if (!$company || !$newPlan) {
                Log::error('Empresa ou plano não encontrado para mudança de plano anual', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id,
                    'plan_id' => $payment->plan_id
                ]);
                return;
            }
            
            // Buscar e cancelar TODAS as assinaturas ativas da empresa
            $activeSubscriptions = Subscription::where('company_id', $payment->company_id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();
            
            Log::info('Cancelando assinaturas ativas para mudança de plano anual', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'active_subscriptions_count' => $activeSubscriptions->count()
            ]);
            
            // Cancelar todas as assinaturas ativas
            foreach ($activeSubscriptions as $activeSubscription) {
                // Cancelar no Asaas se tiver subscription_id
                if ($activeSubscription->asaas_subscription_id) {
                    try {
                        $asaasService = app(\App\Services\AsaasService::class);
                        $asaasService->cancelSubscription($activeSubscription->asaas_subscription_id);
                    } catch (\Exception $e) {
                        Log::warning('Erro ao cancelar assinatura no Asaas via webhook', [
                            'subscription_id' => $activeSubscription->asaas_subscription_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                $activeSubscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'end_date' => now()
                ]);
            }
            
            // Criar nova assinatura anual
            $newSubscription = \App\Models\Subscription::create([
                'company_id' => $payment->company_id,
                'plan_id' => $newPlan->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'next_billing_date' => now()->addYear(),
                'billing_cycle' => 'annual',
                'amount_paid' => $payment->amount,
                'asaas_subscription_id' => $payment->asaas_subscription_id,
                'payment_id' => $payment->id
            ]);
            
            Log::info('Nova assinatura anual criada após mudança de plano', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'new_subscription_id' => $newSubscription->id,
                'new_plan_id' => $newPlan->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar mudança de plano anual', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Processar pagamento de mudança de plano
     */
    private function handlePlanChangePayment(Payment $payment)
    {
        Log::info('Pagamento de mudança de plano confirmado via fila', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'amount' => $payment->amount,
            'metadata' => $payment->metadata
        ]);
        
        try {
            $company = \App\Models\Company::find($payment->company_id);
            $newPlan = \App\Models\Plan::find($payment->plan_id);
            
            if (!$company || !$newPlan) {
                Log::error('Empresa ou plano não encontrado para mudança de plano', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id,
                    'plan_id' => $payment->plan_id
                ]);
                return;
            }
            
            // Buscar assinatura anual ativa
            $activeSubscription = $company->activeSubscription();
            
            if (!$activeSubscription || !$activeSubscription->isAnnual()) {
                Log::error('Assinatura anual ativa não encontrada para mudança de plano', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id
                ]);
                return;
            }
            
            // Cancelar assinatura anual atual
            if ($activeSubscription->asaas_subscription_id) {
                try {
                    $asaasService = app(\App\Services\AsaasService::class);
                    $asaasService->cancelSubscription($activeSubscription->asaas_subscription_id);
                } catch (\Exception $e) {
                    Log::warning('Erro ao cancelar assinatura no Asaas via webhook', [
                        'subscription_id' => $activeSubscription->asaas_subscription_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Marcar assinatura atual como cancelada
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'end_date' => now(),
                'cancellation_fee_paid' => true
            ]);
            
            // Criar nova assinatura mensal
            $newSubscription = \App\Models\Subscription::create([
                'company_id' => $payment->company_id,
                'plan_id' => $newPlan->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'billing_cycle' => 'monthly',
                'amount' => $newPlan->monthly_price,
                'payment_id' => $payment->id
            ]);
            
            // Atualizar pagamento com a nova assinatura
            $payment->update([
                'subscription_id' => $newSubscription->id
            ]);
            
            Log::info('Mudança de plano processada com sucesso via webhook', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'old_subscription_id' => $activeSubscription->id,
                'new_subscription_id' => $newSubscription->id,
                'new_plan_id' => $newPlan->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar mudança de plano via webhook', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job ProcessAsaasWebhook falhou após todas as tentativas', [
            'payload' => $this->payload,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
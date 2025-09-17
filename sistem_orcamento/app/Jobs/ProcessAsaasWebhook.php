<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\UsageControl;
use App\Services\AsaasService;
use App\Services\PlanUpgradeService;
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
    public function handle(AsaasService $asaasService, PlanUpgradeService $planUpgradeService)
    {
        try {
            Log::info('Processando webhook Asaas via fila', [
                'job_id' => $this->job ? $this->job->getJobId() : 'direct_execution',
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
                $this->handlePaymentEvent($planUpgradeService);
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
    private function handlePaymentEvent(PlanUpgradeService $planUpgradeService)
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

        // Atualizar campos se o pagamento foi encontrado
        if ($payment) {
            // Atualizar asaas_subscription_id se veio no webhook e não está preenchido
            if (isset($paymentData['subscription']) && !$payment->asaas_subscription_id) {
                $payment->update(['asaas_subscription_id' => $paymentData['subscription']]);
                Log::info('Campo asaas_subscription_id atualizado via webhook', [
                    'payment_id' => $payment->id,
                    'asaas_subscription_id' => $paymentData['subscription']
                ]);
            }
            
            // Atualizar payment_id se não está preenchido (usar o próprio ID do payment)
            if (!$payment->payment_id) {
                $payment->update(['payment_id' => $payment->id]);
                Log::info('Campo payment_id atualizado via webhook', [
                    'payment_id' => $payment->id
                ]);
            }
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
                $this->handlePaymentApproved($payment, $paymentData, $planUpgradeService);
                break;
                
            case 'PAYMENT_OVERDUE':
                $this->handlePaymentOverdue($payment, $paymentData);
                break;
                
            case 'PAYMENT_DELETED':
            case 'PAYMENT_REFUNDED':
            case 'PAYMENT_CANCELLED':
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
    private function handlePaymentApproved(Payment $payment, array $paymentData, PlanUpgradeService $planUpgradeService)
    {
        // Atualizar status do pagamento
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        Log::info('DEBUG: Iniciando processamento de pagamento aprovado', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id,
            'billing_cycle' => $payment->billing_cycle,
            'amount' => $payment->amount,
            'current_type' => $payment->type
        ]);
        
        // Detectar automaticamente mudanças de plano se o tipo não estiver definido
        if (!$payment->type || $payment->type === 'subscription') {
            $detectedType = $this->detectPaymentType($payment);
            if ($detectedType && $detectedType !== $payment->type) {
                $payment->update(['type' => $detectedType]);
                Log::info('Tipo de pagamento detectado automaticamente', [
                    'payment_id' => $payment->id,
                    'detected_type' => $detectedType,
                    'original_type' => $payment->type
                ]);
            }
        }
        
        Log::info('DEBUG: Tipo de pagamento final', [
            'payment_id' => $payment->id,
            'payment_type' => $payment->type
        ]);
        
        // Verificar tipo de pagamento
        if ($payment->type === 'cancellation_fee') {
            $this->handleCancellationFeePayment($payment);
        } elseif ($payment->type === 'plan_change') {
            $this->handlePlanChangePayment($payment, $planUpgradeService);
        } elseif ($payment->type === 'plan_change_annual') {
            $this->handleAnnualPlanChangePayment($payment, $paymentData);
        } elseif ($payment->type === 'extra_budgets') {
            $this->handleExtraBudgetsPayment($payment, $planUpgradeService);
        } else {
            $this->handleSubscriptionPayment($payment, $paymentData);
        }
    }

    /**
     * Detectar automaticamente o tipo de pagamento baseado no contexto
     */
    private function detectPaymentType(Payment $payment): ?string
    {
        try {
            Log::info('DEBUG: ===== INICIANDO DETECÇÃO DE TIPO DE PAGAMENTO =====', [
                'payment_id' => $payment->id,
                'plan_id' => $payment->plan_id,
                'company_id' => $payment->company_id,
                'billing_cycle' => $payment->billing_cycle
            ]);
            
            // Se não tem plan_id, não é mudança de plano
            if (!$payment->plan_id) {
                Log::info('DEBUG: Sem plan_id, retornando null', [
                    'payment_id' => $payment->id
                ]);
                return null;
            }
            
            // Buscar empresa e assinatura ativa
            $company = \App\Models\Company::find($payment->company_id);
            if (!$company) {
                Log::error('DEBUG: Empresa não encontrada', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id
                ]);
                return null;
            }
            
            $activeSubscription = $company->activeSubscription();
            
            Log::info('DEBUG: Verificando assinatura ativa', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'has_active_subscription' => $activeSubscription ? true : false,
                'active_subscription_id' => $activeSubscription ? $activeSubscription->id : null,
                'active_plan_id' => $activeSubscription ? $activeSubscription->plan_id : null
            ]);
            
            if (!$activeSubscription) {
                // Se não há assinatura ativa, é uma nova assinatura
                Log::info('DEBUG: Sem assinatura ativa, detectado como subscription', [
                    'payment_id' => $payment->id
                ]);
                return 'subscription';
            }
            
            Log::info('DEBUG: ATENÇÃO - Assinatura ativa encontrada quando não deveria ter', [
                'payment_id' => $payment->id,
                'active_subscription_id' => $activeSubscription->id,
                'active_subscription_status' => $activeSubscription->status,
                'active_subscription_ends_at' => $activeSubscription->ends_at,
                'now' => now(),
                'comparison' => $activeSubscription->ends_at >= now() ? 'ends_at >= now (true)' : 'ends_at < now (false)'
            ]);
            
            // Se o plano do pagamento é diferente do plano da assinatura ativa, é mudança de plano
            if ($payment->plan_id != $activeSubscription->plan_id) {
                Log::info('DEBUG: Detectada mudança de plano', [
                    'payment_id' => $payment->id,
                    'old_plan_id' => $activeSubscription->plan_id,
                    'new_plan_id' => $payment->plan_id,
                    'billing_cycle' => $payment->billing_cycle
                ]);
                
                // Verificar se é anual ou mensal baseado no billing_cycle
                if ($payment->billing_cycle === 'annual') {
                    return 'plan_change_annual';
                } else {
                    return 'plan_change';
                }
            }
            
            // Se é o mesmo plano, pode ser renovação ou orçamentos extras
            // Verificar se tem metadata de orçamentos extras
            $metadata = is_string($payment->metadata) ? json_decode($payment->metadata, true) : $payment->metadata;
            if ($metadata && isset($metadata['extra_budgets_quantity'])) {
                Log::info('DEBUG: Detectado como extra_budgets', [
                    'payment_id' => $payment->id,
                    'metadata' => $metadata
                ]);
                return 'extra_budgets';
            }
            
            // CORREÇÃO: Se já tem assinatura ativa, mesmo sendo o mesmo plano,
            // deve ser tratado como mudança de plano para herdar orçamentos corretamente
            Log::info('DEBUG: Mesmo plano com assinatura ativa - tratando como plan_change', [
                'payment_id' => $payment->id,
                'same_plan' => true,
                'active_subscription_id' => $activeSubscription->id
            ]);
            
            // Verificar se é anual ou mensal baseado no billing_cycle
            if ($payment->billing_cycle === 'annual') {
                return 'plan_change_annual';
            } else {
                return 'plan_change';
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao detectar tipo de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Processar pagamento de orçamentos extras
     */
    private function handleExtraBudgetsPayment(Payment $payment, PlanUpgradeService $planUpgradeService)
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
        
        // Obter quantidade de orçamentos extras do metadata do pagamento
        $asaasResponse = is_string($payment->asaas_response) ? json_decode($payment->asaas_response, true) : $payment->asaas_response;
        $quantity = $asaasResponse['extra_budgets_quantity'] ?? ($subscription->plan->budget_limit ?? 10);
        
        // Usar o PlanUpgradeService para processar a compra
        $planUpgradeService->processExtraBudgetsPurchase($subscription, $quantity, $payment);
        
        Log::info('Orçamentos extras processados via PlanUpgradeService (fila)', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'subscription_id' => $subscription->id,
            'quantity' => $quantity,
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
        
        // Criar nova assinatura
        $plan = \App\Models\Plan::find($payment->plan_id);
        if (!$plan) {
            Log::error('Plano não encontrado para criar assinatura', [
                'payment_id' => $payment->id,
                'plan_id' => $payment->plan_id
            ]);
            return;
        }
        
        // Determinar datas baseadas no ciclo de cobrança
        $startDate = now();
        $endDate = $payment->billing_cycle === 'annual' ? $startDate->copy()->addYear() : $startDate->copy()->addMonth();
        $nextBillingDate = $payment->billing_cycle === 'annual' ? $endDate : $startDate->copy()->addMonth();
        $gracePeriodEndDate = $endDate->copy()->addDays(3); // 3 dias de período de graça
        
        // Converter billing_cycle para o formato correto da tabela subscriptions
        $billingCycleForSubscription = $payment->billing_cycle === 'annual' ? 'yearly' : $payment->billing_cycle;
        
        // Criar nova assinatura
        $newSubscription = \App\Models\Subscription::create([
            'company_id' => $payment->company_id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
            'grace_period_ends_at' => $gracePeriodEndDate,
            
            'next_billing_date' => $nextBillingDate,
            'billing_cycle' => $billingCycleForSubscription,
            'amount_paid' => $payment->amount,
            'asaas_subscription_id' => $payment->asaas_subscription_id,
            'payment_id' => $payment->id
        ]);
        
        // Atualizar o payment com o subscription_id
        $payment->update([
            'subscription_id' => $newSubscription->id
        ]);
        
        // Criar controle de uso para a nova assinatura
        // Para planos ilimitados (como Ouro), zerar todos os orçamentos herdados
        $inheritedBudgets = $plan->isUnlimited() ? 0 : 0; // Sempre 0 para novos pagamentos de assinatura
        $usageControl = \App\Models\UsageControl::getOrCreateForCurrentMonthWithReset(
            $payment->company_id,
            $newSubscription->id,
            $inheritedBudgets
        );
        
        Log::info('Nova assinatura criada e payment atualizado (fila)', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'new_subscription_id' => $newSubscription->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $payment->billing_cycle,
            'amount' => $payment->amount,
            'usage_control_created' => true,
            'inherited_budgets' => $inheritedBudgets
        ]);
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
        // Para eventos de cancelamento/exclusão, sempre definir status como 'cancelled'
        // independente do status que vem do Asaas
        $newStatus = 'cancelled';
        
        // Atualizar status e asaas_response com todo o payload do webhook
        $payment->updateStatus($newStatus, $this->payload);
        
        Log::info('Pagamento cancelado/estornado processado via webhook', [
            'payment_id' => $payment->id,
            'asaas_payment_id' => $payment->asaas_payment_id,
            'old_status' => $payment->getOriginal('status'),
            'new_status' => $newStatus,
            'asaas_status' => $paymentData['status'],
            'event' => $this->payload['event'] ?? 'unknown',
            'webhook_payload' => $this->payload
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
            
            // Buscar assinatura ativa para usar com PlanUpgradeService
            $activeSubscription = $company->activeSubscription();
            
            if (!$activeSubscription) {
                Log::error('Assinatura ativa não encontrada para mudança de plano anual', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id
                ]);
                return;
            }
            
            // Usar o PlanUpgradeService para processar o upgrade com herança de orçamentos
            $planUpgradeService = new \App\Services\PlanUpgradeService();
            $newSubscription = $planUpgradeService->processUpgrade($activeSubscription, $newPlan, $payment);
            
            Log::info('Mudança de plano anual processada via PlanUpgradeService (webhook)', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'old_subscription_id' => $activeSubscription->id,
                'new_subscription_id' => $newSubscription->id,
                'new_plan_id' => $newPlan->id,
                'billing_cycle' => $payment->billing_cycle
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar mudança de plano anual via webhook', [
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
    private function handlePlanChangePayment(Payment $payment, PlanUpgradeService $planUpgradeService)
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
            
            // Buscar assinatura ativa
            $activeSubscription = $company->activeSubscription();
            
            if (!$activeSubscription) {
                Log::error('Assinatura ativa não encontrada para mudança de plano', [
                    'payment_id' => $payment->id,
                    'company_id' => $payment->company_id
                ]);
                return;
            }
            
            // Usar o PlanUpgradeService para processar o upgrade
            $newSubscription = $planUpgradeService->processUpgrade($activeSubscription, $newPlan, $payment);
            
            Log::info('Mudança de plano processada via PlanUpgradeService (webhook)', [
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
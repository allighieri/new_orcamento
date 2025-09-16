<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\UsageControl;
use App\Services\AsaasService;
use App\Services\PlanUpgradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    protected $asaasService;
    protected $planUpgradeService;

    public function __construct(AsaasService $asaasService, PlanUpgradeService $planUpgradeService)
    {
        $this->asaasService = $asaasService;
        $this->planUpgradeService = $planUpgradeService;
    }

    /**
     * Exibe a página de seleção de planos
     */
    public function index()
    {
        $company = Auth::user()->company;
        $plans = Plan::where('active', true)->get();
        $currentSubscription = $company->subscriptions()->where('status', 'active')->first();
        
        return view('subscriptions.index', compact('plans', 'currentSubscription'));
    }

    /**
     * Cria uma nova assinatura
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:pix,credit_card,bank_slip'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $company = Auth::user()->company;
            $plan = Plan::findOrFail($request->plan_id);

            // Verificar se já tem assinatura ativa
            $activeSubscription = $company->subscriptions()->where('status', 'active')->first();
            if ($activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa já possui uma assinatura ativa'
                ], 400);
            }

            // Criar assinatura
            $startDate = now();
            $endDate = $request->billing_cycle === 'yearly' ? $startDate->copy()->addYear() : $startDate->copy()->addMonth();
            $gracePeriodEndDate = $endDate->copy()->addDays(3); // 3 dias de período de graça
            
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $request->billing_cycle,
                'status' => 'pending',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'starts_at' => $startDate,
                'ends_at' => $endDate,
                'grace_period_ends_at' => $gracePeriodEndDate,
                'remaining_budgets' => $plan->budget_limit,
                'auto_renew' => true
            ]);

            // Calcular valor do pagamento
            $amount = $request->billing_cycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price;

            // Criar pagamento via Asaas
            $paymentData = $this->createAsaasPayment($subscription, $amount, $request->payment_method);

            if (!$paymentData['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar pagamento: ' . $paymentData['message']
                ], 500);
            }

            // Salvar dados do pagamento
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'asaas_payment_id' => $paymentData['payment_id'],
                'asaas_subscription_id' => null, // Será preenchido se for assinatura recorrente
                'payment_id' => null, // Será preenchido pelo webhook
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'due_date' => now()->addDays(3),
                'pix_qr_code' => $paymentData['pix_qr_code'] ?? null,
                'pix_copy_paste' => $paymentData['pix_copy_paste'] ?? null,
                'bank_slip_url' => $paymentData['bank_slip_url'] ?? null,
                'asaas_response' => $paymentData['response']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'payment_data' => [
                    'method' => $request->payment_method,
                    'amount' => $amount,
                    'pix_qr_code' => $payment->pix_qr_code,
                    'pix_copy_paste' => $payment->pix_copy_paste,
                    'bank_slip_url' => $payment->bank_slip_url
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar assinatura', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Ativa uma assinatura após pagamento confirmado
     */
    public function activate(Subscription $subscription)
    {
        try {
            DB::beginTransaction();

            // Ativar assinatura
            $subscription->activate();

            // Criar controle de uso para o mês atual
            UsageControl::getOrCreateForCurrentMonth(
                $subscription->company_id,
                $subscription->id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assinatura ativada com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao ativar assinatura', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao ativar assinatura'
            ], 500);
        }
    }

    /**
     * Cancela uma assinatura
     */
    public function cancel(Subscription $subscription)
    {
        try {
            $subscription->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Assinatura cancelada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar assinatura', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar assinatura'
            ], 500);
        }
    }

    /**
     * Compra orçamentos extras
     */
    public function buyExtraBudgets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:100',
            'payment_method' => 'required|in:pix,credit_card,bank_slip'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $company = Auth::user()->company;
            $subscription = $company->subscriptions()->where('status', 'active')->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma assinatura ativa encontrada'
                ], 400);
            }

            // Calcular valor (R$ 5 por orçamento extra)
            $pricePerBudget = 5.00;
            $amount = $request->quantity * $pricePerBudget;

            // Criar pagamento via Asaas
            $paymentData = $this->createAsaasPayment($subscription, $amount, $request->payment_method, 'extra_budgets');

            if (!$paymentData['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar pagamento: ' . $paymentData['message']
                ], 500);
            }

            // Salvar dados do pagamento
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'asaas_payment_id' => $paymentData['payment_id'],
                'asaas_subscription_id' => null, // Será preenchido se for assinatura recorrente
                'payment_id' => null, // Será preenchido pelo webhook
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'due_date' => now()->addDays(3),
                'pix_qr_code' => $paymentData['pix_qr_code'] ?? null,
                'pix_copy_paste' => $paymentData['pix_copy_paste'] ?? null,
                'bank_slip_url' => $paymentData['bank_slip_url'] ?? null,
                'asaas_response' => array_merge($paymentData['response'], [
                    'extra_budgets_quantity' => $request->quantity
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'quantity' => $request->quantity,
                'amount' => $amount,
                'payment_data' => [
                    'method' => $request->payment_method,
                    'pix_qr_code' => $payment->pix_qr_code,
                    'pix_copy_paste' => $payment->pix_copy_paste,
                    'bank_slip_url' => $payment->bank_slip_url
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao comprar orçamentos extras', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Cria pagamento no Asaas
     */
    private function createAsaasPayment(Subscription $subscription, float $amount, string $paymentMethod, string $type = 'subscription')
    {
        try {
            $company = $subscription->company;
            
            // Dados do cliente
            $customerData = [
                'name' => $company->name,
                'cpfCnpj' => $company->cnpj,
                'email' => $company->email ?? Auth::user()->email,
                'phone' => $company->phone
            ];

            // Criar ou obter cliente no Asaas
            $customer = $this->asaasService->createOrUpdateCustomer($customerData);
            
            if (!$customer['success']) {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar cliente: ' . $customer['message']
                ];
            }

            // Dados do pagamento
            $paymentData = [
                'customer' => $customer['customer_id'],
                'billingType' => $this->mapPaymentMethod($paymentMethod),
                'value' => $amount,
                'dueDate' => now()->addDays(3)->format('Y-m-d'),
                'description' => $type === 'extra_budgets' 
                    ? 'Compra de orçamentos extras' 
                    : 'Assinatura do plano ' . $subscription->plan->name
            ];

            // Criar pagamento no Asaas
            $payment = $this->asaasService->createPayment($paymentData);

            if (!$payment['success']) {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar pagamento: ' . $payment['message']
                ];
            }

            return [
                'success' => true,
                'payment_id' => $payment['payment_id'],
                'pix_qr_code' => $payment['pix_qr_code'] ?? null,
                'pix_copy_paste' => $payment['pix_copy_paste'] ?? null,
                'bank_slip_url' => $payment['bank_slip_url'] ?? null,
                'response' => $payment['response']
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento Asaas', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno ao processar pagamento'
            ];
        }
    }

    /**
     * Mapeia método de pagamento para formato Asaas
     */
    private function mapPaymentMethod(string $method): string
    {
        return match($method) {
            'pix' => 'PIX',
            'credit_card' => 'CREDIT_CARD',
            'bank_slip' => 'BOLETO',
            default => 'PIX'
        };
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Payment;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    /**
     * Exibir página de seleção de planos
     */
    public function selectPlan()
    {
        $company = Auth::user()->company;
        
        // Verificar se já tem uma assinatura ativa
        $activeSubscription = $company->activeSubscription();
        
        if ($activeSubscription) {
            return redirect()->route('payments.index')
                           ->with('info', 'Você já possui um plano ativo.');
        }
        
        $plans = Plan::all();
        $currentSubscription = null; // Definir como null quando não há assinatura
        
        return view('payments.select-plan', compact('plans', 'company', 'currentSubscription'));
    }

    /**
     * Exibir página de seleção de planos para troca de plano
     */
    public function changePlan()
    {
        $plans = Plan::all();
        $company = Auth::user()->company;
        $currentSubscription = $company->activeSubscription();
        
        return view('payments.select-plan', compact('plans', 'company', 'currentSubscription'));
    }

    /**
     * Exibir página de checkout
     */
    public function checkout(Request $request, Plan $plan)
    {
        $company = Auth::user()->company;
        $period = $request->get('period', 'yearly'); // Default para anual
        
        // Determinar o valor baseado no período
        $amount = $period === 'monthly' ? $plan->monthly_price : $plan->annual_price;
        
        return view('payments.checkout', compact('plan', 'company', 'period', 'amount'));
    }

    /**
     * Processar pagamento PIX
     */
    public function processPixPayment(Request $request, Plan $plan)
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string'
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
            
            // Buscar ou criar cliente no Asaas
            $customers = $this->asaasService->findCustomerByCpfCnpj($request->cpf_cnpj);
            
            if (empty($customers)) {
                $customerData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'cpfCnpj' => $request->cpf_cnpj
                ];
                $customer = $this->asaasService->createCustomer($customerData);
            } else {
                $customer = $customers[0];
            }

            // Determinar preço baseado no ciclo de cobrança
            $billingCycle = session('selected_billing_cycle', 'monthly');
            $price = $billingCycle === 'annual' ? $plan->annual_price : $plan->monthly_price;
            $cycleText = $billingCycle === 'annual' ? 'Anual' : 'Mensal';
            
            // Criar cobrança PIX no Asaas
            $paymentData = [
                'customer' => $customer['id'],
                'value' => $price,
                'dueDate' => now()->addDays(1)->format('Y-m-d'),
                'description' => "Assinatura {$cycleText} do plano {$plan->name} - {$company->name}"
            ];

            $asaasPayment = $this->asaasService->createPixCharge($paymentData);

            // Salvar pagamento no banco
            $payment = Payment::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'asaas_payment_id' => $asaasPayment['id'],
                'asaas_customer_id' => $customer['id'],
                'amount' => $price,
                'billing_type' => 'PIX',
                'status' => 'PENDING',
                'due_date' => $asaasPayment['dueDate'],
                'description' => $paymentData['description'],
                'billing_cycle' => $billingCycle
            ]);

            // Gerar QR Code PIX dinâmico usando o ID da cobrança
            Log::info('Tentando gerar QR Code PIX dinâmico', ['payment_id' => $asaasPayment['id']]);
            $pixData = $this->asaasService->getPixQrCode($asaasPayment['id']);
            Log::info('Resposta do QR Code PIX dinâmico', ['pixData' => $pixData]);
            
            // A API do Asaas retorna 'encodedImage' para o QR Code e 'payload' para copia e cola
            $qrCodeImage = $pixData['encodedImage'] ?? null;
            $payload = $pixData['payload'] ?? null;
            
            // Se o payload vier null, gerar um PIX copia e cola informativo
            if (empty($payload) && !empty($qrCodeImage)) {
                $payload = "PIX disponível via QR Code - Valor: R$ " . number_format($price, 2, ',', '.') . " - Vencimento: " . date('d/m/Y', strtotime($asaasPayment['dueDate']));
                Log::info('Payload PIX gerado como fallback', ['generated_payload' => $payload]);
            }
            
            $payment->update([
                'payment_data' => [
                    'pix_qr_code' => $qrCodeImage,
                    'pix_copy_paste' => $payload
                ]
            ]);
            
            Log::info('QR Code salvo no pagamento', [
                'payment_id' => $payment->id,
                'has_qr_code' => !empty($qrCodeImage),
                'has_payload' => !empty($payload),
                'payload_source' => empty($pixData['payload']) ? 'generated' : 'api'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'pix_qr_code' => $qrCodeImage,
                'pix_copy_paste' => $payload,
                'due_date' => $asaasPayment['dueDate']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pagamento PIX', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id,
                'company_id' => Auth::user()->company->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Processar pagamento com cartão de crédito
     */
    public function processCreditCardPayment(Request $request, Plan $plan)
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'card_number' => 'required|string',
            'card_holder_name' => 'required|string',
            'card_expiry_month' => 'required|string|size:2',
            'card_expiry_year' => 'required|string|size:4',
            'card_ccv' => 'required|string|size:3'
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
            
            // Buscar ou criar cliente no Asaas
            $customers = $this->asaasService->findCustomerByCpfCnpj($request->cpf_cnpj);
            
            if (empty($customers)) {
                $customerData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'cpfCnpj' => $request->cpf_cnpj
                ];
                $customer = $this->asaasService->createCustomer($customerData);
            } else {
                $customer = $customers[0];
            }

            // Determinar preço baseado no ciclo de cobrança
            $billingCycle = session('selected_billing_cycle', 'monthly');
            $price = $billingCycle === 'annual' ? $plan->annual_price : $plan->monthly_price;
            $cycleText = $billingCycle === 'annual' ? 'Anual' : 'Mensal';
            
            // Criar cobrança com cartão no Asaas
            $paymentData = [
                'customer' => $customer['id'],
                'value' => $price,
                'dueDate' => now()->format('Y-m-d'),
                'description' => "Assinatura {$cycleText} do plano {$plan->name} - {$company->name}",
                'creditCard' => [
                    'holderName' => $request->card_holder_name,
                    'number' => $request->card_number,
                    'expiryMonth' => $request->card_expiry_month,
                    'expiryYear' => $request->card_expiry_year,
                    'ccv' => $request->card_ccv
                ],
                'creditCardHolderInfo' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'cpfCnpj' => $request->cpf_cnpj,
                    'phone' => $request->phone
                ]
            ];

            $asaasPayment = $this->asaasService->createCreditCardCharge($paymentData);

            // Salvar pagamento no banco
            $payment = Payment::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'asaas_payment_id' => $asaasPayment['id'],
                'asaas_customer_id' => $customer['id'],
                'amount' => $price,
                'billing_type' => 'CREDIT_CARD',
                'status' => $asaasPayment['status'] ?? 'PENDING',
                'due_date' => $asaasPayment['dueDate'],
                'description' => $paymentData['description'],
                'billing_cycle' => $billingCycle
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $asaasPayment['status'] ?? 'PENDING',
                'message' => 'Pagamento processado com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pagamento com cartão', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id,
                'company_id' => Auth::user()->company->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento. Verifique os dados do cartão e tente novamente.'
            ], 500);
        }
    }

    /**
     * Verificar status do pagamento
     */
    public function checkPaymentStatus(Payment $payment)
    {
        try {
            // Verificar se o pagamento pertence à empresa do usuário
            if ($payment->company_id !== Auth::user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado.'
                ], 403);
            }

            // Recarregar o pagamento do banco para pegar status mais atual
            $payment->refresh();
            $originalStatus = $payment->status;
            
            // Verificar se o webhook já sinalizou aprovação
            $webhookApproved = \Illuminate\Support\Facades\Cache::get("payment_approved_{$payment->id}", false);
            
            // Log para debug
            if ($webhookApproved) {
                \Log::info('Cache de aprovação encontrado', [
                    'payment_id' => $payment->id,
                    'cache_data' => $webhookApproved
                ]);
            }
            
            // Se o pagamento já está pago no banco de dados, retornar imediatamente
            if ($payment->isPaid()) {
                // Limpar o cache se existir
                if ($webhookApproved) {
                    \Illuminate\Support\Facades\Cache::forget("payment_approved_{$payment->id}");
                }
                
                return response()->json([
                    'success' => true,
                    'status' => $payment->status,
                    'status_text' => $this->getStatusText($payment->status),
                    'status_changed' => true,
                    'is_paid' => true,
                    'should_redirect' => true,
                    'webhook_processed' => true
                ]);
            }
            
            // Se o webhook sinalizou aprovação mas o status ainda não foi atualizado
            if ($webhookApproved) {
                // Recarregar novamente para garantir que temos o status mais atual
                $payment->refresh();
                
                // Limpar o cache após usar
                \Illuminate\Support\Facades\Cache::forget("payment_approved_{$payment->id}");
                
                return response()->json([
                    'success' => true,
                    'status' => $payment->status,
                    'status_text' => $this->getStatusText($payment->status),
                    'status_changed' => true,
                    'is_paid' => $payment->isPaid(),
                    'should_redirect' => $payment->isPaid(),
                    'webhook_approved' => true
                ]);
            }
            
            // Buscar status atualizado no Asaas se houver ID (fallback)
            if ($payment->asaas_payment_id) {
                try {
                    $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
                    $payment->updateStatus($asaasPayment['status']);
                } catch (\Exception $e) {
                    // Log do erro mas não falha a verificação
                    \Log::warning('Erro ao consultar Asaas API', [
                        'error' => $e->getMessage(),
                        'payment_id' => $payment->id
                    ]);
                }
            }
            
            $statusChanged = $originalStatus !== $payment->status;
            
            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'status_text' => $this->getStatusText($payment->status),
                'status_changed' => $statusChanged,
                'is_paid' => $payment->isPaid(),
                'should_redirect' => $payment->isPaid(),
                'api_checked' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar status do pagamento', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível verificar o status do pagamento'
            ], 500);
        }
    }

    /**
     * Obter texto do status do pagamento
     */
    private function getStatusText($status)
    {
        return match($status) {
            'PENDING' => 'Aguardando Pagamento',
            'RECEIVED' => 'Pago',
            'CONFIRMED' => 'Confirmado',
            'OVERDUE' => 'Vencido',
            'CANCELLED' => 'Cancelado',
            default => ucfirst($status)
        };
    }

    /**
     * Listar pagamentos da empresa
     */
    public function index()
    {
        $company = Auth::user()->company;
        $payments = Payment::where('company_id', $company->id)
            ->with(['plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        // Buscar assinatura atual da empresa
        $currentSubscription = $company->subscription;
            
        return view('payments.index', compact('payments', 'currentSubscription'));
    }

    /**
     * Exibir detalhes do pagamento
     */
    public function details(Payment $payment)
    {
        // Verificar se o pagamento pertence à empresa do usuário
        if ($payment->company_id !== Auth::user()->company_id) {
            abort(403, 'Acesso negado.');
        }

        try {
            // Buscar informações atualizadas do Asaas se houver ID
            $asaasPayment = null;
            if ($payment->asaas_payment_id) {
                $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
            }

            return view('payments.details', compact('payment', 'asaasPayment'));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar detalhes do pagamento', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            
            return view('payments.details', compact('payment'))->with('error', 'Não foi possível carregar informações atualizadas do pagamento.');
        }
    }

    /**
     * Exibir página de pagamento PIX
     */
    public function pixPayment(Payment $payment)
    {
        // Verificar se o pagamento pertence à empresa do usuário
        if ($payment->company_id !== Auth::user()->company_id) {
            abort(403, 'Acesso negado.');
        }

        // Verificar se é um pagamento PIX
        if ($payment->billing_type !== 'PIX') {
            return redirect()->route('payments.index')
                ->with('error', 'Este pagamento não é do tipo PIX.');
        }

        try {
            // Buscar informações do QR Code do Asaas
            $qrCodeData = null;
            if ($payment->asaas_payment_id) {
                $qrCodeData = $this->asaasService->getPixQrCode($payment->asaas_payment_id);
            }

            return view('payments.pix-payment', compact('payment', 'qrCodeData'));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar QR Code PIX', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            
            return view('payments.pix-payment', compact('payment'))
                ->with('error', 'Não foi possível carregar o QR Code PIX.');
        }
    }
}

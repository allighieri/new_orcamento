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
        
        // Buscar controle de uso atual se houver assinatura ativa
        $usageControl = null;
        if ($currentSubscription) {
            $usageControl = \App\Models\UsageControl::getOrCreateForCurrentMonth(
                $company->id,
                $currentSubscription->id,
                $currentSubscription->plan->budget_limit ?? 0
            );
        }
        
        return view('payments.select-plan', compact('plans', 'company', 'currentSubscription', 'usageControl'));
    }

    /**
     * Exibir página de checkout
     */
    public function checkout(Request $request, Plan $plan)
    {
        Log::info('=== CHECKOUT DEBUG ===', [
            'user_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'N/A',
            'company_id' => Auth::user()->company_id ?? 'N/A',
            'company_name' => Auth::user()->company->name ?? 'N/A',
            'plan_id' => $plan->id,
            'request_data' => $request->all()
        ]);
        
        $company = Auth::user()->company;
        $type = $request->get('type'); // Verificar se é compra de orçamentos extras
        
        if ($type === 'extra_budgets') {
            // Para orçamentos extras, usar o valor mensal do plano atual
            $activeSubscription = $company->activeSubscription();
            if (!$activeSubscription) {
                return redirect()->route('payments.select-plan')
                               ->with('error', 'Você precisa ter um plano ativo para comprar orçamentos extras.');
            }
            
            $amount = $activeSubscription->plan->monthly_price;
            $period = 'extra_budgets';
            
            return view('payments.checkout', compact('plan', 'company', 'period', 'amount', 'type'));
        }
        
        $period = $request->get('period', 'yearly'); // Default para anual
        
        // Determinar o valor baseado no período
        $amount = $period === 'monthly' ? $plan->monthly_price : $plan->yearly_price;
        
        return view('payments.checkout', compact('plan', 'company', 'period', 'amount'));
    }

    /**
     * Processar pagamento PIX
     */
    public function processPixPayment(Request $request, Plan $plan)
    {
        Log::info('=== INÍCIO PROCESSAMENTO PIX ===', [
            'timestamp' => now()->toDateTimeString(),
            'request_data' => $request->all(),
            'plan_id' => $plan->id,
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id ?? 'N/A',
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_headers' => $request->headers->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            Log::warning('Validação falhou no PIX', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Aumentar o tempo limite de execução para operações de pagamento
            set_time_limit(120); // 2 minutos
            
            DB::beginTransaction();
            
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }
            
            $company = $user->company;
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa não encontrada'
                ], 400);
            }
            $type = $request->get('type'); // Verificar se é compra de orçamentos extras
            
            Log::info('Dados validados, iniciando busca de cliente', [
                'cpf_cnpj' => $request->cpf_cnpj,
                'company_id' => $company->id
            ]);
            
            // Buscar ou criar cliente no Asaas
            $customers = $this->asaasService->findCustomerByCpfCnpj($request->cpf_cnpj);
            
            Log::info('Resultado da busca de cliente', [
                'found_customers' => count($customers),
                'cpf_cnpj' => $request->cpf_cnpj
            ]);
            
            if (empty($customers)) {
                $customerData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'cpfCnpj' => $request->cpf_cnpj
                ];
                Log::info('Criando novo cliente no Asaas', $customerData);
                $customer = $this->asaasService->createCustomer($customerData);
                Log::info('Cliente criado com sucesso', ['customer_id' => $customer['id'] ?? 'N/A']);
            } else {
                $customer = $customers[0];
                Log::info('Usando cliente existente', ['customer_id' => $customer['id'] ?? 'N/A']);
            }

            if ($type === 'extra_budgets') {
                // Para orçamentos extras
                $activeSubscription = $company->activeSubscription();
                if (!$activeSubscription) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você precisa ter um plano ativo para comprar orçamentos extras.'
                    ], 400);
                }
                
                $price = $activeSubscription->plan->monthly_price;
                $description = "Compra de {$activeSubscription->plan->budget_limit} orçamentos extras limitados ao período do seu plano - " .
                              ($company->fantasy_name ?? $company->corporate_name);
                $billingCycle = 'one_time';
            } else {
                // Para assinatura de plano - usar período da URL
                $period = $request->get('period', 'yearly');
                $billingCycle = $period === 'monthly' ? 'monthly' : 'annual';
                // Para planos anuais, usar o valor anual
                $price = $billingCycle === 'annual' ? $plan->yearly_price : $plan->monthly_price;
                Log::info('Valores do plano carregado', [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'monthly_price' => $plan->monthly_price,
                    'yearly_price' => $plan->yearly_price,
                    'billing_cycle' => $billingCycle,
                    'calculated_price' => $price
                ]);
                $cycleText = $billingCycle === 'annual' ? 'Anual' : 'Mensal';
                $description = "Assinatura {$cycleText} do plano {$plan->name} - " .
                              ($company->fantasy_name ?? $company->corporate_name);
                              
                // Verificar se já tem assinatura ativa e se pode fazer upgrade/downgrade
                $activeSubscription = $company->activeSubscription();
                if ($activeSubscription) {
                    // Se tem assinatura anual ativa, não pode fazer downgrade para mensal
                    if ($activeSubscription->isYearly() && $billingCycle === 'monthly') {
                        if (!$activeSubscription->canDowngradeToMonthly()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Não é possível fazer downgrade para plano mensal. Você deve aguardar o término do período de 12 meses ou pagar a taxa de cancelamento de R$ ' . number_format($activeSubscription->getCancellationFee(), 2, ',', '.') . '.'
                            ], 400);
                        }
                    }
                    
                    // Se tem assinatura anual ativa e quer trocar para outro plano anual
                    if ($activeSubscription->isYearly() && $billingCycle === 'annual' && $activeSubscription->plan_id !== $plan->id) {
                        // Marcar que é uma mudança de plano (será processado após confirmação do pagamento)
                        $isPlanChange = true;
                        $oldSubscriptionId = $activeSubscription->id;
                        
                        // Não cancelar ainda - apenas continuar com o processo de pagamento
                        // O cancelamento será feito após confirmação do pagamento
                    }
                }
            }
            
            // Para planos anuais, criar pagamento único de 12 meses
            if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                // Para planos anuais, criar cobrança única do valor total de 12 meses
                // Valor total: R$ 45,00 * 12 = R$ 540,00
                
                $annualTotalPrice = $plan->yearly_price; // Valor anual
                Log::info('Calculando valor anual', [
                    'plan_yearly_price' => $plan->yearly_price,
                    'annual_total_price' => $annualTotalPrice
                ]);
                
                $paymentData = [
                    'customer' => $customer['id'],
                    'value' => $annualTotalPrice,
                    'dueDate' => now()->addDays(1)->format('Y-m-d'),
                    'description' => $description . ' - Pagamento único de 12 meses'
                ];
                
                $asaasPayment = $this->asaasService->createPixCharge($paymentData);
                $asaasSubscription = null; // Não há assinatura recorrente
            } else {
                // Criar cobrança PIX normal
                $paymentData = [
                    'customer' => $customer['id'],
                    'value' => $price,
                    'dueDate' => now()->addDays(1)->format('Y-m-d'),
                    'description' => $description
                ];
                
                $asaasPayment = $this->asaasService->createPixCharge($paymentData);
                $asaasSubscription = null;
            }

            // Determinar tipo de pagamento
            $paymentType = 'subscription';
            if (isset($activeSubscription) && $activeSubscription && $activeSubscription->isYearly() && $billingCycle === 'annual' && $activeSubscription->plan_id !== $plan->id) {
                $paymentType = 'plan_change_annual';
            }
            
            // Definir variáveis para evitar erros
            $isPlanChange = false;
            $oldSubscriptionId = null;
            
            // Salvar pagamento no banco
            if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                // Para planos anuais com pagamento único
                $annualTotalPrice = $plan->yearly_price; // Valor anual
                $paymentCreateData = [
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => isset($activeSubscription) ? $activeSubscription->id : null,
                    'asaas_payment_id' => $asaasPayment['id'], // ID da cobrança única
                    'asaas_customer_id' => $customer['id'],
                    'asaas_subscription_id' => $asaasSubscription['id'] ?? null,
                    'payment_id' => null, // Será preenchido após criar a subscription
                    'amount' => $annualTotalPrice, // Valor total de 12 meses
                    'billing_type' => 'PIX',
                    'billing_cycle' => $billingCycle,
                    'type' => $paymentType,
                    'status' => 'PENDING',
                    'due_date' => $asaasPayment['dueDate'],
                    'description' => $description . ' - Pagamento único de 12 meses'
                ];
                
                // Metadados para mudança de plano serão gerenciados externamente
            } else {
                // Para outros tipos de pagamento
                $actualAmount = $price;
                $paymentCreateData = [
                    'company_id' => $company->id,
                    'plan_id' => $type === 'extra_budgets' ? null : $plan->id,
                    'subscription_id' => isset($activeSubscription) ? $activeSubscription->id : null,
                    'asaas_payment_id' => $asaasPayment['id'],
                    'asaas_customer_id' => $customer['id'],
                    'asaas_subscription_id' => $asaasSubscription['id'] ?? null,
                    'payment_id' => null, // Será preenchido após criar a subscription
                    'amount' => $actualAmount,
                    'billing_type' => 'PIX',
                    'billing_cycle' => $billingCycle,
                    'type' => $type === 'extra_budgets' ? 'extra_budgets' : 'subscription',
                    'status' => 'PENDING',
                    'due_date' => $asaasPayment['dueDate'],
                    'description' => $description
                ];
            }
            
            // Campos específicos para orçamentos extras
            if ($type === 'extra_budgets') {
                $paymentCreateData['extra_budgets_quantity'] = $activeSubscription->plan->budget_limit;
            }
            
            $payment = Payment::create($paymentCreateData);

            // Gerar QR Code PIX dinâmico
            if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                // Para planos anuais, usar diretamente a cobrança única criada
                Log::info('Cobrança única anual criada', ['payment_id' => $asaasPayment['id']]);
                
                // Gerar QR Code PIX da cobrança única
                Log::info('Tentando gerar QR Code PIX da cobrança única anual', ['payment_id' => $asaasPayment['id']]);
                $pixData = $this->asaasService->getPixQrCode($asaasPayment['id']);
                Log::info('Resposta do QR Code PIX dinâmico', ['pixData' => $pixData]);
            } else {
                // Para cobranças únicas
                Log::info('Tentando gerar QR Code PIX dinâmico', ['payment_id' => $asaasPayment['id']]);
                $pixData = $this->asaasService->getPixQrCode($asaasPayment['id']);
                Log::info('Resposta do QR Code PIX dinâmico', ['pixData' => $pixData]);
            }
            
            // A API do Asaas retorna 'encodedImage' para o QR Code e 'payload' para copia e cola
            $qrCodeImage = $pixData['encodedImage'] ?? null;
            $payload = $pixData['payload'] ?? null;
            
            // Se o payload vier null, gerar um PIX copia e cola informativo
            if (empty($payload) && !empty($qrCodeImage)) {
                // Definir valor correto baseado no tipo de pagamento
                if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                    $displayAmount = $plan->yearly_price; // Valor anual
                    $dueDate = $asaasPayment['dueDate'];
                } else {
                    $displayAmount = $price;
                    $dueDate = $asaasPayment['dueDate'];
                }
                
                $payload = "PIX disponível via QR Code - Valor: R$ " . number_format($displayAmount, 2, ',', '.') . " - Vencimento: " . date('d/m/Y', strtotime($dueDate));
                Log::info('Payload PIX gerado como fallback', ['generated_payload' => $payload]);
            }
            
            // QR Code PIX será gerenciado externamente via Asaas
            
            Log::info('QR Code salvo no pagamento', [
                'payment_id' => $payment->id,
                'has_qr_code' => !empty($qrCodeImage),
                'has_payload' => !empty($payload),
                'payload_source' => empty($pixData['payload']) ? 'generated' : 'api'
            ]);

            DB::commit();

            // Definir data de vencimento correta baseada no tipo de pagamento
            if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                $responseDueDate = $asaasPayment['dueDate'];
            } else {
                $responseDueDate = $asaasPayment['dueDate'];
            }
            
            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'pix_qr_code' => $qrCodeImage,
                'pix_copy_paste' => $payload,
                'due_date' => $responseDueDate
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pagamento PIX', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id,
                'company_id' => Auth::user() && Auth::user()->company ? Auth::user()->company->id : 'N/A'
            ]);

            // Se for erro específico da API do Asaas, retornar a mensagem específica
            if (strpos($e->getMessage(), 'API do Asaas está temporariamente indisponível') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 503); // Service Unavailable
            }

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
            'postal_code' => 'required|string',
            'address' => 'required|string|max:500',
            'address_number' => 'required|string|max:10',
            'district' => 'required|string|max:255',
            'city' => 'required|string',
            'state' => 'required|string|size:2',
            'card_number' => 'required|string',
            'card_holder_name' => 'required|string',
            'card_expiry_month' => 'required|string|size:2',
            'card_expiry_year' => 'required|string|size:4',
            'card_cvv' => 'required|string|size:3'
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
            $type = $request->get('type'); // Verificar se é compra de orçamentos extras
            
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

            if ($type === 'extra_budgets') {
                // Para orçamentos extras
                $activeSubscription = $company->activeSubscription();
                if (!$activeSubscription) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você precisa ter um plano ativo para comprar orçamentos extras.'
                    ], 400);
                }
                
                $price = $activeSubscription->plan->monthly_price;
                $description = "Compra de {$activeSubscription->plan->budget_limit} orçamentos extras limitados ao período do seu plano - " .
                    ($company->fantasy_name ?? $company->corporate_name);
                $billingCycle = 'one_time';
            } else {
                // Para assinatura de plano - usar período da URL
                $period = $request->get('period', 'yearly');
                $billingCycle = $period === 'monthly' ? 'monthly' : 'annual';
                $cycleText = $billingCycle === 'annual' ? 'Anual' : 'Mensal';
                $description = "Assinatura {$cycleText} do plano {$plan->name} - " .
                    ($company->fantasy_name ?? $company->corporate_name);
                
                // Verificar se já tem assinatura ativa e aplicar taxa de cancelamento se necessário
                $activeSubscription = $company->activeSubscription();
                if ($activeSubscription) {
                    // Se tem assinatura anual ativa, não pode fazer downgrade para mensal
                    if ($activeSubscription->isYearly() && $billingCycle === 'monthly') {
                        if (!$activeSubscription->canDowngradeToMonthly()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Não é possível fazer downgrade para plano mensal. Você deve aguardar o término do período de 12 meses ou pagar a taxa de cancelamento de R$ ' . number_format($activeSubscription->getCancellationFee(), 2, ',', '.') . '.'
                            ], 400);
                        }
                    }
                    
                    // Se tem assinatura anual ativa e quer trocar para outro plano anual
                    if ($activeSubscription->isYearly() && $billingCycle === 'annual' && $activeSubscription->plan_id !== $plan->id) {
                        // Marcar que é uma mudança de plano (será processado após confirmação do pagamento)
                        $isPlanChange = true;
                        $oldSubscriptionId = $activeSubscription->id;
                        
                        // Não cancelar ainda - apenas continuar com o processo de pagamento
                        // O cancelamento será feito após confirmação do pagamento
                    }
                }
            }
            
            // Definir tipo de pagamento baseado no contexto
            if (isset($isPlanChange) && $isPlanChange) {
                $paymentType = 'plan_change_annual';
            } else {
                $paymentType = 'subscription';
            }
            
            // Para planos anuais, criar cobrança única com cartão
            if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                // Para planos anuais, criar cobrança única do valor total de 12 meses
                $annualTotalPrice = $plan->yearly_price; // Valor anual
                
                $paymentData = [
                    'customer' => $customer['id'],
                    'value' => $annualTotalPrice,
                    'dueDate' => now()->format('Y-m-d'),
                    'description' => $description . ' - Pagamento único de 12 meses',
                    'creditCard' => [
                        'holderName' => $request->card_holder_name,
                        'number' => $request->card_number,
                        'expiryMonth' => $request->card_expiry_month,
                        'expiryYear' => $request->card_expiry_year,
                        'ccv' => $request->card_cvv
                    ],
                    'creditCardHolderInfo' => [
                        'name' => $request->name,
                        'email' => $request->email,
                        'cpfCnpj' => $request->cpf_cnpj,
                        'phone' => $request->phone,
                        'postalCode' => $request->postal_code,
                        'addressNumber' => $request->address_number,
                        'city' => $request->city,
                        'state' => $request->state
                    ]
                ];
                
                $asaasPayment = $this->asaasService->createCreditCardCharge($paymentData);
                $asaasSubscription = null; // Não há assinatura recorrente
            } else {
                // Criar cobrança com cartão no Asaas
                $price = $billingCycle === 'annual' ? $plan->yearly_price : $plan->monthly_price;
                
                // Aplicar taxa de cancelamento se necessário
                if (isset($activeSubscription) && $activeSubscription && $activeSubscription->isYearly() && $billingCycle === 'annual' && $activeSubscription->plan_id !== $plan->id) {
                    $cancellationFee = $activeSubscription->getCancellationFee();
                    $price = $cancellationFee + $plan->yearly_price;
                }
                $paymentData = [
                    'customer' => $customer['id'],
                    'value' => $price,
                    'dueDate' => now()->format('Y-m-d'),
                    'description' => $description,
                    'creditCard' => [
                        'holderName' => $request->card_holder_name,
                        'number' => $request->card_number,
                        'expiryMonth' => $request->card_expiry_month,
                        'expiryYear' => $request->card_expiry_year,
                        'ccv' => $request->card_cvv
                    ],
                    'creditCardHolderInfo' => [
                        'name' => $request->name,
                        'email' => $request->email,
                        'cpfCnpj' => $request->cpf_cnpj,
                        'phone' => $request->phone,
                        'postalCode' => $request->postal_code,
                        'addressNumber' => $request->address_number,
                        'city' => $request->city,
                        'state' => $request->state
                    ]
                ];

                $asaasPayment = $this->asaasService->createCreditCardCharge($paymentData);
                $asaasSubscription = null;
            }

            // Salvar pagamento no banco
            if ($type !== 'extra_budgets' && $billingCycle === 'annual') {
                // Para planos anuais, criar cobrança única
                $annualTotalPrice = $plan->yearly_price;
                $paymentCreateData = [
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'asaas_payment_id' => $asaasPayment['id'], // ID da cobrança única
                    'asaas_subscription_id' => null, // Não há assinatura recorrente
                    'asaas_customer_id' => $customer['id'],
                    'amount' => $annualTotalPrice, // Valor total anual
                    'billing_type' => 'CREDIT_CARD',
                    'type' => $paymentType,
                    'status' => 'PENDING',
                    'due_date' => $asaasPayment['dueDate'],
                    'description' => $description . ' - Pagamento único de 12 meses',
                    'billing_cycle' => $billingCycle
                ];
            } elseif ($type !== 'extra_budgets' && $billingCycle === 'monthly') {
                // Para planos mensais com assinatura recorrente
                $paymentCreateData = [
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'asaas_payment_id' => null, // Não há cobrança única
                    'asaas_subscription_id' => $asaasSubscription['id'], // ID da assinatura
                    'asaas_customer_id' => $customer['id'],
                    'amount' => $plan->monthly_price, // Valor mensal
                    'billing_type' => 'CREDIT_CARD',
                    'type' => $paymentType,
                    'status' => 'PENDING',
                    'due_date' => $asaasSubscription['nextDueDate'],
                    'description' => $description,
                    'billing_cycle' => $billingCycle
                ];
            } else {
                // Para outros tipos de pagamento
                $price = $billingCycle === 'annual' ? $plan->yearly_price : $plan->monthly_price;
                $paymentCreateData = [
                    'company_id' => $company->id,
                    'plan_id' => $type === 'extra_budgets' ? null : $plan->id,
                    'subscription_id' => isset($activeSubscription) ? $activeSubscription->id : null,
                    'asaas_payment_id' => $asaasPayment['id'],
                    'asaas_customer_id' => $customer['id'],
                    'asaas_subscription_id' => $asaasSubscription['id'] ?? null,
                    'payment_id' => null, // Será preenchido após criar a subscription
                    'amount' => $price,
                    'billing_type' => 'CREDIT_CARD',
                    'type' => $type === 'extra_budgets' ? 'extra_budgets' : 'subscription',
                    'status' => $asaasPayment['status'] ?? 'PENDING',
                    'due_date' => $asaasPayment['dueDate'],
                    'description' => $description,
                    'billing_cycle' => $billingCycle
                ];
            }
            
            if ($type === 'extra_budgets') {
                $paymentCreateData['extra_budgets_quantity'] = $activeSubscription->plan->budget_limit;
            }
            
            $payment = Payment::create($paymentCreateData);

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
        return match(strtolower($status)) {
            'pending' => 'Aguardando Pagamento',
            'received' => 'Efetuado',
            'confirmed' => 'Efetuado',
            'overdue' => 'Vencido',
            'cancelled' => 'Cancelado',
            default => ucfirst($status)
        };
    }

    /**
     * Listar pagamentos da empresa
     */
    public function index(Request $request)
    {
        $company = Auth::user()->company;
        
        // Verificar se já tem uma assinatura ativa
        $activeSubscription = $company->activeSubscription();
        
        // Buscar pagamentos da empresa
        $query = Payment::where('company_id', $company->id)
            ->with(['plan'])
            ->orderBy('created_at', 'desc');
        
        // Se não há assinatura ativa e não há pagamentos, redirecionar para select-plan
        if (!$activeSubscription && $query->count() == 0) {
            return redirect()->route('payments.select-plan')
                           ->with('info', 'Você precisa selecionar um plano para continuar.');
        }
        
        // Filtro por data inicial
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        
        // Filtro por data final
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro por tipo de plano
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        
        $payments = $query->paginate(10)->appends($request->query());
            
        // Buscar assinatura atual da empresa
        $currentSubscription = $company->subscription;
        
        // Buscar planos para o filtro
        $plans = Plan::all();
            
        return view('payments.index', compact('payments', 'currentSubscription', 'plans'));
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
     * Exibir tela de status do pagamento
     */
    public function status(Payment $payment)
    {

        // Verificar se o pagamento pertence à empresa do usuário
        if ($payment->company_id !== Auth::user()->company_id) {
            abort(403, 'Acesso negado.');
        }

        try {
            // Buscar informações atualizadas do Asaas se houver ID
            $asaasPayment = null;
            $qrCodeData = null;
            
            if ($payment->asaas_payment_id) {
                $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
                
                // Se for PIX e estiver pendente, buscar QR Code
                if ($payment->billing_type === 'PIX' && (strtoupper($payment->status) === 'PENDING' || $payment->status === 'pending')) {
                    try {
                        \Log::info('Tentando buscar QR Code PIX para pagamento', [
                            'payment_id' => $payment->id,
                            'asaas_payment_id' => $payment->asaas_payment_id,
                            'billing_type' => $payment->billing_type,
                            'status' => $payment->status
                        ]);
                        
                        $qrCodeData = $this->asaasService->getPixQrCode($payment->asaas_payment_id);
                        
                        \Log::info('QR Code PIX obtido com sucesso', [
                            'payment_id' => $payment->id,
                            'has_encoded_image' => isset($qrCodeData['encodedImage']),
                            'has_payload' => isset($qrCodeData['payload'])
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('Erro ao buscar QR Code PIX no status', [
                            'error' => $e->getMessage(),
                            'payment_id' => $payment->id
                        ]);
                    }
                }
            }

            // Se for requisição AJAX, retornar apenas o conteúdo da view
            if (request()->ajax()) {
                return view('payments.status-content', compact('payment', 'asaasPayment', 'qrCodeData'))->render();
            }

            return view('payments.status', compact('payment', 'asaasPayment', 'qrCodeData'));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar status do pagamento', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            
            if (request()->ajax()) {
                return view('payments.status-content', compact('payment'))->with('error', 'Não foi possível carregar informações atualizadas do pagamento.')->with('qrCodeData', null)->render();
            }
            
            return view('payments.status', compact('payment'))->with('error', 'Não foi possível carregar informações atualizadas do pagamento.')->with('qrCodeData', null);
        }
    }

    /**
     * Cancelar pagamento
     */
    public function cancel(Payment $payment)
    {
        // Verificar se o pagamento pertence à empresa do usuário
        if ($payment->company_id !== Auth::user()->company_id) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        // Verificar se o pagamento pode ser cancelado (apenas pendentes)
        if (!in_array($payment->status, ['pending', 'PENDING'])) {
            return response()->json(['success' => false, 'message' => 'Apenas pagamentos pendentes podem ser cancelados.'], 400);
        }

        try {
            // Verificar se há asaas_payment_id
            if (!$payment->asaas_payment_id) {
                return response()->json(['success' => false, 'message' => 'Pagamento não possui ID do Asaas para cancelamento.'], 400);
            }

            // Verificar status no Asaas antes de cancelar
            $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
            
            if (!$asaasPayment || !isset($asaasPayment['status'])) {
                return response()->json(['success' => false, 'message' => 'Não foi possível verificar o status do pagamento no Asaas.'], 500);
            }

            // Verificar se o pagamento está pendente no Asaas
            if (!in_array($asaasPayment['status'], ['PENDING', 'AWAITING_PAYMENT'])) {
                return response()->json(['success' => false, 'message' => 'Pagamento não pode ser cancelado. Status atual no Asaas: ' . $asaasPayment['status']], 400);
            }

            // Cancelar no Asaas
            $this->asaasService->cancelPayment($payment->asaas_payment_id);

            \Log::info('Pagamento cancelado no Asaas com sucesso', [
                'payment_id' => $payment->id,
                'asaas_payment_id' => $payment->asaas_payment_id,
                'user_id' => Auth::id()
            ]);

            // Não atualizar o status local aqui - aguardar webhook do Asaas
            // O webhook irá atualizar o status e asaas_response automaticamente

            return response()->json(['success' => true, 'message' => 'Pagamento cancelado com sucesso. O status será atualizado em breve.']);
        } catch (\Exception $e) {
            \Log::error('Erro ao cancelar pagamento', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'asaas_payment_id' => $payment->asaas_payment_id ?? null,
                'user_id' => Auth::id()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Erro ao cancelar pagamento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exibir fatura personalizada do pagamento
     */
    public function invoice(Payment $payment)
    {
        // Verificar se o pagamento pertence à empresa do usuário
        if ($payment->company_id !== Auth::user()->company_id) {
            abort(403, 'Acesso negado.');
        }

        try {
            // Buscar informações detalhadas do pagamento no Asaas
            $asaasPayment = null;
            $customerData = null;
            $billingInfo = null;

            if ($payment->asaas_payment_id) {
                // Buscar dados do pagamento
                $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
                
                // Buscar dados do cliente se disponível
                if (isset($asaasPayment['customer'])) {
                    $customerData = $this->asaasService->getCustomer($asaasPayment['customer']);
                }
                
                // Tentar buscar informações de cobrança (pode não estar disponível para todos os tipos)
                try {
                    $billingInfo = $this->asaasService->getPaymentBillingInfo($payment->asaas_payment_id);
                } catch (\Exception $e) {
                    // Informações de cobrança podem não estar disponíveis
                    \Log::info('Informações de cobrança não disponíveis', [
                        'payment_id' => $payment->id,
                        'asaas_payment_id' => $payment->asaas_payment_id
                    ]);
                }
            }

            // Se for requisição AJAX, retornar apenas o conteúdo da view
            if (request()->ajax()) {
                return view('payments.invoice-content', compact('payment', 'asaasPayment', 'customerData', 'billingInfo'))->render();
            }

            return view('payments.invoice', compact('payment', 'asaasPayment', 'customerData', 'billingInfo'));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar fatura do pagamento', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'error' => 'Não foi possível carregar a fatura. Tente novamente.'
                ], 500);
            }
            
            return back()->with('error', 'Não foi possível carregar a fatura. Tente novamente.');
         }
     }

     /**
      * Gerar recibo do pagamento
      */
     public function receipt(Payment $payment)
     {
         // Verificar se o pagamento pertence à empresa do usuário
         if ($payment->company_id !== Auth::user()->company_id) {
             abort(403, 'Acesso negado.');
         }

         // Verificar se o pagamento foi confirmado
         if ($payment->status !== 'paid') {
             // Verificar também no Asaas
             $isPaid = false;
             if ($payment->asaas_payment_id) {
                 try {
                     $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
                     $isPaid = in_array($asaasPayment['status'], ['RECEIVED', 'CONFIRMED']);
                 } catch (\Exception $e) {
                     // Se não conseguir verificar no Asaas, usar apenas o status local
                 }
             }
             
             if (!$isPaid) {
                 return back()->with('error', 'Recibo disponível apenas para pagamentos confirmados.');
             }
         }

         try {
             // Buscar informações detalhadas do pagamento no Asaas
             $asaasPayment = null;
             $customerData = null;

             if ($payment->asaas_payment_id) {
                 // Buscar dados do pagamento
                 $asaasPayment = $this->asaasService->getPaymentStatus($payment->asaas_payment_id);
                 
                 // Buscar dados do cliente se disponível
                 if (isset($asaasPayment['customer'])) {
                     $customerData = $this->asaasService->getCustomer($asaasPayment['customer']);
                 }
             }

             return view('payments.receipt', compact('payment', 'asaasPayment', 'customerData'));
         } catch (\Exception $e) {
             \Log::error('Erro ao gerar recibo do pagamento', [
                 'error' => $e->getMessage(),
                 'payment_id' => $payment->id
             ]);
             
             return back()->with('error', 'Não foi possível gerar o recibo. Tente novamente.');
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

    /**
     * Exibir página de compra de orçamentos extras
     */
    public function extraBudgets()
    {
        $company = Auth::user()->company;
        $activeSubscription = $company->activeSubscription();
        
        if (!$activeSubscription) {
            return redirect()->route('payments.select-plan')
                           ->with('error', 'Você precisa ter um plano ativo para comprar orçamentos extras.');
        }
        
        // Buscar controle de uso atual
        $usageControl = \App\Models\UsageControl::getOrCreateForCurrentMonth(
            $company->id,
            $activeSubscription->id,
            $activeSubscription->plan->budget_limit
        );
        
        // Preços por orçamento extra (pode ser configurável no futuro)
        $pricePerBudget = 5.00; // R$ 5,00 por orçamento extra
        
        return view('payments.extra-budgets', compact('company', 'activeSubscription', 'usageControl', 'pricePerBudget'));
    }

    /**
     * Processar compra de orçamentos extras
     */
    public function purchaseExtraBudgets(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:pix,credit_card',
            'amount' => 'required|numeric|min:0.01'
        ]);
        
        $company = Auth::user()->company;
        $activeSubscription = $company->activeSubscription();
        
        if (!$activeSubscription) {
            return back()->with('error', 'Você precisa ter um plano ativo para comprar orçamentos extras.');
        }
        
        $quantity = $request->quantity;
        $totalAmount = floatval($request->amount);
        
        // Verificar se o valor corresponde ao plano atual
        $currentPlanPrice = $activeSubscription->plan->price;
        if (abs($totalAmount - $currentPlanPrice) > 0.01) {
            return back()->with('error', 'Valor inválido para orçamentos extras.');
        }
        
        try {
            DB::beginTransaction();
            
            // Criar registro de pagamento
            $payment = Payment::create([
                'company_id' => $company->id,
                'plan_id' => null, // Não é um plano, são orçamentos extras
                'subscription_id' => $activeSubscription->id,
                'asaas_subscription_id' => null, // Não é uma assinatura
                'payment_id' => null, // Será preenchido após processar o pagamento
                'amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'billing_type' => strtoupper($request->payment_method), // PIX ou CREDIT_CARD
                'billing_cycle' => 'one_time',
                'type' => 'extra_budgets',
                'status' => 'pending',
                'description' => "Compra de {$activeSubscription->plan->budget_limit} orçamentos extras",
                'extra_budgets_quantity' => $activeSubscription->plan->budget_limit // Quantidade baseada no plano atual
            ]);
            
            // Processar pagamento via Asaas
            if ($request->payment_method === 'pix') {
                // Buscar ou criar cliente no Asaas
                $customers = $this->asaasService->findCustomerByCpfCnpj($company->document);
                
                if (empty($customers)) {
                    $customerData = [
                        'name' => $company->fantasy_name ?? $company->corporate_name,
                        'email' => $company->email,
                        'phone' => $company->phone ?? '',
                        'cpfCnpj' => $company->document
                    ];
                    $customer = $this->asaasService->createCustomer($customerData);
                } else {
                    $customer = $customers[0];
                }
                
                // Criar cobrança PIX no Asaas
                $paymentData = [
                    'customer' => $customer['id'],
                    'value' => $totalAmount,
                    'dueDate' => now()->addDays(1)->format('Y-m-d'),
                    'description' => "Compra de {$activeSubscription->plan->budget_limit} orçamentos extras limitados ao período do seu plano - " .
                        ($company->fantasy_name ?? $company->corporate_name)
                ];
                
                $asaasPayment = $this->asaasService->createPixCharge($paymentData);
            } else {
                // Para cartão de crédito, redirecionar para página de checkout
                return redirect()->route('payments.checkout-extra-budgets', $payment)
                               ->with('success', 'Pagamento criado. Complete os dados do cartão.');
            }
            
            // Atualizar com ID do Asaas
            $payment->update([
                'asaas_payment_id' => $asaasPayment['id'],
                'asaas_customer_id' => $customer['id'],
                'asaas_invoice_url' => $asaasPayment['invoiceUrl'] ?? null,
                'due_date' => $asaasPayment['dueDate']
            ]);
            
            DB::commit();
            
            if ($request->payment_method === 'pix') {
                return redirect()->route('payments.pix-extra-budgets', $payment)
                               ->with('success', 'Pagamento PIX criado com sucesso!');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar compra de orçamentos extras', [
                'error' => $e->getMessage(),
                'company_id' => $company->id,
                'quantity' => $quantity
            ]);
            
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }
    
    /**
     * Checkout para orçamentos extras
     */
    public function extraBudgetsCheckout()
    {
        $company = Auth::user()->company;
        $activeSubscription = $company->activeSubscription();
        
        if (!$activeSubscription) {
            return redirect()->route('payments.select-plan')
                           ->with('error', 'Você precisa ter um plano ativo para comprar orçamentos extras.');
        }
        
        $plan = $activeSubscription->plan;
        $amount = $plan->monthly_price;
        $type = 'extra_budgets';
        $period = 'extra_budgets';
        
        // Definir que é uma compra de orçamentos extras
        session(['checkout_type' => 'extra_budgets']);
        
        return view('payments.checkout', compact('plan', 'amount', 'type', 'period'))
            ->with('isExtraBudgets', true)
            ->with('pageTitle', 'Adicionar Orçamentos Extras')
            ->with('planDescription', "{$plan->budget_limit} orçamentos extras");
    }
    
    /**
     * Exibir página de checkout para taxa de cancelamento + mudança de plano
     */
    public function cancellationFeeCheckout(Plan $plan, Request $request)
    {
        $company = Auth::user()->company;
        $activeSubscription = $company->activeSubscription();
        
        if (!$activeSubscription || !$activeSubscription->isYearly()) {
            return redirect()->route('payments.select-plan')
                           ->with('error', 'Você não possui um plano anual ativo.');
        }
        
        $period = $request->get('period', 'monthly');
        if (!in_array($period, ['monthly', 'annual'])) {
            return redirect()->route('payments.select-plan')
                           ->with('error', 'Período inválido.');
        }
        
        $cancellationFee = $activeSubscription->getCancellationFee();
        
        if ($period === 'monthly') {
            $planPrice = $plan->monthly_price;
        } else {
            $planPrice = $plan->yearly_price; // Primeira parcela do plano anual
        }
        
        $totalAmount = $cancellationFee + $planPrice;
        
        return view('payments.cancellation-checkout', compact(
            'plan', 
            'company', 
            'activeSubscription', 
            'cancellationFee', 
            'planPrice',
            'totalAmount',
            'period'
        ));
    }
    
    /**
     * Calcular taxa de cancelamento para plano anual
     */
    public function calculateCancellationFee()
    {
        $company = Auth::user()->company;
        $activeSubscription = $company->activeSubscription();
        
        if (!$activeSubscription || !$activeSubscription->isYearly()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui um plano anual ativo.'
            ], 400);
        }
        
        $cancellationFee = $activeSubscription->getCancellationFee();
        $monthsRemaining = $activeSubscription->getMonthsRemaining();
        
        return response()->json([
            'success' => true,
            'cancellation_fee' => $cancellationFee,
            'months_remaining' => $monthsRemaining,
            'formatted_fee' => 'R$ ' . number_format($cancellationFee, 2, ',', '.'),
            'can_cancel_free' => $cancellationFee == 0
        ]);
    }
    
    /**
     * Processar pagamento da taxa de cancelamento
     */
    public function processCancellationFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'payment_type' => 'required|in:pix,credit_card',
            'period' => 'required|in:monthly,annual',
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
            $activeSubscription = $company->activeSubscription();
            $newPlan = Plan::findOrFail($request->plan_id);
            
            if (!$activeSubscription || !$activeSubscription->isYearly()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não possui um plano anual ativo.'
                ], 400);
            }
            
            if ($activeSubscription->cancellation_fee_paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'A taxa de cancelamento já foi paga.'
                ], 400);
            }
            
            $cancellationFee = $activeSubscription->getCancellationFee();
            $planPrice = $request->period === 'monthly' ? $newPlan->monthly_price : $newPlan->yearly_price;
            $totalAmount = $cancellationFee + $planPrice;
            
            if ($cancellationFee <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há taxa de cancelamento a ser paga.'
                ], 400);
            }
            
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
            
            // Criar cobrança para taxa de cancelamento + primeiro mês do novo plano
            $paymentData = [
                'customer' => $customer['id'],
                'value' => $totalAmount,
                'dueDate' => now()->addDays(1)->format('Y-m-d'),
                'description' => "Mudança de plano: {$activeSubscription->plan->name} Anual → {$newPlan->name} " . 
                               ($request->period === 'monthly' ? 'Mensal' : 'Anual') . " - " .
                               "Taxa cancelamento + " . ($request->period === 'monthly' ? '1º mês' : '1ª parcela') . " - " .
                               ($company->fantasy_name ?? $company->corporate_name)
            ];
            
            if ($request->payment_type === 'pix') {
                $asaasPayment = $this->asaasService->createPixCharge($paymentData);
            } else {
                // Adicionar dados do cartão para pagamento com cartão
                $paymentData['creditCard'] = [
                    'holderName' => $request->card_holder_name,
                    'number' => str_replace(' ', '', $request->card_number),
                    'expiryMonth' => $request->card_expiry_month,
                    'expiryYear' => $request->card_expiry_year,
                    'ccv' => $request->card_cvv
                ];
                $asaasPayment = $this->asaasService->createCreditCardCharge($paymentData);
            }
            
            // Salvar pagamento da mudança de plano
            $payment = Payment::create([
                'company_id' => $company->id,
                'plan_id' => $newPlan->id,
                'subscription_id' => $activeSubscription->id,
                'asaas_payment_id' => $asaasPayment['id'],
                'asaas_customer_id' => $customer['id'],
                'asaas_subscription_id' => null, // Não é uma assinatura recorrente
                'payment_id' => null, // Será preenchido pelo webhook
                'amount' => $totalAmount,
                'billing_type' => strtoupper($request->payment_type),
                'type' => 'plan_change',
                'status' => 'PENDING',
                'due_date' => $asaasPayment['dueDate'],
                'description' => $paymentData['description'],
                'billing_cycle' => $request->period === 'monthly' ? 'monthly' : 'annual',
                'metadata' => [
                    'old_plan_id' => $activeSubscription->plan_id,
                    'new_plan_id' => $newPlan->id,
                    'cancellation_fee' => $cancellationFee,
                    'plan_price' => $planPrice,
                    'period' => $request->period
                ]
            ]);
            
            if ($request->payment_type === 'pix') {
                // Gerar QR Code PIX
                $pixData = $this->asaasService->getPixQrCode($asaasPayment['id']);
                $qrCodeImage = $pixData['encodedImage'] ?? null;
                $payload = $pixData['payload'] ?? null;
                
                if (empty($payload) && !empty($qrCodeImage)) {
                    $planType = $request->period === 'monthly' ? 'Mensal' : 'Anual';
                    $payload = "PIX - Mudança para Plano {$planType}: R$ " . number_format($totalAmount, 2, ',', '.') . " - Vencimento: " . date('d/m/Y', strtotime($asaasPayment['dueDate']));
                }
                
                $payment->update([
                    'pix_qr_code' => $payload, // Salvar apenas o texto do PIX, não a imagem base64
                    'pix_copy_paste' => $payload
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('payments.pix-payment', $payment->id)
                ]);
            } else {
                // Pagamento com cartão - processar imediatamente
                if ($asaasPayment['status'] === 'CONFIRMED') {
                    // Pagamento aprovado - processar mudança de plano
                    $this->processSuccessfulPlanChange($payment, $activeSubscription, $newPlan);
                    
                    DB::commit();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Pagamento aprovado! Seu plano foi alterado para ' . ($request->period === 'monthly' ? 'mensal' : 'anual') . '.',
                        'redirect_url' => route('dashboard')
                    ]);
                } else {
                    DB::commit();
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Pagamento não foi aprovado. Verifique os dados do cartão e tente novamente.'
                    ], 400);
                }
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar taxa de cancelamento', [
                'error' => $e->getMessage(),
                'company_id' => Auth::user()->company->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento da taxa de cancelamento.'
            ], 500);
        }
    }
    
    /**
     * Processar mudança de plano após pagamento bem-sucedido
     */
    private function processSuccessfulPlanChange($payment, $activeSubscription, $newPlan)
    {
        try {
            // Usar PlanUpgradeService para processar a mudança corretamente
            // Isso garante que os orçamentos não utilizados sejam transferidos
            $planUpgradeService = new \App\Services\PlanUpgradeService();
            $newSubscription = $planUpgradeService->processUpgrade($activeSubscription, $newPlan, $payment);
            
            // Cancelar assinatura anual no Asaas se necessário
            if ($activeSubscription->asaas_subscription_id) {
                try {
                    $this->asaasService->cancelSubscription($activeSubscription->asaas_subscription_id);
                } catch (\Exception $e) {
                    Log::warning('Erro ao cancelar assinatura no Asaas', [
                        'subscription_id' => $activeSubscription->asaas_subscription_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Mudança de plano processada com sucesso via PlanUpgradeService', [
                'company_id' => $payment->company_id,
                'old_subscription_id' => $activeSubscription->id,
                'new_subscription_id' => $newSubscription->id,
                'payment_id' => $payment->id
            ]);
            
            return $newSubscription;
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar mudança de plano', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id
            ]);
            throw $e;
        }
    }
    
    /**
     * Cancelar plano anual (após pagamento da taxa)
     */
    public function cancelAnnualPlan()
    {
        try {
            DB::beginTransaction();
            
            $company = Auth::user()->company;
            $activeSubscription = $company->activeSubscription();
            
            if (!$activeSubscription || !$activeSubscription->isYearly()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não possui um plano anual ativo.'
                ], 400);
            }
            
            if (!$activeSubscription->cancellation_fee_paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você deve pagar a taxa de cancelamento antes de cancelar o plano.'
                ], 400);
            }
            
            // Cancelar assinatura no Asaas se existir
            if ($activeSubscription->asaas_subscription_id) {
                try {
                    $this->asaasService->cancelSubscription($activeSubscription->asaas_subscription_id);
                } catch (\Exception $e) {
                    Log::warning('Erro ao cancelar assinatura no Asaas', [
                        'subscription_id' => $activeSubscription->asaas_subscription_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Marcar assinatura como cancelada
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'end_date' => now() // Termina imediatamente
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Plano cancelado com sucesso.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar plano anual', [
                'error' => $e->getMessage(),
                'company_id' => Auth::user()->company->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar plano.'
            ], 500);
        }
    }
}

<div class="modal-header">
    <h5 class="modal-title">Fatura #{{ $payment->id }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
</div>

<div class="modal-body">
    <div class="invoice-container">
        <!-- Cabeçalho da Fatura -->
        <div class="invoice-header mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="company-info">
                        <h4 class="text-primary mb-1">{{ config('app.name') }}</h4>
                        <p class="text-muted mb-0">Sistema de Orçamentos</p>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="invoice-details">
                        <h5 class="mb-1">FATURA</h5>
                        <p class="mb-1"><strong>Número:</strong> #{{ $payment->id }}</p>
                        <p class="mb-1"><strong>Data:</strong> {{ $payment->created_at->format('d/m/Y') }}</p>
                        @if($payment->due_date)
                            <p class="mb-0"><strong>Vencimento:</strong> {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Informações do Cliente -->
        <div class="customer-info mb-4">
            <h6 class="text-primary mb-3">Dados do Cliente</h6>
            <div class="row">
                <div class="col-md-6">
                    @if($customerData)
                        <p class="mb-1"><strong>Nome:</strong> {{ $customerData['name'] ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $customerData['email'] ?? 'N/A' }}</p>
                        @if(isset($customerData['cpfCnpj']))
                            <p class="mb-1"><strong>CPF/CNPJ:</strong> {{ $customerData['cpfCnpj'] }}</p>
                        @endif
                    @else
                        <p class="mb-1"><strong>Nome:</strong> {{ $payment->user->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $payment->user->email ?? 'N/A' }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    @if($customerData && isset($customerData['phone']))
                        <p class="mb-1"><strong>Telefone:</strong> {{ $customerData['phone'] }}</p>
                    @endif
                    @if($customerData && isset($customerData['address']))
                        <p class="mb-1"><strong>Endereço:</strong> {{ $customerData['address'] }}</p>
                        @if(isset($customerData['addressNumber']))
                            <p class="mb-1"><strong>Número:</strong> {{ $customerData['addressNumber'] }}</p>
                        @endif
                        @if(isset($customerData['city']))
                            <p class="mb-1"><strong>Cidade:</strong> {{ $customerData['city'] }} - {{ $customerData['state'] ?? '' }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <hr>

        <!-- Detalhes do Pagamento -->
        <div class="payment-details mb-4">
            <h6 class="text-primary mb-3">Detalhes do Pagamento</h6>
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td><strong>Descrição:</strong></td>
                            <td>{{ $payment->description }}</td>
                        </tr>
                        <tr>
                            <td><strong>Valor:</strong></td>
                            <td class="text-success"><strong>R$ {{ number_format($payment->amount, 2, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @php
                                    // Status do sistema local
                                    $localStatusClass = match($payment->status) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'cancelled' => 'danger',
                                        'expired' => 'secondary',
                                        default => 'info'
                                    };
                                    $localStatusText = match($payment->status) {
                                        'pending' => 'Pendente',
                                        'paid' => 'Efetuado',
                                        'cancelled' => 'Cancelado',
                                        'expired' => 'Expirado',
                                        default => ucfirst($payment->status)
                                    };
                                    
                                    // Status do Asaas (se disponível)
                                    $asaasStatusText = '';
                                    $asaasStatusClass = 'info';
                                    if($asaasPayment && isset($asaasPayment['status'])) {
                                        $asaasStatusClass = match($asaasPayment['status']) {
                                            'PENDING' => 'warning',
                                            'RECEIVED', 'CONFIRMED' => 'success',
                                            'OVERDUE' => 'danger',
                                            'REFUNDED' => 'secondary',
                                            default => 'info'
                                        };
                                        $asaasStatusText = match($asaasPayment['status']) {
                                            'PENDING' => 'Pendente',
                                            'RECEIVED' => 'Recebido',
                                            'CONFIRMED' => 'Efetuado',
                                            'confirmed' => 'Efetuado',
                                            'OVERDUE' => 'Vencido',
                                            'REFUNDED' => 'Reembolsado',
                                            default => $asaasPayment['status']
                                        };
                                    }
                                @endphp
                                <span class="badge bg-{{ $localStatusClass }}">{{ $localStatusText }}</span>
                                @if($asaasStatusText)
                                    <span class="badge bg-{{ $asaasStatusClass }} ms-2">Gateway: {{ $asaasStatusText }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Método de Pagamento:</strong></td>
                            <td>
                                @php
                                    $paymentMethodText = match($payment->billing_type) {
                                        'PIX' => 'PIX',
                                        'CREDIT_CARD' => 'Cartão de Crédito',
                                        'BOLETO' => 'Boleto Bancário',
                                        'DEBIT_CARD' => 'Cartão de Débito',
                                        default => ucfirst(str_replace('_', ' ', strtolower($payment->billing_type ?? '')))
                                    };
                                @endphp
                                {{ $paymentMethodText }}
                            </td>
                        </tr>
                        @if($asaasPayment)
                            <tr>
                                <td><strong>ID Pg:</strong></td>
                                <td>{{ $payment->asaas_payment_id }}</td>
                            </tr>
                            @if(isset($asaasPayment['dateCreated']))
                                <tr>
                                    <td><strong>Data de Criação:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($asaasPayment['dateCreated'])->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endif
                            @if(isset($asaasPayment['paymentDate']))
                                <tr>
                                    <td><strong>Data de Pagamento:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($asaasPayment['paymentDate'])->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($asaasPayment && isset($asaasPayment['discount']))
            <hr>
            <!-- Informações de Desconto -->
            <div class="discount-info mb-4">
                <h6 class="text-primary mb-3">Desconto Aplicado</h6>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td><strong>Valor do Desconto:</strong></td>
                                <td class="text-info">R$ {{ number_format($asaasPayment['discount']['value'], 2, ',', '.') }}</td>
                            </tr>
                            @if(isset($asaasPayment['discount']['type']))
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>{{ $asaasPayment['discount']['type'] == 'PERCENTAGE' ? 'Percentual' : 'Valor Fixo' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($billingInfo)
            <hr>
            <!-- Informações de Cobrança -->
            <div class="billing-info mb-4">
                <h6 class="text-primary mb-3">Informações de Cobrança</h6>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            @foreach($billingInfo as $key => $value)
                                @if(!is_array($value) && !is_null($value))
                                    <tr>
                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                                        <td>{{ $value }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Rodapé -->
        <div class="invoice-footer mt-4 pt-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        Esta é uma fatura gerada automaticamente pelo sistema.<br>
                        Para dúvidas, entre em contato conosco.
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Gerado em: {{ now()->format('d/m/Y H:i:s') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
    @if($payment->status === 'paid' || $payment->status === 'confirmed' || ($asaasPayment && in_array($asaasPayment['status'], ['RECEIVED', 'CONFIRMED'])))
        <button type="button" class="btn btn-success me-2" onclick="generateReceipt({{ $payment->id }})">
            <i class="fas fa-receipt"></i> Gerar Recibo
        </button>
    @endif
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>
</div>

<style>
@media print {
    .modal-header, .modal-footer {
        display: none !important;
    }
    
    .modal-body {
        padding: 0 !important;
    }
    
    .invoice-container {
        max-width: none !important;
        box-shadow: none !important;
    }
}

.invoice-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.company-info h4 {
    color: #0d6efd;
    font-weight: 600;
}

.invoice-details {
    text-align: right;
}

.table td {
    padding: 8px 12px;
    border: none;
}

.table td:first-child {
    width: 200px;
    color: #6c757d;
}

.badge {
    font-size: 0.875em;
    padding: 6px 12px;
}

.text-primary {
    color: #0d6efd !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}
</style>
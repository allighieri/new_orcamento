<div class="payment-details">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted mb-2">Informações do Pagamento</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="fw-bold">ID:</td>
                    <td>#{{ $payment->id }}</td>
                </tr>
                @if($payment->asaas_payment_id)
                <tr>
                    <td class="fw-bold">ID Asaas:</td>
                    <td>{{ $payment->asaas_payment_id }}</td>
                </tr>
                @endif
                <tr>
                    <td class="fw-bold">Plano:</td>
                    <td>
                        @if($payment->plan)
                            {{ $payment->plan->name }}
                        @else
                            Orçamentos Extras
                        @endif
                        @if($payment->extra_budgets_quantity)
                            <small class="text-info d-block">{{ $payment->extra_budgets_quantity }} orçamentos adicionais</small>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Valor:</td>
                    <td class="text-success fw-bold">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Tipo:</td>
                    <td>
                        @if($payment->billing_type === 'PIX')
                            <span class="badge bg-info"><i class="bi bi-qr-code me-1"></i>PIX</span>
                        @elseif($payment->billing_type === 'CREDIT_CARD')
                            <span class="badge bg-primary"><i class="bi bi-credit-card me-1"></i>Cartão</span>
                        @else
                            <span class="badge bg-secondary">{{ $payment->billing_type }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Status:</td>
                    <td>
                        @php
                    $statusTranslations = [
                        'PENDING' => 'Pendente',
                        'RECEIVED' => 'Efetuado',
                        'CONFIRMED' => 'Efetuado',
                        'confirmed' => 'Efetuado',
                        'OVERDUE' => 'Vencido',
                        'CANCELLED' => 'Cancelado',
                        'paid' => 'Efetuado',
                        'pending' => 'Pendente',
                        'overdue' => 'Vencido',
                        'cancelled' => 'Cancelado',
                        'expired' => 'Expirado'
                    ];
                    $translatedStatus = $statusTranslations[$payment->status] ?? ucfirst($payment->status);
                @endphp
                @if($payment->status === 'RECEIVED' || $payment->status === 'CONFIRMED' || $payment->status === 'confirmed')
                    <span class="badge bg-success">Pago</span>
                @elseif($payment->status === 'PENDING')
                    <span class="badge bg-warning">Pendente</span>
                @elseif($payment->status === 'OVERDUE')
                    <span class="badge bg-danger">Vencido</span>
                @elseif($payment->status === 'CANCELLED')
                    <span class="badge bg-secondary">Cancelado</span>
                @else
                    <span class="badge bg-light text-dark">{{ $translatedStatus }}</span>
                @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-2">Datas</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="fw-bold">Criado em:</td>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Vencimento:</td>
                    <td>{{ $payment->due_date->format('d/m/Y H:i') }}</td>
                </tr>
                @if($payment->paid_at)
                <tr>
                    <td class="fw-bold">Pago em:</td>
                    <td class="text-success">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @if($payment->description)
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="text-muted mb-2">Descrição</h6>
            <p class="mb-0">{{ $payment->description }}</p>
        </div>
    </div>
    @endif

    @if(isset($asaasPayment) && $asaasPayment)
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="text-muted mb-2">Informações do Asaas</h6>
            <div class="alert alert-info">
                <small>
                    <strong>Status Asaas:</strong> {{ $asaasPayment['status'] ?? 'N/A' }}<br>
                    @if(isset($asaasPayment['invoiceUrl']))
                    <strong>URL da Fatura:</strong> <a href="{{ $asaasPayment['invoiceUrl'] }}" target="_blank">Ver Fatura</a><br>
                    @endif
                    @if(isset($asaasPayment['bankSlipUrl']))
                    <strong>Boleto:</strong> <a href="{{ $asaasPayment['bankSlipUrl'] }}" target="_blank">Ver Boleto</a><br>
                    @endif
                </small>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-4">
        <div class="col-12 text-end">
            @if($payment->status === 'PENDING' && $payment->billing_type === 'PIX')
                <a href="{{ route('payments.pix-payment', $payment) }}" class="btn btn-primary me-2">
                    <i class="bi bi-qr-code me-1"></i>Ver QR Code PIX
                </a>
            @endif
            <!-- Botão removido - status atualizado automaticamente via webhook -->
        </div>
    </div>
</div>
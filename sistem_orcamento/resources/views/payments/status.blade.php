@extends('layouts.app')

@section('title', 'Status do Pagamento')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Status do Pagamento #{{ $payment->id }}</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Pagamentos</a></li>
                    <li class="breadcrumb-item active">Status</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Card Principal do Status -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Status Visual -->
                        <div class="col-md-4 text-center">
                            <div class="status-visual mb-4">
                                @if($payment->isPaid())
                                    <div class="status-icon mb-3">
                                        <i class="bi bi-check-circle-fill display-1 text-success"></i>
                                    </div>
                                    <h3 class="text-success">Pagamento Aprovado</h3>
                                    <p class="text-muted">Seu pagamento foi processado com sucesso</p>
                                @elseif($payment->status === 'PENDING')
                                    <div class="status-icon mb-3">
                                        <i class="bi bi-clock-fill display-1 text-warning"></i>
                                    </div>
                                    <h3 class="text-warning">Aguardando Pagamento</h3>
                                    <p class="text-muted">Seu pagamento está sendo processado</p>
                                @elseif($payment->status === 'OVERDUE')
                                    <div class="status-icon mb-3">
                                        <i class="bi bi-exclamation-triangle-fill display-1 text-danger"></i>
                                    </div>
                                    <h3 class="text-danger">Pagamento Vencido</h3>
                                    <p class="text-muted">O prazo para pagamento expirou</p>
                                @else
                                    <div class="status-icon mb-3">
                                        <i class="bi bi-info-circle-fill display-1 text-info"></i>
                                    </div>
                                    <h3 class="text-info">{{ ucfirst($payment->status) }}</h3>
                                    <p class="text-muted">Status atual do pagamento</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Informações do Pagamento -->
                        <div class="col-md-8">
                            <h5 class="mb-3">Detalhes do Pagamento</h5>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">ID do Pagamento</label>
                                        <div class="fw-bold">#{{ $payment->id }}</div>
                                    </div>
                                    
                                    @if($payment->asaas_payment_id)
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">ID Asaas</label>
                                        <div class="fw-bold">{{ $payment->asaas_payment_id }}</div>
                                    </div>
                                    @endif
                                    
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Plano</label>
                                        <div class="fw-bold">
                                            @if($payment->plan)
                                                {{ $payment->plan->name }}
                                            @else
                                                Orçamentos Extras
                                            @endif
                                        </div>
                                        @if($payment->extra_budgets_quantity)
                                            <small class="text-info">+ {{ $payment->extra_budgets_quantity }} orçamentos extras</small>
                                        @endif
                                    </div>
                                    
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Valor</label>
                                        <div class="fw-bold text-success fs-5">R$ {{ number_format($payment->amount, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Método de Pagamento</label>
                                        <div>
                                            @if($payment->billing_type === 'PIX')
                                                <span class="badge bg-info fs-6"><i class="bi bi-qr-code me-1"></i>PIX</span>
                                            @elseif($payment->billing_type === 'CREDIT_CARD')
                                                <span class="badge bg-primary fs-6"><i class="bi bi-credit-card me-1"></i>Cartão de Crédito</span>
                                            @else
                                                <span class="badge bg-secondary fs-6">{{ $payment->billing_type }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Data de Criação</label>
                                        <div class="fw-bold">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Vencimento</label>
                                        <div class="fw-bold">{{ $payment->due_date->format('d/m/Y H:i') }}</div>
                                    </div>
                                    
                                    @if($payment->paid_at)
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Data do Pagamento</label>
                                        <div class="fw-bold text-success">{{ $payment->paid_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timeline do Pagamento -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Histórico do Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pagamento Criado</h6>
                                <p class="timeline-text text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($payment->status === 'PENDING')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Aguardando Pagamento</h6>
                                <p class="timeline-text text-muted">Pagamento pendente de confirmação</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($payment->isPaid())
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pagamento Confirmado</h6>
                                <p class="timeline-text text-muted">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : 'Data não disponível' }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($payment->status === 'OVERDUE')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pagamento Vencido</h6>
                                <p class="timeline-text text-muted">O prazo para pagamento expirou</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Informações Adicionais do Asaas -->
            @if(isset($asaasPayment) && $asaasPayment)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Gateway</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Status no Gateway</label>
                                <div class="fw-bold">{{ $asaasPayment['status'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @if(isset($asaasPayment['invoiceUrl']))
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Fatura</label>
                                <div><a href="{{ $asaasPayment['invoiceUrl'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Ver Fatura</a></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Ações -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($payment->status === 'PENDING' && $payment->billing_type === 'PIX')
                                <a href="{{ route('payments.pix-payment', $payment) }}" class="btn btn-primary me-2">
                                    <i class="bi bi-qr-code me-1"></i>Ver QR Code PIX
                                </a>
                            @endif
                            
                            <button type="button" class="btn btn-outline-info me-2" onclick="checkPaymentStatus({{ $payment->id }})">
                                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar Status
                            </button>
                        </div>
                        
                        <div>
                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Voltar aos Pagamentos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(session('error'))
            <div class="alert alert-warning mt-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-visual {
    padding: 2rem 1rem;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 0;
    font-size: 0.875rem;
}

.info-item label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
function checkPaymentStatus(paymentId) {
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Verificando...';
    button.disabled = true;
    
    $.ajax({
        url: `/payments/check-status/${paymentId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.status_changed) {
                // Recarregar a página para mostrar o novo status
                location.reload();
            } else {
                // Mostrar mensagem de que não houve mudança
                Swal.fire({
                    icon: 'info',
                    title: 'Status Verificado',
                    text: 'O status do pagamento não foi alterado.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao verificar status do pagamento. Tente novamente.'
            });
        },
        complete: function() {
            button.innerHTML = originalHtml;
            button.disabled = false;
        }
    });
}
</script>
@endpush
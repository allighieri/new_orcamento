@extends('layouts.app')

@section('title', 'Histórico de Pagamentos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('payments.select-plan') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i>Novo Plano
                    </a>
                </div>
                <h4 class="page-title">Histórico de Pagamentos</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Pagamentos</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Resumo da Assinatura Atual -->
    @if($currentSubscription)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="text-white mb-1">
                                <i class="mdi mdi-crown me-2"></i>Plano Atual: {{ $currentSubscription->plan->name }}
                            </h5>
                            <p class="mb-0 opacity-75">
                                {{ $currentSubscription->plan->budget_limit }} orçamentos por mês • 
                                Válido até {{ $currentSubscription->ends_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="subscription-status">
                                @if($currentSubscription->status === 'active')
                                    <span class="badge bg-success fs-6">Ativo</span>
                                @elseif($currentSubscription->status === 'expired')
                                    <span class="badge bg-danger fs-6">Expirado</span>
                                @else
                                    <span class="badge bg-warning fs-6">{{ ucfirst($currentSubscription->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencido</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="billing_type" class="form-label">Tipo de Pagamento</label>
                            <select name="billing_type" id="billing_type" class="form-select">
                                <option value="">Todos os tipos</option>
                                <option value="PIX" {{ request('billing_type') === 'PIX' ? 'selected' : '' }}>PIX</option>
                                <option value="CREDIT_CARD" {{ request('billing_type') === 'CREDIT_CARD' ? 'selected' : '' }}>Cartão de Crédito</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-filter me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                                <i class="mdi mdi-refresh me-1"></i>Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Pagamentos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Plano</th>
                                        <th>Valor</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Vencimento</th>
                                        <th>Criado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#{{ $payment->id }}</span>
                                            @if($payment->asaas_payment_id)
                                                <br><small class="text-muted">{{ $payment->asaas_payment_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="plan-icon me-2">
                                                    @if($payment->plan->name === 'Bronze')
                                                        <i class="mdi mdi-medal text-warning"></i>
                                                    @elseif($payment->plan->name === 'Prata')
                                                        <i class="mdi mdi-medal text-secondary"></i>
                                                    @else
                                                        <i class="mdi mdi-crown text-warning"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $payment->plan->name }}</div>
                                                    <small class="text-muted">{{ $payment->plan->budget_limit }} orçamentos</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if($payment->billing_type === 'PIX')
                                                <span class="badge bg-info"><i class="mdi mdi-qrcode me-1"></i>PIX</span>
                                            @elseif($payment->billing_type === 'CREDIT_CARD')
                                                <span class="badge bg-primary"><i class="mdi mdi-credit-card me-1"></i>Cartão</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $payment->billing_type }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->status === 'paid')
                                                <span class="badge bg-success">Pago</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge bg-warning">Pendente</span>
                                            @elseif($payment->status === 'overdue')
                                                <span class="badge bg-danger">Vencido</span>
                                            @elseif($payment->status === 'cancelled')
                                                <span class="badge bg-secondary">Cancelado</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ ucfirst($payment->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $payment->due_date->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $payment->due_date->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $payment->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($payment->status === 'pending' && $payment->billing_type === 'PIX')
                                                    <a href="{{ route('payments.pix-payment', $payment) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Ver PIX">
                                                        <i class="mdi mdi-qrcode"></i>
                                                    </a>
                                                @endif
                                                
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="checkPaymentStatus({{ $payment->id }})" title="Verificar Status">
                                                    <i class="mdi mdi-refresh"></i>
                                                </button>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="showPaymentDetails({{ $payment->id }})" title="Detalhes">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-credit-card-off display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum pagamento encontrado</h5>
                            <p class="text-muted">Você ainda não possui pagamentos registrados.</p>
                            <a href="{{ route('payments.select-plan') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus me-1"></i>Escolher Plano
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Pagamento -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function checkPaymentStatus(paymentId) {
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    
    button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i>';
    button.disabled = true;
    
    $.ajax({
        url: `/payments/${paymentId}/check-status`,
        method: 'GET',
        success: function(response) {
            if (response.status_changed) {
                // Recarregar a página para mostrar o status atualizado
                location.reload();
            } else {
                // Mostrar mensagem de que não houve mudança
                showToast('Status verificado', 'Não houve alteração no status do pagamento.', 'info');
            }
        },
        error: function() {
            showToast('Erro', 'Não foi possível verificar o status do pagamento.', 'error');
        },
        complete: function() {
            button.innerHTML = originalHtml;
            button.disabled = false;
        }
    });
}

function showPaymentDetails(paymentId) {
    $('#paymentDetailsContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
    `);
    
    $('#paymentDetailsModal').modal('show');
    
    $.ajax({
        url: `/payments/${paymentId}/details`,
        method: 'GET',
        success: function(response) {
            $('#paymentDetailsContent').html(response);
        },
        error: function() {
            $('#paymentDetailsContent').html(`
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert me-2"></i>
                    Erro ao carregar detalhes do pagamento.
                </div>
            `);
        }
    });
}

function showToast(title, message, type) {
    // Implementar sistema de toast/notificação
    const alertClass = type === 'error' ? 'alert-danger' : type === 'success' ? 'alert-success' : 'alert-info';
    
    const toast = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(function() {
        toast.alert('close');
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.plan-icon {
    font-size: 1.2rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    border-color: #f1f3f4;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group .btn {
    border-radius: 6px;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

.subscription-status .badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }
}
</style>
@endpush
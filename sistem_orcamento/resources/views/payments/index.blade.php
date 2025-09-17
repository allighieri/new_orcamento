@extends('layouts.app')

@section('title', 'Histórico de Pagamentos')

@section('content')
<div class="container mx-auto">

    

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-box"></i> Histórico de Pagamentos
            </h1>
            
           <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                 <a href="{{ route('payments.change-plan') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Novo Plano
                </a>
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
                                <i class="bi bi-gem me-2"></i>Plano Atual: {{ $currentSubscription->plan->name }}
                            </h5>
                            <p class="mb-0 opacity-75">
                                {{ $currentSubscription->plan->budget_limit }} orçamentos por mês • 
                                Válido até {{ $currentSubscription->ends_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="subscription-status">
                                @php
                    $subscriptionStatusTranslations = [
                        'active' => 'Ativo',
                        'expired' => 'Expirado',
                        'cancelled' => 'Cancelado',
                        'pending' => 'Pendente'
                    ];
                    $translatedSubscriptionStatus = $subscriptionStatusTranslations[$currentSubscription->status] ?? ucfirst($currentSubscription->status);
                @endphp
                @if($currentSubscription->status === 'active')
                    <span class="badge bg-success fs-6">Ativo</span>
                @elseif($currentSubscription->status === 'expired')
                    <span class="badge bg-danger fs-6">Expirado</span>
                @else
                    <span class="badge bg-warning fs-6">{{ $translatedSubscriptionStatus }}</span>
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ request('date_to', now()->endOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pendente</option>
                                <option value="RECEIVED" {{ request('status') === 'RECEIVED' ? 'selected' : '' }}>Pago</option>
                                <!-- <option value="CONFIRMED" {{ request('status') === 'CONFIRMED' ? 'selected' : '' }}>Confirmado</option> -->
                                <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>Vencido</option>
                                <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="plan_id" class="form-label">Plano</label>
                            <select class="form-select" id="plan_id" name="plan_id">
                                <option value="">Todos</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
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
                                        <th>Pagamento</th>
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
                                                <div class="plan-icon">
                                                    @if($payment->plan)
                                                        @if($payment->plan->name === 'Bronze')
                                                            <i class="bi bi-award text-warning"></i>
                                                        @elseif($payment->plan->name === 'Prata')
                                                            <i class="bi bi-award text-secondary"></i>
                                                        @else
                                                            <i class="bi bi-gem text-warning"></i>
                                                        @endif
                                                    @else
                                                        <i class="bi bi-plus-circle text-primary"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($payment->plan)
                                                        <div class="fw-bold">{{ $payment->plan->name }}</div>
                                                        <small class="text-muted">{{ $payment->plan->budget_limit }} orçamentos</small>
                                                    @else
                                                        <div class="fw-bold">Orçamentos Extras</div>
                                                        <small class="text-muted">{{ $payment->extra_budgets_quantity ?? 0 }} orçamentos adicionais</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if($payment->billing_type === 'PIX')
                                <span class="badge bg-info"><i class="bi bi-qr-code me-1"></i>PIX</span>
                            @elseif($payment->billing_type === 'CREDIT_CARD')
                                <span class="badge bg-primary"><i class="bi bi-credit-card me-1"></i>Cartão</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $payment->billing_type }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                $statusTranslations = [
                                    'PENDING' => 'Pendente',
                                    'RECEIVED' => 'Efetuado',
                                    'received' => 'Efetuado',
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
                            @if($payment->status === 'paid' || $payment->status === 'RECEIVED' || $payment->status === 'received' || $payment->status === 'CONFIRMED' || $payment->status === 'confirmed')
                                <span class="badge bg-success">Efetuado</span>
                            @elseif($payment->status === 'pending' || $payment->status === 'PENDING')
                                <span class="badge bg-warning">Pendente</span>
                            @elseif($payment->status === 'overdue' || $payment->status === 'OVERDUE')
                                <span class="badge bg-danger">Vencido</span>
                            @elseif($payment->status === 'cancelled' || $payment->status === 'CANCELLED')
                                <span class="badge bg-secondary">Cancelado</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $translatedStatus }}</span>
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
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="showPaymentStatus({{ $payment->id }})" title="Ver Status">
                                                    <i class="bi bi-info-circle"></i>
                                                </button>
                                                @if($payment->status === 'paid' || $payment->status === 'RECEIVED' || $payment->status === 'received' || $payment->status === 'CONFIRMED' || $payment->status === 'confirmed')
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="generateReceipt({{ $payment->id }})" title="Gerar Recibo">
                                                        <i class="bi bi-receipt"></i>
                                                    </button>
                                                @endif
                                                @if($payment->status === 'pending' || $payment->status === 'PENDING')
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="cancelPayment({{ $payment->id }})" title="Cancelar Pagamento">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                       
                        <div class="mt-3">
                          {{ $payments->appends(request()->query())->links() }}
                         </div>

                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-credit-card-2-front display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum pagamento encontrado</h5>
                            <p class="text-muted">Você ainda não possui pagamentos registrados.</p>
                            <a href="{{ route('payments.select-plan') }}" class="btn btn-primary">
                                <i class="bi bi-plus me-1"></i>Novo Plano
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Status do Pagamento -->
<div class="modal fade" id="paymentStatusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Status do Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentStatusContent">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showPaymentStatus(paymentId) {
    $('#paymentStatusContent').html('<div class="text-center py-5"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div><p class="mt-2">Carregando status do pagamento...</p></div>');
    $('#paymentStatusModal').modal('show');
    
    $.ajax({
        url: `/payments/${paymentId}/status`,
        method: 'GET',
        success: function(response) {
            $('#paymentStatusContent').html(response);
        },
        error: function(xhr, status, error) {
            $('#paymentStatusContent').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Erro ao carregar status do pagamento. Tente novamente.</div>');
        }
    });
}

function cancelPayment(paymentId) {
    Swal.fire({
        title: 'Cancelar Pagamento',
        text: 'Tem certeza que deseja cancelar este pagamento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, cancelar!',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Processando...',
                text: 'Verificando status no Asaas e cancelando pagamento',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `/payments/${paymentId}/cancel`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Pagamento cancelado com sucesso!',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            toast: true,
                            position: 'bottom-start'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message || 'Erro desconhecido',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao cancelar pagamento. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Função para gerar recibo
function generateReceipt(paymentId) {
    // Abrir recibo em nova janela
    const receiptUrl = `/payments/${paymentId}/receipt`;
    window.open(receiptUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
}

// Validação de datas
function validateDates() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    
    if (dateFrom && dateTo && dateFrom > dateTo) {
        Swal.fire({
            icon: 'error',
            title: 'Data Inválida!',
            text: 'A data final não pode ser menor que a data inicial.',
            confirmButtonText: 'OK'
        });
        return false;
    }
    return true;
}

// Adicionar eventos aos campos de data
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const filterForm = document.querySelector('form[action="{{ route('payments.index') }}"]');
    
    // Validar ao alterar as datas
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', validateDates);
        dateToInput.addEventListener('change', validateDates);
    }
    
    // Validar ao submeter o formulário
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
            }
        });
    }
});
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
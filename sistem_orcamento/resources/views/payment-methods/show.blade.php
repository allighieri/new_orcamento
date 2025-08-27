@extends('layouts.app')

@section('title', 'Método de Pagamento: ' . $paymentMethod->name . ' - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-credit-card"></i> {{ $paymentMethod->paymentOptionMethod ? $paymentMethod->paymentOptionMethod->method : 'N/A' }}
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                @if(!$paymentMethod->is_global || auth()->user()->role === 'super_admin')
                    <a href="{{ route('payment-methods.edit', $paymentMethod) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <!-- Informações Básicas -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Método</h5>
            </div>
            <div class="card-body">
               
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Método de Pagamento</label>
                            <p class="fw-bold fs-5">{{ $paymentMethod->paymentOptionMethod ? $paymentMethod->paymentOptionMethod->method : 'N/A' }}</p>
                        </div>
                    </div>
                   
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p>
                                @if($paymentMethod->is_active)
                                    <span class="badge bg-success fs-6">
                                        <i class="bi bi-check-circle"></i> Ativo
                                    </span>
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="bi bi-x-circle"></i> Inativo
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Empresa</label>
                            <p class="fw-bold">
                                @if($paymentMethod->company)
                                    {{ $paymentMethod->company->fantasy_name ?? $paymentMethod->company->corporate_name }}
                                @else
                                    <span class="text-muted">Sistema (Global)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Configurações de Parcelamento -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Parcelamento</h5>
            </div>
            <div class="card-body text-center">
                @if($paymentMethod->allows_installments)
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                        <h6 class="text-success mt-2">Permite Parcelamento</h6>
                    </div>
                    <div class="alert alert-success">
                        <h4 class="mb-0">{{ $paymentMethod->max_installments }}x</h4>
                        <small>Máximo de parcelas</small>
                    </div>
                @else
                    <div class="mb-3">
                        <i class="bi bi-x-circle-fill text-secondary" style="font-size: 2rem;"></i>
                        <h6 class="text-secondary mt-2">Apenas à Vista</h6>
                    </div>
                    <div class="alert alert-secondary">
                        <h4 class="mb-0">1x</h4>
                        <small>Pagamento único</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Estatísticas de Uso -->
<div class="container mx-auto row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas de Uso</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                            <h4 class="mt-2 mb-0">{{ $paymentMethod->budget_payments_count }}</h4>
                            <small class="text-muted">Orçamentos usando este método</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-calendar-plus text-info" style="font-size: 2rem;"></i>
                            <h4 class="mt-2 mb-0">{{ $paymentMethod->created_at->format('d/m/Y') }}</h4>
                            <small class="text-muted">Data de criação</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-calendar-check text-warning" style="font-size: 2rem;"></i>
                            <h4 class="mt-2 mb-0">{{ $paymentMethod->updated_at->format('d/m/Y') }}</h4>
                            <small class="text-muted">Última atualização</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            @if($paymentMethod->is_active)
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0 text-success">Disponível</h4>
                            @else
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0 text-danger">Indisponível</h4>
                            @endif
                            <small class="text-muted">Status atual</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ações -->
@if(!$paymentMethod->is_global || auth()->user()->role === 'super_admin')
<div class="container mx-auto row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="{{ route('payment-methods.edit', $paymentMethod) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Método
                    </a>
                    
                    @if($paymentMethod->budget_payments_count == 0)
                        <form action="{{ route('payment-methods.destroy', $paymentMethod) }}" method="POST" class="d-inline" id="delete-form-method-{{ $paymentMethod->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteMethod({{ $paymentMethod->id }})">
                                <i class="bi bi-trash"></i> Excluir Método
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-danger" disabled title="Não é possível excluir um método que está sendo usado">
                            <i class="bi bi-trash"></i> Excluir Método
                        </button>
                    @endif
                </div>
                
                @if($paymentMethod->budget_payments_count > 0)
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Este método não pode ser excluído pois está sendo usado em {{ $paymentMethod->budget_payments_count }} orçamento(s).
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<script>
function confirmDeleteMethod(methodId) {
    Swal.fire({
        title: 'Confirmação',
        html: 'Tem certeza de que deseja excluir este método de pagamento?<br><br><strong>Atenção:</strong> Esta ação não poderá ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-method-' + methodId).submit();
        }
    });
}
</script>

@endsection
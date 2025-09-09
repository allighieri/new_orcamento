@extends('layouts.app')

@section('title', 'Opção de Método: ' . $paymentOptionMethod->method . ' - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-credit-card-2-front"></i> {{ $paymentOptionMethod->method }}
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('payment-option-methods.edit', $paymentOptionMethod) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
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
                            <label class="form-label text-muted">Nome do Método</label>
                            <p class="fw-bold fs-5">{{ $paymentOptionMethod->method }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p>
                                @if($paymentOptionMethod->active)
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
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label text-muted">Descrição</label>
                            <p class="text-break">{{ $paymentOptionMethod->description ?: 'Nenhuma descrição fornecida.' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Data de Criação</label>
                            <p class="fw-bold">{{ $paymentOptionMethod->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Última Atualização</label>
                            <p class="fw-bold">{{ $paymentOptionMethod->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($paymentOptionMethod->paymentMethods->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Métodos de Pagamento Vinculados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Parcelamento</th>
                                <th>Máx. Parcelas</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentOptionMethod->paymentMethods as $method)
                            <tr>
                                <td>{{ $method->id }}</td>
                                <td>
                                    @if($method->company)
                                        <span class="badge bg-info">{{ $method->company->fantasy_name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Global</span>
                                    @endif
                                </td>
                                <td>
                                    @if($method->allows_installments)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sim</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Não</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $method->max_installments }}x</span>
                                </td>
                                <td>
                                    @if($method->is_active)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Ativo</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('payment-methods.show', $method) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Estatísticas</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">ID do Método:</small>
                    <div class="fw-bold">#{{ $paymentOptionMethod->id }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Métodos Vinculados:</small>
                    <div class="fw-bold text-primary">{{ $paymentOptionMethod->paymentMethods->count() }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Métodos Ativos:</small>
                    <div class="fw-bold text-success">{{ $paymentOptionMethod->paymentMethods->where('is_active', true)->count() }}</div>
                </div>
                
                <div class="mb-0">
                    <small class="text-muted">Métodos Inativos:</small>
                    <div class="fw-bold text-danger">{{ $paymentOptionMethod->paymentMethods->where('is_active', false)->count() }}</div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-gear"></i> Ações</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('payment-option-methods.edit', $paymentOptionMethod) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Editar Método
                    </a>
                    
                    <a href="{{ route('payment-methods.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Criar Método de Pagamento
                    </a>
                    
                    @if($paymentOptionMethod->paymentMethods->count() == 0)
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                        <i class="bi bi-trash"></i> Excluir Método
                    </button>
                    @else
                    <button type="button" class="btn btn-danger btn-sm" disabled title="Não é possível excluir: possui métodos vinculados">
                        <i class="bi bi-trash"></i> Excluir Método
                    </button>
                    @endif
                </div>
            </div>
        </div>
        
        @if($paymentOptionMethod->paymentMethods->count() > 0)
        <div class="card mt-3">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Aviso</h6>
            </div>
            <div class="card-body">
                <p class="small mb-0">
                    Este método possui <strong>{{ $paymentOptionMethod->paymentMethods->count() }}</strong> método(s) de pagamento vinculado(s). 
                    Para excluir esta opção, primeiro remova todos os vínculos.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

@if($paymentOptionMethod->paymentMethods->count() == 0)
<form action="{{ route('payment-option-methods.destroy', $paymentOptionMethod) }}" method="POST" class="d-none" id="delete-form">
    @csrf
    @method('DELETE')
</form>
@endif

<script>
function confirmDelete() {
    Swal.fire({
        title: 'Confirmação',
        html: 'Tem certeza de que deseja excluir esta opção de método de pagamento?<br><br><strong>Atenção:</strong> Esta ação não poderá ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}
</script>

@endsection
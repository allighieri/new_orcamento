@extends('layouts.app')

@section('title', 'Métodos de Pagamento - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-credit-card"></i> Métodos de Pagamento
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Novo Método
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($paymentMethods->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Parcelamento</th>
                                    <th>Máx. Parcelas</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentMethods as $method)
                                <tr>
                                    <td>{{ $method->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-credit-card me-2 text-primary"></i>
                                            {{ $method->paymentOptionMethod ? $method->paymentOptionMethod->method : 'N/A' }}
                                        </div>
                                    </td>
                                    
                                    <td>
                                        @if($method->allows_installments)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Sim
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Não
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $method->max_installments }}x</span>
                                    </td>
                                    <td>
                                        @if($method->is_active)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Ativo
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Inativo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('payment-methods.show', $method) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(!$method->is_global || auth()->user()->role === 'super_admin')
                                                <a href="{{ route('payment-methods.edit', $method) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteMethod({{ $method->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @if(!$method->is_global || auth()->user()->role === 'super_admin')
                                        <form action="{{ route('payment-methods.destroy', $method) }}" method="POST" class="d-inline" id="delete-form-method-{{ $method->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $paymentMethods->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-credit-card fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum método de pagamento cadastrado</h4>
                        <p class="text-muted">Comece cadastrando seu primeiro método de pagamento</p>
                        <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Novo Método
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
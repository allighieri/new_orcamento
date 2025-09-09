@extends('layouts.app')

@section('title', 'Opções de Métodos de Pagamento - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-credit-card-2-front"></i> Opções de Pagamento
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('payment-option-methods.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Nova Opção
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($paymentOptionMethods->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Método</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    <th class="text-center">Usos</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentOptionMethods as $method)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-credit-card-2-front me-2 text-primary"></i>
                                            <strong>{{ $method->method }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $method->description ?: 'Sem descrição' }}</span>
                                    </td>
                                    <td>
                                        @if($method->active)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Ativo
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Inativo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            <i class="bi bi-graph-up"></i> {{ $method->payment_methods_count }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('payment-option-methods.show', $method) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('payment-option-methods.edit', $method) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="submit" class="btn btn-sm {{ $method->active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $method->active ? 'Desativar' : 'Ativar' }}" onclick="document.getElementById('toggle-form-{{ $method->id }}').submit();">
                                                <i class="bi {{ $method->active ? 'bi-pause' : 'bi-play' }}"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteMethod({{ $method->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('payment-option-methods.toggle-active', $method) }}" method="POST" class="d-none" id="toggle-form-{{ $method->id }}">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <form action="{{ route('payment-option-methods.destroy', $method) }}" method="POST" class="d-inline" id="delete-form-method-{{ $method->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $paymentOptionMethods->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-credit-card-2-front fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma opção de método de pagamento cadastrada</h4>
                        <p class="text-muted">Comece cadastrando sua primeira opção de método de pagamento</p>
                        <a href="{{ route('payment-option-methods.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Opção
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
        html: 'Tem certeza de que deseja excluir esta opção de método de pagamento?<br><br><strong>Atenção:</strong> Esta ação não poderá ser desfeita.',
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
@extends('layouts.app')

@section('title', 'Orçamentos - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>
                    <i class="bi bi-file-earmark-text"></i> 
                    @if($client)
                        Orçamentos de {{ $client->corporate_name ?? $client->fantasy_name }}
                    @else
                        Orçamentos
                    @endif
                </h1>
                @if($client)
                    <p class="text-muted mb-0">
                        <i class="bi bi-person"></i> Cliente: {{ $client->corporate_name ?? $client->fantasy_name }}
                    </p>
                @endif
            </div>
            <div>
                @if($client)
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Voltar ao Cliente
                    </a>
                    <a href="{{ route('budgets.index') }}" class="btn btn-outline-info me-2">
                        <i class="bi bi-list"></i> Todos os Orçamentos
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                @endif
               <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Novo Orçamento
            </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($budgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Empresa</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                <tr>
                                    <td><strong>{{ $budget->number }}</strong></td>
                                    <td>
                                        <a href="{{ route('clients.show', $budget->client) }}" class="text-decoration-none">
                                            {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('companies.show', $budget->company) }}" class="text-decoration-none">
                                            {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}
                                        </a>
                                    </td>
                                    <td>{{ $budget->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge status-clickable
                                            @if($budget->status == 'Pendente') bg-warning
                                            @elseif($budget->status == 'Enviado') bg-info
                                            @elseif($budget->status == 'Em negociação') bg-primary
                                            @elseif($budget->status == 'Aprovado') bg-success
                                            @elseif($budget->status == 'Expirado') bg-danger
                                            @elseif($budget->status == 'Concluído') bg-secondary
                                            @else bg-light text-dark
                                            @endif" 
                                            style="cursor: pointer;" 
                                            onclick="openStatusModal({{ $budget->id }}, '{{ $budget->status }}')" 
                                            title="Clique para alterar o status">
                                            {{ $budget->status }}
                                        </span>
                                    </td>
                                    <td><strong>R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</strong></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('budgets.pdf', $budget) }}" class="btn btn-sm btn-outline-secondary" title="Gerar PDF" target="_blank">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" id="delete-form-budget-{{ $budget->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteBudget({{ $budget->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                    </div>
                    
                    {{ $budgets->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum orçamento cadastrado</h4>
                        <p class="text-muted">Comece cadastrando seu primeiro orçamento</p>
                        <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Orçamento
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteBudget(budgetId) {
    Swal.fire({
        title: 'Confirmação',
        text: 'Tem certeza de que deseja excluir este orçamento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-budget-' + budgetId).submit();
        }
    });
}
</script>

@endsection
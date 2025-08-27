@extends('layouts.app')

@section('title', 'Contas Bancárias - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-bank"></i> Contas Bancárias
            </h1>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
               <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Nova Conta
            </a>
            </div>
            
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($bankAccounts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Banco</th>
                                    <th>Agência</th>
                                    <th>Conta</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <th>Empresa</th>
                                    @endif
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bankAccounts as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>
                                        @if($account->type === 'PIX')
                                            <span class="badge bg-info">PIX</span>
                                        @else
                                            <span class="badge bg-primary">Conta</span>
                                        @endif
                                    </td>
                                    <td>{{ $account->compe->bank_name ?? 'N/A' }}</td>
                                    <td>{{ $account->branch ?? '-' }}</td>
                                    <td>{{ $account->account ?? '-' }}</td>
                                    <td>{{ $account->description }}</td>
                                    <td>
                                        @if($account->active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>{{ $account->company->fantasy_name ?? 'N/A' }}</td>
                                    @endif
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('bank-accounts.show', $account) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('bank-accounts.edit', $account) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('bank-accounts.destroy', $account) }}" method="POST" class="d-inline" id="delete-form-{{ $account->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDelete({{ $account->id }})">
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
                    
                    {{ $bankAccounts->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-bank fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma conta bancária cadastrada</h4>
                        <p class="text-muted">Comece cadastrando sua primeira conta bancária</p>
                        <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Conta
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(accountId) {
    Swal.fire({
        title: 'Atenção!',
        html: 'Tem certeza que deseja excluir esta conta bancária?<br><br>Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + accountId).submit();
        }
    });
}
</script>

@endsection
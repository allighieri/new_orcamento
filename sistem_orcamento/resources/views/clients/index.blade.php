@extends('layouts.app')

@section('title', 'Clientes - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-people"></i> Clientes
            </h1>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
               <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Novo Cliente
            </a>
            </div>
            
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <!-- Formulário de Pesquisa -->
        <div class="mb-4">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Pesquisar por nome fantasia, razão social, CNPJ/CPF, telefone ou email">
                </div>
                @if(request('search'))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                @endif
            </form>
        </div>
        
        <div class="card">
            <div class="card-body">
                @if($clients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Fantasia</th>
                                <th>Razão Social</th>
                                <th>CPF/CNPJ</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                @if(auth()->guard('web')->user()->role === 'super_admin')
                                    <th>Empresa</th>
                                @endif
                                <th class="text-end" style="width: 1%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td>{{ $client->fantasy_name ?: '-' }}</td>
                                    <td>{{ $client->corporate_name ?: '-' }}</td>
                                    <td>{{ $client->document_number }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ $client->email }}</td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>{{ $client->company->fantasy_name ?? 'N/A' }}</td>
                                    @endif
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDelete({{ $client->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-none" id="delete-form-{{ $client->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $clients->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        @if(request('search'))
                            <h4 class="text-muted mt-3">Nenhum cliente encontrado</h4>
                            <p class="text-muted">Não há clientes que correspondam à sua pesquisa</p>
                        @else
                            <h4 class="text-muted mt-3">Nenhum cliente cadastrado</h4>
                            <p class="text-muted">Comece cadastrando seu primeiro cliente</p>
                            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Novo Cliente
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(clientId) {
    Swal.fire({
        title: 'Atenção!',
        html: 'Excluir um Cliente excluirá todos os registros relacionados, inclusive orçamentos e contatos.<br><br>Tem certeza que deseja excluir este cliente?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + clientId).submit();
        }
    });
}
</script>

@endsection
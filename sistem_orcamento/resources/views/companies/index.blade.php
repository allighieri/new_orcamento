@extends('layouts.app')

@section('title', 'Empresas - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-building-add"></i> Empresas
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('companies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Nova Empresa
                </a>
            </div>
        </div>
        
        <div class="mb-4">
            <form method="GET" action="{{ route('companies.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nome da empresa ou CNPJ">
                </div>
                @if(request('search'))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                @endif
            </form>
        </div>


    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($companies->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Razão Social</th>
                                <th>Nome Fantasia</th>
                                <th>CNPJ</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                <tr>
                                    <td>{{ $company->id }}</td>
                                    <td>
                                        @if($company->logo)
                                            <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <i class="bi bi-building-add text-muted" style="font-size: 1.5rem;"></i>
                                        @endif
                                    </td>
                                    <td>{{ $company->corporate_name ?: '-' }}</td>
                                    <td>{{ $company->fantasy_name ?: '-' }}</td>
                                    <td>{{ $company->document_number }}</td>
                                    <td>{{ $company->phone }}</td>
                                    <td>{{ $company->email }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" id="delete-form-company-{{ $company->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteCompany({{ $company->id }})">
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
                    
                    {{ $companies->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-building fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma empresa cadastrada</h4>
                        <p class="text-muted">Comece cadastrando sua primeira empresa</p>
                        <a href="{{ route('companies.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Empresa
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteCompany(companyId) {
    Swal.fire({
        title: '⚠️ ATENÇÃO: Exclusão Permanente',
        html: `
            <div class="text-start">
                <p><strong>Esta ação é IRREVERSÍVEL e excluirá PERMANENTEMENTE:</strong></p>
                <ul class="text-danger">
                    <li>✗ Todos os <strong>orçamentos</strong> da empresa</li>
                    <li>✗ Todos os <strong>clientes</strong> da empresa</li>
                    <li>✗ Todos os <strong>produtos</strong> da empresa</li>
                    <li>✗ Todas as <strong>categorias</strong> da empresa</li>
                    <li>✗ Todos os <strong>contatos</strong> da empresa</li>
                    <li>✗ Todos os <strong>usuários</strong> da empresa</li>
                    <li>✗ Todos os <strong>métodos de pagamento</strong> específicos</li>
                    <li>✗ Todos os <strong>arquivos PDF</strong> gerados</li>
                    <li>✗ Todos os <strong>dados relacionados</strong></li>
                </ul>
                <p class="text-danger mt-3"><strong>Não será possível recuperar estes dados!</strong></p>
                <p>Tem certeza absoluta de que deseja continuar?</p>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '🗑️ Sim, excluir TUDO!',
        cancelButtonText: '❌ Cancelar',
        width: '600px',
        customClass: {
            popup: 'swal-wide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Segunda confirmação para ações críticas
            Swal.fire({
                title: 'Última confirmação',
                text: 'Digite "EXCLUIR" para confirmar a exclusão permanente:',
                input: 'text',
                inputPlaceholder: 'Digite EXCLUIR',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmar exclusão',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (value !== 'EXCLUIR') {
                        return 'Você deve digitar "EXCLUIR" para confirmar!';
                    }
                }
            }).then((secondResult) => {
                if (secondResult.isConfirmed) {
                    document.getElementById('delete-form-company-' + companyId).submit();
                }
            });
        }
    });
}
</script>

@endsection
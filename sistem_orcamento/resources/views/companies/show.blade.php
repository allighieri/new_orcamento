@extends('layouts.app')

@section('title', 'Visualizar Empresa - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-building-add"></i> Detalhes da Empresa
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informações da Empresa
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12 p-3">
                        <div class="d-flex align-items-center text-start">
                            @if($company->logo)
                                <div class="me-3">
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo da {{ $company->corporate_name ?? $company->fantasy_name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px; font-size: 1.5rem; font-weight: bold;">
                                    {{ strtoupper(substr($company->fantasy_name ?? $company->corporate_name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <h4>{{ $company->fantasy_name ?? $company->corporate_name }}</h4>
                                <p class="text-muted mb-0">{{ $company->email ?: 'Email não informado' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Razão Social:</strong>
                    <span class="flex-grow-1">{{ $company->corporate_name }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Nome Fantasia:</strong>
                    <span class="flex-grow-1">{{ $company->fantasy_name ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">CNPJ:</strong>
                    <span class="flex-grow-1">{{ $company->document_number }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Insc. Estadual:</strong>
                    <span class="flex-grow-1">{{ $company->state_registration ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Telefone:</strong>
                    <span class="flex-grow-1">
                        @if($company->phone)
                            <a href="tel:{{ preg_replace('/\D/', '', $company->phone) }}" class="text-decoration-none">
                                {{ $company->phone }}
                            </a>
                        @else
                            Não informado
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Endereço:</strong>
                    <span class="flex-grow-1">{{ $company->address ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Bairro:</strong>
                    <span class="flex-grow-1">{{ $company->district ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Cidade:</strong>
                    <span class="flex-grow-1">{{ $company->city ?: 'Não informado' }}</span>
                    </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">UF:</strong>
                    <span class="flex-grow-1">{{ $company->state ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Data:</strong>
                    <span class="flex-grow-1">{{ $company->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                <div class="mb-3 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 120px;">Atualizado:</strong>
                    <span class="flex-grow-1">{{ $company->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Ações
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Empresa
                    </a>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Novo Usuário
                    </a>
                    
                    <form action="{{ route('companies.destroy', $company) }}" method="POST" id="delete-form-company-{{ $company->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger w-100" onclick="confirmDeleteCompany({{ $company->id }})">
                            <i class="bi bi-trash"></i> Excluir Sua Empresa
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contatos (apenas para super_admin) -->
        @if(auth()->guard('web')->user()->role === 'super_admin')
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people"></i> Contatos ({{ $company->contacts->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($company->contacts->count() > 0)
                    <div class="row">
                        @foreach($company->contacts as $contact)
                            <div class="col-md-12 mb-12">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td class="fw-bold" style="width: 30%;">Nome:</td>
                                                <td>
                                                    
                                                    {{ $contact->name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">CPF:</td>
                                                <td>
                                                    @if($contact->cpf)
                                                        {{ $contact->cpf }}
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Telefone:</td>
                                                <td>
                                                    @if($contact->phone)
                                                        <a href="tel:{{ $contact->phone }}" class="text-decoration-none">
                                                             {{ $contact->phone }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Email:</td>
                                                <td>
                                                    @if($contact->email)
                                                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                                             {{ $contact->email }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">Nenhum contato cadastrado para esta empresa.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
         
        <!-- Usuários -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people"></i> Usuários ({{ $company->users->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($company->users->count() > 0)
                    <div class="row">
                        @foreach($company->users as $user)
                            <div class="col-md-12 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr>
                                                <td class="fw-bold" style="width: 30%;">Nome:</td>
                                                <td>{{ $user->name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Email:</td>
                                                <td>
                                                    @if($user->email)
                                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                                            {{ $user->email }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Função:</td>
                                                <td>
                                                    @if($user->role == 'super_admin')
                                                        <span class="badge bg-danger">Super Admin</span>
                                                    @elseif($user->role == 'admin')
                                                        <span class="badge bg-warning">Admin</span>
                                                    @else
                                                        <span class="badge bg-info">Usuário</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Status:</td>
                                                <td>
                                                    @if($user->active)
                                                        <span class="badge bg-success">Ativo</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($user->id == auth()->id())
                                            <tr>
                                                <td colspan="2"><small class="text-muted">Você não pode excluri seu próprio usuário. Somente um Admin por excluir outro.</small></td>
                                            </tr>
                                            @endif
                                        </table>
                                        <div class="d-flex gap-2 mt-3">
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user) }}?from_company=1" method="POST" class="d-inline" id="delete-form-user-{{ $user->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteUser({{ $user->id }})">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-danger btn-sm" disabled title="Você não pode excluir sua própria conta">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">Nenhum usuário cadastrado para esta empresa.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteCompany(companyId) {
    Swal.fire({
        title: 'Atenção!',
        html: 'Excluir uma Empresa excluirá todos os registros relacionados, inclusive orçamentos, contatos e usuários.<br><br>Tem certeza que deseja excluir esta empresa?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-company-' + companyId).submit();
        }
    });
}

function confirmDeleteUser(userId) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir este usuário?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-user-' + userId).submit();
        }
    });
}
</script>

@endsection
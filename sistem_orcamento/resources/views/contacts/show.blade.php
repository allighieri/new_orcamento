@extends('layouts.app')

@section('title', 'Visualizar Contato - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-person-lines-fill"></i> Detalhes do Contato
            </h1>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informações do Contato
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12 p-3">
                        <div class="d-flex align-items-center text-start">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px; font-size: 1.5rem; font-weight: bold;">
                                {{ strtoupper(substr($contact->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4>{{ $contact->name }}</h4>
                                <p class="text-muted mb-0">{{ $contact->email ?: 'Email não informado' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">CPF:</strong>
                    <span class="flex-grow-1">{{ $contact->cpf ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">    
                    <strong class="me-3" style="min-width: 90px;">Telefone:</strong>
                    <span class="flex-grow-1">{{ $contact->phone ?: 'Não informado' }}</span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Empresa:</strong>
                    <span>
                        @if($contact->company)
                            @if(auth()->guard('web')->user()->role === 'super_admin' || auth()->guard('web')->user()->role === 'admin')
                                <a href="{{ route('companies.show', $contact->company) }}" class="text-decoration-none">
                                    {{ $contact->company->fantasy_name }}
                                </a>
                            @else
                                {{ $contact->company->fantasy_name }}
                            @endif
                        @else
                            Não informado
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Cliente:</strong>
                    <span class="flex-grow-1">
                        @if($contact->client)
                            <a href="{{ route('clients.show', $contact->client) }}" class="text-decoration-none">
                                {{ $contact->client->fantasy_name }}
                            </a>
                        @else
                            Não informado
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Criado em:</strong>
                    <span class="flex-grow-1">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                <div class="mb-3 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Última atualização:</strong>
                    <span class="flex-grow-1">{{ $contact->updated_at->format('d/m/Y H:i') }}</span>
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
                    <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Contato
                    </a>
                    
                    @if(auth()->guard('web')->user()->role === 'admin' || auth()->guard('web')->user()->role === 'super_admin')
                        <form action="{{ route('contacts.destroy', $contact) }}" method="POST" id="delete-form-contact-{{ $contact->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDeleteContact({{ $contact->id }})">
                                <i class="bi bi-trash"></i> Excluir Contato
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar à Lista
                    </a>
                </div>
            </div>
        </div>
        
        @if($contact->company)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Empresa Associada
                </h5>
            </div>
            <div class="card-body">
                <h6>{{ $contact->company->fantasy_name ?? $contact->company->corporate_name }}</h6>
                <p class="text-muted mb-2">
                    <i class="bi bi-envelope"></i> {{ $contact->company->email }}
                </p>
                <p class="text-muted mb-2">
                    <i class="bi bi-telephone"></i> {{ $contact->company->phone }}
                </p>
                <p class="text-muted mb-3">
                    <i class="bi bi-geo-alt"></i> {{ $contact->company->city }}, {{ $contact->company->state }}
                </p>
                @if(in_array(auth()->guard('web')->user()->role, ['admin', 'super_admin']))
                    <a href="{{ route('companies.show', $contact->company) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Detalhes da Empresa
                    </a>
                @endif
            </div>
        </div>
        @endif
        
        @if($contact->client)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge"></i> Cliente Associado
                </h5>
            </div>
            <div class="card-body">
                <h6>{{ $contact->client->fantasy_name ?? $contact->client->corporate_name }}</h6>
                <p class="text-muted mb-2">
                    <i class="bi bi-envelope"></i> {{ $contact->client->email }}
                </p>
                <p class="text-muted mb-2">
                    <i class="bi bi-telephone"></i> {{ $contact->client->phone }}
                </p>
                <p class="text-muted mb-3">
                    <i class="bi bi-geo-alt"></i> {{ $contact->client->city }}, {{ $contact->client->state }}
                </p>
                @if(in_array(auth()->guard('web')->user()->role, ['admin', 'super_admin']))
                    <a href="{{ route('clients.show', $contact->client) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Detalhes do Cliente
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function confirmDeleteContact(contactId) {
    Swal.fire({
        title: 'Confirmação',
        text: 'Tem certeza de que deseja excluir este contato?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-contact-' + contactId).submit();
        }
    });
}
</script>


@endsection
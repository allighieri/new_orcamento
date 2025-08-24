@extends('layouts.app')

@section('title', 'Visualizar Contato - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-person-lines-fill"></i> Detalhes do Contato
            </h1>
            <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary me-2">
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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome:</label>
                        <p class="form-control-plaintext">{{ $contact->name }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">CPF:</label>
                        <p class="form-control-plaintext">{{ $contact->cpf ?: 'Não informado' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Telefone:</label>
                        <p class="form-control-plaintext">{{ $contact->phone ?: 'Não informado' }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email:</label>
                        <p class="form-control-plaintext">{{ $contact->email ?: 'Não informado' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Empresa:</label>
                        <p class="form-control-plaintext">
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
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cliente:</label>
                        <p class="form-control-plaintext">
                            @if($contact->client)
                                <a href="{{ route('clients.show', $contact->client) }}" class="text-decoration-none">
                                    {{ $contact->client->fantasy_name }}
                                </a>
                            @else
                                Não informado
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Criado em:</label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-calendar"></i> {{ $contact->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Última atualização:</label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-clock"></i> {{ $contact->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
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
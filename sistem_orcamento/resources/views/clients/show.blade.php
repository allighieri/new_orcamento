@extends('layouts.app')

@section('title', 'Visualizar Cliente - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-person"></i> Detalhes do Cliente
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
                    <i class="bi bi-info-circle"></i> Informações do Cliente
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="fw-bold">Nome Fantasia:</span> {{ $client->fantasy_name }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Razão Social:</span> {{ $client->corporate_name ?: 'Não informado' }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">CPF/CNPJ:</span> {{ $client->document_number }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Inscrição Estadual:</span> {{ $client->state_registration ?: 'Não informado' }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Telefone:</span> 
                    @if($client->phone)
                        <a href="tel:{{ preg_replace('/\D/', '', $client->phone) }}" class="text-decoration-none">
                            {{ $client->phone }}
                        </a>
                    @else
                        Não informado
                    @endif
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Email:</span> 
                    @if($client->email)
                        <a href="mailto:{{ $client->email }}" class="text-decoration-none">
                            {{ $client->email }}
                        </a>
                    @else
                        Não informado
                    @endif
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Endereço:</span> {{ $client->address ?: 'Não informado' }}, {{ $client->district ?: 'Não informado' }}, {{ $client->city ?: 'Não informado' }} - {{ $client->state ?: 'Não informado' }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">CEP:</span> {{ $client->cep ?: 'Não informado' }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Cadastrado em:</span> 
                    {{ $client->created_at->format('d/m/Y H:i') }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Última atualização:</span> 
                    {{ $client->updated_at->format('d/m/Y H:i') }}
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
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Cliente
                    </a>
                    
                    <a href="{{ route('contacts.create', ['client_id' => $client->id]) }}" class="btn btn-info" title="Adicionar novo contato para este cliente">
                        <i class="bi bi-person-plus"></i> Adicionar Contato
                    </a>
                    
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" id="delete-form-{{ $client->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger w-100" onclick="confirmDelete({{ $client->id }})">
                            <i class="bi bi-trash"></i> Excluir Cliente
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contatos -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people"></i> Contatos ({{ $client->contacts->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($client->contacts->count() > 0)
                    <div class="row">
                        @foreach($client->contacts as $contact)
                            <div class="col-md-12 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <table class="table table-borderless table-sm mb-0">
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
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#contactModal{{ $contact->id }}">
                                                <i class="bi bi-eye"></i> Ver Detalhes
                                            </button>
                                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <form action="{{ route('contacts.destroy', $contact) }}?from_client=1" method="POST" class="d-inline" id="delete-form-contact-{{ $contact->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteContact({{ $contact->id }})">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">Nenhum contato cadastrado para este cliente.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Modais de Detalhes dos Contatos -->
        @foreach($client->contacts as $contact)
        <div class="modal fade" id="contactModal{{ $contact->id }}" tabindex="-1" aria-labelledby="contactModalLabel{{ $contact->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactModalLabel{{ $contact->id }}">
                            <i class="bi bi-person-rolodex"></i> Detalhes do Contato
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="bi bi-person"></i> Informações Pessoais
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Nome:</td>
                                                <td>{{ $contact->name }}</td>
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
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="bi bi-telephone"></i> Informações de Contato
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Telefone:</td>
                                                <td>
                                                    @if($contact->phone)
                                                        <a href="tel:{{ $contact->phone }}" class="text-decoration-none">
                                                            <i class="bi bi-telephone"></i> {{ $contact->phone }}
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
                                                            <i class="bi bi-envelope"></i> {{ $contact->email }}
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
                        </div>
                        
                        @if($contact->client)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="bi bi-building"></i> Cliente Associado
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td class="fw-bold" style="width: 20%;">Nome Fantasia:</td>
                                                <td>{{ $contact->client->fantasy_name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Razão Social:</td>
                                                <td>{{ $contact->client->corporate_name ?? 'Não informado' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">CNPJ:</td>
                                                <td>{{ $contact->client->document_number ?? 'Não informado' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Fechar
                        </button>
                        <form action="{{ route('contacts.destroy', $contact) }}?from_client=1" method="POST" class="d-inline" id="delete-form-contact-{{ $contact->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteContact({{ $contact->id }})">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </form>
                        <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar Contato
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        
        <!-- Estatísticas de Orçamentos -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Orçamentos
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-primary mb-1">{{ $client->budgets->count() }}</h4>
                            <small class="text-muted">Orçamentos Cadastrados</small>
                        </div>
                    </div>
                </div>
                
                @if($client->budgets->count() > 0)
                    <div class="mt-3">
                        <a href="{{ route('budgets.index', ['client' => $client->id]) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-file-earmark-text"></i> Ver Orçamentos
                        </a>
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

function confirmDeleteContact(contactId) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir este contato?',
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
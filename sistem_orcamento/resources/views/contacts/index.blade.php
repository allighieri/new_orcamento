@extends('layouts.app')

@section('title', 'Contatos - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-person-lines-fill"></i> Contatos
            </h1>
            <div>    
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Novo Contato
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($contacts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                    <th>Empresa</th>
                                    @endif
                                    <th>Cliente</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                <tr>
                                    <td>{{ $contact->id }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->cpf }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td>{{ $contact->email }}</td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                    <td>{{ $contact->company ? $contact->company->fantasy_name : 'N/A' }}</td>
                                    @endif
                                    <td>
                                        @if($contact->client)
                                            <a href="{{ route('clients.show', $contact->client) }}" class="text-decoration-none">
                                                {{ $contact->client->fantasy_name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(auth()->guard('web')->user()->role === 'admin' || auth()->guard('web')->user()->role === 'super_admin')
                                                <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="d-inline" id="delete-form-contact-{{ $contact->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteContact({{ $contact->id }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $contacts->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-person-lines-fill fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum contato cadastrado</h4>
                        <p class="text-muted">Comece cadastrando seu primeiro contato</p>
                        <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Contato
                        </a>
                    </div>
                @endif
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
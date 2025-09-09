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
                    <th class="text-end" style="width: 1%;">Ações</th>
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
                    <td class="text-end">
                        <div class="btn-group" role="group">
                            <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(auth()->guard('web')->user()->role === 'admin' || auth()->guard('web')->user()->role === 'super_admin')
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteContact({{ $contact->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </div>
                        @if(auth()->guard('web')->user()->role === 'admin' || auth()->guard('web')->user()->role === 'super_admin')
                            <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="d-none" id="delete-form-contact-{{ $contact->id }}">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
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
        <h4 class="text-muted mt-3">Nenhum contato encontrado</h4>
        <p class="text-muted">Não há contatos que correspondam à sua pesquisa</p>
    </div>
@endif
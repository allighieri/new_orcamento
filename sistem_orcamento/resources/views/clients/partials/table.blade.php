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
        <h4 class="text-muted mt-3">Nenhum cliente encontrado</h4>
        <p class="text-muted">Não há clientes que correspondam à sua pesquisa</p>
    </div>
@endif
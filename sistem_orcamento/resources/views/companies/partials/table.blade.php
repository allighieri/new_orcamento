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
        <h4 class="text-muted mt-3">Nenhuma empresa encontrada</h4>
        <p class="text-muted">Não há empresas que correspondam à sua pesquisa</p>
    </div>
@endif
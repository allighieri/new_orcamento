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
                                    <td>{{ $company->corporate_name }}</td>
                                    <td>{{ $company->fantasy_name }}</td>
                                    <td>{{ $company->cnpj }}</td>
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
        title: 'Confirmação',
        html: 'Esta ação é irreversível. Todos os registros referente a sua empresa, incluindo orçamentos serão permanentemente perdidos.<br><br>Tem certeza de que deseja excluir a empresa esta empresa?',
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
</script>

@endsection
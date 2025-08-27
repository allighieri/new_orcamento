@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row dashboard-page">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2"></i>  {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
        </h1>
    </div>
</div>

<div class="container mx-auto row mb-4">
    <div class="d-flex flex-wrap justify-content-start gap-3 w-100">

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['budgets_count'] }}</h4>
                            <p class="card-text">Orçamentos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('budgets.index') }}" class="text-white text-decoration-none">
                        <small>Ver todos <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['categories_count'] }}</h4>
                            <p class="card-text">Categorias</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-tags fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('categories.index') }}" class="text-white text-decoration-none">
                        <small>Ver todas <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['products_count'] }}</h4>
                            <p class="card-text">Produtos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('products.index') }}" class="text-white text-decoration-none">
                        <small>Ver todos <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['clients_count'] }}</h4>
                            <p class="card-text">Clientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('clients.index') }}" class="text-white text-decoration-none">
                        <small>Ver todos <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['contacts_count'] }}</h4>
                            <p class="card-text">Contatos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-rolodex fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('contacts.index') }}" class="text-white text-decoration-none">
                        <small>Ver todas <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        @if(Auth::check() && Auth::user()->role === 'super_admin')
        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ App\Models\Company::count() }}</h4>
                            <p class="card-text">Empresas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-building-add fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('companies.index') }}" class="text-white text-decoration-none">
                        <small>Ver todas <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Ações Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-start gap-3 w-100">
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('budgets.create') }}" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-file-earmark-text"></i><br>
                            Novo Orçamento
                        </a>
                    </div>
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('clients.create') }}" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-person-plus"></i><br>
                            Novo Cliente
                        </a>
                    </div>
                    
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('categories.create') }}" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-tag"></i><br>
                            Nova Categoria
                        </a>
                    </div>

                    @if(Auth::check() && Auth::user()->role === 'super_admin')
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('companies.create') }}" class="btn btn-secondary btn-lg w-100">
                            <i class="bi bi-building-add"></i><br>
                            Nova Empresa
                        </a>
                    </div>
                    @endif
                    
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('products.create') }}" class="btn btn-info btn-lg w-100">
                            <i class="bi bi-box-seam"></i><br>
                            Novo Produto
                        </a>
                    </div>
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('contacts.create') }}" class="btn btn-danger btn-lg w-100">
                            <i class="bi bi-person-rolodex"></i><br>
                            Novo Contato
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Últimos Orçamentos
                </h5>
                <a href="{{ route('budgets.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body">
                @if($recentBudgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <th>Empresa</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Valor Final</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBudgets as $budget)
                                <tr>
                                    <td><strong>#{{ $budget->number }}</strong></td>
                                    <td>{{ $budget->client->corporate_name ?? $budget->client->fantasy_name }}</td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>{{ $budget->company->fantasy_name ?? 'N/A' }}</td>
                                    @endif
                                    <td>
                                        <span class="badge 
                                            @if($budget->status == 'Pendente') bg-warning
                                            @elseif($budget->status == 'Enviado') bg-info
                                            @elseif($budget->status == 'Em negociação') bg-primary
                                            @elseif($budget->status == 'Aprovado') bg-success
                                            @elseif($budget->status == 'Expirado') bg-danger
                                            @elseif($budget->status == 'Concluído') bg-secondary
                                            @else bg-light text-dark
                                            @endif
                                            status-badge"
                                            style="cursor: pointer;"
                                            onclick="openStatusModal({{ $budget->id }}, '{{ $budget->status }}')"
                                            title="Clique para alterar o status">
                                            {{ $budget->status }}
                                        </span>
                                    </td>
                                    <td>R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</td>
                                    <td>{{ $budget->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-outline-primary" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Nenhum orçamento encontrado</p>
                        <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Criar primeiro orçamento
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
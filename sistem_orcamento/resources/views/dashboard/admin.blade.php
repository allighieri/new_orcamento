@extends('layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard Administrativo</h2>
                <div>
                    <span class="badge bg-danger">{{ ucfirst($user->role) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estatísticas Principais -->
    <div class="row mb-4">
        <div class="col-md-2 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $stats['users_count'] }}</h4>
                    <p class="mb-0">Usuários</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $stats['budgets_count'] }}</h4>
                    <p class="mb-0">Orçamentos</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-person-check" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $stats['clients_count'] }}</h4>
                    <p class="mb-0">Clientes</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-box" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $stats['products_count'] }}</h4>
                    <p class="mb-0">Produtos</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="bi bi-building" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $stats['companies_count'] }}</h4>
                    <p class="mb-0">Empresas</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-person-plus" style="font-size: 2rem;"></i>
                    <h4 class="mt-2"><a href="{{ route('users.index') }}" class="text-white text-decoration-none">+</a></h4>
                    <p class="mb-0">Gerenciar Usuários</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Orçamentos Recentes -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Orçamentos Recentes</h5>
                </div>
                <div class="card-body">
                    @if($stats['recent_budgets']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['recent_budgets'] as $budget)
                                        <tr>
                                            <td><a href="{{ route('budgets.show', $budget->id) }}">#{{ $budget->id }}</a></td>
                                            <td>{{ $budget->client->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $budget->status == 'approved' ? 'success' : ($budget->status == 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($budget->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $budget->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Nenhum orçamento encontrado.</p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('budgets.index') }}" class="btn btn-sm btn-primary">
                        Ver todos os orçamentos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Usuários Recentes -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Usuários Recentes</h5>
                </div>
                <div class="card-body">
                    @if($stats['recent_users']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Cadastro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['recent_users'] as $recent_user)
                                        <tr>
                                            <td>{{ $recent_user->name }}</td>
                                            <td>{{ $recent_user->email }}</td>
                                            <td>
                                                <span class="badge bg-{{ $recent_user->role == 'super_admin' ? 'danger' : ($recent_user->role == 'admin' ? 'warning' : 'primary') }}">
                                                    {{ ucfirst($recent_user->role) }}
                                                </span>
                                            </td>
                                            <td>{{ $recent_user->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Nenhum usuário encontrado.</p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
                        Criar novo usuário <i class="bi bi-person-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Administrativas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Ações Administrativas</h5>
                </div>
                <div class="card-body">
                    <p>Olá, <strong>{{ $user->name }}</strong>! Você está logado como <strong>{{ ucfirst($user->role) }}</strong>.</p>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('budgets.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-file-earmark-text me-2"></i>Gerenciar Orçamentos
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('clients.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-people me-2"></i>Gerenciar Clientes
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-box me-2"></i>Gerenciar Produtos
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('companies.index') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-building me-2"></i>Gerenciar Empresas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Dashboard - Usuário')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
                <div>
                    <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
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

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['budgets_count'] }}</h4>
                            <p class="mb-0">Orçamentos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
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

        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['clients_count'] }}</h4>
                            <p class="mb-0">Clientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
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

        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['products_count'] }}</h4>
                            <p class="mb-0">Produtos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box" style="font-size: 2rem;"></i>
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
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Bem-vindo ao Sistema de Orçamentos</h5>
                </div>
                <div class="card-body">
                    <p>Olá, <strong>{{ $user->name }}</strong>! Você está logado como <strong>{{ ucfirst($user->role) }}</strong>.</p>
                    <p>Use o menu de navegação para acessar as funcionalidades do sistema:</p>
                    <ul>
                        <li>Gerenciar orçamentos</li>
                        <li>Cadastrar e editar clientes</li>
                        <li>Controlar produtos e estoque</li>
                        <li>Visualizar relatórios</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
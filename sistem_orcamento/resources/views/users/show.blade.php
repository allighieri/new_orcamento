@extends('layouts.app')

@section('title', 'Visualizar Usuário - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-person"></i> Detalhes do Usuário
            </h1>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informações do Usuário
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <div class="avatar-circle-large mb-3">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome Completo:</label>
                        <p class="form-control-plaintext">{{ $user->name }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">E-mail:</label>
                        <p class="form-control-plaintext">{{ $user->email }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Função:</label>
                        <p class="form-control-plaintext">
                            @if($user->role === 'super_admin')
                                <span class="badge bg-danger fs-6">Super Administrador</span>
                            @elseif($user->role === 'admin')
                                <span class="badge bg-warning fs-6">Administrador</span>
                            @else
                                <span class="badge bg-info fs-6">Usuário</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status:</label>
                        <p class="form-control-plaintext">
                            @if($user->active)
                                <span class="badge bg-success fs-6">Ativo</span>
                            @else
                                <span class="badge bg-secondary fs-6">Inativo</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Empresa:</label>
                        <p class="form-control-plaintext">
                            @if($user->company)
                                <a href="{{ route('companies.show', $user->company) }}" class="text-decoration-none">
                                    {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
                                </a>
                            @else
                                <span class="text-muted">Nenhuma empresa associada</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Data de Cadastro:</label>
                        <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                
                @if($user->email_verified_at)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">E-mail Verificado:</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-success">Verificado em {{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                        </p>
                    </div>
                </div>
                @endif
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
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Usuário
                    </a>
                    
                    @if($user->id !== auth()->id())
                        <form action="{{ route('users.toggle-active', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $user->active ? 'btn-secondary' : 'btn-success' }} w-100">
                                <i class="bi {{ $user->active ? 'bi-pause' : 'bi-play' }}"></i> 
                                {{ $user->active ? 'Desativar' : 'Ativar' }} Usuário
                            </button>
                        </form>
                        
                        <hr>
                        
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Excluir Usuário
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Você não pode alterar ou excluir sua própria conta.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        @if($user->company)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Empresa Associada
                </h5>
            </div>
            <div class="card-body">
                <h6>{{ $user->company->fantasy_name ?? $user->company->corporate_name }}</h6>
                <p class="text-muted mb-2">{{ $user->company->email }}</p>
                <p class="text-muted mb-2">{{ $user->company->phone }}</p>
                <a href="{{ route('companies.show', $user->company) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye"></i> Ver Empresa
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.avatar-circle-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
    margin: 0 auto;
}
</style>
@endsection
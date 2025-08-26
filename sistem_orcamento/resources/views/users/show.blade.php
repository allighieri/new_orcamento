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
                    <div class="col-12 p-3">
                        <div class="d-flex align-items-center text-start">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px; font-size: 1.5rem; font-weight: bold;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4>{{ $user->name }}</h4>
                                <p class="text-muted mb-0">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Função:</strong>
                    <span class="flex-grow-1">
                        @if($user->role === 'super_admin')
                            <span>Super Administrador</span>
                        @elseif($user->role === 'admin')
                            <span>Administrador</span>
                        @else
                            <span>Usuário</span>
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Status:</strong>
                    <span class="flex-grow-1">
                        @if($user->active)
                            <span>Ativo</span>
                        @else
                            <span>Inativo</span>
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Empresa:</strong>
                    <span class="flex-grow-1">
                        @if($user->company)
                            @if(in_array($user->role, ['admin', 'super_admin']))
                                <a href="{{ route('companies.show', $user->company) }}" class="text-decoration-none">
                                    {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
                                </a>
                            @else
                                {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
                            @endif
                        @else
                            <span class="text-muted">Nenhuma empresa associada</span>
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">Cadastro:</strong>
                    <span class="flex-grow-1">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                @if($user->email_verified_at)
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-3" style="min-width: 90px;">E-mail Verificado:</strong>
                    <span class="flex-grow-1">
                        <span class="badge bg-success">Verificado em {{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                    </span>
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
                    <i class="bi bi-eye"></i> Detalhes da Empresa
                </a>
            </div>
        </div>
        @endif
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Resumo de Atividades
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Perfil desde:</span>
                    <strong>{{ $user->created_at->format('M/Y') }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Último acesso:</span>
                    <strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Status da conta:</span>
                    <span class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }}">{{ $user->active ? 'Ativa' : 'Inativa' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@extends('layouts.app')

@section('title', 'Meu Perfil - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-person-circle"></i> Meu Perfil
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
                    <i class="bi bi-info-circle"></i> Minhas Informações
                </h5>
            </div>
            <div class="card-body">
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center text-start">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center p-4 fw-bold me-4">
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
                    <strong class="me-1" style="min-width: 90px;">Função:</strong>
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
                    <strong class="me-1" style="min-width: 90px;">Status:</strong>
                    <span class="flex-grow-1">
                        @if($user->active)
                            <span>Ativo</span>
                        @else
                            <span>Inativo</span>
                        @endif
                    </span>
                </div>
                
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-1" style="min-width: 90px;">Empresa:</strong>
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
                    <strong class="me-1" style="min-width: 90px;">Cadastro:</strong>
                    <span class="flex-grow-1">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                @if($user->email_verified_at)
                <div class="mb-1 d-flex align-items-center">
                    <strong class="me-1" style="min-width: 90px;">E-mail Verificado:</strong>
                    <span class="flex-grow-1">
                        <span class="badge bg-success">Verificado em {{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    @if($user->company)
    
    <div class="col-md-4">

        <div class="card mt-3 mt-lg-0">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Ações
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Perfil
                    </a>
                    
                    
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building-add"></i> Minha Empresa
                </h5>
            </div>
            <div class="card-body">
                <h6><i class="bi bi-building-add"></i> {{ $user->company->fantasy_name ?? $user->company->corporate_name }}</h6>
                <p class="text-muted mb-2">
                    <i class="bi bi-envelope"></i> {{ $user->company->email }}
                </p>
                <p class="text-muted mb-2">
                    <i class="bi bi-telephone"></i> {{ $user->company->phone }}
                </p>
                <p class="text-muted mb-3">
                    <i class="bi bi-geo-alt"></i> {{ $user->company->city }}, {{ $user->company->state }}
                </p>
                @if(in_array($user->role, ['admin', 'super_admin']))
                    <a href="{{ route('companies.show', $user->company) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Detalhes da Empresa
                    </a>
                @endif
            </div>
        </div>
        
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
                    <span class="badge bg-success">Ativa</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


@endsection
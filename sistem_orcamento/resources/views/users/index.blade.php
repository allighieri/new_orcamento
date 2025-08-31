@extends('layouts.app')

@section('title', 'Usuários - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-people"></i> Usuários
            </h1>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Novo Usuário
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Função</th>
                                    <th>Empresa</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 1%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            {{ $user->name }}
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($user->role === 'admin')
                                            <span class="badge bg-warning">Admin</span>
                                        @else
                                            <span class="badge bg-info">Usuário</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->company)
                                            {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
                                        @else
                                            <span class="text-muted">Sem empresa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <button type="submit" class="btn btn-sm {{ $user->active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $user->active ? 'Desativar' : 'Ativar' }}" onclick="document.getElementById('toggle-form-{{ $user->id }}').submit();">
                                                    <i class="bi {{ $user->active ? 'bi-pause' : 'bi-play' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="if(confirm('Tem certeza que deseja excluir este usuário?')) document.getElementById('delete-form-{{ $user->id }}').submit();">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="d-none" id="toggle-form-{{ $user->id }}">
                                                @csrf
                                                @method('PATCH')
                                            </form>
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-none" id="delete-form-{{ $user->id }}">
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
                    
                    {{ $users->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum usuário cadastrado</h4>
                        <p class="text-muted">Comece cadastrando o primeiro usuário</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Usuário
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
}
</style>
@endsection
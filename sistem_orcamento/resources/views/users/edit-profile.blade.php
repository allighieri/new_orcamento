@extends('layouts.app')

@section('title', 'Editar Perfil - Sistema de Orçamento')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-person-gear"></i> Editar Meu Perfil</h4>
                    <a href="{{ route('profile') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">
                                        Deixe em branco para manter a senha atual
                                    </small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               id="password_confirmation" name="password_confirmation">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                            <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações não editáveis -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Função Atual</label>
                                    <div class="form-control-plaintext">
                                        @if($user->role === 'super_admin')
                                            <span class="badge bg-danger fs-6">Super Administrador</span>
                                        @elseif($user->role === 'admin')
                                            <span class="badge bg-warning fs-6">Administrador</span>
                                        @else
                                            <span class="badge bg-info fs-6">Usuário</span>
                                        @endif
                                    </div>
                                    <small class="form-text text-muted">
                                        Sua função não pode ser alterada por você mesmo
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Empresa Associada</label>
                                    <div class="form-control-plaintext">
                                        @if($user->company)
                                            {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
                                        @else
                                            <span class="text-muted">Nenhuma empresa associada</span>
                                        @endif
                                    </div>
                                    <small class="form-text text-muted">
                                        Sua empresa não pode ser alterada por você mesmo
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campo de status apenas para super_admin -->
                        @if($user->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $user->active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">
                                            Conta ativa
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Contas inativas não conseguem fazer login no sistema
                                    </small>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status da Conta</label>
                                    <div class="form-control-plaintext">
                                        @if($user->active)
                                            <span class="badge bg-success fs-6">Ativa</span>
                                        @else
                                            <span class="badge bg-secondary fs-6">Inativa</span>
                                        @endif
                                    </div>
                                    <small class="form-text text-muted">
                                        O status da sua conta não pode ser alterado por você mesmo
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('profile') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Atualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Campos de senha
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');
    
    // Toggle para campo de senha
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Trocar ícone
        if (type === 'text') {
            togglePasswordIcon.classList.remove('bi-eye');
            togglePasswordIcon.classList.add('bi-eye-slash');
        } else {
            togglePasswordIcon.classList.remove('bi-eye-slash');
            togglePasswordIcon.classList.add('bi-eye');
        }
    });
    
    // Toggle para campo de confirmação de senha
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const togglePasswordConfirmationIcon = document.getElementById('togglePasswordConfirmationIcon');
    
    togglePasswordConfirmation.addEventListener('click', function() {
        const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordField.setAttribute('type', type);
        
        // Trocar ícone
        if (type === 'text') {
            togglePasswordConfirmationIcon.classList.remove('bi-eye');
            togglePasswordConfirmationIcon.classList.add('bi-eye-slash');
        } else {
            togglePasswordConfirmationIcon.classList.remove('bi-eye-slash');
            togglePasswordConfirmationIcon.classList.add('bi-eye');
        }
    });
});
</script>
@endsection
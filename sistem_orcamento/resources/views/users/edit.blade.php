@extends('layouts.app')

@section('title', 'Editar Usuário - Sistema de Orçamento')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-pencil"></i> Editar Usuário</h4>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user) }}">
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Função *</label>
                                    @if(auth()->guard('web')->user()->role === 'admin' && auth()->guard('web')->user()->id === $user->id)
                                        <!-- Admin não pode alterar sua própria função -->
                                        <select class="form-select" id="role" name="role" disabled>
                                            <option value="{{ $user->role }}" selected>
                                                @if($user->role === 'admin')
                                                    Admin
                                                @elseif($user->role === 'user')
                                                    Usuário
                                                @elseif($user->role === 'super_admin')
                                                    Super Admin
                                                @endif
                                            </option>
                                        </select>
                                        <input type="hidden" name="role" value="{{ $user->role }}">
                                        <small class="form-text text-muted">Você não pode alterar sua própria função.</small>
                                    @else
                                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                            <option value="">Selecione uma função</option>
                                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Usuário</option>
                                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                            @if(auth()->guard('web')->user()->role === 'super_admin')
                                                <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                            @endif
                                        </select>
                                    @endif
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if(auth()->guard('web')->user()->role === 'super_admin')
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_id" class="form-label">Empresa <span id="company-required" style="display: none;">*</span></label>
                                        <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id">
                                            <option value="">Selecione uma empresa</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                    {{ $company->fantasy_name ?? $company->corporate_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('company_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @else
                                {{-- Campo oculto para admins enviarem o company_id automaticamente --}}
                                <input type="hidden" name="company_id" value="{{ auth()->guard('web')->user()->company_id }}">
                            @endif
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $user->active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">
                                            Usuário ativo
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Usuários inativos não conseguem fazer login no sistema
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Atualizar Usuário
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
    
    // Controlar obrigatoriedade do campo empresa baseado na função (apenas para super_admin)
    const roleSelect = document.getElementById('role');
    const companySelect = document.getElementById('company_id');
    const companyRequired = document.getElementById('company-required');
    
    // Verificar se os elementos existem (campo empresa só aparece para super_admin)
    if (companySelect && companyRequired) {
        function toggleCompanyRequired() {
            const selectedRole = roleSelect.value;
            if (selectedRole === 'user' || selectedRole === 'admin') {
                companyRequired.style.display = 'inline';
            } else {
                companyRequired.style.display = 'none';
            }
        }
        
        roleSelect.addEventListener('change', toggleCompanyRequired);
        
        // Verificar estado inicial
        toggleCompanyRequired();
    }
});
</script>
@endsection
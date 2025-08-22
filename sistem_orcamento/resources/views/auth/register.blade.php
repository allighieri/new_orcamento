@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Criar Conta</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                    <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
                                </button>
                            </div>
                        </div>

                        @if(Auth::check() && Auth::user()->isSuperAdmin())
                            <div class="mb-3">
                                <label for="role" class="form-label">Nível de Acesso</label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuário</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Administrador</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-plus me-2"></i>Criar Conta
                            </button>
                        </div>
                    </form>

                    @if(!Auth::check())
                        <div class="text-center mt-3">
                            <p class="mb-0">Já tem uma conta? <a href="{{ route('login') }}">Faça login aqui</a></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle para o campo senha
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const passwordIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            passwordIcon.classList.remove('bi-eye');
            passwordIcon.classList.add('bi-eye-slash');
        } else {
            passwordField.type = 'password';
            passwordIcon.classList.remove('bi-eye-slash');
            passwordIcon.classList.add('bi-eye');
        }
    });
    
    // Toggle para o campo confirmar senha
    document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
        const passwordConfirmationField = document.getElementById('password_confirmation');
        const passwordConfirmationIcon = document.getElementById('togglePasswordConfirmationIcon');
        
        if (passwordConfirmationField.type === 'password') {
            passwordConfirmationField.type = 'text';
            passwordConfirmationIcon.classList.remove('bi-eye');
            passwordConfirmationIcon.classList.add('bi-eye-slash');
        } else {
            passwordConfirmationField.type = 'password';
            passwordConfirmationIcon.classList.remove('bi-eye-slash');
            passwordConfirmationIcon.classList.add('bi-eye');
        }
    });
</script>
@endpush
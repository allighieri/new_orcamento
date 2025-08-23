@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Editar Contato</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('contacts.update', $contact) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $contact->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cpf" class="form-label">CPF</label>
                                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                           id="cpf" name="cpf" value="{{ old('cpf', $contact->cpf) }}">
                                    @error('cpf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $contact->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $contact->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            @if(auth()->user()->role === 'super_admin')
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" 
                                            id="company_id" name="company_id">
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $contact->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->fantasy_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Selecione uma empresa OU um cliente</small>
                                </div>
                            </div>
                            @endif
                            <div class="{{ auth()->user()->role === 'super_admin' ? 'col-md-6' : 'col-md-12' }}">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Cliente</label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" 
                                            id="client_id" name="client_id">
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id', $contact->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->fantasy_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Selecione um cliente{{ auth()->user()->role === 'super_admin' ? ' OU uma empresa' : '' }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar Contato</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Máscara para CPF
    $('#cpf').mask('000.000.000-00');
    
    // Máscara para telefone
    $('#phone').mask('(00) 00000-0000');
});
</script>
@endpush
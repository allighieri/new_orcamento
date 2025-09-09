@extends('layouts.app')

@section('content')
<div class="container row mx-auto">
    
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                     <h5 class="mb-0">
                        <i class="bi bi-person-lines-fill"></i> Novo Contato
                     </h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('contacts.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cpf" class="form-label">CPF/CNPJ</label>
                                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                           id="cpf" name="cpf" value="{{ old('cpf') }}">
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
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            @if(auth()->guard('web')->user()->role === 'super_admin')
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" 
                                            id="company_id" name="company_id">
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
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
                            <div class="{{ auth()->guard('web')->user()->role === 'super_admin' ? 'col-md-6' : 'col-md-6' }}">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Cliente</label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" 
                                            id="client_id" name="client_id" >
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ (old('client_id') == $client->id || (isset($selectedClientId) && $selectedClientId == $client->id)) ? 'selected' : '' }}>
                                                {{ $client->fantasy_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Selecione um cliente {{ auth()->guard('web')->user()->role === 'super_admin' ? ' OU uma empresa' : '' }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('contacts.index') }}" class="btn btn-secondary me-md-2">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Converter campos de texto para maiúsculo durante a digitação (exceto email)
    $('#name').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    /// Máscara dinâmica para CPF/CNPJ
    var documentInput = $('#cpf');
    documentInput.on('input', function() {
        var cleanValue = $(this).val().replace(/\D/g, '');

        if (cleanValue.length > 11) {
            $(this).mask('00.000.000/0000-00', {clearIfNotMatch: true});
        } else {
            $(this).mask('000.000.000-009', {clearIfNotMatch: true});
        }
    }).trigger('input'); // O trigger('input') já aplica a máscara inicial
    
    // Máscara dinâmica para telefone/celular
    var phoneOptions = {
        onKeyPress: function(phone, e, field, options) {
            var masks = ['(00) 0000-00009', '(00) 00000-0000'];
            var mask = (phone.length > 14) ? masks[1] : masks[0];
            $('#phone').mask(mask, options);
        }
    };
    $('#phone').mask('(00) 0000-00009', phoneOptions);
});
</script>
@endpush
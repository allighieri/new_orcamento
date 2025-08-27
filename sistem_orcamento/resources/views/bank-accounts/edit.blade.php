@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-bank"></i> Editar Conta Bancária</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}">
                        @csrf
                        @method('PUT')
                        
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" 
                                                {{ (old('company_id') ?? $bankAccount->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->fantasy_name ?? $company->corporate_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipo *</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required onchange="toggleBankFields()">
                                        <option value="">Selecione o tipo</option>
                                        <option value="PIX" {{ (old('type') ?? $bankAccount->type) == 'PIX' ? 'selected' : '' }}>PIX</option>
                                        <option value="Conta" {{ (old('type') ?? $bankAccount->type) == 'Conta' ? 'selected' : '' }}>Conta</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="compe_id" class="form-label">Banco</label>
                                    <select class="form-select @error('compe_id') is-invalid @enderror" id="compe_id" name="compe_id">
                                        <option value="">Selecione um banco</option>
                                        @foreach($compes as $compe)
                                            <option value="{{ $compe->id }}" 
                                                {{ (old('compe_id') ?? $bankAccount->compe_id) == $compe->id ? 'selected' : '' }}>
                                                {{ $compe->code }} - {{ $compe->bank_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('compe_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="account-fields">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="branch" class="form-label">Agência</label>
                                    <input type="text" class="form-control @error('branch') is-invalid @enderror" 
                                           id="branch" name="branch" value="{{ old('branch') ?? $bankAccount->branch }}" placeholder="Ex: 1234">
                                    @error('branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account" class="form-label">Conta</label>
                                    <input type="text" class="form-control @error('account') is-invalid @enderror" 
                                           id="account" name="account" value="{{ old('account') ?? $bankAccount->account }}" placeholder="Ex: 12345-6">
                                    @error('account')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição *</label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                           id="description" name="description" value="{{ old('description') ?? $bankAccount->description }}" 
                                           placeholder="Ex: Conta principal, PIX para recebimentos, etc." required>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                               {{ (old('active') ?? $bankAccount->active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">
                                            Conta ativa
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('bank-accounts.index') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Atualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleBankFields() {
    const type = document.getElementById('type').value;
    const accountFields = document.getElementById('account-fields');
    const branchField = document.getElementById('branch');
    const accountField = document.getElementById('account');
    
    if (type === 'Conta') {
        accountFields.style.display = 'block';
        branchField.required = true;
        accountField.required = true;
    } else {
        accountFields.style.display = 'none';
        branchField.required = false;
        accountField.required = false;
        if (type === 'PIX') {
            branchField.value = '';
            accountField.value = '';
        }
    }
}

// Executar na inicialização da página
document.addEventListener('DOMContentLoaded', function() {
    toggleBankFields();
});
</script>

@endsection
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-bank"></i> Nova Conta Bancária</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('bank-accounts.store') }}">
                        @csrf
                        
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
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
                                        <option value="PIX" {{ old('type') == 'PIX' ? 'selected' : '' }}>PIX</option>
                                        <option value="Conta" {{ old('type') == 'Conta' ? 'selected' : '' }}>Conta</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bank_search" class="form-label">Banco</label>
                                    <input class="form-control @error('compe_id') is-invalid @enderror" 
                                        list="bankOptions" 
                                        id="bank_search" 
                                        name="bank_name" 
                                        placeholder="Digite o código ou nome do banco..." required>

                                    <input type="hidden" id="compe_id" name="compe_id" value="{{ old('compe_id') }}">

                                    <datalist id="bankOptions">
                                        {{-- As opções serão preenchidas via JavaScript ou PHP --}}
                                    </datalist>

                                    @error('compe_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="account-fields" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="branch" class="form-label">Agência</label>
                                    <input type="text" class="form-control @error('branch') is-invalid @enderror" 
                                           id="branch" name="branch" value="{{ old('branch') }}" placeholder="Ex: 1234" maxlength="10">

                                    @error('branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account" class="form-label">Conta</label>
                                    <input type="text" class="form-control @error('account') is-invalid @enderror" 
                                           id="account" name="account" value="{{ old('account') }}" placeholder="Ex: 12345-6" maxlength="20">
                                    @error('account')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="pix-type-field" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="key" class="form-label">Tipo de Chave PIX</label>
                                    <select class="form-select @error('key') is-invalid @enderror" id="key" name="key" onchange="togglePixKeyField()">
                                        <option value="">Selecione o tipo de chave</option>
                                        <option value="CPF" {{ old('key') == 'CPF' ? 'selected' : '' }}>CPF</option>
                                        <option value="CNPJ" {{ old('key') == 'CNPJ' ? 'selected' : '' }}>CNPJ</option>
                                        <option value="email" {{ old('key') == 'email' ? 'selected' : '' }}>E-mail</option>
                                        <option value="telefone" {{ old('key') == 'telefone' ? 'selected' : '' }}>Telefone</option>
                                    </select>
                                    @error('key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="pix-key-field" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="key_desc" class="form-label">Chave PIX</label>
                                    <input type="text" class="form-control @error('key_desc') is-invalid @enderror" 
                                           id="key_desc" name="key_desc" value="{{ old('key_desc') }}" 
                                           placeholder="">
                                    @error('key_desc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                           id="description" name="description" value="{{ old('description') }}" 
                                           placeholder="Ex: Conta principal, PIX para recebimentos, etc.">
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
                                        <input type="hidden" name="active" value="0">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                               {{ old('active', true) ? 'checked' : '' }}>
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
                                <i class="bi bi-check-circle"></i> Salvar
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
    const pixTypeField = document.getElementById('pix-type-field');
    const pixKeyField = document.getElementById('pix-key-field');
    const branchField = document.getElementById('branch');
    const accountField = document.getElementById('account');
    const keyField = document.getElementById('key');
    const keyDescField = document.getElementById('key_desc');
    
    if (type === 'Conta') {
        accountFields.style.display = 'block';
        pixTypeField.style.display = 'none';
        pixKeyField.style.display = 'none';
        branchField.required = true;
        accountField.required = true;
        keyField.required = false;
        keyDescField.required = false;
        // Limpar campos PIX
        keyField.value = '';
        keyDescField.value = '';
    } else if (type === 'PIX') {
        accountFields.style.display = 'none';
        pixTypeField.style.display = 'block';
        // pixKeyField permanece oculto até selecionar tipo de chave
        branchField.required = false;
        accountField.required = false;
        keyField.required = true;
        keyDescField.required = true;
        // Limpar campos de conta
        branchField.value = '';
        accountField.value = '';
    } else {
        accountFields.style.display = 'none';
        pixTypeField.style.display = 'none';
        pixKeyField.style.display = 'none';
        branchField.required = false;
        accountField.required = false;
        keyField.required = false;
        keyDescField.required = false;
        // Limpar todos os campos
        branchField.value = '';
        accountField.value = '';
        keyField.value = '';
        keyDescField.value = '';
    }
}

function togglePixKeyField() {
    const keyType = document.getElementById('key').value;
    const pixKeyField = document.getElementById('pix-key-field');
    const keyDescInput = document.getElementById('key_desc');
    
    // Limpar o campo ao trocar o tipo
    keyDescInput.value = '';
    
    if (keyType) {
        pixKeyField.style.display = 'block';
        
        // Remover máscara anterior
        keyDescInput.removeAttribute('data-mask');
        keyDescInput.oninput = null;
        
        // Aplicar máscara e placeholder baseado no tipo
        switch(keyType) {
            case 'CPF':
                keyDescInput.placeholder = '999.999.999-99';
                keyDescInput.maxLength = 14;
                keyDescInput.oninput = function() {
                    this.value = this.value.replace(/\D/g, '')
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                };
                break;
            case 'CNPJ':
                keyDescInput.placeholder = '99.999.999/9999-99';
                keyDescInput.maxLength = 18;
                keyDescInput.oninput = function() {
                    this.value = this.value.replace(/\D/g, '')
                        .replace(/(\d{2})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d)/, '$1/$2')
                        .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
                };
                break;
            case 'telefone':
                keyDescInput.placeholder = '99 99999-9999';
                keyDescInput.maxLength = 13;
                keyDescInput.oninput = function() {
                    this.value = this.value.replace(/\D/g, '')
                        .replace(/(\d{2})(\d)/, '$1 $2')
                        .replace(/(\d{5})(\d{1,4})$/, '$1-$2');
                };
                break;
            case 'email':
                keyDescInput.placeholder = 'email@email.com';
                keyDescInput.maxLength = 255;
                keyDescInput.oninput = null; // Sem máscara para email
                break;
        }
    } else {
        pixKeyField.style.display = 'none';
        keyDescInput.value = '';
    }
}

// Autocomplete para bancos
document.addEventListener('DOMContentLoaded', function() {
    const bankSearch = document.getElementById('bank_search');
    const compeId = document.getElementById('compe_id');
    const bankOptions = document.getElementById('bankOptions');
    let searchTimeout;
    let banksData = [];

    bankSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        // Verificar se o usuário selecionou uma opção do datalist
        const selectedBank = banksData.find(bank => 
            bankSearch.value === `${bank.code} - ${bank.bank_name}`
        );
        
        if (selectedBank) {
            compeId.value = selectedBank.id;
            return;
        }
        
        // Limpar compe_id se não há correspondência exata
        compeId.value = '';
        
        if (query.length < 2) {
            bankOptions.innerHTML = '';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('compes.autocomplete') }}?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    bankOptions.innerHTML = '';
                    banksData = data;
                    
                    if (data.length > 0) {
                        data.forEach(bank => {
                            const option = document.createElement('option');
                            option.value = `${bank.code} - ${bank.bank_name}`;
                            bankOptions.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro na busca:', error);
                    bankOptions.innerHTML = '';
                });
        }, 300);
    });

    // Executar na inicialização da página
    toggleBankFields();
});
</script>

@endsection
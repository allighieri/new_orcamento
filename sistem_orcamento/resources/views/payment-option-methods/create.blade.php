@extends('layouts.app')

@section('title', 'Nova Opção de Método de Pagamento - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Nova Opção de Método de Pagamento</h4>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('payment-option-methods.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="method" class="form-label">Nome do Método *</label>
                                <input type="text" 
                                       class="form-control @error('method') is-invalid @enderror" 
                                       id="method" 
                                       name="method" 
                                       value="{{ old('method') }}" 
                                       required 
                                       maxlength="100"
                                       placeholder="Ex: Cartão de Crédito, PIX, Boleto...">
                                @error('method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Digite o nome do método de pagamento.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="active" class="form-label">Status *</label>
                                <select class="form-select @error('active') is-invalid @enderror" 
                                        id="active" name="active" required>
                                    <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Defina se o método estará disponível para uso.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3" 
                                          maxlength="500"
                                          placeholder="Descrição opcional do método de pagamento...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Descrição opcional para o método de pagamento (máximo 500 caracteres).</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('payment-option-methods.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar Método
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Validação em tempo real
document.getElementById('method').addEventListener('input', function() {
    const value = this.value.trim();
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (value.length < 2) {
        this.classList.add('is-invalid');
        submitBtn.disabled = true;
    } else {
        this.classList.remove('is-invalid');
        submitBtn.disabled = false;
    }
});

// Contador de caracteres para descrição
document.getElementById('description').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let helpText = this.parentNode.querySelector('.form-text');
    helpText.textContent = `Descrição opcional para o método de pagamento (${remaining} caracteres restantes).`;
    
    if (remaining < 50) {
        helpText.classList.add('text-warning');
    } else {
        helpText.classList.remove('text-warning');
    }
});
</script>

@endsection
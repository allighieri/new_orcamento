@extends('layouts.app')

@section('title', 'Editar Opção de Método de Pagamento - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-pencil"></i> Editar Opção de Método de Pagamento
            </h1>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Informações do Método</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-option-methods.update', $paymentOptionMethod) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="method" class="form-label">Nome do Método <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('method') is-invalid @enderror" 
                                       id="method" 
                                       name="method" 
                                       value="{{ old('method', $paymentOptionMethod->method) }}" 
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
                                <label for="active" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('active') is-invalid @enderror" 
                                        id="active" name="active" required>
                                    <option value="1" {{ old('active', $paymentOptionMethod->active) == '1' ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('active', $paymentOptionMethod->active) == '0' ? 'selected' : '' }}>Inativo</option>
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
                                          placeholder="Descrição opcional do método de pagamento...">{{ old('description', $paymentOptionMethod->description) }}</textarea>
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
                            <i class="bi bi-check-circle"></i> Atualizar Método
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações Adicionais</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">ID do Método:</small>
                    <div class="fw-bold">#{{ $paymentOptionMethod->id }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Criado em:</small>
                    <div class="fw-bold">{{ $paymentOptionMethod->created_at->format('d/m/Y H:i') }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Última atualização:</small>
                    <div class="fw-bold">{{ $paymentOptionMethod->updated_at->format('d/m/Y H:i') }}</div>
                </div>
                
                @if($paymentOptionMethod->paymentMethods->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">Métodos vinculados:</small>
                    <div class="fw-bold text-info">{{ $paymentOptionMethod->paymentMethods->count() }} método(s)</div>
                    <small class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Este método possui vínculos ativos
                    </small>
                </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Dicas</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Use nomes claros e descritivos
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        A descrição ajuda na identificação
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success"></i>
                        Métodos inativos não aparecem nas opções
                    </li>
                </ul>
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
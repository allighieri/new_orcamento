@extends('layouts.app')

@section('title', 'Editar Método de Pagamento - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-pencil"></i> Editar Método de Pagamento
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
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Informações do Método</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-methods.update', $paymentMethod) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Método de Pagamento -->
                    <div class="mb-3">
                        <label for="payment_option_method_id" class="form-label">Método de Pagamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_option_method_id') is-invalid @enderror" 
                                id="payment_option_method_id" 
                                name="payment_option_method_id" 
                                required>
                            <option value="">Selecione um método...</option>
                            @foreach($paymentOptionMethods as $option)
                                <option value="{{ $option->id }}" 
                                        {{ old('payment_option_method_id', $paymentMethod->payment_option_method_id) == $option->id ? 'selected' : '' }}>
                                    {{ $option->method }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_option_method_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Tipo de método de pagamento que será exibido nos orçamentos.
                        </div>
                    </div>
                    
                    
                <div class="row">
                    <div class="col-md-6">
                        <!-- Permite Parcelamento -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('allows_installments') is-invalid @enderror" 
                                    type="checkbox" 
                                    role="switch" 
                                    id="allows_installments" 
                                    name="allows_installments" 
                                    value="1" 
                                    {{ old('allows_installments', $paymentMethod->allows_installments) ? 'checked' : '' }}
                                    onchange="toggleInstallments()">
                                <label class="form-check-label" for="allows_installments">
                                    <strong>Permite Parcelamento</strong>
                                </label>
                                @error('allows_installments')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-text">
                                Marque se este método permite pagamento parcelado.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Máximo de Parcelas -->
                        <div class="mb-3" id="max_installments_group" style="{{ old('allows_installments', $paymentMethod->allows_installments) ? '' : 'display: none;' }}">
                            <label for="max_installments" class="form-label">Máximo de Parcelas <span class="text-danger">*</span></label>
                            <input type="number" 
                                class="form-control @error('max_installments') is-invalid @enderror" 
                                id="max_installments" 
                                name="max_installments" 
                                value="{{ old('max_installments', $paymentMethod->max_installments) }}" 
                                min="1" 
                                max="60"
                                placeholder="Ex: 12">
                            @error('max_installments')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                Número máximo de parcelas permitidas (1 a 60).
                            </div>
                        </div>
                    </div>    
                </div>
                    
                    

                    <!-- Status Ativo -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Método Ativo</strong>
                            </label>
                            @error('is_active')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-text">
                            Apenas métodos ativos ficam disponíveis para seleção nos orçamentos.
                        </div>
                    </div>

                    <hr class="my-4"/>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        
                        <a href="{{ route('payment-methods.show', $paymentMethod) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Informações Adicionais -->
    <div class="col-md-4">
        <!-- Informações do Método -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">ID:</small>
                    <strong>{{ $paymentMethod->id }}</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Slug:</small>
                    <code>{{ $paymentMethod->slug }}</code>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Tipo:</small>
                    @if($paymentMethod->is_global)
                        <span class="badge bg-info">Global</span>
                    @else
                        <span class="badge bg-secondary">Empresa</span>
                    @endif
                </div>
                <div class="mb-2">
                    <small class="text-muted">Criado em:</small>
                    <strong>{{ $paymentMethod->created_at->format('d/m/Y H:i') }}</strong>
                </div>
                <div>
                    <small class="text-muted">Atualizado em:</small>
                    <strong>{{ $paymentMethod->updated_at->format('d/m/Y H:i') }}</strong>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas de Uso -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Uso</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-0">{{ $paymentMethod->budget_payments_count }}</h4>
                <small class="text-muted">Orçamentos usando este método</small>
                
                @if($paymentMethod->budget_payments_count > 0)
                    <div class="alert alert-info mt-3 p-2">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Este método está sendo usado e não pode ser excluído.
                        </small>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Dicas -->
        <div class="card mt-3">
            <div class="card-header">
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
                        Métodos inativos não aparecem nos orçamentos
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Configure o parcelamento conforme necessário
                    </li>
                    @if($paymentMethod->is_global)
                        <li class="mb-0">
                            <i class="bi bi-info-circle text-info"></i>
                            Métodos globais ficam disponíveis para todas as empresas
                        </li>
                    @else
                        <li class="mb-0">
                            <i class="bi bi-info-circle text-info"></i>
                            Este método é específico da sua empresa
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function toggleInstallments() {
    const checkbox = document.getElementById('allows_installments');
    const installmentsGroup = document.getElementById('max_installments_group');
    const maxInstallmentsInput = document.getElementById('max_installments');
    
    if (checkbox.checked) {
        installmentsGroup.style.display = 'block';
        maxInstallmentsInput.required = true;
        if (!maxInstallmentsInput.value) {
            maxInstallmentsInput.value = '1';
        }
    } else {
        installmentsGroup.style.display = 'none';
        maxInstallmentsInput.required = false;
        maxInstallmentsInput.value = '1';
    }
}

// Inicializar o estado do formulário
document.addEventListener('DOMContentLoaded', function() {
    toggleInstallments();
});
</script>

@endsection
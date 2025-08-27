@extends('layouts.app')

@section('title', 'Novo Método de Pagamento - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-credit-card"></i> Novo Método de Pagamento</h4>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('payment-methods.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="payment_option_method_id" class="form-label">Método de Pagamento *</label>
                                <select class="form-select @error('payment_option_method_id') is-invalid @enderror" 
                                        id="payment_option_method_id" name="payment_option_method_id" required>
                                    <option value="">Selecione um método...</option>
                                    @foreach($paymentOptionMethods as $option)
                                        <option value="{{ $option->id }}" {{ old('payment_option_method_id') == $option->id ? 'selected' : '' }}>
                                            {{ $option->method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_option_method_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Selecione o tipo de método de pagamento.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Método Ativo
                                    </label>
                                </div>
                                <div class="form-text">Métodos inativos não aparecerão nos orçamentos.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Configurações de Parcelamento</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="allows_installments" name="allows_installments" 
                                           {{ old('allows_installments') ? 'checked' : '' }}
                                           onchange="toggleInstallments()">
                                    <label class="form-check-label" for="allows_installments">
                                        Permite Parcelamento
                                    </label>
                                </div>
                                <div class="form-text">Marque se este método permite dividir o pagamento em parcelas.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_installments" class="form-label">Máximo de Parcelas</label>
                                <select class="form-select @error('max_installments') is-invalid @enderror" 
                                        id="max_installments" name="max_installments" disabled>
                                    <option value="1" {{ old('max_installments', 1) == 1 ? 'selected' : '' }}>1x (À vista)</option>
                                    @for($i = 2; $i <= 60; $i++)
                                        <option value="{{ $i }}" {{ old('max_installments') == $i ? 'selected' : '' }}>{{ $i }}x</option>
                                    @endfor
                                </select>
                                @error('max_installments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Número máximo de parcelas permitidas para este método.</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações sobre o método -->
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Informações Importantes</h6>
                        <ul class="mb-0">
                            <li>Métodos inativos não aparecerão como opção ao criar orçamentos.</li>
                            <li>Se o método não permitir parcelamento, será considerado apenas pagamento à vista.</li>
                            <li>Você pode editar essas configurações a qualquer momento.</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">
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
function toggleInstallments() {
    const allowsInstallments = document.getElementById('allows_installments').checked;
    const maxInstallmentsSelect = document.getElementById('max_installments');
    
    if (allowsInstallments) {
        maxInstallmentsSelect.disabled = false;
        if (maxInstallmentsSelect.value === '1') {
            maxInstallmentsSelect.value = '12'; // Padrão para parcelamento
        }
    } else {
        maxInstallmentsSelect.disabled = true;
        maxInstallmentsSelect.value = '1'; // Forçar à vista
    }
}

// Executar ao carregar a página para manter o estado correto
document.addEventListener('DOMContentLoaded', function() {
    toggleInstallments();
});
</script>

@endsection
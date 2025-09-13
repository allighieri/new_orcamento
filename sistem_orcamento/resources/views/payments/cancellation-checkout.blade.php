@extends('layouts.app')

@section('title', 'Mudança de Plano - Taxa de Cancelamento')

@section('content')
<div class="container mx-auto m-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Mudança de Plano</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.select-plan') }}">Escolher Plano</a></li>
                    <li class="breadcrumb-item active">Taxa de Cancelamento</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Resumo da Mudança -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">Mudança: {{ $activeSubscription->plan->name }} Anual → {{ $plan->name }} {{ $period === 'monthly' ? 'Mensal' : 'Anual' }}</h5>
                            <p class="text-muted mb-2">Para mudar {{ $period === 'monthly' ? 'para o plano mensal' : 'entre planos anuais' }}, é necessário pagar a taxa de cancelamento antecipado do plano anual.</p>
                            <div class="alert alert-info mb-0">
                                <h6><i class="mdi mdi-information"></i> Detalhes do Pagamento:</h6>
                                <ul class="mb-0">
                                    <li><strong>Cancelamento:</strong> R$ {{ number_format($cancellationFee, 2, ',', '.') }}</li>
                                    <li><strong>{{ $period === 'monthly' ? 'Primeiro mês do plano mensal' : 'Primeira parcela do novo plano' }}:</strong> R$ {{ number_format($planPrice, 2, ',', '.') }}</li>
                                    <li><strong>Meses restantes no plano anual:</strong> {{ $activeSubscription->getMonthsRemaining() }} meses</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h4 class="mb-0 text-primary">R$ {{ number_format($totalAmount, 2, ',', '.') }}</h4>
                            <small class="text-muted">Total a pagar</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métodos de Pagamento -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Escolha a forma de pagamento</h5>
                    
                    <!-- Tabs de Pagamento -->
                    <ul class="nav nav-pills nav-justified mb-4" id="paymentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pix-tab" data-bs-toggle="pill" data-bs-target="#pix" type="button" role="tab">
                                <i class="mdi mdi-qrcode me-2"></i>PIX
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="credit-card-tab" data-bs-toggle="pill" data-bs-target="#credit-card" type="button" role="tab">
                                <i class="mdi mdi-credit-card me-2"></i>Cartão de Crédito
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="paymentTabsContent">
                        <!-- PIX -->
                        <div class="tab-pane fade show active" id="pix" role="tabpanel">
                            <form id="pixForm" action="{{ route('payments.process-cancellation-fee') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="payment_type" value="pix">
                                <input type="hidden" name="period" value="{{ $period }}">
                                
                                <div class="text-center mb-4">
                                    <i class="mdi mdi-qrcode display-4 text-success"></i>
                                    <h5 class="mt-3">Pagamento via PIX</h5>
                                    <p class="text-muted">Pagamento instantâneo e seguro</p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="{{ old('name', $company->corporate_name) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">E-mail *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="{{ old('email', $company->email) }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cpf_cnpj" class="form-label">CPF/CNPJ *</label>
                                            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" 
                                                   value="{{ old('cpf_cnpj', $company->document_number) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Telefone *</label>
                                            <input type="text" class="form-control" id="phone" name="phone" 
                                                   value="{{ old('phone', $company->phone) }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg" id="pixSubmitBtn">
                                        <i class="mdi mdi-qrcode me-2"></i>
                                        Gerar PIX - R$ {{ number_format($totalAmount, 2, ',', '.') }}
                                    </button>
                                    <a href="{{ route('payments.select-plan') }}" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left me-2"></i>Voltar
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Cartão de Crédito -->
                        <div class="tab-pane fade" id="credit-card" role="tabpanel">
                            <form id="creditCardForm" action="{{ route('payments.process-cancellation-fee') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="payment_type" value="credit_card">
                                <input type="hidden" name="period" value="{{ $period }}">
                                
                                <div class="text-center mb-4">
                                    <i class="mdi mdi-credit-card display-4 text-primary"></i>
                                    <h5 class="mt-3">Pagamento com Cartão</h5>
                                    <p class="text-muted">Pagamento seguro e processado instantaneamente</p>
                                </div>
                                
                                <!-- Dados do Titular -->
                                <h6 class="mb-3">Dados do Titular</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_name" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="card_name" name="name" 
                                                   value="{{ old('name', $company->corporate_name) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_email" class="form-label">E-mail *</label>
                                            <input type="email" class="form-control" id="card_email" name="email" 
                                                   value="{{ old('email', $company->email) }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_cpf_cnpj" class="form-label">CPF/CNPJ *</label>
                                            <input type="text" class="form-control" id="card_cpf_cnpj" name="cpf_cnpj" 
                                                   value="{{ old('cpf_cnpj', $company->document_number) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_phone" class="form-label">Telefone *</label>
                                            <input type="text" class="form-control" id="card_phone" name="phone" 
                                                   value="{{ old('phone', $company->phone) }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dados do Cartão -->
                                <h6 class="mb-3 mt-4">Dados do Cartão</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_holder_name" class="form-label">Nome no Cartão *</label>
                                            <input type="text" class="form-control" id="card_holder_name" name="card_holder_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_number" class="form-label">Número do Cartão *</label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                                   placeholder="0000 0000 0000 0000" maxlength="19" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="card_expiry_month" class="form-label">Mês *</label>
                                            <select class="form-select" id="card_expiry_month" name="card_expiry_month" required>
                                                <option value="">Mês</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="card_expiry_year" class="form-label">Ano *</label>
                                            <select class="form-select" id="card_expiry_year" name="card_expiry_year" required>
                                                <option value="">Ano</option>
                                                @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="card_ccv" class="form-label">CVV *</label>
                                            <input type="text" class="form-control" id="card_ccv" name="card_ccv" 
                                                   placeholder="123" maxlength="4" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg" id="cardSubmitBtn">
                                        <i class="mdi mdi-credit-card me-2"></i>
                                        Pagar com Cartão - R$ {{ number_format($totalAmount, 2, ',', '.') }}
                                    </button>
                                    <a href="{{ route('payments.select-plan') }}" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left me-2"></i>Voltar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <h5>Processando pagamento...</h5>
                <p class="text-muted mb-0">Aguarde enquanto processamos sua solicitação.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Máscara para CPF/CNPJ
    $('#cpf_cnpj, #card_cpf_cnpj').mask('00.000.000/0000-00', {
        reverse: true,
        placeholder: '00.000.000/0000-00'
    });
    
    // Máscara para telefone
    $('#phone, #card_phone').mask('(00) 00000-0000');
    
    // Máscara para número do cartão
    $('#card_number').mask('0000 0000 0000 0000');
    
    // Submissão do formulário PIX
    $('#pixForm').on('submit', function(e) {
        e.preventDefault();
        
        var submitButton = $('#pixSubmitBtn');
        submitButton.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Processando...');
        
        $('#loadingModal').modal('show');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.success) {
                    window.location.href = response.redirect_url;
                } else {
                    submitButton.prop('disabled', false).html('<i class="mdi mdi-qrcode me-2"></i>Gerar PIX - R$ {{ number_format($totalAmount, 2, ",", ".") }}');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro no Pagamento',
                        text: response.message || 'Erro ao processar pagamento. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                submitButton.prop('disabled', false).html('<i class="mdi mdi-qrcode me-2"></i>Gerar PIX - R$ {{ number_format($totalAmount, 2, ",", ".") }}');
                
                var errorMsg = 'Erro ao processar pagamento. Tente novamente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro no Pagamento',
                    text: errorMsg,
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    
    // Submissão do formulário de cartão
    $('#creditCardForm').on('submit', function(e) {
        e.preventDefault();
        
        var submitButton = $('#cardSubmitBtn');
        submitButton.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Processando...');
        
        $('#loadingModal').modal('show');
        
        // Submeter o formulário
        this.submit();
    });
});
</script>
@endpush
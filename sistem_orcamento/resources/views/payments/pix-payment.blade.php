@extends('layouts.app')

@section('title', 'Pagamento PIX')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Pagamento PIX - Plano {{ $payment->plan->name }}</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.select-plan') }}">Escolher Plano</a></li>
                    <li class="breadcrumb-item active">Pagamento PIX</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="payment-status mb-4">
                        <div class="status-icon mb-3">
                            <i class="bi bi-qr-code display-1 text-warning"></i>
                        </div>
                        <h3 class="text-dark">PIX Gerado com Sucesso!</h3>
                        <p class="text-muted">Escaneie o QR Code ou copie o código PIX para realizar o pagamento</p>
                    </div>

                    <!-- QR Code -->
                    <div class="qr-code-container mb-4">
                        <div class="qr-code-wrapper">
                            @if(isset($qrCodeData) && isset($qrCodeData['encodedImage']))
                                <img src="data:image/png;base64,{{ $qrCodeData['encodedImage'] }}" alt="QR Code PIX" class="qr-code-image">
                            @else
                                <div class="qr-code-placeholder">
                                    <i class="bi bi-qr-code display-4 text-muted"></i>
                                    <p class="text-muted mt-2">QR Code não disponível</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Código PIX Copia e Cola -->
                    @if(isset($qrCodeData) && isset($qrCodeData['payload']))
                    <div class="pix-code-section mb-4">
                        <h5 class="mb-3">Código PIX Copia e Cola</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" id="pixCode" value="{{ $qrCodeData['payload'] }}" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copyPixCode()">
                                <i class="bi bi-clipboard me-1"></i>Copiar
                            </button>
                        </div>
                        <small class="text-muted">Cole este código no seu app do banco para fazer o pagamento</small>
                    </div>
                    @endif

                    <!-- Informações do Pagamento -->
                    <div class="payment-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong>Valor:</strong>
                                    <span class="text-success">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong>Vencimento:</strong>
                                    <span>{{ $payment->due_date->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong>Status:</strong>
                                    <span class="badge bg-warning">{{ ucfirst($payment->status) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong>ID do Pagamento:</strong>
                                    <span class="text-muted">#{{ $payment->id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status de Verificação -->
                    <div class="payment-verification mt-4">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Verificando pagamento...</strong>
                            <div class="mt-2">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                Aguardando confirmação do pagamento
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="payment-actions mt-4">
                        <button class="btn btn-primary me-2" onclick="checkPaymentStatus()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Verificar Status
                        </button>
                        <a href="{{ route('payments.select-plan') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Voltar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Instruções -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-question-circle me-2"></i>Como pagar com PIX
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Pelo QR Code:</h6>
                            <ol class="small">
                                <li>Abra o app do seu banco</li>
                                <li>Procure pela opção PIX</li>
                                <li>Escolha "Ler QR Code"</li>
                                <li>Aponte a câmera para o código acima</li>
                                <li>Confirme o pagamento</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6>Pelo Código Copia e Cola:</h6>
                            <ol class="small">
                                <li>Copie o código PIX acima</li>
                                <li>Abra o app do seu banco</li>
                                <li>Procure pela opção PIX</li>
                                <li>Escolha "Colar código"</li>
                                <li>Cole o código e confirme</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sucesso -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="mdi mdi-check-circle display-1 text-success mb-3"></i>
                <h4 class="text-success">Pagamento Confirmado!</h4>
                <p class="text-muted">Seu plano foi ativado com sucesso.</p>
                <button type="button" class="btn btn-success" onclick="window.location.href='{{ route('payments.select-plan') }}'">
                    Voltar aos Planos
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let checkInterval;

$(document).ready(function() {
    // Verificar status imediatamente ao carregar a página
    checkPaymentStatus();
    
    // Verificar status automaticamente a cada 2 segundos para ser mais responsivo
    checkInterval = setInterval(checkPaymentStatus, 2000);
    
    // Parar verificação após 30 minutos
    setTimeout(function() {
        clearInterval(checkInterval);
        console.log('Verificação automática de pagamento interrompida após 30 minutos');
    }, 30 * 60 * 1000);
});

function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    pixCode.setSelectionRange(0, 99999);
    
    navigator.clipboard.writeText(pixCode.value).then(function() {
        // Mostrar feedback visual
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check me-1"></i>Copiado!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    });
}

function checkPaymentStatus() {
    $.ajax({
        url: '{{ route('payments.check-status', $payment) }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Status do pagamento:', response);
            
            // Log adicional para debug
            if (response.webhook_processed) {
                console.log('✅ Pagamento processado via webhook');
            } else if (response.webhook_approved) {
                console.log('✅ Webhook sinalizou aprovação');
            } else if (response.api_checked) {
                console.log('🔄 Status verificado via API Asaas');
            }
            
            if (response.success && (response.is_paid || response.status === 'RECEIVED' || response.status === 'CONFIRMED')) {
                clearInterval(checkInterval);
                console.log('🎉 Pagamento confirmado! Atualizando interface...');
                
                // Atualizar interface com animação
                $('.payment-verification').fadeOut(300, function() {
                    $(this).html(`
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Pagamento confirmado!</strong>
                            <div class="mt-2">Status: ${response.status_text || 'Pago'}</div>
                            <div class="mt-2">Redirecionando para suas assinaturas...</div>
                        </div>
                    `).fadeIn(300);
                });
                
                // Redirecionar após 2 segundos para dar tempo de ver a confirmação
                setTimeout(function() {
                    console.log('🔄 Redirecionando para assinaturas...');
                    window.location.href = '{{ route('payments.select-plan') }}';
                }, 2000);
            } else if (response.status === 'OVERDUE' || response.status === 'overdue') {
                clearInterval(checkInterval);
                console.log('⚠️ Pagamento vencido');
                
                $('.payment-verification').fadeOut(300, function() {
                    $(this).html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle me-2"></i>
                            <strong>Pagamento vencido</strong>
                            <div class="mt-2">Este PIX expirou. Gere um novo pagamento.</div>
                        </div>
                    `).fadeIn(300);
                });
            } else {
                // Status ainda pendente, continuar verificando
                console.log(`⏳ Status atual: ${response.status} (${response.status_text})`);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Erro ao verificar status do pagamento:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
        }
    });
}
</script>
@endpush

@push('styles')
<style>
.qr-code-wrapper {
    display: inline-block;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 3px solid #f8f9fa;
}

.qr-code-image {
    max-width: 250px;
    height: auto;
    border-radius: 10px;
}

.qr-code-placeholder {
    width: 250px;
    height: 250px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 10px;
}

.pix-code-section .form-control {
    font-family: monospace;
    font-size: 12px;
    background: #f8f9fa;
}

.info-item {
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
}

.payment-verification .alert {
    border-radius: 10px;
}

.card {
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .qr-code-image {
        max-width: 200px;
    }
    
    .qr-code-placeholder {
        width: 200px;
        height: 200px;
    }
}
</style>
@endpush
@php
    // Definição única das traduções de status para uso em todo o arquivo
    $statusTranslations = [
        'PENDING' => 'Pendente',
        'RECEIVED' => 'Pago',
        'CONFIRMED' => 'Pago',
        'OVERDUE' => 'Vencido',
        'REFUNDED' => 'Reembolsado',
        'RECEIVED_IN_CASH' => 'Pago em Dinheiro',
        'REFUND_REQUESTED' => 'Reembolso Solicitado',
        'CHARGEBACK_REQUESTED' => 'Chargeback Solicitado',
        'CHARGEBACK_DISPUTE' => 'Disputa de Chargeback',
        'AWAITING_CHARGEBACK_REVERSAL' => 'Aguardando Reversão de Chargeback'
    ];
    $currentStatus = $payment->status;
    $translatedStatus = $statusTranslations[$currentStatus] ?? ucfirst($currentStatus);
@endphp

<!-- Cabeçalho Azul -->
<div class="card p-4 my-3 bg-primary text-white">
    <div class="header-content">
        <div class="customer-info">
            <h4 class="customer-name">Orça Já!</h4>
            <div class="customer-details">
                {{-- <div>(61) 99461-9520</div> --}}
                <div>agenciaolhardigital@gmail.com</div>
                <div>CPF: 975.........20</div>
                <div>Brasília, DF</div>
            </div>
        </div>
        <div class="payment-status-header">
            @if($payment->isPaid())
                <i class="bi bi-check-circle-fill status-icon-header text-success"></i>
                <span class="status-text">{{ $translatedStatus }}</span>
            @elseif($payment->status === 'PENDING')
                <i class="bi bi-clock-fill status-icon-header text-white"></i>
                <span class="status-text">{{ $translatedStatus }}</span>
            @elseif($payment->status === 'OVERDUE')
                <i class="bi bi-exclamation-triangle-fill status-icon-header text-white"></i>
                <span class="status-text">{{ $translatedStatus }}</span>
            @else
                <i class="bi bi-info-circle-fill status-icon-header text-white"></i>
                <span class="status-text">{{ $translatedStatus }}</span>
            @endif
            {{-- 
            <div class="text-end mt-2">
                <a href="#" class="btn btn-link text-white p-0" onclick="reportProblem()">Reportar problema</a>
            </div>
             --}}
        </div>
    </div>
</div>

<!-- Dados da Fatura -->
<div class="card mt-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados da fatura - {{ $payment->asaas_payment_id ?? $payment->id }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="info-section">
                    <label class="info-label">Valor total</label>
                    <div class="info-value text-success fw-bold fs-4">R$ {{ number_format($payment->amount, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-section">
                    <label class="info-label">Data de vencimento</label>
                    <div class="info-value">{{ $payment->due_date->format('d/m/Y') }}</div>
                    <small class="text-muted">({{ $payment->due_date->diffForHumans() }})</small>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <label class="info-label">Descrição</label>
            <div class="info-value">
                @if($payment->plan)
                    Assinatura Mensal do plano {{ $payment->plan->name }} - KARAOKÊ CLUBE
                @else
                    Orçamentos Extras - KARAOKÊ CLUBE
                @endif
                @if($payment->extra_budgets_quantity)
                    (+ {{ $payment->extra_budgets_quantity }} orçamentos extras)
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Dados do Comprador -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados do comprador</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="info-section">
                    <label class="info-label">Nome</label>
                    <div class="info-value">{{ $payment->user->name ?? 'KARAOKÊ CLUBE' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-section">
                    <label class="info-label">Email</label>
                    <div class="info-value">{{ $payment->user->email ?? 'agenciabardigital@gmail.com' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forma de Pagamento -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Forma de pagamento</h5>
    </div>
    <div class="card-body">
        @if($payment->billing_type === 'PIX')
            <div class="pix-payment-section">
                <div class="pix-header mb-3">
                    <span class="pix-label">Pix</span>
                </div>
                
                @if((strtoupper($payment->status) === 'PENDING' || $payment->status === 'pending') && isset($qrCodeData) && (isset($qrCodeData['encodedImage']) || isset($qrCodeData['payload'])))
                    <div class="row">
                        <!-- QR Code -->
                        @if(isset($qrCodeData['encodedImage']))
                        <div class="col-md-6 text-center">
                            <div class="qr-code-container mb-3">
                                <img src="data:image/png;base64,{{ $qrCodeData['encodedImage'] }}" alt="QR Code PIX" class="qr-code-image" style="max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                            <p class="text-muted small">Abra seu APP de pagamentos e faça a leitura do QR Code acima para efetuar o pagamento de forma rápida e segura.</p>
                        </div>
                        @endif
                        
                        <!-- Código Copia e Cola -->
                        @if(isset($qrCodeData['payload']))
                        <div class="col-md-6">
                            <div class="pix-code-section">
                                <label class="info-label mb-2">Código Pix copia e cola</label>
                                <div class="pix-code-container">
                                    <textarea class="form-control pix-code-textarea" id="pixCodeModal" readonly>{{ $qrCodeData['payload'] }}</textarea>
                                    <button class="btn btn-primary mt-2 w-100" type="button" onclick="copyPixCodeModal()">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block">Cole este código no seu app do banco para fazer o pagamento</small>
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        @if($payment->isPaid())
                            Pagamento realizado via PIX
                        @else
                            QR Code PIX não disponível no momento
                        @endif
                    </div>
                @endif
            </div>
        @elseif($payment->billing_type === 'CREDIT_CARD')
            <div class="credit-card-section">
                <span class="badge bg-primary fs-6"><i class="bi bi-credit-card me-1"></i>Cartão de Crédito</span>
            </div>
        @else
            <div class="other-payment-section">
                <span class="badge bg-secondary fs-6">{{ $payment->billing_type }}</span>
            </div>
        @endif
    </div>
</div>

<!-- Timeline do Pagamento -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Histórico do Pagamento</h5>
    </div>
    <div class="card-body">
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-marker bg-primary"></div>
                <div class="timeline-content">
                    <h6 class="timeline-title">Pagamento Criado</h6>
                    <p class="timeline-text text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            
            @if($payment->status === 'PENDING')
            <div class="timeline-item">
                <div class="timeline-marker bg-warning"></div>
                <div class="timeline-content">
                    <h6 class="timeline-title">Aguardando Pagamento</h6>
                    <p class="timeline-text text-muted">Pagamento pendente de confirmação</p>
                </div>
            </div>
            @endif
            
            @if($payment->isPaid())
            <div class="timeline-item">
                <div class="timeline-marker bg-success"></div>
                <div class="timeline-content">
                    <h6 class="timeline-title">Pagamento Confirmado</h6>
                    <p class="timeline-text text-muted">
                        {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->updated_at ? $payment->updated_at->format('d/m/Y H:i') : 'Data não disponível') }}
                    </p>
                </div>
            </div>
            @endif
            
            @if($payment->status === 'OVERDUE')
            <div class="timeline-item">
                <div class="timeline-marker bg-danger"></div>
                <div class="timeline-content">
                    <h6 class="timeline-title">Pagamento Vencido</h6>
                    <p class="timeline-text text-muted">O prazo para pagamento expirou</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Informações Adicionais do Asaas -->
@if(isset($asaasPayment) && $asaasPayment)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Informações do Gateway</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="info-item mb-3">
                    <label class="text-muted small">Status no Gateway</label>
                    <div class="fw-bold">
                        @php
                            $gatewayStatus = $asaasPayment['status'] ?? 'N/A';
                            $gatewayTranslatedStatus = $statusTranslations[$gatewayStatus] ?? $gatewayStatus;
                        @endphp
                        {{ $gatewayTranslatedStatus }}
                    </div>
                </div>
            </div>
            @if(isset($asaasPayment['invoiceUrl']))
            <div class="col-md-6">
                <div class="info-item mb-3">
                    <label class="text-muted small">Fatura</label>
                    <div><button type="button" class="btn btn-sm btn-outline-primary" onclick="openCustomInvoiceModal({{ $payment->id }})">Ver Fatura</button></div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif



<!-- Ações -->
<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                @if($payment->status === 'PENDING' && $payment->billing_type === 'PIX')
                    <a href="{{ route('payments.pix-payment', $payment) }}" class="btn btn-outline-primary me-2" target="_blank">
                        <i class="bi bi-external-link me-1"></i>Abrir Página de Pagamento
                    </a>
                @endif
                
                <button type="button" class="btn btn-outline-info me-2" onclick="updatePaymentStatusInModal({{ $payment->id }})">
                     <i class="bi bi-arrow-clockwise me-1"></i>Atualizar Status
                 </button>
            </div>
            
            <div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-warning mt-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    {{ session('error') }}
</div>
@endif

<style>
/* Cabeçalho Azul */


.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.customer-info .customer-name {
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: 1.5rem;
}

.customer-details div {
    margin-bottom: 0.25rem;
    opacity: 0.9;
}

.payment-status-header {
    text-align: right;
}

.status-icon-header {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

.status-text {
    font-size: 1.25rem;
    font-weight: 600;
    display: block;
}

/* Seções de Informação */
.info-section {
    margin-bottom: 1rem;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    font-weight: 600;
    color: #111827;
}

/* PIX Styles */
.pix-label {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.pix-code-textarea {
    min-height: 100px;
    font-family: monospace;
    font-size: 0.875rem;
    resize: none;
}

.qr-code-image {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e5e7eb;
}

.timeline-content {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 3px solid #d1d5db;
}

.timeline-title {
    margin-bottom: 0.25rem;
    font-weight: 600;
    color: #111827;
}

.timeline-text {
    margin-bottom: 0;
    font-size: 0.875rem;
    color: #6b7280;
}

/* Cards */
.card {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.card-header {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .payment-header {
        padding: 1.5rem;
    }
    
    .header-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .payment-status-header {
        text-align: left;
    }
    
    .customer-info .customer-name {
        font-size: 1.25rem;
    }
}
</style>

<script>
// Função para copiar código PIX na modal
function copyPixCodeModal() {
    const pixCodeInput = document.getElementById('pixCodeModal');
    if (pixCodeInput) {
        pixCodeInput.select();
        pixCodeInput.setSelectionRange(0, 99999); // Para dispositivos móveis
        
        try {
            document.execCommand('copy');
            
            // Feedback visual
            const button = event.target.closest('button');
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check me-1"></i>Copiado!';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            }, 2000);
            
        } catch (err) {
            console.error('Erro ao copiar código PIX:', err);
            alert('Erro ao copiar código PIX');
        }
    }
}

// Função para reportar problema
function reportProblem() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Reportar Problema',
            text: 'Entre em contato conosco através do email: suporte@karaokeclube.com.br',
            confirmButtonText: 'OK'
        });
    } else {
        alert('Entre em contato conosco através do email: suporte@karaokeclube.com.br');
    }
}

function updatePaymentStatusInModal(paymentId) {
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Verificando...';
    button.disabled = true;
    
    $.ajax({
        url: `/payments/check-status/${paymentId}`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.status_changed) {
                // Recarregar o conteúdo da modal
                showPaymentStatus(paymentId);
                
                // Mostrar mensagem de sucesso se houve mudança
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Atualizado',
                        text: 'O status do pagamento foi atualizado com sucesso.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else if (response.success) {
                // Mostrar mensagem de que não houve mudança
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Status Verificado',
                        text: 'O status do pagamento não foi alterado.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert('O status do pagamento não foi alterado.');
                }
            } else {
                throw new Error(response.message || 'Erro desconhecido');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição:', xhr.responseText);
            
            let errorMessage = 'Erro ao verificar status do pagamento.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: errorMessage,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert(errorMessage);
            }
        },
        complete: function() {
            button.innerHTML = originalHtml;
            button.disabled = false;
        }
    });
}

// Função para abrir modal de fatura do Asaas (mantida para compatibilidade)
function openInvoiceModal(invoiceUrl) {
    // Criar modal dinamicamente
    const modalId = 'invoiceModal';
    let modal = document.getElementById(modalId);
    
    if (modal) {
        modal.remove();
    }
    
    // Criar nova modal
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="invoiceModalLabel">Fatura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="d-flex justify-content-center align-items-center" style="height: 400px;" id="invoiceLoader">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                        <iframe id="invoiceFrame" src="${invoiceUrl}" style="width: 100%; height: 600px; border: none; display: none;"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <a href="${invoiceUrl}" target="_blank" class="btn btn-primary">Abrir em Nova Aba</a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    modal = document.getElementById(modalId);
    
    // Configurar iframe
    const iframe = document.getElementById('invoiceFrame');
    const loader = document.getElementById('invoiceLoader');
    
    iframe.onload = function() {
        loader.style.display = 'none';
        iframe.style.display = 'block';
    };
    
    // Mostrar modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Limpar modal quando fechar
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// Função para abrir nossa fatura personalizada
function openCustomInvoiceModal(paymentId) {
    const modalId = 'customInvoiceModal';
    let modal = document.getElementById(modalId);
    
    if (modal) {
        modal.remove();
    }
    
    // Criar modal
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="customInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content" id="customInvoiceContent">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando fatura...</span>
                        </div>
                        <p class="mt-3 text-muted">Carregando fatura...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    modal = document.getElementById(modalId);
    
    // Mostrar modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Carregar conteúdo da fatura via AJAX
    fetch(`/payments/${paymentId}/invoice`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao carregar fatura');
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('customInvoiceContent').innerHTML = html;
    })
    .catch(error => {
        console.error('Erro:', error);
        document.getElementById('customInvoiceContent').innerHTML = `
            <div class="modal-header">
                <h5 class="modal-title">Erro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Não foi possível carregar a fatura. Tente novamente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        `;
    });
    
    // Limpar modal quando fechar
     modal.addEventListener('hidden.bs.modal', function () {
         modal.remove();
     });
 }

 // Função para gerar recibo
 function generateReceipt(paymentId) {
     // Abrir recibo em nova janela
     const receiptUrl = `/payments/${paymentId}/receipt`;
     window.open(receiptUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
 }
 </script>
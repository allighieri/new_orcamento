@extends('layouts.app')

@section('title', isset($isExtraBudgets) && $isExtraBudgets ? 'Checkout - Orçamentos Extras' : 'Checkout - ' . $plan->name)

@section('content')
<div class="container  mx-auto m-4">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    {{ $pageTitle ?? 'Checkout - Plano ' . $plan->name }}
                </h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    @if(isset($isExtraBudgets) && $isExtraBudgets)
                        <li class="breadcrumb-item"><a href="{{ route('payments.extra-budgets') }}">Orçamentos Extras</a></li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('payments.select-plan') }}">Escolher Plano</a></li>
                    @endif
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Resumo do Plano -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            @if(isset($isExtraBudgets) && $isExtraBudgets)
                                <h5 class="mb-1">Orçamentos Extras</h5>
                                <p class="text-muted mb-0">{{ $planDescription ?? 'Orçamentos extras limitados ao período do seu plano' }}</p>
                                <p class="text-info mb-0">
                                    <i class="mdi mdi-calendar"></i> 
                                    Válido pelo período restante do seu plano atual
                                </p>
                            @else
                                <h5 class="mb-1">Plano {{ $plan->name }}</h5>
                                <p class="text-muted mb-0">{{ $plan->budget_limit }} orçamentos por mês</p>
                                <p class="text-info mb-0">
                                    <i class="mdi mdi-calendar"></i> 
                                    Ciclo: {{ $period === 'yearly' ? 'Anual' : 'Mensal' }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            @if(isset($type) && $type === 'extra_budgets')
                                <h4 class="mb-0 text-primary">R$ {{ number_format($amount, 2, ',', '.') }}</h4>
                                <small class="text-muted">Pagamento único</small>
                            @else
                                @php
                                    $periodLabel = $period === 'yearly' ? 'ano' : 'mês';
                                @endphp
                                <h4 class="mb-0 text-primary">R$ {{ number_format($amount, 2, ',', '.') }}/{{ $periodLabel }}</h4>
                                @if($period === 'yearly')
                                    <small class="text-success">
                                        Economia de R$ {{ number_format((($plan->monthly_price * 12) - $plan->annual_price), 2, ',', '.') }}
                                    </small>
                                @endif
                            @endif
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
                            <form id="pixForm" action="{{ route('payments.process-pix', $plan->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="period" value="{{ $period }}">
                                @if(isset($isExtraBudgets) && $isExtraBudgets)
                                    <input type="hidden" name="type" value="extra_budgets">
                                @endif
                                
                                <div class="text-center mb-4">
                                    <i class="mdi mdi-qrcode display-4 text-success"></i>
                                    <h5 class="mt-2">Pagamento via PIX</h5>
                                    <p class="text-muted">Aprovação instantânea após o pagamento</p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pix_name" class="form-label">Nome Completo</label>
                                            <input type="text" class="form-control" id="pix_name" name="name" 
                                                   value="{{ auth()->user()->name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pix_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                                            <input type="text" class="form-control" id="pix_cpf_cnpj" name="cpf_cnpj" 
                                                   value="{{ auth()->user()->company->document_number ?? '' }}" placeholder="000.000.000-00" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pix_email" class="form-label">E-mail</label>
                                            <input type="email" class="form-control" id="pix_email" name="email" 
                                                   value="{{ auth()->user()->email }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pix_phone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="pix_phone" name="phone" 
                                                   value="{{ auth()->user()->company->phone ?? '' }}" placeholder="(11) 99999-9999" required>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="mdi mdi-qrcode me-2"></i>Gerar PIX - R$ {{ number_format($amount, 2, ',', '.') }}
                                </button>
                            </form>
                        </div>

                        <!-- Cartão de Crédito -->
                        <div class="tab-pane fade" id="credit-card" role="tabpanel">
                            <form id="creditCardForm" action="{{ route('payments.process-credit-card', $plan->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                @if(isset($isExtraBudgets) && $isExtraBudgets)
                                    <input type="hidden" name="type" value="extra_budgets">
                                @endif
                                
                                <div class="text-center mb-4">
                                    <i class="mdi mdi-credit-card display-4 text-primary"></i>
                                    <h5 class="mt-2">Pagamento com Cartão</h5>
                                    <p class="text-muted">Aprovação instantânea</p>
                                </div>

                                <!-- Dados do Cliente -->
                                <h6 class="mb-3">Dados do Titular</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_name" class="form-label">Nome Completo</label>
                                            <input type="text" class="form-control" id="cc_name" name="name" 
                                                   value="{{ auth()->user()->name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                                            <input type="text" class="form-control" id="cc_cpf_cnpj" name="cpf_cnpj" 
                                                   placeholder="000.000.000-00" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_email" class="form-label">E-mail</label>
                                            <input type="email" class="form-control" id="cc_email" name="email" 
                                                   value="{{ auth()->user()->email }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_phone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="cc_phone" name="phone" 
                                                   value="{{ auth()->user()->company->phone ?? '' }}" placeholder="(11) 99999-9999" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dados do Cartão -->
                                <h6 class="mb-3 mt-4">Dados do Cartão</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="card_number" class="form-label">Número do Cartão</label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                                   placeholder="0000 0000 0000 0000" maxlength="19" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="card_cvv" class="form-label">CVV</label>
                                            <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                                                   placeholder="123" maxlength="4" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_expiry_month" class="form-label">Mês de Vencimento</label>
                                            <select class="form-select" id="card_expiry_month" name="card_expiry_month" required>
                                                <option value="">Selecione</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="card_expiry_year" class="form-label">Ano de Vencimento</label>
                                            <select class="form-select" id="card_expiry_year" name="card_expiry_year" required>
                                                <option value="">Selecione</option>
                                                @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="card_holder_name" class="form-label">Nome no Cartão</label>
                                    <input type="text" class="form-control" id="card_holder_name" name="card_holder_name" 
                                           placeholder="Nome como impresso no cartão" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="mdi mdi-credit-card me-2"></i>Pagar R$ {{ number_format($amount, 2, ',', '.') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <h5>Processando pagamento...</h5>
                <p class="text-muted mb-0">Aguarde enquanto processamos sua solicitação</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {


   

    

    // Máscara dinâmica para CPF/CNPJ (baseada na view de clientes)
    $('#pix_cpf_cnpj, #cc_cpf_cnpj').on('input', function() {
        var cleanValue = $(this).val().replace(/\D/g, '');

        if (cleanValue.length > 11) {
            $(this).mask('00.000.000/0000-00');
        } else {
            $(this).mask('000.000.000-009');
        }
    }).trigger('input'); // Aplica a máscara inicial
    
    // Máscara dinâmica para telefone (baseada na view de clientes)
    var phoneOptions = {
        onKeyPress: function(phone, e, field, options) {
            var masks = ['(00) 0000-00009', '(00) 00000-0000'];
            var mask = (phone.length > 14) ? masks[1] : masks[0];
            $(field).mask(mask, options);
        }
    };
    $('#pix_phone, #cc_phone').mask('(00) 0000-00009', phoneOptions);
    
    // Máscara para número do cartão
    $('#card_number').mask('0000 0000 0000 0000');
    
    // Máscara para CVV
    $('#card_cvv').mask('0000');
    
    // Submissão do formulário PIX
    $('#pixForm').on('submit', function(e) {
        e.preventDefault();
        
        console.log('=== INÍCIO SUBMISSÃO PIX ===');
        console.log('Form action:', $(this).attr('action'));
        console.log('Form data:', $(this).serialize());
        console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
        
        // Prevenir múltiplas submissões
        var submitButton = $(this).find('button[type="submit"]');
        if (submitButton.prop('disabled')) {
            return false;
        }
        
        submitButton.prop('disabled', true).text('Processando...');
        
        // Mostrar modal de loading
        $('#loadingModal').modal('show');
        
        // Fazer requisição AJAX com timeout
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            timeout: 90000, // 90 segundos - aumentado devido a possíveis timeouts da API Asaas
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                console.log('Resposta do PIX:', response);
                console.log('QR Code recebido:', response.pix_qr_code);
                console.log('Payload recebido:', response.pix_copy_paste);
                
                if (response.success) {
                    // Exibir QR Code
                    showPixQrCode(response.pix_qr_code, response.pix_copy_paste, response.due_date);
                    
                    // Iniciar verificação de status em tempo real
                    if (response.payment_id) {
                        startPaymentStatusCheck(response.payment_id);
                    }
                } else {
                    alert('Erro ao processar pagamento: ' + (response.message || 'Tente novamente'));
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                
                // Log detalhado do erro
                console.error('Erro na requisição PIX:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON,
                    headers: xhr.getAllResponseHeaders()
                });
                
                // Reabilitar botão
                submitButton.prop('disabled', false).text('Gerar PIX');
                
                var errorMsg = 'Erro ao processar pagamento. Tente novamente.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                    
                    // Verificar se é erro de downgrade para mensal
                    if (errorMsg.includes('Não é possível fazer downgrade para plano mensal')) {
                        // Extrair valor da taxa de cancelamento da mensagem
                        var feeMatch = errorMsg.match(/R\$ ([\d,\.]+)/);
                        var cancellationFee = feeMatch ? feeMatch[1] : '0,00';
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Mudança para Plano Mensal',
                            html: `
                                <p>Para mudar para o plano mensal, você precisa pagar a taxa de cancelamento antecipado.</p>
                                <div class="alert alert-info mt-3">
                                    <strong>Taxa de cancelamento:</strong> R$ ${cancellationFee}<br>
                                    <strong>Valor do plano mensal:</strong> R$ {{ number_format($plan->monthly_price, 2, ',', '.') }}<br>
                                    <hr>
                                    <strong>Total a pagar:</strong> R$ ${parseFloat(cancellationFee.replace(',', '.')) + {{ $plan->monthly_price }}}
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Pagar e Mudar para Mensal',
                            cancelButtonText: 'Fechar',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            // Garantir que o modal de loading seja fechado
                            $('#loadingModal').modal('hide');
                            
                            if (result.isConfirmed) {
                                // Redirecionar para página de pagamento da taxa de cancelamento
                                window.location.href = '{{ route("payments.cancellation-fee", $plan->id) }}?period=monthly';
                            }
                        });
                        return;
                    }
                    
                    // Verificar se é erro de mudança entre planos anuais
                    if (errorMsg.includes('Não é possível fazer mudança entre planos anuais diretamente')) {
                        // Extrair valores da taxa de cancelamento e primeira parcela
                        var feeMatches = errorMsg.match(/R\$ ([\d,\.]+)/g);
                        var cancellationFee = feeMatches && feeMatches[0] ? feeMatches[0].replace('R$ ', '') : '0,00';
                        var firstPayment = feeMatches && feeMatches[1] ? feeMatches[1].replace('R$ ', '') : '0,00';
                        var totalAmount = parseFloat(cancellationFee.replace(',', '.')) + parseFloat(firstPayment.replace(',', '.'));
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Mudança entre Planos Anuais',
                            html: `
                                <p>Para mudar entre planos anuais, você precisa pagar a taxa de cancelamento antecipado mais a primeira parcela do novo plano.</p>
                                <div class="alert alert-info mt-3">
                                    <strong>Taxa de cancelamento:</strong> R$ ${cancellationFee}<br>
                                    <strong>Primeira parcela do novo plano:</strong> R$ ${firstPayment}<br>
                                    <hr>
                                    <strong>Total a pagar:</strong> R$ ${totalAmount.toFixed(2).replace('.', ',')}
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Pagar e Mudar de Plano',
                            cancelButtonText: 'Fechar',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            // Garantir que o modal de loading seja fechado
                            $('#loadingModal').modal('hide');
                            
                            if (result.isConfirmed) {
                                // Redirecionar para página de pagamento da taxa de cancelamento para plano anual
                                window.location.href = '{{ route("payments.cancellation-fee", $plan->id) }}?period=annual';
                            }
                        });
                        return;
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('\n');
                } else if (xhr.status === 503) {
                    // Erro específico de serviço indisponível (API do Asaas)
                    errorMsg = 'A API de pagamentos está temporariamente indisponível. Tente novamente em alguns minutos.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro no Pagamento PIX',
                    text: errorMsg,
                    confirmButtonText: 'Tentar Novamente',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });
    
    // Submissão do formulário de cartão de crédito
    $('#creditCardForm').on('submit', function(e) {
        e.preventDefault();
        
        // Mostrar modal de loading
        $('#loadingModal').modal('show');
        
        // Submeter o formulário
        this.submit();
    });
    
    // Validação em tempo real do cartão
    $('#card_number').on('input', function() {
        var number = $(this).val().replace(/\s/g, '');
        var cardType = getCardType(number);
        
        // Adicionar classe visual baseada no tipo do cartão
        $(this).removeClass('visa mastercard amex');
        if (cardType) {
            $(this).addClass(cardType);
        }
    });
    
    function getCardType(number) {
        var patterns = {
            visa: /^4/,
            mastercard: /^5[1-5]/,
            amex: /^3[47]/
        };
        
        for (var type in patterns) {
            if (patterns[type].test(number)) {
                return type;
            }
        }
        return null;
    }
    
    // Variáveis globais para controle de verificação
    let paymentCheckInterval = null;
    let paymentCheckTimeout = null;
    
    // Polling removido - status atualizado automaticamente via webhook
    
    // Função para iniciar verificação de status (removida)
    function startPaymentStatusCheck(paymentId) {
        console.log('Aguardando confirmação do pagamento via webhook para:', paymentId);
    }
    
    // Função para mostrar sucesso do pagamento
    function showPaymentSuccess(paymentData) {
        // Atualizar a interface com animação
        $('#pix').fadeOut(500, function() {
            var successHtml = `
                <div class="text-center">
                    <div class="payment-success-animation mb-4">
                        <i class="mdi mdi-check-circle display-1 text-success"></i>
                    </div>
                    <h3 class="text-success mb-3">Pagamento Aprovado!</h3>
                    <p class="text-muted mb-4">Seu pagamento PIX foi processado com sucesso.</p>
                    <div class="alert alert-success">
                        <h6><i class="mdi mdi-information"></i> Próximos Passos:</h6>
                        <ul class="mb-0 text-start">
                            <li>Sua assinatura foi ativada automaticamente</li>
                            <li>Você receberá um e-mail de confirmação</li>
                            <li>Já pode começar a usar todos os recursos do plano</li>
                        </ul>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg me-2">
                            <i class="mdi mdi-home"></i> Ir para Dashboard
                        </a>
                        <a href="{{ route('payments.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="mdi mdi-receipt"></i> Ver Pagamentos
                        </a>
                    </div>
                </div>
            `;
            
            $(this).html(successHtml).fadeIn(500);
        });
        
        // Mostrar notificação de sucesso
        Swal.fire({
            icon: 'success',
            title: 'Pagamento Aprovado!',
            text: 'Seu pagamento PIX foi processado com sucesso. Sua assinatura já está ativa!',
            confirmButtonText: 'Ótimo!',
            confirmButtonColor: '#28a745'
        });
    }
    
    // Função para exibir QR Code PIX
    function showPixQrCode(qrCode, copyPaste, dueDate) {
        var qrCodeSection = '';
        
        if (qrCode && qrCode !== null && qrCode !== 'null') {
            qrCodeSection = `
                <h5 class="card-title">Escaneie o QR Code</h5>
                <div class="mb-3">
                    <img src="data:image/png;base64,${qrCode}" alt="QR Code PIX" class="img-fluid" style="max-width: 250px;">
                </div>
                <p class="text-muted">Ou copie e cole o código PIX:</p>
            `;
        } else {
            qrCodeSection = `
                <h5 class="card-title">Código PIX Copia e Cola</h5>
                <p class="text-muted">Copie o código abaixo e cole no seu app do banco:</p>
            `;
        }
        
        var qrCodeHtml = `
            <div class="text-center">
                <h4 class="text-success mb-3">
                    <i class="mdi mdi-check-circle"></i> PIX Gerado com Sucesso!
                </h4>
                <div class="card border-success">
                    <div class="card-body">
                        ${qrCodeSection}
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="pixCopyPaste" value="${copyPaste || ''}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPixCode()">
                                <i class="mdi mdi-content-copy"></i> Copiar
                            </button>
                        </div>
                        <p class="text-warning"><strong>Vencimento:</strong> ${new Date(dueDate).toLocaleDateString('pt-BR')}</p>
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            Após o pagamento, sua assinatura será ativada automaticamente.
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Substituir o conteúdo da aba PIX
        $('#pix').html(qrCodeHtml);
    }
    
    // Função para copiar código PIX
    window.copyPixCode = function() {
        var copyText = document.getElementById('pixCopyPaste');
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand('copy');
        
        // Feedback visual
        var btn = event.target.closest('button');
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Copiado!';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    };
});
</script>
@endpush

@push('styles')
<style>
.nav-pills .nav-link {
    border-radius: 10px;
    padding: 12px 20px;
    font-weight: 500;
}

.nav-pills .nav-link.active {
    background: linear-gradient(45deg, #5a67d8, #667eea);
}

.card {
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-control, .form-select {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    padding: 12px 15px;
}

.form-control:focus, .form-select:focus {
    border-color: #5a67d8;
    box-shadow: 0 0 0 0.2rem rgba(90, 103, 216, 0.25);
}

.btn-lg {
    padding: 15px 30px;
    border-radius: 10px;
    font-weight: 600;
}

#card_number.visa {
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzAwNTFBNSIvPgo8cGF0aCBkPSJNMTYuNzUgN0gxNC4yNUwxMi41IDE3SDE1TDE2Ljc1IDdaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 30px;
}

#card_number.mastercard {
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iI0VCMDAxQiIvPgo8Y2lyY2xlIGN4PSIxNSIgY3k9IjEyIiByPSI3IiBmaWxsPSIjRkY1RjAwIi8+CjxjaXJjbGUgY3g9IjI1IiBjeT0iMTIiIHI9IjciIGZpbGw9IiNGRkY1RjAiLz4KPC9zdmc+Cg==');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 30px;
}
</style>
@endpush
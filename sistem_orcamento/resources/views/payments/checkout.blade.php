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
                        <li class="breadcrumb-item"><a href="{{ route('payments.change-plan') }}">Escolher Plano</a></li>
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
                                    Ciclo: {{ $period === 'yearly' ? '12 meses' : 'Mensal' }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            @if(isset($type) && $type === 'extra_budgets')
                                <h4 class="mb-0 text-primary">R$ {{ number_format($amount, 2, ',', '.') }}</h4>
                                <small class="text-muted">Pagamento único</small>
                            @else
                                @php
                                    $periodLabel = ($period === 'yearly') ? 'ano' : 'mês';
                                    $amountDisplay = $amount;
                                @endphp

                                <h4 class="mb-0 text-primary">R$ {{ number_format($amountDisplay, 2, ',', '.') }}/{{ $periodLabel }}</h4>

                                @if($period === 'yearly')
                                    @php
                                        $monthlyPrice = $plan->monthly_price ?? 0;
                                        $yearlyPrice = $plan->yearly_price ?? 0;
                                        $amountDifference = ($monthlyPrice * 12) - $yearlyPrice;
                                    @endphp
                                    <small class="text-success">
                                        Economia de R$ {{ number_format($amountDifference, 2, ',', '.') }}
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
                            <form id="pixForm" action="{{ route('payments.process-pix', $plan->slug) }}" method="POST">
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
                                                   value="{{ auth()->user()->company->fantasy_name ?? auth()->user()->name }}" required>
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
                                    @if($period === 'yearly')
                                        <i class="mdi mdi-qrcode me-2"></i>Gerar PIX - R$ {{ number_format($amount, 2, ',', '.') }} / ano
                                    @else
                                        <i class="mdi mdi-qrcode me-2"></i>Gerar PIX - R$ {{ number_format($amount, 2, ',', '.') }} / mês
                                    @endif
                                </button>
                            </form>
                        </div>

                        <!-- Cartão de Crédito -->
                        <div class="tab-pane fade" id="credit-card" role="tabpanel">
                            <form id="creditCardForm" action="{{ route('payments.process-credit-card', $plan->slug) }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="period" value="{{ $period }}">
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
                                                   value="{{ auth()->user()->company->fantasy_name ?? auth()->user()->name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                                            <input type="text" class="form-control" id="cc_cpf_cnpj" name="cpf_cnpj" 
                                                   placeholder="000.000.000-00" value="{{ auth()->user()->company->document_number ?? '' }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_email" class="form-label">E-mail</label>
                                            <input type="email" class="form-control" id="cc_email" name="email" 
                                                   value="{{ auth()->user()->company->email ?? auth()->user()->email }}" required>
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

                                <!-- Endereço do Titular -->
                                <h6 class="mb-3 mt-4">Endereço do Titular</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="cc_postal_code" class="form-label">CEP</label>
                                            <input type="text" class="form-control" id="cc_postal_code" name="postal_code" 
                                                   value="{{ auth()->user()->company->cep ?? '' }}" placeholder="00000-000" maxlength="9" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cc_address" class="form-label">Logradouro</label>
                                            <input type="text" class="form-control" id="cc_address" name="address" 
                                                   value="{{ auth()->user()->company->address ?? '' }} {{ auth()->user()->company->address_line_2 ?? '' }}" placeholder="Rua, Avenida, etc." required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="cc_address_number" class="form-label">Número</label>
                                            <input type="text" class="form-control" id="cc_address_number" name="address_number" 
                                                   value="" placeholder="07" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cc_district" class="form-label">Bairro</label>
                                            <input type="text" class="form-control" id="cc_district" name="district" 
                                                   value="{{ auth()->user()->company->district ?? '' }}" placeholder="Bairro" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cc_city" class="form-label">Cidade</label>
                                            <input type="text" class="form-control" id="cc_city" name="city" 
                                                   value="{{ auth()->user()->company->city ?? '' }}" placeholder="Cidade" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cc_state" class="form-label">Estado</label>
                                            <select class="form-select" id="cc_state" name="state" required>
                                                <option value="" {{ empty(auth()->user()->company->state) ? 'selected' : '' }}>Selecione</option>
                                                <option value="AC" {{ (auth()->user()->company->state ?? '') == 'AC' ? 'selected' : '' }}>Acre</option>
                                                <option value="AL" {{ (auth()->user()->company->state ?? '') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                                                <option value="AP" {{ (auth()->user()->company->state ?? '') == 'AP' ? 'selected' : '' }}>Amapá</option>
                                                <option value="AM" {{ (auth()->user()->company->state ?? '') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                                                <option value="BA" {{ (auth()->user()->company->state ?? '') == 'BA' ? 'selected' : '' }}>Bahia</option>
                                                <option value="CE" {{ (auth()->user()->company->state ?? '') == 'CE' ? 'selected' : '' }}>Ceará</option>
                                                <option value="DF" {{ (auth()->user()->company->state ?? '') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                                                <option value="ES" {{ (auth()->user()->company->state ?? '') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                                                <option value="GO" {{ (auth()->user()->company->state ?? '') == 'GO' ? 'selected' : '' }}>Goiás</option>
                                                <option value="MA" {{ (auth()->user()->company->state ?? '') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                                                <option value="MT" {{ (auth()->user()->company->state ?? '') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                                                <option value="MS" {{ (auth()->user()->company->state ?? '') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                                                <option value="MG" {{ (auth()->user()->company->state ?? '') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                                                <option value="PA" {{ (auth()->user()->company->state ?? '') == 'PA' ? 'selected' : '' }}>Pará</option>
                                                <option value="PB" {{ (auth()->user()->company->state ?? '') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                                                <option value="PR" {{ (auth()->user()->company->state ?? '') == 'PR' ? 'selected' : '' }}>Paraná</option>
                                                <option value="PE" {{ (auth()->user()->company->state ?? '') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                                                <option value="PI" {{ (auth()->user()->company->state ?? '') == 'PI' ? 'selected' : '' }}>Piauí</option>
                                                <option value="RJ" {{ (auth()->user()->company->state ?? '') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                                                <option value="RN" {{ (auth()->user()->company->state ?? '') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                                                <option value="RS" {{ (auth()->user()->company->state ?? '') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                                                <option value="RO" {{ (auth()->user()->company->state ?? '') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                                                <option value="RR" {{ (auth()->user()->company->state ?? '') == 'RR' ? 'selected' : '' }}>Roraima</option>
                                                <option value="SC" {{ (auth()->user()->company->state ?? '') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                                                <option value="SP" {{ (auth()->user()->company->state ?? '') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                                                <option value="SE" {{ (auth()->user()->company->state ?? '') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                                                <option value="TO" {{ (auth()->user()->company->state ?? '') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                                            </select>
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

                                <!-- Parcelamento -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="installments" class="form-label">Parcelamento</label>
                                        <select class="form-select" id="installments" name="installments" required>
                                            <option value="1">1x sem juros</option>
                                            <option value="2">2x sem juros</option>
                                            <option value="3">3x sem juros</option>
                                            <option value="4">4x sem juros</option>
                                            <option value="5">5x sem juros</option>
                                            <option value="6">6x sem juros</option>
                                            <option value="7">7x sem juros</option>
                                            <option value="8">8x sem juros</option>
                                            <option value="9">9x sem juros</option>
                                            <option value="10">10x sem juros</option>
                                            <option value="11">11x sem juros</option>
                                            <option value="12">12x sem juros</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Valor da Parcela</label>
                                        <div class="form-control-plaintext fw-bold text-primary" id="installment_value">
                                            R$ {{ number_format($amount, 2, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100" id="payment_button">
                                    @if($period === 'yearly')
                                        <i class="mdi mdi-credit-card me-2"></i>Pagar R$ {{ number_format($amount, 2, ',', '.') }}
                                    @else
                                        <i class="mdi mdi-credit-card me-2"></i>Pagar R$ {{ number_format($amount, 2, ',', '.') }}
                                    @endif
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
    
    // Máscara para CEP
    $('#cc_postal_code').mask('00000-000');
    
    // Máscara para número do endereço (somente números)
    $('#cc_address_number').mask('0000000000');
    
    // Busca automática de endereço por CEP
    $('#cc_postal_code').on('input', function() {
        var cep = $(this).val().replace(/\D/g, '');
        
        if (cep.length === 8) {
            // Exibe loading nos campos
            $('#cc_address, #cc_district, #cc_city').prop('disabled', true);
            $('#cc_address').val('Carregando...');
            $('#cc_district').val('Carregando...');
            $('#cc_city').val('Carregando...');
            $('#cc_state').val('');
            
            $.ajax({
                url: 'https://viacep.com.br/ws/' + cep + '/json/',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (!data.erro) {
                        $('#cc_address').val(data.logradouro.toUpperCase());
                        $('#cc_district').val(data.bairro.toUpperCase());
                        $('#cc_city').val(data.localidade.toUpperCase());
                        $('#cc_state').val(data.uf.toUpperCase());
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'CEP não encontrado',
                            text: 'O CEP informado não foi encontrado. Verifique se está correto.',
                            toast: true,
                            position: 'bottom-start',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        $('#cc_address, #cc_district, #cc_city, #cc_state').val('');
                    }
                    // Reabilita os campos (mantém editáveis)
                $('#cc_address, #cc_district, #cc_city').prop('disabled', false);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Conexão',
                        text: 'Não foi possível conectar ao serviço de CEP. Verifique sua conexão com a internet.',
                        toast: true,
                        position: 'bottom-start',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    $('#cc_address, #cc_district, #cc_city, #cc_state').val('');
                    // Reabilita os campos (mantém editáveis)
                $('#cc_address, #cc_district, #cc_city').prop('disabled', false);
                }
            });
        } else {
            // Limpa os campos se o CEP não tiver 8 dígitos
            $('#cc_address, #cc_district, #cc_city, #cc_state').val('');
        }
    });
    
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
                submitButton.prop('disabled', false).text('Gerar PIX - {{ number_format($plan->monthly_price, 2, ',', '.') }}');
                
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
                    if (errorMsg.includes('Mudança entre Planos Anuais')) {
                        // Nova lógica: processar mensagem formatada (título|descrição|valor|planId)
                        var parts = errorMsg.split('|');
                        var title = parts[0] || 'Mudança entre Planos Anuais';
                        var description = parts[1] || 'Mudança de plano anual';
                        var totalValue = parts[2] || 'R$ 0,00';
                        var planId = parts[3] || '';
                        
                        Swal.fire({
                            icon: 'info',
                            title: title,
                            html: `
                                <p>${description}</p>
                                <div class="alert alert-info mt-3">
                                    <strong>${totalValue}</strong>
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
                                // Redirecionar diretamente para o checkout do novo plano anual
                                if (planId) {
                                    window.location.href = '/payments/checkout/' + planId + '?billing_cycle=yearly';
                                } else {
                                    window.location.href = '{{ route("payments.select-plan") }}';
                                }
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
        
        console.log('=== INÍCIO SUBMISSÃO CARTÃO ===');
        console.log('Form action:', $(this).attr('action'));
        console.log('Form data:', $(this).serialize());
        
        // Prevenir múltiplas submissões
        var submitButton = $(this).find('button[type="submit"]');
        if (submitButton.prop('disabled')) {
            return false;
        }
        
        submitButton.prop('disabled', true).text('Processando...');
        
        // Mostrar modal de loading
        $('#loadingModal').modal('show');
        
        // Fazer requisição AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            timeout: 90000,
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                console.log('Resposta do Cartão:', response);
                
                if (response.success) {
                    // Mostrar sucesso e redirecionar para status
                    Swal.fire({
                        icon: 'success',
                        title: 'Pagamento Processado!',
                        text: 'Seu pagamento com cartão foi enviado para processamento.',
                        confirmButtonText: 'Ver Histórico',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        // Redirecionar para página de payments
                        window.location.href = '/payments';
                    });
                } else {
                    // Reabilitar botão
                    submitButton.prop('disabled', false).text('Pagar R$ {{ number_format($amount, 2, ",", ".") }}');
                    
                    alert('Erro ao processar pagamento: ' + (response.message || 'Tente novamente'));
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                
                console.error('Erro na requisição Cartão:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });
                
                // Reabilitar botão
                submitButton.prop('disabled', false).text('Pagar R$ {{ number_format($amount, 2, ",", ".") }}');
                
                var errorMsg = 'Erro ao processar pagamento. Verifique os dados do cartão e tente novamente.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro no Pagamento',
                    text: errorMsg,
                    confirmButtonText: 'Tentar Novamente',
                    confirmButtonColor: '#d33'
                });
            }
        });
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
    
    // Calcular valor das parcelas
    function calculateInstallmentValue() {
        const installments = parseInt($('#installments').val()) || 1;
        const totalAmount = {{ $amount }};
        const installmentValue = totalAmount / installments;
        
        $('#installment_value').text('R$ ' + installmentValue.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        // Atualizar texto do botão
        const buttonText = installments === 1 
            ? 'Pagar R$ ' + totalAmount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : 'Pagar ' + installments + 'x de R$ ' + installmentValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
        $('#payment_button').html('<i class="mdi mdi-credit-card me-2"></i>' + buttonText);
    }
    
    // Evento para recalcular quando mudar o parcelamento
    $('#installments').on('change', calculateInstallmentValue);
    
    // Calcular valor inicial
    calculateInstallmentValue();
    
    // Configurar escuta de eventos de pagamento em tempo real
    @if(isset($payment) && $payment->id)
    window.Echo.channel('payments')
        .listen('.payment.confirmed', (e) => {
            console.log('Evento de pagamento recebido:', e);
            
            // Verificar se é o pagamento atual
            if (e.paymentId == {{ $payment->id ?? 'null' }}) {
                // Mostrar notificação de sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Pagamento Confirmado!',
                    text: 'Seu pagamento foi processado com sucesso.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Redirecionar para página de sucesso ou payments
                    @if(isset($isExtraBudgets) && $isExtraBudgets)
                        window.location.href = '{{ route("payments.extra-budgets") }}';
                    @else
                        window.location.href = '{{ route("payments.index") }}';
                    @endif
                });
            }
        });    
    @endif
    
    // Configurar escuta de eventos de pagamento em tempo real
    if (window.Echo) {
        window.Echo.channel('payments')
            .listen('.payment.confirmed', (e) => {
                console.log('Evento de pagamento recebido em checkout:', e);
                
                // Mostrar notificação de sucesso
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pagamento Confirmado!',
                        text: 'Seu pagamento foi processado com sucesso.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
                
                // Redirecionar após confirmação
                setTimeout(() => {
                    @if(isset($isExtraBudgets) && $isExtraBudgets)
                        window.location.href = '{{ route("payments.extra-budgets") }}';
                    @else
                        window.location.href = '{{ route("payments.index") }}';
                    @endif
                }, 2000);
            });
    }
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
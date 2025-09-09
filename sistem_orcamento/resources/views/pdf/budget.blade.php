<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orçamento - {{ $budget->client->fantasy_name }} - {{ $budget->number }}</title>
    <style>
        @page {
            margin-top: 130px;
            margin-bottom: 30px;
            margin-left: 40px;
            margin-right: 40px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 110px;
            margin-bottom: 50px;
        }

        .bordered{
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 10px;
        }

        .borderless{
            border-bottom: 1px dotted #ccc;
            padding-bottom: 10px;
        }

        .header-content {
            position: relative;
            margin-bottom: 5px;
        }
        .company-info {
            display: flex;
            align-items: flex-start;
            flex: 1;
        }

        .company-logo {
            position: relative;
            top: 0;
            float: left;
            margin-right: 20px;
            width: 120px;
            height: 120px;
        }
        .company-details {
            flex: 1;
            text-align: left;
            width: 500px;
        }
        .company-details p {
            margin: 3px 0;
           
        }
        .company-info h2 {
            margin: 5px 0;
            color: #333;
        }

        .client-info {
            margin:50px 0 20px 0;   
        }

         .client-info p{
            margin: 3px 0;
         }

         .client-info h3{
            margin: 0;
         }

        .budget-info {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
            min-width: 400px;
        }

        .budget-number {
            font-size: 14px;
            font-weight: bold;
            color:rgb(255, 0, 0);
            margin-top: 0;
            margin-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd; 
            padding: 5px;
            text-align: left;
        }
        .items-table th {
            background-color: rgba(150, 150, 150, 0.1);
            font-weight: bold;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            margin: 5px 0;
        }
        .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px dotted #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .observations {
            margin-top: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #333;
        }

        .observations p{
            margin: 2px 0;
        }

        .observations h4{
            margin: 0 0 5px 0;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            width: 700px;
            height: 700px;
        }
        .signature{
            border: none; padding: 0 20px; text-align: center; width: 50%;
        }

        .signature-line{
            height: 50px; border-bottom: 1px solid #aaa; margin-bottom: 10px; text-align: center;
        }
    </style>
</head>
<body>
    @if($budget->company->logo && $settings->enable_pdf_watermark)
        <div class="watermark">
            <img src="{{ public_path('storage/' . $budget->company->logo) }}" alt="{{ $budget->company->fantasy_name }}" style="width: 100%; height: 100%;">
        </div>
    @endif
    <div class="header {{ $settings->border ? 'bordered' : 'borderless' }}">
        <div class="header-content">
            <div class="company-info">
                @if($budget->company->logo)
                    <img class="company-logo" src="{{ public_path('storage/' . $budget->company->logo) }}" alt="Logo {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}">
                @endif
                <div class="company-details">
                    <h2>{{ $budget->company->fantasy_name ?? $budget->company->corporate_name }}</h2>
                    <p>
                        @php
                            $document_number = $budget->company->document_number ?? null;
                            $state_registration = $budget->company->state_registration ?? null;
                            $docType = ($document_number && strlen($document_number) === 14) ? 'CPF' : 'CNPJ';
                        @endphp

                        @if($document_number && $state_registration)
                            <strong>{{ $docType }}:</strong> {{ $document_number }} <strong>IE:</strong> {{ $state_registration }}
                        @elseif($document_number)
                            <strong>{{ $docType }}:</strong> {{ $document_number }}
                        @elseif($state_registration)
                            <strong>IE:</strong> {{ $state_registration }}
                        @else
                            <span class="text-muted">Nenhum documento informado</span>
                        @endif
                    </p>
                    @php
                        $consolidatedContacts = $budget->company->getConsolidatedContacts();
                    @endphp
                    @if($consolidatedContacts->isNotEmpty())
                        <p>
                            @foreach($consolidatedContacts as $index => $contact)
                                {{ ucfirst($contact['type']) }}: {{ $contact['description'] }}@if($index < count($consolidatedContacts) - 1) | @endif
                            @endforeach
                        </p>
                    @endif
                    @if($budget->company->address)
                        <p>
                            {{ $budget->company->address }}, 
                            @if($budget->company->district)
                                {{ $budget->company->district }}, 
                            @endif
                        </p>    
                        <p>
                            {{ $budget->company->city }}-{{ $budget->company->state }}
                            @if($budget->company->cep)
                                - {{ $budget->company->cep }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
            
            <div class="budget-info">
                <p style="margin: 0 0 3px 0; padding: 0; font-weight: bold">Orçamento</p>
                <p class="budget-number">Nº. {{ $budget->number }}</p>
                <p style="margin: 0 0 5px 0; padding-bottom: 0"><strong>Data:</strong> {{ $budget->issue_date->format('d/m/Y') }}</p>
                @if($budget->delivery_date_enabled && $budget->delivery_date)
                <p style="margin: 0 0 5px 0; padding-bottom: 0"><strong>Previsão de Entrega:</strong> {{ $budget->delivery_date->format('d/m/Y') }}</p>
                @elseif($budget->delivery_date_enabled && !$budget->delivery_date)
                <p style="margin: 0 0 5px 0; padding-bottom: 0"><strong>Previsão de Entrega:</strong> A combinar</p>
                @elseif(!$budget->delivery_date_enabled)
                <p style="margin: 0 0 5px 0; padding-bottom: 0"><strong>Previsão de Entrega:</strong> A combinar</p>
                @endif
                <p style="margin-top: 0; padding-top: 0"><strong>Validade:</strong> 
                @if(isset($settings) && $settings->show_validity_as_text)
                    {{ $settings->budget_validity_days }} dias após a emissão
                @else
                    {{ $budget->valid_until->format('d/m/Y') }}
                @endif
                </p>
            </div>
        </div>
    </div>

    <div class="client-info {{ $settings->border ? 'bordered' : 'borderless' }}">
        <h3>Dados do Cliente</h3>
        <p><strong>Nome:</strong> {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }}</p>
        <p>
            @if($budget->client->document_number || $budget->client->state_registration)
    {{-- Verifica se o número do documento existe --}}
    @if($budget->client->document_number)
        @php
            $docLength = strlen($budget->client->document_number);
            $docType = ($docLength === 14) ? 'CPF' : 'CNPJ';
        @endphp
        <strong>{{ $docType }}:</strong> {{ $budget->client->document_number }}
            @endif

            {{-- Adiciona um separador se ambos os documentos existirem --}}
            @if($budget->client->document_number && $budget->client->state_registration)
                &nbsp;&nbsp;
            @endif

            {{-- Verifica se a Inscrição Estadual existe --}}
            @if($budget->client->state_registration)
                <strong>IE:</strong> {{ $budget->client->state_registration }}
            @endif
        @else
            {{-- Exibe esta mensagem se nenhum dos documentos estiver presente --}}
            <span class="text-muted">Nenhum documento informado</span>
        @endif
        </p>
        @if($budget->client->phone)
            <p><strong>Telefone:</strong> {{ $budget->client->phone }}</p>
        @endif
        @if($budget->client->email)
            <p><strong>Email:</strong> {{ $budget->client->email }}</p>
        @endif
        @if($budget->client->address)
            <p>
                <strong>Endereço:</strong> {{ $budget->client->address }}, 
                
                @if($budget->client->district)
                    {{ $budget->client->district }}, 
                @endif
                {{ $budget->client->city }}-{{ $budget->client->state }}
                @if($budget->client->cep)
                    - {{ $budget->client->cep }}
                @endif    
            </p>
        @endif
    </div>

    <h2 style="text-align: center; margin-bottom: 5px;">Itens do Orçamento</h2>
    <table class="items-table" style="font-size: 10px; margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="text-align: center; width:20px;">Item</th>
                <th>Produto</th>
                <th>Descrição</th>
                <th class="text-right" style="text-align: center; width:30px;">Qtd</th>
                <th class="text-right" style="width: 70px">Valor Unit.</th>
                <th class="text-right" style="width: 80px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budget->items as $item)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>
                    @if($item->product)
                        {{ $item->product->name }}
                    @elseif($item->produto)
                        {{ $item->produto }}
                    @else
                        <em>Produto excluído</em>
                    @endif
                </td>
                <td>
                    @if($item->description)
                        {{ $item->description ?? ($item->product->description ?? '') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

       
    @php
    $hasNotes = $budget->budgetPayments->some(function ($payment) {
        return !empty($payment->notes);
    });
@endphp

@if($budget->budgetPayments->count() == 0)
    <h3 style="margin-bottom: 3px; font-size: 11px;">Formas de Pagamento:</h3>
    <p style="text-align: left; font-size: 12px; color: #666; margin: 0 0 10px 0;">A combinar</p> 
@else 
    <h4 style="text-align: center; margin-bottom: 5px">Formas de Pagamento:</h4>
    <table class="items-table" style="width: 100%; border-collapse: collapse; font-size: 10px;">
        <thead>
            <tr style="border: 1px solid #ddd; text-align: left;">
                <th style="border: 1px solid #ddd; text-align: left;">Método</th>
                <th style="border: 1px solid #ddd; text-align: center;">Valor</th>
                <th style="border: 1px solid #ddd; text-align: center;">Parcelas</th>
                <th style="border: 1px solid #ddd; text-align: center;">Pagamento</th>
                @if($hasNotes)
                <th style="border: 1px solid #ddd; text-align: left;">Observações</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($budget->budgetPayments as $payment)
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="border: 1px solid #ddd;">{{ $payment->paymentMethod->paymentOptionMethod->method ?? 'N/A' }}</td>
                <td style="text-align: center; border: 1px solid #ddd;">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                <td style="text-align: center; border: 1px solid #ddd;">
                    {{ $payment->installments }}x
                    @if($payment->installments > 1 && $payment->paymentInstallments->count() > 0)
                        R$ {{ number_format($payment->paymentInstallments->first()->amount, 2, ',', '.') }}
                    @endif
                </td>
                <td style="text-align: center; border: 1px solid #ddd;">
                    @if($payment->payment_moment == 'approval')
                        Na aprovação
                    @elseif($payment->payment_moment == 'pickup')
                        Na retirada
                    @elseif($payment->payment_moment == 'days_after_pickup')
                        {{ $payment->days_after_pickup }} dias após retirada
                    @elseif($payment->payment_moment == 'custom')
                        {{ $payment->custom_date ? $payment->custom_date->format('d/m/Y') : 'Data personalizada' }}
                    @else
                        -
                    @endif
                </td>
                @if($hasNotes)
                <td style="border: 1px solid #ddd;">{{ $payment->notes ?? '-' }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
     
    @if($budget->bankAccounts->count() > 0)
    <div class="bank-accounts-section" style="margin-bottom: 10px;">
        <h3 style="margin-bottom: 5px; font-size: 12px;">Dados Bancários:</h3>
        @foreach($budget->bankAccounts as $bankAccount)
        <div style="margin:0;">
            <p style="margin:0; font-size: 12px; color: #333;">
                {{ $bankAccount->type }}
                @if($bankAccount->compe)
                    - ({{ $bankAccount->compe->code }}) {{ $bankAccount->compe->bank_name }}
                @endif
            </p>
            @if($bankAccount->type === 'Conta')
                <h4 style="margin: 0; font-size: 11px;">Agência: {{ $bankAccount->branch }}</h4>
                <h4 style="margin: 0 0 10px 0; font-size: 11px;">Conta: {{ $bankAccount->account }}</h4>
            @elseif($bankAccount->type === 'PIX')
                <h4 style="margin: 0 0 10px 0; font-size: 11px;">Chave: {{ ucfirst($bankAccount->key) }} - {{ $bankAccount->key_desc }}</h4>
            @endif
        </div>
        @endforeach
    </div>
    @endif


    <table class="items-table" style="font-size: 10px; margin-bottom: 1px;">
        @if($budget->observations)
        <div class="observations">
            <h4>Observações:</h4>
            <p>{!! nl2br(e($budget->observations)) !!}</p>
        </div>
            @endif
    </table>

    <table class="" style="text-align: center; width: 100%; border: none; border-collapse: collapse;">
        <tr>
            <td>
                <div class="total-section" style="margin-top: 30px;">   
                    
                     @if($budget->total_discount > 0)
                        <div class="total-row">
                            <strong>Subtotal: R$ {{ number_format($budget->items->sum('total_price'), 2, ',', '.') }}</strong>
                        </div>
                       
                        <div class="total-row">
                            Desconto: R$ {{ number_format($budget->total_discount, 2, ',', '.') }}
                        </div>
                        @endif

                    <div class="total-row total-final">
                        <strong>TOTAL: R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </td>
        </tr>   
    </table>    

    <table style="text-align: center; width: 100%; border: none; border-collapse: collapse; table-layout: fixed;">
    <tr>
        <td class="signature">
            <div style="text-align: center; margin: 0 auto;">
                <div class="signature-line"></div>
                <p style="margin: 0 !important;"><strong>Assinatura do Cliente</strong></p>
                <small>{{ $budget->client->corporate_name ?? $budget->client->fantasy_name ?? 'Cliente' }}</small>
            </div>
        </td>
        <td class="signature">
            <div style="text-align: center; margin: 0 auto;">
                <div class="signature-line"></div>
                <p style="margin: 0 !important;"><strong>Assinatura da Empresa</strong></p>
                <small>{{ $budget->company->corporate_name }}</small>
            </div>
        </td>
    </tr>
</table>

    
    <!--    
        <div class="footer">
            <p>Orçamento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Este orçamento é válido até 
            @if(isset($settings) && $settings->show_validity_as_text)
                {{ $settings->budget_validity_days }} dias após a emissão
            @else
                {{ $budget->valid_until->format('d/m/Y') }}
            @endif
            </p>
        </div>
    -->    
</body>
</html>
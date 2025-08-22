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
            top: -110px;
            left: 0;
            right: 0;
            height: 90px;
            margin-bottom: 30px;
            border-bottom: 1px dotted #333;
            padding-bottom: 5px;
            padding: 10px 0;
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
            float: left;
            margin-right: 20px;
            width: 90px;
            height: 90px;
        }
        .company-details {
            flex: 1;
            text-align: left;
        }
        .company-details p {
            margin: 3px 0;
            width: 500px;
        }


        .company-info h2 {
            margin: 5px 0;
            color: #333;
        }

        .client-info {
            margin-bottom: 30px;
            border-bottom: 1px dotted #333;
            padding: 5px;
        }

         .client-info p{
            margin: 3px 0;
         }

         .client-info h2{
            margin: 0;
         }

        .budget-info {
            position: absolute;
            top: 10px;
            right: 0;
            text-align: right;
            min-width: 300px;
        }

        .budget-number {
            font-size: 18px;
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
            background-color: rgba(100, 100, 100, 0.3);
            font-weight: bold;
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
            padding: 10px;
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
    </style>
</head>
<body>
    @if($budget->company->logo)
        <div class="watermark">
            <img src="{{ public_path('storage/' . $budget->company->logo) }}" alt="Watermark" style="width: 100%; height: 100%;">
        </div>
    @endif
    <div class="header">
        <div class="header-content">
            <div class="company-info">
                @if($budget->company->logo)
                    <img src="{{ public_path('storage/' . $budget->company->logo) }}" alt="Logo da {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}" class="company-logo">
                @endif
                <div class="company-details">
                    <h2>{{ $budget->company->fantasy_name ?? $budget->company->corporate_name }}</h2>
                    @if($budget->company->document_number)
                        <p>CNPJ: {{ $budget->company->document_number }} - 
                    @endif
                    @if($budget->company->phone)
                        Telefone: {{ $budget->company->phone }}</p>
                    @endif
                    @if($budget->company->email)
                        <p>Email: {{ $budget->company->email }}</p>
                    @endif
                    @if($budget->company->address)
                        <p>{{ $budget->company->address }}, {{ $budget->company->city }}-{{ $budget->company->state }}</p>
                    @endif
                </div>
            </div>
            
            <div class="budget-info">
                <div class="budget-number">Nº. {{ $budget->number }}</div>
                <p><strong>Data:</strong> {{ $budget->issue_date->format('d/m/Y') }}</p>
                <p><strong>Validade:</strong> {{ $budget->valid_until->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    <div class="client-info">
        <h3>Dados do Cliente</h3>
        <p><strong>Nome:</strong> {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }}</p>
        @if($budget->client->document_number)
            <p><strong>CPF/CNPJ:</strong> {{ $budget->client->document_number }}</p>
        @endif
        @if($budget->client->phone)
            <p><strong>Telefone:</strong> {{ $budget->client->phone }} - 
        @endif
        @if($budget->client->email)
            <strong>Email:</strong> {{ $budget->client->email }}</p>
        @endif
        @if($budget->client->address)
            <p><strong>Endereço:</strong> {{ $budget->client->address }}, {{ $budget->client->city }}-{{ $budget->client->state }}</p>
        @endif
    </div>

    <h2 style="text-align: center; margin-bottom: 5px;">Itens do Orçamento</h2>
    <table class="items-table" style="font-size: 10px;">
        <thead>
            <tr>
                <th>Item</th>
                <th>Produto</th>
                <th>Descrição</th>
                <th class="text-right">Qtd</th>
                <th class="text-right">Valor Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budget->items as $item)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>
                    @if($item->product)
                        {{ $item->product->name }}
                    @else
                        <em>Produto excluído</em>
                    @endif
                </td>
                <td>
                    @if($item->product)
                        {{ $item->product->description ?? '' }}
                    @else
                        {{ $item->description ?? '' }}
                    @endif
                </td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    

    <table style="width: 100%; border: none; border-collapse: collapse;">
    <tr>
        <td style="border: none; padding: 0;">

            @if($budget->observations)
            <div class="observations">
                <h4>Observações:</h4>
                <p>{!! nl2br(e($budget->observations)) !!}</p>
            </div>
            @endif

            <div class="total-section">
                <div class="total-row">
                    <strong>Subtotal: R$ {{ number_format($budget->items->sum('total_price'), 2, ',', '.') }}</strong>
                </div>
                @if($budget->total_discount > 0)
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

    
    <!--    
        <div class="footer">
            <p>Orçamento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Este orçamento é válido até {{ $budget->valid_until->format('d/m/Y') }}</p>
        </div>
    -->    
</body>
</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pagamento #{{ $payment->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 10px;
                font-size: 12px;
            }
            
            .receipt-container {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                margin: 0 !important;
                padding: 15px !important;
                max-width: none !important;
            }
            
            .receipt-title {
                font-size: 1.2rem !important;
            }
            
            .receipt-subtitle {
                font-size: 0.8rem !important;
            }
            
            .info-row {
                padding: 3px 0 !important;
                font-size: 0.8rem !important;
            }
            
            .receipt-info {
                padding: 10px !important;
                margin-bottom: 10px !important;
            }
            
            .amount-value {
                font-size: 1.4rem !important;
            }
            
            .amount-label {
                font-size: 0.8rem !important;
            }
            
            .amount-highlight {
                padding: 8px !important;
                margin: 10px 0 !important;
            }
            
            .receipt-footer {
                margin-top: 15px !important;
                padding-top: 10px !important;
                font-size: 0.75rem !important;
            }
            
            h6 {
                font-size: 0.9rem !important;
                margin-bottom: 8px !important;
            }
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .receipt-container {
            max-width: 500px;
            margin: 10px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .receipt-title {
            color: #0d6efd;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .receipt-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 150px;
        }
        
        .info-value {
            color: #212529;
            text-align: right;
        }
        
        .amount-highlight {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin: 15px 0;
        }
        
        .amount-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        
        .amount-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <button class="btn btn-primary print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>
    
    <div class="receipt-container">
        <!-- Cabeçalho do Recibo -->
        <div class="receipt-header">
            <img src="{{ asset('images/logo-orca-ja-azul.png') }}" alt="Orça Já!" style="height: 30px; margin-bottom: 5px;">
            <h1 class="receipt-title">
                <i class="fas fa-receipt"></i>
                RECIBO DE PAGAMENTO
            </h1>
            <p class="receipt-subtitle">{{ config('app.name') }} - Sistema de Orçamentos</p>
        </div>
        
        <!-- Informações do Recibo -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Número do Recibo:</span>
                <span class="info-value">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Data de Emissão:</span>
                <span class="info-value">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Data do Pagamento:</span>
                <span class="info-value">
                    @if($asaasPayment && isset($asaasPayment['paymentDate']))
                        {{ \Carbon\Carbon::parse($asaasPayment['paymentDate'])->format('d/m/Y H:i:s') }}
                    @else
                        {{ $payment->updated_at->format('d/m/Y H:i:s') }}
                    @endif
                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">ID do Pagamento:</span>
                <span class="info-value">{{ $payment->asaas_payment_id ?? $payment->id }}</span>
            </div>
        </div>
        
        <!-- Dados do Pagador -->
        <div class="receipt-info">
            <h6 class="text-primary mb-2">
                <i class="fas fa-user"></i> Dados do Pagador
            </h6>
            
            <div class="info-row">
                <span class="info-label">Nome:</span>
                <span class="info-value">
                    @if($customerData)
                        {{ $customerData['name'] ?? 'N/A' }}
                    @else
                        {{ $payment->user->name ?? 'N/A' }}
                    @endif
                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">
                    @if($customerData)
                        {{ $customerData['email'] ?? 'N/A' }}
                    @else
                        {{ $payment->user->email ?? 'N/A' }}
                    @endif
                </span>
            </div>
            
            @if($customerData && isset($customerData['cpfCnpj']))
                <div class="info-row">
                    <span class="info-label">CPF/CNPJ:</span>
                    <span class="info-value">{{ $customerData['cpfCnpj'] }}</span>
                </div>
            @endif
        </div>
        
        <!-- Detalhes do Pagamento -->
        <div class="receipt-info">
            <h6 class="text-primary mb-2">
                <i class="fas fa-credit-card"></i> Detalhes do Pagamento
            </h6>
            
            <div class="info-row">
                <span class="info-label">Descrição:</span>
                <span class="info-value">{{ $payment->description }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Método de Pagamento:</span>
                <span class="info-value">
                    @php
                        $paymentMethodText = match($payment->payment_method) {
                            'pix' => 'PIX',
                            'credit_card' => 'Cartão de Crédito',
                            'boleto' => 'Boleto Bancário',
                            'debit_card' => 'Cartão de Débito',
                            default => ucfirst(str_replace('_', ' ', $payment->payment_method))
                        };
                    @endphp
                    {{ $paymentMethodText }}
                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-confirmed">
                        <i class="fas fa-check-circle"></i> CONFIRMADO
                    </span>
                </span>
            </div>
        </div>
        
        <!-- Valor Pago -->
        <div class="amount-highlight">
            <p class="amount-label">Valor Recebido</p>
            <p class="amount-value">R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
        </div>
        
        <!-- Rodapé -->
        <div class="receipt-footer">
            <p class="mb-2">
                <strong>Este documento comprova o recebimento do pagamento acima descrito.</strong>
            </p>
            <p class="mb-2">
                <i class="fas fa-shield-alt"></i>
                Pagamento processado com segurança através do Orça Já! Sistema de Orçamentos.
            </p>
            
            
            <div class="mt-4">
                <small class="text-muted">
                    {{ config('app.name') }} - Sistema de Orçamentos<br>
                    Documento gerado automaticamente em {{ now()->format('d/m/Y H:i:s') }}
                </small>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus para impressão
        window.addEventListener('load', function() {
            // Opcional: abrir diálogo de impressão automaticamente
            // window.print();
        });
    </script>
</body>
</html>
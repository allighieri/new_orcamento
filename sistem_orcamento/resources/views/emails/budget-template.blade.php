<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento {{ $budgetNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #8A2BE2 0%, #6A1B9A 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px 20px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .budget-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #8A2BE2;
        }
        
        .budget-number {
            font-size: 20px;
            font-weight: 600;
            color: #8A2BE2;
            margin-bottom: 10px;
        }
        
        .budget-value {
            font-size: 28px;
            font-weight: 700;
            color: #2e7d32;
            margin: 15px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
        }
        
        .info-value {
            font-weight: 600;
            color: #333;
        }
        
        .message {
            background-color: #fff3e0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
        }
        
        .message p {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #8A2BE2 0%, #6A1B9A 100%);
            color: white !important;
            text-decoration: none !important;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .cta-button,
        .cta-button:link,
        .cta-button:visited,
        .cta-button:hover,
        .cta-button:active {
            color: white !important;
            text-decoration: none !important;
        }

        .cta-button a{
            color: white !important;
            text-decoration: none !important;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 25px 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        
        .company-info {
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #8A2BE2;
            text-decoration: none;
            font-size: 14px;
        }
        
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
        
        .attachment-notice {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
            font-size: 14px;
            color: #1565c0;
        }
        
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .budget-value {
                font-size: 24px;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-value {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Novo Orçamento</h1>
            <p>Conforme solicitado, segue o orçamento detalhado</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Olá, <strong>{{ $recipientName }}</strong>! 👋
            </div>
            
            <p>Esperamos que você esteja bem! Conforme nossa conversa, preparamos um orçamento personalizado para você.</p>
            
            <!-- Budget Info -->
            <div class="budget-info">
                <div class="budget-number">
                    Orçamento #{{ $budgetNumber }}
                </div>
                
                <div class="budget-value">
                    R$ {{ $budgetValue }}
                </div>
                
                <div class="info-row">
                    <span class="info-label">Data de emissão:</span>
                    <span class="info-value">{{ $budgetDate }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Previsão de Entrega:</span>
                    <span class="info-value">{{ $deliveryDate }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Validade:</span>
                    <span class="info-value">{{ $budgetValidity }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ $budgetStatus }}</span>
                </div>
            </div>
            
            <!-- Message -->
            <div class="message">
                <p><strong>💡 Sobre este orçamento:</strong></p>
                <p>Este orçamento foi elaborado especialmente para atender às suas necessidades. Todos os valores e especificações foram cuidadosamente calculados para oferecer a melhor relação custo-benefício.</p>
                <p>Caso tenha alguma dúvida ou precise de ajustes, não hesite em entrar em contato conosco!</p>
            </div>
            
            <!-- Attachment Notice -->
            <div class="attachment-notice">
                📎 <strong>Anexo:</strong> O orçamento completo em PDF está anexado a este email para sua conveniência.
            </div>
            
            <!-- CTA Section -->
            <div class="cta-section">
                <p style="margin-bottom: 15px; color: #666;">Pronto para dar o próximo passo?</p>
                <a href="tel:{{ $companyPhone }}" class="cta-button">
                    📞 Entrar em Contato
                </a>
            </div>
            
            <div class="divider"></div>
            
            <p style="color: #666; font-size: 14px; text-align: center;">
                Agradecemos pela confiança e esperamos trabalhar juntos em breve! 🤝
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="company-info">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-details">
                    📍 {{ $companyAddress }}, {{ $companyCity }} - {{ $companyState }}<br>
                    📞 {{ $companyPhone }}<br>
                    ✉️ {{ $companyEmail }}
                </div>
            </div>
            
            <!--
            <div class="social-links">
                <a href="#">🌐 Website</a>
                <a href="#">📱 WhatsApp</a>
                <a href="#">📧 Email</a>
            </div>
            -->
            
            <p style="font-size: 12px; color: #999; margin-top: 15px;">
                Este email foi enviado automaticamente pelo nosso sistema de orçamentos.
            </p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $emailTemplate->name }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .preview-header {
            background:rgb(219, 219, 219);
            color: ##999;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .preview-header h1 {
            margin: 0;
            font-size: 18px;
            display: inline-block;
        }
        .preview-actions {
            float: right;
        }
        .preview-actions button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .preview-actions button:hover {
            background: #5a6268;
        }
        .preview-actions .btn-primary {
            background: #007bff;
        }
        .preview-actions .btn-primary:hover {
            background: #0056b3;
        }
        .preview-info {
            background: #e9ecef;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .preview-info strong {
            color: #495057;
        }
        .preview-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-preview {
            padding: 20px;
        }
        .responsive-toggle {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .responsive-toggle button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 4px;
            cursor: pointer;
        }
        .responsive-toggle button.active {
            background: #007bff;
        }
        .mobile-view {
            max-width: 375px;
            margin: 0 auto;
        }
        .tablet-view {
            max-width: 768px;
            margin: 0 auto;
        }
        .desktop-view {
            max-width: 100%;
        }
        @media print {
            .preview-header,
            .preview-info,
            .responsive-toggle {
                display: none;
            }
            .preview-container {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <h1>📧 Preview: {{ $emailTemplate->name }}</h1>
        <div class="preview-actions">
            <button onclick="window.print()">🖨️ Imprimir</button>
            <button onclick="copyHtml()">📋 Copiar HTML</button>
            <button class="btn-primary" onclick="window.close()">✖️ Fechar</button>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="preview-info">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <strong>Assunto:</strong> {{ $renderedSubject }}
            </div>
            <div>
                <strong>Template:</strong> {{ $emailTemplate->name }}
                @if($emailTemplate->is_active)
                    <span style="color: #28a745;">✅ Ativo</span>
                @else
                    <span style="color: #dc3545;">❌ Inativo</span>
                @endif
            </div>
        </div>
    </div>

    <div class="responsive-toggle">
        <strong>Visualização:</strong>
        <button onclick="setView('mobile')" id="mobile-btn">📱 Mobile</button>
        <button onclick="setView('tablet')" id="tablet-btn">📱 Tablet</button>
        <button onclick="setView('desktop')" id="desktop-btn" class="active">🖥️ Desktop</button>
    </div>

    <div class="preview-container" id="preview-container">
        <div class="email-preview">
            {!! $renderedContent !!}
        </div>
    </div>

    <script>
        function setView(viewType) {
            const container = document.getElementById('preview-container');
            const buttons = document.querySelectorAll('.responsive-toggle button');
            
            // Remove active class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Remove all view classes
            container.classList.remove('mobile-view', 'tablet-view', 'desktop-view');
            
            // Add new view class and activate button
            container.classList.add(viewType + '-view');
            document.getElementById(viewType + '-btn').classList.add('active');
        }

        function copyHtml() {
            const htmlContent = @json($emailTemplate->html_content);
            
            navigator.clipboard.writeText(htmlContent).then(function() {
                // Criar notificação de sucesso
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    background: #28a745;
                    color: white;
                    padding: 15px 20px;
                    border-radius: 5px;
                    z-index: 9999;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                    font-family: Arial, sans-serif;
                `;
                notification.innerHTML = '✅ HTML copiado para a área de transferência!';
                
                document.body.appendChild(notification);
                
                // Remover após 3 segundos
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }).catch(function(err) {
                alert('Erro ao copiar: ' + err);
            });
        }

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'p':
                        e.preventDefault();
                        window.print();
                        break;
                    case 'c':
                        if (e.shiftKey) {
                            e.preventDefault();
                            copyHtml();
                        }
                        break;
                    case 'w':
                        e.preventDefault();
                        window.close();
                        break;
                }
            }
            
            // Teclas de visualização
            switch(e.key) {
                case '1':
                    setView('mobile');
                    break;
                case '2':
                    setView('tablet');
                    break;
                case '3':
                    setView('desktop');
                    break;
                case 'Escape':
                    window.close();
                    break;
            }
        });

        // Informações sobre atalhos
        console.log('Atalhos disponíveis:');
        console.log('Ctrl+P: Imprimir');
        console.log('Ctrl+Shift+C: Copiar HTML');
        console.log('Ctrl+W: Fechar');
        console.log('1: Visualização Mobile');
        console.log('2: Visualização Tablet');
        console.log('3: Visualização Desktop');
        console.log('Esc: Fechar');
    </script>
</body>
</html>
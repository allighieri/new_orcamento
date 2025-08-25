@extends('layouts.app')

@section('title', 'Editar Template de Email')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">📧 Editar Template: {{ $emailTemplate->name }}</h3>
                    <div>
                        <a href="{{ route('email-templates.preview', $emailTemplate) }}" class="btn btn-info me-2" target="_blank">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('email-templates.update', $emailTemplate) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome do Template *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $emailTemplate->name) }}" 
                                           placeholder="Ex: Orçamento Padrão" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Assunto do Email *</label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject', $emailTemplate->subject) }}" 
                                           placeholder="Ex: Novo Orçamento #12345" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2" 
                                      placeholder="Descreva o propósito deste template...">{{ old('description', $emailTemplate->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="html_content" class="form-label">Conteúdo HTML *</label>
                            <div class="row">
                                <div class="col-md-8">
                                    <textarea class="form-control @error('html_content') is-invalid @enderror" 
                                              id="html_content" name="html_content" rows="20" 
                                              placeholder="Cole aqui o código HTML do seu template..." required>{{ old('html_content', $emailTemplate->html_content) }}</textarea>
                                    @error('html_content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">📝 Variáveis Disponíveis</h6>
                                        </div>
                                        <div class="card-body">
                                            <small class="text-muted">Use estas variáveis no seu template:</small>
                                            <div class="mt-2">
                                                <code>@{{recipientName}}</code><br>
                                    <code>@{{budgetNumber}}</code><br>
                                    <code>@{{budgetValue}}</code><br>
                                    <code>@{{budgetDate}}</code><br>
                                    <code>@{{budgetValidity}}</code><br>
                                    <code>@{{budgetStatus}}</code><br>
                                    <code>@{{companyName}}</code><br>
                                    <code>@{{companyAddress}}</code><br>
                                    <code>@{{companyCity}}</code><br>
                                    <code>@{{companyState}}</code><br>
                                    <code>@{{companyPhone}}</code><br>
                                    <code>@{{companyEmail}}</code><br>
                                            </div>
                                            <hr>
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-sm btn-info" onclick="loadSampleTemplate()">
                                                    <i class="fas fa-magic"></i> Template Exemplo
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="resetToOriginal()">
                                                    <i class="fas fa-undo"></i> Restaurar Original
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Template ativo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Criado em: {{ $emailTemplate->created_at->format('d/m/Y H:i') }}<br>
                                        Última atualização: {{ $emailTemplate->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <div>
                                <a href="{{ route('email-templates.preview', $emailTemplate) }}" class="btn btn-info me-2" target="_blank">
                                    <i class="fas fa-eye"></i> Visualizar Preview
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const originalContent = @json($emailTemplate->html_content);

function loadSampleTemplate() {
    const sampleTemplate = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #8A2BE2 0%, #6A1B9A 100%); color: white; padding: 30px 20px; text-align: center; }
        .content { padding: 30px 20px; }
        .budget-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #8A2BE2 0%, #6A1B9A 100%); color: white !important; text-decoration: none !important; padding: 12px 30px; border-radius: 25px; font-weight: 600; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e0e0e0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Novo Orçamento</h1>
            <p>Conforme solicitado, segue o orçamento detalhado</p>
        </div>
        
        <div class="content">
            <p>Olá, <strong>{{recipientName}}</strong>! 👋</p>
            
            <p>Esperamos que você esteja bem! Conforme nossa conversa, preparamos um orçamento personalizado para você.</p>
            
            <div class="budget-info">
                <h3>Orçamento #{{budgetNumber}}</h3>
                <p><strong>Valor:</strong> R$ {{budgetValue}}</p>
                <p><strong>Data:</strong> {{budgetDate}}</p>
                <p><strong>Validade:</strong> {{budgetValidity}}</p>
                <p><strong>Status:</strong> {{budgetStatus}}</p>
            </div>
            
            <p>📎 <strong>Anexo:</strong> O orçamento completo em PDF está anexado a este email.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="tel:{{companyPhone}}" class="cta-button">
                    📞 Entrar em Contato
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>{{companyName}}</strong></p>
            <p>{{companyAddress}}, {{companyCity}} - {{companyState}}</p>
            <p>📞 {{companyPhone}} | ✉️ {{companyEmail}}</p>
        </div>
    </div>
</body>
</html>`;
    
    if (confirm('Isso substituirá o conteúdo atual. Deseja continuar?')) {
        document.getElementById('html_content').value = sampleTemplate;
    }
}

function resetToOriginal() {
    if (confirm('Isso restaurará o conteúdo original do template. Deseja continuar?')) {
        document.getElementById('html_content').value = originalContent;
    }
}
</script>
@endpush
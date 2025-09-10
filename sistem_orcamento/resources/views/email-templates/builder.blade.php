@extends('layouts.app')

@section('title', 'Construtor de Template de Email')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="bi bi-palette"></i> Construtor Visual de Template
                    </h4>
                    <div>
                        <a href="{{ route('email-templates.index') }}" class="btn btn-secondary btn-sm me-2">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <button type="button" class="btn btn-success btn-sm" onclick="saveTemplate()">
                            <i class="bi bi-check-circle"></i> Salvar Template
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Painel de Opções -->
                        <div class="col-md-3 border-end bg-light">
                            <div class="p-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-gear"></i> Configurações
                                </h6>
                                
                                <!-- Informações Básicas -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Nome do Template</label>
                                    <input type="text" class="form-control form-control-sm" id="templateName" placeholder="Ex: Orçamento Padrão">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Assunto do Email</label>
                                    <input type="text" class="form-control form-control-sm" id="templateSubject" placeholder="Ex: Novo Orçamento">
                                </div>
                                
                                <!-- Templates Pré-definidos -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Escolha um Modelo</label>
                                    <div class="template-options">
                                        <div class="template-option" data-template="modern" onclick="selectTemplate('modern')">
                                            <div class="template-preview modern-preview">
                                                <div class="preview-header"></div>
                                                <div class="preview-content"></div>
                                            </div>
                                            <span class="template-name">Moderno</span>
                                        </div>
                                        
                                        <div class="template-option" data-template="classic" onclick="selectTemplate('classic')">
                                            <div class="template-preview classic-preview">
                                                <div class="preview-header"></div>
                                                <div class="preview-content"></div>
                                            </div>
                                            <span class="template-name">Clássico</span>
                                        </div>
                                        
                                        <div class="template-option" data-template="minimal" onclick="selectTemplate('minimal')">
                                            <div class="template-preview minimal-preview">
                                                <div class="preview-header"></div>
                                                <div class="preview-content"></div>
                                            </div>
                                            <span class="template-name">Minimalista</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Personalização -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Cor Principal</label>
                                    <div class="color-options">
                                        <div class="color-option" style="background: #0dcaf0" onclick="changeColor('#0dcaf0')"></div>
                                        <div class="color-option" style="background: #17a2b8" onclick="changeColor('#17a2b8')"></div>
                                        <div class="color-option" style="background: #20c997" onclick="changeColor('#20c997')"></div>
                                        <div class="color-option" style="background: #8A2BE2" onclick="changeColor('#8A2BE2')"></div>
                                        <div class="color-option" style="background: #007bff" onclick="changeColor('#007bff')"></div>
                                        <div class="color-option" style="background: #28a745" onclick="changeColor('#28a745')"></div>
                                        <div class="color-option" style="background: #dc3545" onclick="changeColor('#dc3545')"></div>
                                        <div class="color-option" style="background: #fd7e14" onclick="changeColor('#fd7e14')"></div>
                                        <div class="color-option" style="background: #ffc107" onclick="changeColor('#ffc107')"></div>
                                    </div>
                                    <input type="color" class="form-control form-control-color mt-2" id="customColor" onchange="changeColor(this.value)" title="Cor personalizada">
                                </div>
                                
                                <!-- Personalização do Cabeçalho -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Cabeçalho</label>
                                    <input type="text" class="form-control form-control-sm" id="companyHeader" placeholder="Ex: Minha Empresa Ltda">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Cabeçalho 2</label>
                                    <input type="text" class="form-control form-control-sm" id="budgetHeader" placeholder="Ex: Orçamento Nº {BUDGET_NUMBER}" value="Orçamento Nº {BUDGET_NUMBER}">
                                </div>
                                
                                <!-- Texto Personalizado -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Mensagem Inicial</label>
                                    <div class="formatting-toolbar mb-2">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('mainMessage', 'bold')" title="Negrito"><i class="bi bi-type-bold"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('mainMessage', 'italic')" title="Itálico"><i class="bi bi-type-italic"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('mainMessage', 'underline')" title="Sublinhado"><i class="bi bi-type-underline"></i></button>
                                        </div>
                                        <div class="btn-group btn-group-sm ms-2" role="group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('mainMessage', 'justifyLeft')" title="Alinhar à esquerda"><i class="bi bi-text-left"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('mainMessage', 'justifyCenter')" title="Centralizar"><i class="bi bi-text-center"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('mainMessage', 'justifyRight')" title="Alinhar à direita"><i class="bi bi-text-right"></i></button>
                                        </div>
                                        <input type="color" class="btn btn-outline-secondary ms-2" id="mainMessageColor" onchange="formatText('mainMessage', 'foreColor', this.value)" title="Cor do texto" style="width: 40px; height: 31px;">
                                    </div>
                                    <div contenteditable="true" class="form-control form-control-sm" id="mainMessage" style="min-height: 80px; white-space: pre-wrap;" placeholder="Olá [Nome do Cliente], segue em anexo seu orçamento..." oninput="updatePreview()"></div>
                                </div>
                                
                                <div class="mb-4">
                    <label class="form-label fw-bold">Mensagem Final</label>
                    <div class="formatting-toolbar mb-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerMessage', 'bold')" title="Negrito"><i class="bi bi-type-bold"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerMessage', 'italic')" title="Itálico"><i class="bi bi-type-italic"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerMessage', 'underline')" title="Sublinhado"><i class="bi bi-type-underline"></i></button>
                        </div>
                        <div class="btn-group btn-group-sm ms-2" role="group">
                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerMessage', 'justifyLeft')" title="Alinhar à esquerda"><i class="bi bi-text-left"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerMessage', 'justifyCenter')" title="Centralizar"><i class="bi bi-text-center"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerMessage', 'justifyRight')" title="Alinhar à direita"><i class="bi bi-text-right"></i></button>
                        </div>
                        <input type="color" class="btn btn-outline-secondary ms-2" id="footerMessageColor" onchange="formatText('footerMessage', 'foreColor', this.value)" title="Cor do texto" style="width: 40px; height: 31px;">
                    </div>
                    <div contenteditable="true" class="form-control form-control-sm" id="footerMessage" style="min-height: 60px; white-space: pre-wrap;" placeholder="Obrigado pela preferência!" oninput="updatePreview()"><h4>💡 Sobre este orçamento:</h4><p> Este orçamento foi elaborado especialmente para atender às suas necessidades. Todos os valores e especificações foram cuidadosamente calculados para oferecer a melhor relação custo-benefício.</p><p> Caso tenha alguma dúvida ou precise de ajustes, não hesite em entrar em contato conosco!</p></div>
                </div>
                                
                                <!-- Rodapé -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Rodapé</label>
                                    <div class="formatting-toolbar mb-2">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerText', 'bold')" title="Negrito"><i class="bi bi-type-bold"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerText', 'italic')" title="Itálico"><i class="bi bi-type-italic"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerText', 'underline')" title="Sublinhado"><i class="bi bi-type-underline"></i></button>
                                        </div>
                                        <div class="btn-group btn-group-sm ms-2" role="group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerText', 'justifyLeft')" title="Alinhar à esquerda"><i class="bi bi-text-left"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerText', 'justifyCenter')" title="Centralizar"><i class="bi bi-text-center"></i></button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="formatText('footerText', 'justifyRight')" title="Alinhar à direita"><i class="bi bi-text-right"></i></button>
                                        </div>
                                        <input type="color" class="btn btn-outline-secondary ms-2" id="footerTextColor" onchange="formatText('footerText', 'foreColor', this.value)" title="Cor do texto" style="width: 40px; height: 31px;">
                                    </div>
                                    <div contenteditable="true" class="form-control form-control-sm" id="footerText" style="min-height: 80px; white-space: pre-wrap;" placeholder="Digite as informações da empresa..." oninput="updatePreview()"></div>
                                </div>
                                
                                <!-- Opções de Detalhes do Orçamento -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Detalhes do Orçamento</label>
                                    <div class="budget-options">
                                        <small class="text-muted d-block mb-2">Selecione quais informações incluir:</small>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="showBudgetNumber" checked onchange="updatePreview()">
                                            <label class="form-check-label" for="showBudgetNumber">Número do Orçamento</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="showBudgetValue" checked onchange="updatePreview()">
                                            <label class="form-check-label" for="showBudgetValue">Valor do Orçamento</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="showBudgetDate" checked onchange="updatePreview()">
                                            <label class="form-check-label" for="showBudgetDate">Data do Orçamento</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="showBudgetValidity" checked onchange="updatePreview()">
                                            <label class="form-check-label" for="showBudgetValidity">Validade do Orçamento</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" id="showDeliveryDate" onchange="updatePreview()">
                            <label class="form-check-label" for="showDeliveryDate">Data de Entrega</label>
                        </div>
                                    </div>
                                </div>
                                
                                <!-- Variáveis Disponíveis -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Variáveis Disponíveis</label>
                                    <div class="variables-list">
                                        <small class="text-muted d-block mb-2">Clique para inserir:</small>
                                        <div class="variable-tag" onclick="insertVariable('recipientName')">Nome do Destinatário</div>
                                        <div class="variable-tag" onclick="insertVariable('budgetNumber')">Número do Orçamento</div>
                                        <div class="variable-tag" onclick="insertVariable('budgetValue')">Valor do Orçamento</div>
                                        <div class="variable-tag" onclick="insertVariable('budgetDate')">Data do Orçamento</div>
                                        <div class="variable-tag" onclick="insertVariable('deliveryDate')">Data de Entrega</div>
                                        <div class="variable-tag" onclick="insertVariable('budgetValidity')">Validade do Orçamento</div>
                                        <div class="variable-tag" onclick="insertVariable('budgetStatus')">Status do Orçamento</div>
                                        <div class="variable-tag" onclick="insertVariable('companyName')">Nome da Empresa</div>
                                        <div class="variable-tag" onclick="insertVariable('companyAddress')">Endereço da Empresa</div>
                                        <div class="variable-tag" onclick="insertVariable('companyCity')">Cidade da Empresa</div>
                                        <div class="variable-tag" onclick="insertVariable('companyState')">Estado da Empresa</div>
                                        <div class="variable-tag" onclick="insertVariable('companyPhone')">Telefone da Empresa</div>
                                        <div class="variable-tag" onclick="insertVariable('companyEmail')">Email da Empresa</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview do Template -->
                        <div class="col-md-9">
                            <div class="sticky-preview-wrapper" style="position: sticky; top: 20px; height: calc(100vh - 40px);">
                                <div class="p-3 h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0">
                                            <i class="bi bi-eye"></i> Visualização do Template
                                        </h6>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="previewMode" id="desktop" checked>
                                            <label class="btn btn-outline-secondary" for="desktop">
                                                <i class="bi bi-display"></i> Desktop
                                            </label>
                                            <input type="radio" class="btn-check" name="previewMode" id="mobile">
                                            <label class="btn btn-outline-secondary" for="mobile">
                                                <i class="bi bi-phone"></i> Mobile
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="template-preview-container flex-grow-1" id="templatePreview" style="overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; background: #f8f9fa;">
                                        <div class="email-preview" id="emailPreview">
                                            <!-- O conteúdo do template será inserido aqui via JavaScript -->
                                            <div class="placeholder-content">
                                                <div class="text-center py-5">
                                                    <i class="bi bi-envelope-open text-muted" style="font-size: 3rem;"></i>
                                                    <h5 class="text-muted mt-3">Selecione um modelo para começar</h5>
                                                    <p class="text-muted">Escolha um dos modelos ao lado para visualizar o template</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Salvamento -->
<div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Salvar Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="saveTemplateForm">
                    @csrf
                    <input type="hidden" id="htmlContent" name="html_content">
                    <div class="mb-3">
                        <label class="form-label">Nome do Template *</label>
                        <input type="text" class="form-control" name="name" id="finalTemplateName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assunto do Email *</label>
                        <input type="text" class="form-control" name="subject" id="finalTemplateSubject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Descreva o propósito deste template..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                        <label class="form-check-label" for="isActive">
                            Template ativo
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submitTemplate()">Salvar Template</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.template-options {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.template-option {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.template-option:hover {
    border-color: #8A2BE2;
    transform: translateY(-2px);
}

.template-option.active {
    border-color: #8A2BE2;
    background-color: #f8f4ff;
}

.template-preview {
    height: 60px;
    border-radius: 4px;
    margin-bottom: 8px;
    position: relative;
    overflow: hidden;
}

.modern-preview {
    background: linear-gradient(135deg, #8A2BE2 0%, #6A1B9A 100%);
}

.classic-preview {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.minimal-preview {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.preview-header {
    height: 20px;
    background: rgba(255,255,255,0.2);
    margin: 5px;
    border-radius: 2px;
}

.preview-content {
    height: 25px;
    background: rgba(255,255,255,0.1);
    margin: 5px;
    border-radius: 2px;
}

.minimal-preview .preview-header,
.minimal-preview .preview-content {
    background: rgba(0,0,0,0.1);
}

.template-name {
    font-size: 12px;
    font-weight: 500;
    color: #495057;
}

.color-options {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    gap: 2px;
    margin-bottom: 10px;
}

.color-option {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.color-option:hover {
    transform: scale(1.1);
}

.color-option.active {
    border-color: #333;
    transform: scale(1.1);
}

.variables-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.variable-tag {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.variable-tag:hover {
    background: #8A2BE2;
    color: white;
}

.template-preview-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    min-height: 600px;
}

.email-preview {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 0 auto;
    overflow: hidden;
}

.placeholder-content {
    padding: 40px 20px;
}

#mobile:checked ~ .template-preview-container .email-preview {
    max-width: 375px;
}

.btn-group-sm .btn {
    font-size: 12px;
}
</style>
@endpush

@push('scripts')
<script>
// Definir variáveis globais
window.currentTemplate = null;
window.currentColor = '#8A2BE2';
window.templates = {};

// Aliases para compatibilidade
let currentTemplate = null;
let currentColor = '#8A2BE2';
let templates = window.templates;

// Templates pré-definidos
window.templates.modern = templates.modern = {
    name: 'Moderno',
    html: `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>\{\{budgetNumber\}\}</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f5f5;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
            <div style="background: linear-gradient(135deg, {PRIMARY_COLOR} 0%, {SECONDARY_COLOR} 100%); color: white; padding: 30px 20px; text-align: center;">
                <h1 style="font-size: 24px; font-weight: 600; margin: 0 0 8px 0;">{COMPANY_HEADER}</h1>
                <p style="font-size: 16px; margin: 0; opacity: 0.9;">{BUDGET_HEADER}</p>
            </div>
            <div style="padding: 30px 20px;">
                <p style="font-size: 18px; margin-bottom: 20px; color: #333;">{MAIN_MESSAGE}</p>
                <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid {PRIMARY_COLOR};">
                    <h3 style="margin: 0 0 15px 0; color:{PRIMARY_COLOR};">Detalhes do Orçamento</h3>
                    <p style="margin: 5px 0;"><strong>Número:</strong> \{\{budgetNumber\}\}</p>
                    <p style="margin: 5px 0;"><strong>Valor:</strong> R$ \{\{budgetValue\}\}</p>
                    <p style="margin: 5px 0;"><strong>Data:</strong> \{\{budgetDate\}\}</p>
                    <p style="margin: 5px 0;"><strong>Validade:</strong> \{\{budgetValidity\}\}</p>
                </div>
                <div style="background-color: #f8f9fa; border-radius: 8px; padding: 5px 20px; margin: 20px 0; border-left: 4px solid {TERTIARY_COLOR};">
                    <p style="color: #666; line-height: 1.6;">{FOOTER_MESSAGE}</p>
                </div>    
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                    <p style="margin: 5px 0; color: #666;"><strong>\{\{companyName\}\}</strong></p>
                    <p style="margin: 5px 0; color: #666;">📞 \{\{companyPhone\}\} | 📧 \{\{companyEmail\}\}</p>
                </div>
            </div>
        </div>
    </body>
    </html>`
};

window.templates.classic = templates.classic = {
    name: 'Clássico',
    html: `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>\{\{budgetNumber\}\}</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Georgia, serif; background-color: #f9f9f9;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #ddd;">
            <div style="background-color: {PRIMARY_COLOR}; color: white; padding: 20px; text-align: center;">
                <h1 style="font-size: 28px; margin: 0; font-weight: normal;">{COMPANY_HEADER}</h1>
            </div>
            <div style="padding: 30px;">
                <h2 style="color: {PRIMARY_COLOR}; border-bottom: 2px solid {PRIMARY_COLOR}; padding-bottom: 10px;">{BUDGET_HEADER}</h2>
                <p style="font-size: 16px; line-height: 1.6; color: #333;">{MAIN_MESSAGE}</p>
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr style="background-color: #f8f9fa;">
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">Número do Orçamento</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">\{\{budgetNumber\}\}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">Valor Total</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">R$ \{\{budgetValue\}\}</td>
                    </tr>
                    <tr style="background-color: #f8f9fa;">
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">Data</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">\{\{budgetDate\}\}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">Validade</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">\{\{budgetValidity\}\}</td>
                    </tr>
                </table>
                <p style="color: #666; line-height: 1.6;">{FOOTER_MESSAGE}</p>
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
                <div style="text-align: center; color: #666;">
                    <p style="margin: 5px 0;"><strong>\{\{companyName\}\}</strong></p>
                    <p style="margin: 5px 0;">\{\{companyPhone\}\} | \{\{companyEmail\}\}</p>
                </div>
            </div>
        </div>
    </body>
    </html>`
};

window.templates.minimal = templates.minimal = {
    name: 'Minimalista',
    html: `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>\{\{budgetNumber\}\}</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #ffffff;">
        <div style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h1 style="font-size: 32px; font-weight: 300; color: {PRIMARY_COLOR}; margin: 0;">{COMPANY_HEADER}</h1>
                <div style="width: 50px; height: 2px; background-color: {PRIMARY_COLOR}; margin: 20px auto;"></div>
            </div>
            <div style="margin-bottom: 40px;">
                <p style="font-size: 18px; line-height: 1.6; color: #333; margin-bottom: 30px;">{MAIN_MESSAGE}</p>
                <div style="background-color: #fafafa; padding: 30px; border-left: 3px solid {PRIMARY_COLOR};">
                    <h2 style="font-size: 20px; font-weight: 400; color: #333; margin: 0 0 20px 0;">{BUDGET_HEADER}</h2>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #666;">Valor:</span>
                        <span style="font-weight: 500; color: #333;">R$ \{\{budgetValue\}\}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #666;">Data:</span>
                        <span style="color: #333;">\{\{budgetDate\}\}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Validade:</span>
                        <span style="color: #333;">\{\{budgetValidity\}\}</span>
                    </div>
                </div>
            </div>
            <p style="color: #666; line-height: 1.6; margin-bottom: 40px;">{FOOTER_MESSAGE}</p>
            <div style="text-align: center; padding-top: 30px; border-top: 1px solid #eee;">
                <p style="margin: 0; color: #999; font-size: 14px;">\{\{companyPhone\}\} | \{\{companyEmail\}\}</p>
            </div>
        </div>
    </body>
    </html>`
};

// Definir função no escopo global
window.selectTemplate = function(templateType) {
    // Remove active class from all options
    document.querySelectorAll('.template-option').forEach(option => {
        option.classList.remove('active');
    });
    
    // Add active class to selected option
    const templateElement = document.querySelector(`[data-template="${templateType}"]`);
    if (templateElement) {
        templateElement.classList.add('active');
    }
    
    // Sincronizar variáveis globais e locais
    window.currentTemplate = templateType;
    currentTemplate = templateType;
    updatePreview();
}

// Alias para compatibilidade
function selectTemplate(templateType) {
    return window.selectTemplate(templateType);
}

function changeColor(color) {
    currentColor = color;
    
    // Update color options visual feedback
    document.querySelectorAll('.color-option').forEach(option => {
        option.classList.remove('active');
    });
    
    // Encontrar a opção de cor específica pelo valor exato do background
    document.querySelectorAll('.color-option').forEach(option => {
        const bgColor = option.style.background || option.style.backgroundColor;
        if (bgColor === color || bgColor.toLowerCase() === color.toLowerCase()) {
            option.classList.add('active');
        }
    });
    
    updatePreview();
}

function formatText(elementId, command, value) {
    const element = document.getElementById(elementId);
    element.focus();
    document.execCommand(command, false, value);
    updatePreview();
}

function updatePreview() {
    if (!currentTemplate) return;
    
    const template = templates[currentTemplate];
    const mainMessage = document.getElementById('mainMessage').innerHTML || 'Olá {RECIPIENT_NAME}!👋 <br />Esperamos que você esteja bem! Conforme nossa conversa, preparamos um orçamento personalizado para você.';
    const footerMessage = document.getElementById('footerMessage').innerHTML || '<h4>💡 Sobre este orçamento:</h4><p> Este orçamento foi elaborado especialmente para atender às suas necessidades. Todos os valores e especificações foram cuidadosamente calculados para oferecer a melhor relação custo-benefício.</p><p> Caso tenha alguma dúvida ou precise de ajustes, não hesite em entrar em contato conosco!</p>';
    const footerText = document.getElementById('footerText').innerHTML || '';
    const companyHeader = document.getElementById('companyHeader').value || '{COMPANY_NAME}';
    const budgetHeader = document.getElementById('budgetHeader').value || 'Orçamento #{BUDGET_NUMBER}';
    
    // Get budget detail options
    const showBudgetNumber = document.getElementById('showBudgetNumber').checked;
    const showBudgetValue = document.getElementById('showBudgetValue').checked;
    const showBudgetDate = document.getElementById('showBudgetDate').checked;
    const showBudgetValidity = document.getElementById('showBudgetValidity').checked;
    const showDeliveryDate = document.getElementById('showDeliveryDate').checked;
    
    // Calculate secondary color (darker version of primary)
    const secondaryColor = darkenColor(currentColor, 20);
    const tertiaryColor = lightenColor(currentColor, 30);
    
    let html = template.html
        .replace(/{PRIMARY_COLOR}/g, currentColor)
        .replace(/{SECONDARY_COLOR}/g, secondaryColor)
        .replace(/{TERTIARY_COLOR}/g, tertiaryColor)
        .replace(/{MAIN_MESSAGE}/g, mainMessage)
        .replace(/{FOOTER_MESSAGE}/g, footerMessage)
        .replace(/{COMPANY_HEADER}/g, companyHeader)
        .replace(/{BUDGET_HEADER}/g, budgetHeader)
        .replace(/{RECIPIENT_NAME}/g, '{' + '{recipientName}' + '}')
        .replace(/{COMPANY_NAME}/g, '{' + '{companyName}' + '}')
        .replace(/{BUDGET_NUMBER}/g, '{' + '{budgetNumber}' + '}')
        .replace(/{BUDGET_VALUE}/g, '{' + '{budgetValue}' + '}')
        .replace(/{BUDGET_DATE}/g, '{' + '{budgetDate}' + '}')
        .replace(/{BUDGET_VALIDITY}/g, '{' + '{budgetValidity}' + '}')
        .replace(/{COMPANY_PHONE}/g, '{' + '{companyPhone}' + '}')
        .replace(/{COMPANY_EMAIL}/g, '{' + '{companyEmail}' + '}');
    
    // Replace footer section with custom footer text only if footerText is not empty
    if (footerText && footerText.trim() !== '') {
        // Replace footer sections with custom footer text
        // For modern template footer
        html = html.replace(/<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">([\s\S]*?)<\/div>/g, 
            `<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                <div style="color: #666; line-height: 1.6;">${footerText}</div>
            </div>`);
        
        // For classic template footer
        html = html.replace(/<div style="text-align: center; color: #666;">([\s\S]*?)<\/div>/g,
            `<div style="text-align: center; color: #666;">
                <div style="line-height: 1.6;">${footerText}</div>
            </div>`);
            
        // For minimal template footer
        html = html.replace(/<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid [^"]*; color: #666;">([\s\S]*?)<\/div>/g,
            `<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid ${currentColor}; color: #666;">
                <div style="line-height: 1.6;">${footerText}</div>
            </div>`);
    } else {
        // Restaurar valores padrão quando rodapé estiver vazio
        html = html.replace(/\{\{companyName\}\}/g, '\{\{companyName\}\}');
        html = html.replace(/\{\{companyPhone\}\}/g, '\{\{companyPhone\}\}');
        html = html.replace(/\{\{companyEmail\}\}/g, '\{\{companyEmail\}\}');
    }
    
    // Remove budget details based on user selection
    if (!showBudgetNumber) {
        // Remove linha da tabela com "Número do Orçamento" (template clássico)
        html = html.replace(/<tr[^>]*>\s*<td[^>]*>Número do Orçamento<\/td>\s*<td[^>]*>\{\{budgetNumber\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Número:" (template moderno)
        html = html.replace(/<p[^>]*>\s*<strong>Número:<\/strong>\s*\{\{budgetNumber\}\}\s*<\/p>/gi, '');
        // Remove div com "Número:" (template minimalista) - não existe no template atual
    }
    if (!showBudgetValue) {
        // Remove linha da tabela com "Valor Total" (template clássico)
        html = html.replace(/<tr[^>]*>\s*<td[^>]*>Valor Total<\/td>\s*<td[^>]*>R\$ \{\{budgetValue\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Valor:" (template moderno)
        html = html.replace(/<p[^>]*>\s*<strong>Valor:<\/strong>\s*R\$ \{\{budgetValue\}\}\s*<\/p>/gi, '');
        // Remove div com "Valor:" (template minimalista)
        html = html.replace(/<div[^>]*>\s*<span[^>]*>Valor:<\/span>\s*<span[^>]*>R\$ \{\{budgetValue\}\}<\/span>\s*<\/div>/gi, '');
    }
    if (!showBudgetDate) {
        // Remove linha da tabela com "Data" (template clássico)
        html = html.replace(/<tr[^>]*>\s*<td[^>]*>Data<\/td>\s*<td[^>]*>\{\{budgetDate\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Data:" (template moderno)
        html = html.replace(/<p[^>]*>\s*<strong>Data:<\/strong>\s*\{\{budgetDate\}\}\s*<\/p>/gi, '');
        // Remove div com "Data:" (template minimalista)
        html = html.replace(/<div[^>]*>\s*<span[^>]*>Data:<\/span>\s*<span[^>]*>\{\{budgetDate\}\}<\/span>\s*<\/div>/gi, '');
    }
    if (!showBudgetValidity) {
        // Remove linha da tabela com "Validade" (template clássico)
        html = html.replace(/<tr[^>]*>\s*<td[^>]*>Validade<\/td>\s*<td[^>]*>\{\{budgetValidity\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Validade:" (template moderno)
        html = html.replace(/<p[^>]*>\s*<strong>Validade:<\/strong>\s*\{\{budgetValidity\}\}\s*<\/p>/gi, '');
        // Remove div com "Validade:" (template minimalista)
        html = html.replace(/<div[^>]*>\s*<span[^>]*>Validade:<\/span>\s*<span[^>]*>\{\{budgetValidity\}\}<\/span>\s*<\/div>/gi, '');
    }
    if (!showDeliveryDate) {
        // Remove linha da tabela com "Data de Entrega" (template clássico) - não existe no template atual
        // Remove p com "Entrega:" (template moderno) - não existe no template atual
        // Remove div com "Entrega:" (template minimalista) - não existe no template atual
    }
    
    const previewContainer = document.getElementById('emailPreview');
    // Remove placeholder content first
    const placeholder = previewContainer.querySelector('.placeholder-content');
    if (placeholder) {
        placeholder.remove();
    }
    previewContainer.innerHTML = html;
}

function darkenColor(color, percent) {
    const num = parseInt(color.replace("#", ""), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) - amt;
    const G = (num >> 8 & 0x00FF) - amt;
    const B = (num & 0x0000FF) - amt;
    return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
        (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
        (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
}

function lightenColor(color, percent) {
    const num = parseInt(color.replace("#", ""), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) + amt;
    const G = (num >> 8 & 0x00FF) + amt;
    const B = (num & 0x0000FF) + amt;
    return "#" + (0x1000000 + (R > 255 ? 255 : R < 0 ? 0 : R) * 0x10000 +
        (G > 255 ? 255 : G < 0 ? 0 : G) * 0x100 +
        (B > 255 ? 255 : B < 0 ? 0 : B)).toString(16).slice(1);
}

let lastFocusedElement = null;
let savedSelection = null;
let savedCursorPosition = null;

// Track focused elements and save selection
document.addEventListener('focusin', function(e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
        lastFocusedElement = e.target;
    }
});

// Save cursor position on mouse up and key up events
document.addEventListener('mouseup', function(e) {
    if (e.target.contentEditable === 'true') {
        lastFocusedElement = e.target;
        saveSelection();
    }
});

document.addEventListener('keyup', function(e) {
    if (e.target.contentEditable === 'true') {
        lastFocusedElement = e.target;
        saveSelection();
    }
});

// Prevent losing focus when clicking on variable buttons
document.addEventListener('mousedown', function(e) {
    if (e.target.classList.contains('variable-tag')) {
        e.preventDefault();
    }
});

function saveSelection() {
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
        savedSelection = selection.getRangeAt(0).cloneRange();
    }
}

function restoreSelection() {
    if (savedSelection && lastFocusedElement) {
        lastFocusedElement.focus();
        const selection = window.getSelection();
        selection.removeAllRanges();
        try {
            selection.addRange(savedSelection);
        } catch (e) {
            // If range is invalid, place cursor at end
            const range = document.createRange();
            range.selectNodeContents(lastFocusedElement);
            range.collapse(false);
            selection.addRange(range);
        }
    }
}

function insertVariable(variable) {
    const variableText = `\{\{${variable}\}\}`;
    
    // If we have a saved selection and focused element, use it
    if (lastFocusedElement && lastFocusedElement.contentEditable === 'true') {
        // Restore focus and selection
        lastFocusedElement.focus();
        
        if (savedSelection) {
            restoreSelection();
        }
        
        // Use execCommand for better cursor position handling
        if (document.queryCommandSupported('insertText')) {
            document.execCommand('insertText', false, variableText);
        } else {
            // Fallback for browsers that don't support insertText
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.deleteContents();
                const textNode = document.createTextNode(variableText);
                range.insertNode(textNode);
                
                // Move cursor after inserted text
                range.setStartAfter(textNode);
                range.setEndAfter(textNode);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }
        
        // Save the new selection
        saveSelection();
        updatePreview();
        
    } else if (lastFocusedElement && (lastFocusedElement.tagName === 'INPUT' || lastFocusedElement.tagName === 'TEXTAREA')) {
        // Handle input and textarea elements
        const cursorPos = lastFocusedElement.selectionStart || lastFocusedElement.value.length;
        const textBefore = lastFocusedElement.value.substring(0, cursorPos);
        const textAfter = lastFocusedElement.value.substring(cursorPos);
        
        lastFocusedElement.value = textBefore + variableText + textAfter;
        lastFocusedElement.focus();
        
        // Set cursor position after the inserted variable
        const newCursorPos = cursorPos + variableText.length;
        lastFocusedElement.setSelectionRange(newCursorPos, newCursorPos);
        
        updatePreview();
    } else {
        // If no element is focused, show a message
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'Clique em um campo de texto primeiro, depois clique na variável que deseja inserir.',
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000
        });
    }
}

window.saveTemplate = function() {
    if (!currentTemplate) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'Por favor, selecione um modelo primeiro.',
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }
    
    const templateName = document.getElementById('templateName').value;
    const templateSubject = document.getElementById('templateSubject').value;
    
    if (!templateName || !templateSubject) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'Por favor, preencha o nome e assunto do template.',
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }
    
    // Populate modal fields
    document.getElementById('finalTemplateName').value = templateName;
    document.getElementById('finalTemplateSubject').value = templateSubject;
    
    // Generate final HTML
    const template = templates[currentTemplate];
    const mainMessage = document.getElementById('mainMessage').innerHTML || 'Olá {RECIPIENT_NAME}!👋 <br />Esperamos que você esteja bem! Conforme nossa conversa, preparamos um orçamento personalizado para você.';
    const footerMessage = document.getElementById('footerMessage').innerHTML || '<h4>💡 Sobre este orçamento:</h4><p> Este orçamento foi elaborado especialmente para atender às suas necessidades. Todos os valores e especificações foram cuidadosamente calculados para oferecer a melhor relação custo-benefício.</p><p> Caso tenha alguma dúvida ou precise de ajustes, não hesite em entrar em contato conosco!</p>';
    const footerText = document.getElementById('footerText').innerHTML || '';
    const companyHeader = document.getElementById('companyHeader').value || '\{\{companyName\}\}';
    const budgetHeader = document.getElementById('budgetHeader').value || 'Orçamento #\{\{budgetNumber\}\}';
    const secondaryColor = darkenColor(currentColor, 20);
    const tertiaryColor = lightenColor(currentColor, 30);
    
    // Get budget detail options
    const showBudgetNumber = document.getElementById('showBudgetNumber').checked;
    const showBudgetValue = document.getElementById('showBudgetValue').checked;
    const showBudgetDate = document.getElementById('showBudgetDate').checked;
    const showBudgetValidity = document.getElementById('showBudgetValidity').checked;
    const showDeliveryDate = document.getElementById('showDeliveryDate').checked;
    
    let finalHtml = template.html
        .replace(/\{PRIMARY_COLOR\}/g, currentColor)
        .replace(/\{SECONDARY_COLOR\}/g, secondaryColor)
        .replace(/\{TERTIARY_COLOR\}/g, tertiaryColor)
        .replace(/\{MAIN_MESSAGE\}/g, mainMessage)
        .replace(/\{FOOTER_MESSAGE\}/g, footerMessage)
        .replace(/\{COMPANY_HEADER\}/g, companyHeader)
        .replace(/\{BUDGET_HEADER\}/g, budgetHeader)
        .replace(/\{COMPANY_NAME\}/g, '\{\{companyName\}\}')
        .replace(/\{COMPANY_PHONE\}/g, '\{\{companyPhone\}\}')
        .replace(/\{COMPANY_EMAIL\}/g, '\{\{companyEmail\}\}')
        .replace(/\{BUDGET_NUMBER\}/g, '\{\{budgetNumber\}\}')
        .replace(/\{BUDGET_VALUE\}/g, '\{\{budgetValue\}\}')
        .replace(/\{BUDGET_DATE\}/g, '\{\{budgetDate\}\}')
        .replace(/\{BUDGET_VALIDITY\}/g, '\{\{budgetValidity\}\}')
        .replace(/\{RECIPIENT_NAME\}/g, '\{\{recipientName\}\}');
    
    // Handle footer replacement properly
    if (footerText && footerText.trim() !== '') {
        // Replace footer sections with custom footer text
        // For modern template footer
        finalHtml = finalHtml.replace(/<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">([\s\S]*?)<\/div>/g, 
            `<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                <div style="color: #666; line-height: 1.6;">${footerText}</div>
            </div>`);
        
        // For classic template footer
        finalHtml = finalHtml.replace(/<div style="text-align: center; color: #666;">([\s\S]*?)<\/div>/g,
            `<div style="text-align: center; color: #666;">
                <div style="line-height: 1.6;">${footerText}</div>
            </div>`);
            
        // For minimal template footer
        finalHtml = finalHtml.replace(/<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid [^"]*; color: #666;">([\s\S]*?)<\/div>/g,
            `<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid ${currentColor}; color: #666;">
                <div style="line-height: 1.6;">${footerText}</div>
            </div>`);
    } else {
        // Restore default values when footer text is empty
        finalHtml = finalHtml.replace(/\{\{companyName\}\}/g, '\{\{companyName\}\}');
        finalHtml = finalHtml.replace(/\{\{companyPhone\}\}/g, '\{\{companyPhone\}\}');
        finalHtml = finalHtml.replace(/\{\{companyEmail\}\}/g, '\{\{companyEmail\}\}');
    }
    
    // Remove budget details based on user selection
    if (!showBudgetNumber) {
        // Remove linha da tabela com "Número do Orçamento" (template clássico)
        finalHtml = finalHtml.replace(/<tr[^>]*>\s*<td[^>]*>Número do Orçamento<\/td>\s*<td[^>]*>\{\{budgetNumber\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Número:" (template moderno)
        finalHtml = finalHtml.replace(/<p[^>]*>\s*<strong>Número:<\/strong>\s*\{\{budgetNumber\}\}\s*<\/p>/gi, '');
        // Remove div com "Número:" (template minimalista) - não existe no template atual
    }
    if (!showBudgetValue) {
        // Remove linha da tabela com "Valor Total" (template clássico)
        finalHtml = finalHtml.replace(/<tr[^>]*>\s*<td[^>]*>Valor Total<\/td>\s*<td[^>]*>R\$ \{\{budgetValue\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Valor:" (template moderno)
        finalHtml = finalHtml.replace(/<p[^>]*>\s*<strong>Valor:<\/strong>\s*R\$ \{\{budgetValue\}\}\s*<\/p>/gi, '');
        // Remove div com "Valor:" (template minimalista)
        finalHtml = finalHtml.replace(/<div[^>]*>\s*<span[^>]*>Valor:<\/span>\s*<span[^>]*>R\$ \{\{budgetValue\}\}<\/span>\s*<\/div>/gi, '');
    }
    if (!showBudgetDate) {
        // Remove linha da tabela com "Data" (template clássico)
        finalHtml = finalHtml.replace(/<tr[^>]*>\s*<td[^>]*>Data<\/td>\s*<td[^>]*>\{\{budgetDate\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Data:" (template moderno)
        finalHtml = finalHtml.replace(/<p[^>]*>\s*<strong>Data:<\/strong>\s*\{\{budgetDate\}\}\s*<\/p>/gi, '');
        // Remove div com "Data:" (template minimalista)
        finalHtml = finalHtml.replace(/<div[^>]*>\s*<span[^>]*>Data:<\/span>\s*<span[^>]*>\{\{budgetDate\}\}<\/span>\s*<\/div>/gi, '');
    }
    if (!showBudgetValidity) {
        // Remove linha da tabela com "Validade" (template clássico)
        finalHtml = finalHtml.replace(/<tr[^>]*>\s*<td[^>]*>Validade<\/td>\s*<td[^>]*>\{\{budgetValidity\}\}<\/td>\s*<\/tr>/gi, '');
        // Remove p com "Validade:" (template moderno)
        finalHtml = finalHtml.replace(/<p[^>]*>\s*<strong>Validade:<\/strong>\s*\{\{budgetValidity\}\}\s*<\/p>/gi, '');
        // Remove div com "Validade:" (template minimalista)
        finalHtml = finalHtml.replace(/<div[^>]*>\s*<span[^>]*>Validade:<\/span>\s*<span[^>]*>\{\{budgetValidity\}\}<\/span>\s*<\/div>/gi, '');
    }
    if (!showDeliveryDate) {
        // Remove linha da tabela com "Data de Entrega" (template clássico) - não existe no template atual
        // Remove p com "Entrega:" (template moderno) - não existe no template atual
        // Remove div com "Entrega:" (template minimalista) - não existe no template atual
    }
    
    document.getElementById('htmlContent').value = finalHtml;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('saveModal')).show();
}

function submitTemplate() {
    const form = document.getElementById('saveTemplateForm');
    const formData = new FormData(form);
    
    fetch('{{ route("email-templates.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Sucesso!',
                text: 'Template criado com sucesso!',
                icon: 'success'
            }).then(() => {
                window.location.href = '{{ route("email-templates.index") }}';
            });
        } else {
            Swal.fire({
                title: 'Erro!',
                text: data.message || 'Erro ao criar template.',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'Erro ao processar solicitação.';
        if (error.errors) {
            errorMessage = Object.values(error.errors).flat().join('\n');
        } else if (error.message) {
            errorMessage = error.message;
        }
        Swal.fire({
            title: 'Erro!',
            text: errorMessage,
            icon: 'error'
        });
    });
}

// Event listeners
document.getElementById('mainMessage').addEventListener('input', updatePreview);
document.getElementById('footerMessage').addEventListener('input', updatePreview);
document.getElementById('companyHeader').addEventListener('input', updatePreview);
document.getElementById('budgetHeader').addEventListener('input', updatePreview);

// Preview mode toggle
document.getElementById('desktop').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('emailPreview').style.maxWidth = '600px';
        document.getElementById('emailPreview').style.margin = '0 auto';
    }
});

document.getElementById('mobile').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('emailPreview').style.maxWidth = '375px';
        document.getElementById('emailPreview').style.margin = '0 auto';
    }
});

// Create local aliases for global functions
const saveTemplate = window.saveTemplate;

// Initialize with modern template
document.addEventListener('DOMContentLoaded', function() {
    // Sincronizar variáveis locais com globais
    currentTemplate = window.currentTemplate;
    currentColor = window.currentColor;
    templates = window.templates;
    
    selectTemplate('modern');
    // Força a atualização inicial do preview
    setTimeout(function() {
        updatePreview();
    }, 100);
});
</script>
@endpush
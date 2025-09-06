@extends('layouts.app')

@section('title', 'Orçamentos - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>
                    <i class="bi bi-file-earmark-text"></i> 
                    @if($client)
                        Orçamentos de {{ $client->corporate_name ?? $client->fantasy_name }}
                    @else
                        Orçamentos
                    @endif
                </h1>
                @if($client)
                    <p class="text-muted mb-0">
                        <i class="bi bi-person"></i> Cliente: {{ $client->corporate_name ?? $client->fantasy_name }}
                    </p>
                @endif
            </div>
            <div>
                @if($client)
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Voltar ao Cliente
                    </a>
                    <a href="{{ route('budgets.index') }}" class="btn btn-outline-info me-2">
                        <i class="bi bi-list"></i> Todos os Orçamentos
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                @endif
               <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Novo Orçamento
            </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <!-- Formulário de Pesquisa -->
        <div class="mb-4">
           
                <form method="GET" action="{{ route('budgets.index') }}" class="row g-3">
                    <div class="col-md-4">
                       
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nome do cliente ou número do orçamento">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </form>
            
        </div>
        
        <div class="card">
            <div class="card-body">
                @if($budgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Número</th>
                                    <th style="width: 30%;">Cliente</th>
                                    @if(Auth::user()->hasRole('super_admin'))
                                    <th>Empresa</th>
                                    @endif
                                    <th style="width: 120px;">Data</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 120px;">Total</th>
                                    <th class="text-end" style="width: 1%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                <tr>
                                    <td><strong>{{ $budget->number }}</strong></td>
                                    <td>
                                        <a href="{{ route('clients.show', $budget->client) }}" class="text-decoration-none">
                                            {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }}
                                        </a>
                                    </td>
                                    @if(Auth::user()->hasRole('super_admin'))
                                    <td>
                                        <a href="{{ route('companies.show', $budget->company) }}" class="text-decoration-none">
                                            {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}
                                        </a>
                                    </td>
                                    @endif
                                    <td>{{ $budget->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge status-clickable info-status-badge-{{ $budget->id }}
                                            @if($budget->status == 'Pendente') bg-warning
                                            @elseif($budget->status == 'Enviado') bg-info
                                            @elseif($budget->status == 'Em negociação') bg-primary
                                            @elseif($budget->status == 'Aprovado') bg-success
                                            @elseif($budget->status == 'Expirado') bg-danger
                                            @elseif($budget->status == 'Concluído') bg-secondary
                                            @else bg-light text-dark
                                            @endif" 
                                            style="cursor: pointer;" 
                                            onclick="openStatusModal({{ $budget->id }}, '{{ $budget->status }}')" 
                                            title="Clique para alterar o status">
                                            {{ $budget->status }}
                                        </span>
                                    </td>
                                    <td><strong>R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</strong></td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="#" 
                                                class="btn btn-sm btn-outline-secondary generate-pdf-btn" 
                                                title="Gerar PDF" 
                                                data-budget-id="{{ $budget->id }}"
                                                data-route="{{ route('budgets.pdf', $budget) }}">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>

                                                {{-- Grupo de botões de e-mail e WhatsApp (inicialmente ocultos) --}}
                                                <div class="btn-group budget-actions-{{ $budget->id }}" role="group" 
                                                    @if($budget->pdfFiles->count() == 0) style="display:none;" @endif>
                                                    <button type="button" class="btn btn-sm btn-outline-success" title="Enviar PDF via WhatsApp" onclick="handleWhatsAppSend({{ $budget->id }})">
                                                        <i class="bi bi-whatsapp"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Enviar PDF por Email" onclick="handleEmailSend({{ $budget->id }})">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteBudget({{ $budget->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-none" id="delete-form-budget-{{ $budget->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                    </div>
                    
                    {{ $budgets->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum orçamento cadastrado</h4>
                        <p class="text-muted">Comece cadastrando seu primeiro orçamento</p>
                        <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Orçamento
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de Seleção de Contatos -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">
                    <i class="bi bi-whatsapp text-success"></i> Selecionar Contato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Selecione um contato para enviar o PDF via WhatsApp:</p>
                <div class="mb-3">
                    <label for="contactSelect" class="form-label">Contato:</label>
                    <select class="form-select" id="contactSelect">
                        <option value="">Selecione um contato...</option>
                    </select>
                </div>
                <div id="contactInfo" class="alert alert-info d-none">
                    <strong>Telefone:</strong> <span id="contactPhone"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="sendWhatsAppBtn" disabled>
                    <i class="bi bi-whatsapp"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Seleção de Contatos para Email -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">
                    <i class="bi bi-envelope text-primary"></i> Selecionar Contato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Selecione um contato e template para enviar o PDF por email:</p>
                <div class="mb-3">
                    <label for="emailContactSelect" class="form-label">Contato:</label>
                    <select class="form-select" id="emailContactSelect">
                        <option value="">Selecione um contato...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="emailTemplateSelect" class="form-label">Template de Email:</label>
                    <select class="form-select" id="emailTemplateSelect">
                        <option value="">Template Padrão</option>
                    </select>
                    <small class="form-text text-muted">Escolha Template Padrão para usar no sistema</small>
                </div>
                <div id="emailContactInfo" class="alert alert-info d-none">
                    <strong>Email:</strong> <span id="contactEmail"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="sendEmailBtn" disabled>
                    <i class="bi bi-envelope"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
// Função openStatusModal está definida no app.blade.php

function confirmDeleteBudget(budgetId) {
    Swal.fire({
        title: 'Confirmação',
        text: 'Tem certeza de que deseja excluir este orçamento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-budget-' + budgetId).submit();
        }
    });
}

function handleWhatsAppSend(budgetId) {
    currentBudgetId = budgetId;
    
    // Fazer requisição AJAX para verificar se o cliente tem contatos
    fetch(`{{ url('/budgets') }}/${budgetId}/whatsapp`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_contacts) {
            // Cliente tem contatos, mostrar modal
            populateContactModal(data.contacts, data.client);
            const modal = new bootstrap.Modal(document.getElementById('contactModal'));
            modal.show();
        } else if (data.success === false) {
            // Erro
            Swal.fire({
                title: 'Erro',
                text: data.message,
                icon: 'error'
            });
        } else if (data.whatsapp_url) {
            // Cliente não tem contatos, abrir WhatsApp diretamente
            window.open(data.whatsapp_url, '_blank');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: 'Erro ao processar solicitação.',
            icon: 'error'
        });
    });
}

function populateContactModal(contacts, client) {
    const select = document.getElementById('contactSelect');
    const sendBtn = document.getElementById('sendWhatsAppBtn');
    const contactInfo = document.getElementById('contactInfo');
    const contactPhone = document.getElementById('contactPhone');
    
    // Limpar opções anteriores
    select.innerHTML = '<option value="">Selecione um contato...</option>';
    
    // Adicionar cliente como primeira opção
    if (client && client.phone) {
        const clientOption = document.createElement('option');
        clientOption.value = 'client_' + client.id;
        clientOption.textContent = client.name + ' (Cliente)';
        clientOption.dataset.phone = client.phone;
        clientOption.dataset.isClient = 'true';
        select.appendChild(clientOption);
    }
    
    // Adicionar contatos
    contacts.forEach(contact => {
        const option = document.createElement('option');
        option.value = contact.id;
        option.textContent = contact.name;
        option.dataset.phone = contact.phone;
        option.dataset.isClient = 'false';
        select.appendChild(option);
    });
    
    // Event listener para mudança de seleção
    select.onchange = function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            contactPhone.textContent = selectedOption.dataset.phone;
            contactInfo.classList.remove('d-none');
            sendBtn.disabled = false;
        } else {
            contactInfo.classList.add('d-none');
            sendBtn.disabled = true;
        }
    };
    
    // Event listener para botão enviar
    sendBtn.onclick = function() {
        const contactId = select.value;
        const selectedOption = select.options[select.selectedIndex];
        const isClient = selectedOption.dataset.isClient === 'true';
        
        if (contactId && currentBudgetId) {
            if (isClient) {
                // Enviar diretamente para o cliente
                sendWhatsAppToClient(currentBudgetId);
            } else {
                // Enviar para contato
                sendWhatsAppToContact(currentBudgetId, contactId);
            }
        }
    };
}

function sendWhatsAppToClient(budgetId) {
    const sendBtn = document.getElementById('sendWhatsAppBtn');
    const originalText = sendBtn.innerHTML;
    
    // Mostrar loading
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...';
    sendBtn.disabled = true;
    
    // Usar a rota que força o envio direto para o cliente
    fetch(`{{ url('/budgets') }}/${budgetId}/whatsapp?force_client=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.whatsapp_url) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
            modal.hide();
            
            // Limpar dados do modal
            clearWhatsAppModal();
            
            // Abrir WhatsApp
            window.open(data.whatsapp_url, '_blank');
            
            Swal.fire({
                title: 'Sucesso',
                text: 'WhatsApp aberto com sucesso!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

            $('.info-status-badge-' + budgetId).text('Enviado');
            $('.info-status-badge-' + budgetId).removeClass('bg-warning');
            $('.info-status-badge-' + budgetId).addClass('bg-info');

        } else {
            Swal.fire({
                title: 'Erro',
                text: data.message || 'Erro ao enviar mensagem.',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: 'Erro ao enviar mensagem.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restaurar botão
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}

function sendWhatsAppToContact(budgetId, contactId) {
    const sendBtn = document.getElementById('sendWhatsAppBtn');
    const originalText = sendBtn.innerHTML;
    
    // Mostrar loading
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...';
    sendBtn.disabled = true;
    
    fetch(`{{ url('/budgets') }}/${budgetId}/whatsapp-contact`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            contact_id: contactId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
            modal.hide();
            
            // Limpar dados do modal
            clearWhatsAppModal();
            
            // Abrir WhatsApp
            window.open(data.whatsapp_url, '_blank');
            
            Swal.fire({
                title: 'Sucesso',
                text: 'WhatsApp aberto com sucesso!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            
            $('.info-status-badge-' + budgetId).text('Enviado');
            $('.info-status-badge-' + budgetId).removeClass('bg-warning');
            $('.info-status-badge-' + budgetId).addClass('bg-info');
            
        } else {
            Swal.fire({
                title: 'Erro',
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: 'Erro ao enviar mensagem.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restaurar botão
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}

// Funções para envio de Email
let currentBudgetIdForEmail = null;

function handleEmailSend(budgetId) {
    currentBudgetIdForEmail = budgetId;
    
    fetch(`{{ url('/budgets') }}/${budgetId}/email`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.has_contacts) {
                // Tem contatos, mostrar modal
                populateEmailModal(data.contacts, data.client, data.email_templates);
                const modal = new bootstrap.Modal(document.getElementById('emailModal'));
                modal.show();
            } else {
                // Não tem contatos, email foi enviado direto para o cliente


                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message || 'Email enviado com sucesso!',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                

            }
        } else {
            Swal.fire({
                title: 'Erro',
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: 'Erro ao processar solicitação.',
            icon: 'error'
        });
    });
}

function populateEmailModal(contacts, client, emailTemplates = []) {
    const select = document.getElementById('emailContactSelect');
    const templateSelect = document.getElementById('emailTemplateSelect');
    const contactInfo = document.getElementById('emailContactInfo');
    const contactEmail = document.getElementById('contactEmail');
    
    // Limpar opções anteriores
    select.innerHTML = '<option value="">Selecione um contato...</option>';
    templateSelect.innerHTML = '<option value="">Template Padrão</option>';
    
    // Adicionar cliente como primeira opção se tiver email
    if (client && client.email) {
        const clientOption = document.createElement('option');
        clientOption.value = JSON.stringify({
            id: client.id,
            name: client.name,
            email: client.email,
            isClient: true
        });
        clientOption.textContent = `${client.name} (Cliente)`;
        select.appendChild(clientOption);
    }
    
    // Adicionar contatos
    contacts.forEach(contact => {
        if (contact.email) {
            const option = document.createElement('option');
            option.value = JSON.stringify({
                id: contact.id,
                name: contact.name,
                email: contact.email,
                isClient: false
            });
            option.textContent = contact.name;
            select.appendChild(option);
        }
    });
    
    // Adicionar templates de email
    if (emailTemplates && emailTemplates.length > 0) {
        emailTemplates.forEach(template => {
            const option = document.createElement('option');
            option.value = template.id;
            option.textContent = `${template.name}`;
            templateSelect.appendChild(option);
        });
    }
    
    // Event listener para mudança de seleção
    select.addEventListener('change', function() {
        const sendBtn = document.getElementById('sendEmailBtn');
        
        if (this.value) {
            const contactData = JSON.parse(this.value);
            contactEmail.textContent = contactData.email;
            contactInfo.classList.remove('d-none');
            sendBtn.disabled = false;
        } else {
            contactInfo.classList.add('d-none');
            sendBtn.disabled = true;
        }
    });
    
    // Event listener para o botão de envio
    document.getElementById('sendEmailBtn').addEventListener('click', function() {
        const selectedValue = select.value;
        if (selectedValue) {
            const contactData = JSON.parse(selectedValue);
            
            if (contactData.isClient) {
                sendEmailToClient(currentBudgetIdForEmail);
            } else {
                sendEmailToContact(currentBudgetIdForEmail, contactData.id);
            }
        }
    });
}

function sendEmailToClient(budgetId) {
    const sendBtn = document.getElementById('sendEmailBtn');
    const templateSelect = document.getElementById('emailTemplateSelect');
    const originalText = sendBtn.innerHTML;
    
    // Mostrar loading
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...';
    sendBtn.disabled = true;
    
    // Obter template_id selecionado
    const templateId = templateSelect.value || '';
    
    // Usar a rota que força o envio direto para o cliente
    fetch(`{{ url('/budgets') }}/${budgetId}/email?force_client=1&template_id=${templateId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal e limpar dados
            const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
            modal.hide();
            clearEmailModal();
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message || 'Email enviado com sucesso!',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

            $('.info-status-badge-' + budgetId).text('Enviado');
            $('.info-status-badge-' + budgetId).removeClass('bg-warning');
            $('.info-status-badge-' + budgetId).addClass('bg-info');

        } else {
            if (data.auth_required) {
                Swal.fire({
                    title: 'Configuração Necessária',
                    html: data.message + ' <a href="{{ route("google.settings") }}" class="text-primary"><u>Configurar agora</u></a>',
                    icon: 'warning',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Erro',
                    text: data.message || 'Erro ao enviar email.',
                    icon: 'error'
                });
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: 'Erro ao enviar email.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restaurar botão
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}

function sendEmailToContact(budgetId, contactId) {
    const sendBtn = document.getElementById('sendEmailBtn');
    const templateSelect = document.getElementById('emailTemplateSelect');
    const originalText = sendBtn.innerHTML;
    
    // Mostrar loading
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...';
    sendBtn.disabled = true;
    
    // Obter template_id selecionado
    const templateId = templateSelect.value || '';
    
    fetch(`{{ url('/budgets') }}/${budgetId}/email-contact`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            contact_id: contactId,
            template_id: templateId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal e limpar dados
            const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
            modal.hide();
            clearEmailModal();
            
            Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message || 'Email enviado com sucesso!',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                $('.info-status-badge-' + budgetId).text('Enviado');
                $('.info-status-badge-' + budgetId).removeClass('bg-warning');
                $('.info-status-badge-' + budgetId).addClass('bg-info');
        } else {
            if (data.auth_required) {
                Swal.fire({
                    title: 'Configuração Necessária',
                    html: data.message + ' <a href="{{ route("google.settings") }}" class="text-primary"><u>Configurar agora</u></a>',
                    icon: 'warning',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Erro',
                    text: data.message,
                    icon: 'error'
                });
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: 'Erro ao enviar email.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restaurar botão
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}

// Função para limpar dados do modal de email
function clearEmailModal() {
    const emailContactSelect = document.getElementById('emailContactSelect');
    const emailTemplateSelect = document.getElementById('emailTemplateSelect');
    const emailContactInfo = document.getElementById('emailContactInfo');
    const sendEmailBtn = document.getElementById('sendEmailBtn');
    
    // Resetar selects
    if (emailContactSelect) emailContactSelect.selectedIndex = 0;
    if (emailTemplateSelect) emailTemplateSelect.selectedIndex = 0;
    
    // Ocultar informações do contato
    if (emailContactInfo) emailContactInfo.classList.add('d-none');
    
    // Desabilitar botão de envio
    if (sendEmailBtn) sendEmailBtn.disabled = true;
    
    // Limpar variável global se existir
    if (typeof currentBudgetIdForEmail !== 'undefined') {
        currentBudgetIdForEmail = null;
    }
}

// Função para limpar dados do modal do WhatsApp
function clearWhatsAppModal() {
    const contactSelect = document.getElementById('contactSelect');
    const contactInfo = document.getElementById('contactInfo');
    const sendWhatsAppBtn = document.getElementById('sendWhatsAppBtn');
    
    // Resetar select
    if (contactSelect) contactSelect.selectedIndex = 0;
    
    // Ocultar informações do contato
    if (contactInfo) contactInfo.classList.add('d-none');
    
    // Desabilitar botão de envio
    if (sendWhatsAppBtn) sendWhatsAppBtn.disabled = true;
    
    // Limpar variável global se existir
    if (typeof currentBudgetId !== 'undefined') {
        currentBudgetId = null;
    }
}

// Event listener para limpar modal quando for fechado
document.addEventListener('DOMContentLoaded', function() {
    const emailModal = document.getElementById('emailModal');
    if (emailModal) {
        emailModal.addEventListener('hidden.bs.modal', function() {
            clearEmailModal();
        });
    }
    
    const contactModal = document.getElementById('contactModal');
    if (contactModal) {
        contactModal.addEventListener('hidden.bs.modal', function() {
            clearWhatsAppModal();
        });
    }
});

// Listener para o clique no botão "Gerar PDF"
$(document).on('click', '.generate-pdf-btn', function(e) {
    e.preventDefault(); 
    
    const generatePdfButton = $(this);
    const budgetId = generatePdfButton.data('budget-id');
    const route = generatePdfButton.data('route');
    const originalButtonHtml = generatePdfButton.html();

    // 1. Mostrar o SweetAlert de loading
    Swal.fire({
        title: 'Gerando PDF',
        html: 'Por favor, aguarde...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Desabilitar o botão para evitar cliques duplicados
    //generatePdfButton.prop('disabled', true);

    // Mostrar loading no botão

        generatePdfButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Gerando...');

        generatePdfButton.prop('disabled', true); // Desabilitar o botão 

    // 2. Requisição AJAX para gerar o PDF
    $.ajax({
        url: route,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            Swal.close(); // Fechar o alerta de loading

            if (response.success) {
                // Abrir o PDF em uma nova aba
                window.open(response.pdf_url, '_blank');

                // Mostrar os botões de e-mail e WhatsApp
                $(`.budget-actions-${budgetId}`).show(); 

                // Mostrar SweetAlert de sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'PDF Gerado!',
                    text: response.message || 'PDF gerado e salvo com sucesso!',
                    showConfirmButton: false,
                    timer: 2000,
                    toast: true,
                    position: 'top-end'
                });

            } else {
                Swal.fire({
                    title: 'Erro',
                    text: response.message || 'Erro ao gerar PDF.',
                    icon: 'error'
                });
            }
        },
        error: function(xhr) {
            Swal.close(); // Fechar o alerta de loading

            console.error('Erro ao gerar PDF:', xhr.responseText);
            Swal.fire({
                title: 'Erro',
                text: 'Não foi possível gerar o PDF.',
                icon: 'error'
            });
        },
        complete: function() {
            // Restaurar o botão
            generatePdfButton.html(originalButtonHtml);
            generatePdfButton.prop('disabled', false);
        }
    });
});
</script>
@endpush
@endsection
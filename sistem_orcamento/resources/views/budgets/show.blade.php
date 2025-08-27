@extends('layouts.app')

@section('title', 'Orçamento #' . $budget->number)

@section('content')
<div class="container mx-auto">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-lg-row justify-content-lg-between align-items-center">
                    <h3 class="card-title mb-2 mb-lg-0">
                        {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }} {{ $budget->number }}
                    </h3>
                    <div class="d-flex flex-wrap justify-content-start mt-2 mt-lg-0">
                        <a href="#" 
                            class="btn btn-secondary me-2 mb-2 generate-pdf-btn" 
                            title="Gerar PDF" 
                            data-budget-id="{{ $budget->id }}"
                            data-route="{{ route('budgets.pdf', $budget) }}">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        
                        <span class="budget-actions-{{ $budget->id }} d-flex me-2 mb-2" role="group" 
                            @if($budget->pdfFiles->count() == 0) style="display:none;" @endif>
                            <button type="button" class="btn btn-success me-2" title="Enviar via WhatsApp" onclick="handleWhatsAppSend({{ $budget->id }})">
                                <i class="bi bi-whatsapp"></i> PDF
                            </button>
                            <button type="button" class="btn btn-primary" title="Enviar por Email" onclick="handleEmailSend({{ $budget->id }})">
                                <i class="bi bi-envelope"></i> Email
                            </button>
                        </span>
                        
                        <a href="{{ route('contacts.create', ['client_id' => $budget->client_id]) }}" class="btn btn-info me-2 mb-2" title="Adicionar contato">
                            <i class="bi bi-person-plus"></i> Contato
                        </a>
                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-warning me-2 mb-2">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" id="delete-form-budget-{{ $budget->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Seção Empresa (75%) e Informações do Orçamento (25%) -->
                    <div class="row mb-4">
                        <!-- Empresa - 75% -->
                        <div class="col-md-9 mb-4 mb-lg-0">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Empresa</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Logo da Empresa -->
                                        @if($budget->company->logo)
                                        <div class="col-md-3 text-center mb-2">
                                            <img src="{{ asset('storage/' . $budget->company->logo) }}" alt="Logo da {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}" class="img-fluid" style="width: 100%; height: 100%;">
                                        </div>
                                        <div class="col-md-9">
                                        @else
                                        <div class="col-md-12">
                                        @endif
                                            <h6 class="fw-bold mb-3">{{ $budget->company->corporate_name }}</h6>
                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                    @if($budget->company->document_number)
                                                    <p class="mb-2"><strong>CNPJ:</strong> {{ $budget->company->document_number }}</p>
                                                    @endif
                                                    @if($budget->company->phone)
                                                    <p class="mb-2"><strong>Telefone:</strong> {{ $budget->company->phone }}</p>
                                                    @endif
                                                    @if($budget->company->email)
                                                    <p class="mb-2"><strong>Email:</strong> {{ $budget->company->email }}</p>
                                                    @endif
                                                     @if($budget->company->address || $budget->company->city || $budget->company->state)
                                                    <p class="mb-2"><strong>Endereço:</strong>
                                                        {{ $budget->company->address }}@if($budget->company->address && ($budget->company->city || $budget->company->state)), @endif{{ $budget->company->city }}@if($budget->company->city && $budget->company->state) - @endif{{ $budget->company->state }}
                                                    </p>
                                                    @endif
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações do Orçamento - 25% -->
                        <div class="col-md-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <h4 class="text-primary mb-3">{{ $budget->number }}</h4>
                                    <p class="mb-2"><strong>Data:</strong><br>{{ $budget->issue_date->format('d/m/Y') }}</p>
                                    @if($budget->delivery_date)
                                    <p class="mb-2"><strong>Previsão de Entrega:</strong><br>{{ $budget->delivery_date->format('d/m/Y') }}</p>
                                    @endif
                                    <p class="mb-2"><strong>Validade:</strong><br>{{ $budget->valid_until->format('d/m/Y') }}</p>
                                    <p class="mb-2"><strong>Status:</strong><br>
                                        <span class="badge status-clickable info-status-badge
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
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dados do Cliente -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Nome:</strong> 
                                                @if($budget->client->corporate_name)
                                                    {{ $budget->client->corporate_name }}
                                                @elseif($budget->client->fantasy_name)
                                                    {{ $budget->client->fantasy_name }}
                                                @else
                                                    <span class="text-muted">Nome não informado</span>
                                                @endif
                                            </p>
                                            @if($budget->client->document_number)
                                            <p class="mb-1"><strong>CPF/CNPJ:</strong> {{ $budget->client->document_number }}</p>
                                            @endif
                                            @if($budget->client->phone)
                                            <p class="mb-1"><strong>Telefone:</strong> {{ $budget->client->phone }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            @if($budget->client->email)
                                            <p class="mb-1"><strong>Email:</strong> {{ $budget->client->email }}</p>
                                            @endif
                                            @if($budget->client->address || $budget->client->city || $budget->client->state)
                                            <p class="mb-1"><strong>Endereço:</strong><br>
                                                {{ $budget->client->address }}@if($budget->client->address && ($budget->client->city || $budget->client->state)), @endif{{ $budget->client->city }}@if($budget->client->city && $budget->client->state) - @endif{{ $budget->client->state }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Itens do Orçamento -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Itens do Orçamento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Produto</th>
                                                    <th>Descrição</th>
                                                    <th class="text-end">Qtd</th>
                                                    <th class="text-end">Valor Unit.</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($budget->items as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($item->product)
                                                            {{ $item->product->name }}
                                                        @elseif($item->produto)
                                                            {{ $item->produto }}
                                                        @else
                                                            Produto excluído
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item->description)
                                                            {{ $item->description }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                                    <td class="text-end">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                                    <td class="text-end"><strong>R$ {{ number_format($item->total_price, 2, ',', '.') }}</strong></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Totais -->
                                    <div class="row">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <span>Subtotal:</span>
                                                        <span>R$ {{ number_format($budget->items->sum('total_price'), 2, ',', '.') }}</span>
                                                    </div>
                                                    @if($budget->total_discount > 0)
                                                    <div class="d-flex justify-content-between">
                                                        <span>Desconto:</span>
                                                        <span>R$ {{ number_format($budget->total_discount, 2, ',', '.') }}</span>
                                                    </div>
                                                    @endif
                                                    <hr>
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Total:</strong>
                                                        <strong class="text-success">R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($budget->observations)
                    <!-- Observações -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Observações</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{!! nl2br(e($budget->observations)) !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($budget->budgetPayments->count() > 0)
                    <!-- Métodos de Pagamento -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Métodos de Pagamento</h5>
                                </div>
                                <div class="card-body">
                                    @foreach($budget->budgetPayments as $payment)
                                    <div class="mb-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-primary">{{ $payment->paymentMethod->name }}</h6>
                                                <p class="mb-1"><strong>Valor:</strong> R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
                                                <p class="mb-1"><strong>Parcelas:</strong> {{ $payment->installments }}x</p>
                                                <p class="mb-1"><strong>Momento:</strong> {{ $payment->payment_moment_description }}</p>
                                                @if($payment->notes)
                                                <p class="mb-1"><strong>Observações:</strong> {{ $payment->notes }}</p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($payment->paymentInstallments->count() > 0)
                                                <h6 class="text-secondary">Parcelas</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Parcela</th>
                                                                <th>Valor</th>
                                                                <th>Vencimento</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($payment->paymentInstallments as $installment)
                                                            <tr>
                                                                <td>{{ $installment->installment_number }}/{{ $payment->installments }}</td>
                                                                <td>R$ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                                                                <td>{{ $installment->due_date->format('d/m/Y') }}</td>
                                                                <td>
                                                                    <span class="badge bg-{{ $installment->status == 'paid' ? 'success' : ($installment->status == 'overdue' ? 'danger' : 'warning') }}">
                                                                        {{ $installment->status_description }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if(!$loop->last)
                                        <hr>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Linhas de Assinatura -->
                    <div class="row mb-4">
                        <div class="col-md-5">
                            
                                <div class="text-center">
                                    <div style="height: 80px; border-bottom: 2px solid #000; margin-bottom: 10px;"></div>
                                    <p class="mb-0"><strong>Assinatura do Cliente</strong></p>
                                    <small class="text-muted">{{ $budget->client->corporate_name ?? $budget->client->fantasy_name ?? 'Cliente' }}</small>
                                </div>
                            
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5">
                           
                                <div class="text-center">
                                    <div style="height: 80px; border-bottom: 2px solid #000; margin-bottom: 10px;"></div>
                                    <p class="mb-0"><strong>Assinatura da Empresa</strong></p>
                                    <small class="text-muted">{{ $budget->company->corporate_name }}</small>
                                </div>
                            
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar para Lista
                        </a>
                    </div>
                </div>
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
                window.open(response.url, '_blank');

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
            $('.info-status-badge').text('Enviado');
            $('.info-status-badge').removeClass('bg-warning');
            $('.info-status-badge').addClass('bg-info');
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
            $('.info-status-badge').text('Enviado');
            $('.info-status-badge').removeClass('bg-warning');
            $('.info-status-badge').addClass('bg-info');
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
                // Tem contatos ou cliente com email, mostrar modal
                populateEmailModal(data.contacts, data.client, data.email_templates);
                const modal = new bootstrap.Modal(document.getElementById('emailModal'));
                modal.show();
            } else {
                // Não tem contatos nem cliente com email
                Swal.fire({
                    title: 'Erro',
                    text: data.message || 'Cliente não possui email cadastrado.',
                    icon: 'error'
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
    
    // Obter template selecionado
    const templateId = templateSelect.value;
    const url = `{{ url('/budgets') }}/${budgetId}/email?force_client=1${templateId ? '&template_id=' + templateId : ''}`;
    
    // Usar a rota que força o envio direto para o cliente
    fetch(url, {
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
                title: 'Sucesso',
                text: 'Email enviado com sucesso!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            
            $('.info-status-badge').text('Enviado');
            $('.info-status-badge').removeClass('bg-warning');
            $('.info-status-badge').addClass('bg-info');
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
    
    // Obter template selecionado
    const templateId = templateSelect.value;
    
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
                title: 'Sucesso',
                text: 'Email enviado com sucesso!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

            $('.info-status-badge').text('Enviado');
            $('.info-status-badge').removeClass('bg-warning');
            $('.info-status-badge').addClass('bg-info');
            
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
    emailContactSelect.selectedIndex = 0;
    emailTemplateSelect.selectedIndex = 0;
    
    // Ocultar informações do contato
    emailContactInfo.classList.add('d-none');
    
    // Desabilitar botão de envio
    sendEmailBtn.disabled = true;
    
    // Limpar variável global
    currentBudgetIdForEmail = null;
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
</script>
@endpush
@endsection
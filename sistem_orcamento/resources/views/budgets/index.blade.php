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
        <div class="card">
            <div class="card-body">
                @if($budgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Empresa</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Ações</th>
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
                                    <td>
                                        <a href="{{ route('companies.show', $budget->company) }}" class="text-decoration-none">
                                            {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}
                                        </a>
                                    </td>
                                    <td>{{ $budget->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge status-clickable
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
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('budgets.pdf', $budget) }}" class="btn btn-sm btn-outline-secondary" title="Gerar PDF" target="_blank">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            @if($budget->pdfFiles->count() > 0)
                                            <button type="button" class="btn btn-sm btn-outline-success" title="Enviar PDF via WhatsApp" onclick="handleWhatsAppSend({{ $budget->id }})">
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                            @endif
                                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" id="delete-form-budget-{{ $budget->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteBudget({{ $budget->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
            populateContactModal(data.contacts);
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

function populateContactModal(contacts) {
    const select = document.getElementById('contactSelect');
    const sendBtn = document.getElementById('sendWhatsAppBtn');
    const contactInfo = document.getElementById('contactInfo');
    const contactPhone = document.getElementById('contactPhone');
    
    // Limpar opções anteriores
    select.innerHTML = '<option value="">Selecione um contato...</option>';
    
    // Adicionar contatos
    contacts.forEach(contact => {
        const option = document.createElement('option');
        option.value = contact.id;
        option.textContent = contact.name;
        option.dataset.phone = contact.phone;
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
        if (contactId && currentBudgetId) {
            sendWhatsAppToContact(currentBudgetId, contactId);
        }
    };
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
            
            // Abrir WhatsApp
            window.open(data.whatsapp_url, '_blank');
            
            Swal.fire({
                title: 'Sucesso',
                text: 'WhatsApp aberto com sucesso!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
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
</script>

@endsection
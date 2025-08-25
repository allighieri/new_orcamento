@extends('layouts.app')

@section('title', 'Orçamento #' . $budget->number)

@section('content')
<div class="container mx-auto">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }} {{ $budget->number }}
                    </h3>
                    <div>
                        <a href="{{ route('budgets.pdf', $budget) }}" class="btn btn-secondary mb-2 mb-lg-0" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        @if($budget->pdfFiles->count() > 0)
                        <button type="button" class="btn btn-success mb-2 mb-lg-0" title="Enviar via WhatsApp" onclick="handleWhatsAppSend({{ $budget->id }})">
                            <i class="bi bi-whatsapp"></i> Enviar PDF
                        </button>
                        @endif
                        <a href="{{ route('contacts.create', ['client_id' => $budget->client_id]) }}" class="btn btn-info mb-2 mb-lg-0" title="Adicionar novo contato para este cliente">
                            <i class="bi bi-person-plus"></i> Adicionar Contato
                        </a>
                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-warning mb-2 mb-lg-0">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" id="delete-form-budget-{{ $budget->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteBudget({{ $budget->id }})">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Seção Empresa (75%) e Informações do Orçamento (25%) -->
                    <div class="row mb-4">
                        <!-- Empresa - 75% -->
                        <div class="col-md-9">
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
                                    <p class="mb-2"><strong>Validade:</strong><br>{{ $budget->valid_until->format('d/m/Y') }}</p>
                                    <p class="mb-2"><strong>Status:</strong><br>
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
                        <a href="{{ route('budgets.index') }}" class="btn btn-secondary">
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

<script>
let currentBudgetId = null;

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
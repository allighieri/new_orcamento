@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row dashboard-page">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2"></i>  
            @if($user->role === 'super_admin')
                {{ explode(' ', Auth::user()->name)[0] }}
            @else
                {{ $user->company->fantasy_name ?? $user->company->corporate_name }}
            @endif
        </h1>
    </div>
</div>

<div class="container mx-auto row mb-4">
    <div class="d-flex flex-wrap justify-content-start gap-3 w-100">

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['budgets_count'] }}</h4>
                            <p class="card-text">Orçamentos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('budgets.index') }}" class="text-white text-decoration-none">
                        <small>Ver todos <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['categories_count'] }}</h4>
                            <p class="card-text">Categorias</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-tags fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('categories.index') }}" class="text-white text-decoration-none">
                        <small>Ver todas <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['products_count'] }}</h4>
                            <p class="card-text">Produtos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('products.index') }}" class="text-white text-decoration-none">
                        <small>Ver todos <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['clients_count'] }}</h4>
                            <p class="card-text">Clientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('clients.index') }}" class="text-white text-decoration-none">
                        <small>Ver todos <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['contacts_count'] }}</h4>
                            <p class="card-text">Contatos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-rolodex fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('contacts.index') }}" class="text-white text-decoration-none">
                        <small>Ver todas <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>

        @if(Auth::check() && Auth::user()->role === 'super_admin')
        <div class="flex-grow-1" style="min-width: 150px;">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ App\Models\Company::count() }}</h4>
                            <p class="card-text">Empresas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-building-add fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('companies.index') }}" class="text-white text-decoration-none">
                        <small>Ver todas <i class="bi bi-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Ações Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-start gap-3 w-100">
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('budgets.create') }}" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-file-earmark-text"></i><br>
                            Novo Orçamento
                        </a>
                    </div>
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('clients.create') }}" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-person-plus"></i><br>
                            Novo Cliente
                        </a>
                    </div>
                    
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('categories.create') }}" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-tag"></i><br>
                            Nova Categoria
                        </a>
                    </div>

                    @if(Auth::check() && Auth::user()->role === 'super_admin')
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('companies.create') }}" class="btn btn-secondary btn-lg w-100">
                            <i class="bi bi-building-add"></i><br>
                            Nova Empresa
                        </a>
                    </div>
                    @endif
                    
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('products.create') }}" class="btn btn-info btn-lg w-100">
                            <i class="bi bi-box-seam"></i><br>
                            Novo Produto
                        </a>
                    </div>
                    <div class="flex-grow-1" style="min-width: 180px;">
                        <a href="{{ route('contacts.create') }}" class="btn btn-danger btn-lg w-100">
                            <i class="bi bi-person-rolodex"></i><br>
                            Novo Contato
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="container mx-auto row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Últimos Orçamentos
                </h5>
                
                <a href="{{ route('budgets.index') }}" class="btn  btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body">
                <!-- Formulário de Pesquisa -->
                <div class="mb-4">
                    <form method="GET" action="{{ route('dashboard') }}" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Pesquisar por nome do cliente ou número do orçamento">
                        </div>
                        @if(request('search'))
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Limpar
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
                @if($recentBudgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th  style="width: 120px;">Número</th>
                                    <th style="width: 30%;">Cliente</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <th>Empresa</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Valor Final</th>
                                    <th style="width: 120px;">Data</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBudgets as $budget)
                                <tr>
                                    <td><strong>{{ $budget->number }}</strong></td>
                                    <td>
                                        <a href="{{ route('clients.show', $budget->client) }}" class="text-decoration-none">
                                            {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }}
                                        </a>
                                    </td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>{{ $budget->company->fantasy_name ?? 'N/A' }}</td>
                                    @endif
                                    <td>
                                        <span class="badge 
                                            @if($budget->status == 'Pendente') bg-warning
                                            @elseif($budget->status == 'Enviado') bg-info
                                            @elseif($budget->status == 'Em negociação') bg-primary
                                            @elseif($budget->status == 'Aprovado') bg-success
                                            @elseif($budget->status == 'Expirado') bg-danger
                                            @elseif($budget->status == 'Concluído') bg-secondary
                                            @else bg-light text-dark
                                            @endif
                                            status-badge"
                                            style="cursor: pointer;"
                                            onclick="openStatusModal({{ $budget->id }}, '{{ $budget->status }}')"
                                            title="Clique para alterar o status">
                                            {{ $budget->status }}
                                        </span>
                                    </td>
                                    <td>R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</td>
                                    <td>{{ $budget->issue_date->format('d/m/Y') }}</td>
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
                                            
                                            {{-- Botões de e-mail e WhatsApp (inicialmente ocultos) --}}
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-success budget-actions-{{ $budget->id }}" 
                                                title="Enviar PDF via WhatsApp" 
                                                onclick="handleWhatsAppSend({{ $budget->id }})"
                                                @if($budget->pdfFiles->count() == 0) style="display:none;" @endif>
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-primary budget-actions-{{ $budget->id }}" 
                                                title="Enviar PDF por Email" 
                                                onclick="handleEmailSend({{ $budget->id }})"
                                                @if($budget->pdfFiles->count() == 0) style="display:none;" @endif>
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteBudget({{ $budget->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        
                                        {{-- Formulário oculto para exclusão --}}
                                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-none" id="delete-form-budget-{{ $budget->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="redirect_to" value="dashboard">
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    @if($recentBudgets->hasPages())
                        
                            
                            <div class="mt-3">
                                {{ $recentBudgets->links() }}
                            </div>
                        
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        @if(request('search'))
                            <h4 class="text-muted mt-3">Nenhum orçamento encontrado</h4>
                            <p class="text-muted">Não há orçamentos que correspondam à sua pesquisa</p>
                        @else
                            <h4 class="text-muted mt-3">Nenhum orçamento cadastrado</h4>
                            <p class="text-muted">Comece cadastrando seu primeiro orçamento</p>
                            <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Novo Orçamento
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Modal de Seleção de Contatos para WhatsApp -->
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
// Variáveis globais
let currentBudgetId = null;
let currentBudgetIdForEmail = null;

// Função para confirmar exclusão de orçamento
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

$(document).ready(function() {
    // Listeners para limpar modais quando fechados
    $('#contactModal').on('hidden.bs.modal', function () {
        document.getElementById('contactSelect').innerHTML = '<option value="">Selecione um contato...</option>';
        document.getElementById('contactInfo').classList.add('d-none');
        document.getElementById('sendWhatsAppBtn').disabled = true;
        currentBudgetId = null;
    });

    $('#emailModal').on('hidden.bs.modal', function () {
         document.getElementById('emailContactSelect').innerHTML = '<option value="">Selecione um contato...</option>';
         document.getElementById('emailTemplateSelect').innerHTML = '<option value="">Template Padrão</option>';
         document.getElementById('emailContactInfo').classList.add('d-none');
         document.getElementById('sendEmailBtn').disabled = true;
         currentBudgetIdForEmail = null;
     });

     // Listener para o clique no botão "Gerar PDF"
     $(document).on('click', '.generate-pdf-btn', function(e) {
         e.preventDefault(); 
         
         const generatePdfButton = $(this);
         const budgetId = generatePdfButton.data('budget-id');
         const route = generatePdfButton.data('route');
         const originalButtonHtml = generatePdfButton.html();

         // Mostrar o SweetAlert de loading
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
         generatePdfButton.prop('disabled', true);

         // Requisição AJAX para gerar o PDF
         $.ajax({
             url: route,
             method: 'GET',
             headers: {
                 'X-Requested-With': 'XMLHttpRequest'
             },
             success: function(response) {
                 Swal.close();

                 if (response.success) {
                     // Abrir o PDF em nova aba
                     window.open(response.pdf_url, '_blank');

                     // Mostrar os botões de e-mail e WhatsApp
                     $(`.budget-actions-${budgetId}`).show();

                     Swal.fire({
                         icon: 'success',
                         title: 'Sucesso!',
                         text: 'PDF gerado com sucesso!',
                         timer: 2000,
                         showConfirmButton: false,
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
                 Swal.close();

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

}); // Fim do document ready

// Função para enviar via WhatsApp
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

// Função para enviar por Email
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

// Função para popular modal de contatos do WhatsApp
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

// Função para enviar WhatsApp para cliente
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

// Função para enviar WhatsApp para contato
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

// Função para popular modal de email
function populateEmailModal(contacts, client, emailTemplates = []) {
    const select = document.getElementById('emailContactSelect');
    const templateSelect = document.getElementById('emailTemplateSelect');
    const contactInfo = document.getElementById('emailContactInfo');
    const contactEmail = document.getElementById('contactEmail');
    const sendBtn = document.getElementById('sendEmailBtn');
    
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
    select.onchange = function() {
        if (this.value) {
            const contactData = JSON.parse(this.value);
            contactEmail.textContent = contactData.email;
            contactInfo.classList.remove('d-none');
            sendBtn.disabled = false;
        } else {
            contactInfo.classList.add('d-none');
            sendBtn.disabled = true;
        }
    };
    
    // Event listener para botão enviar
    sendBtn.onclick = function() {
        const contactValue = select.value;
        const templateId = templateSelect.value;
        
        if (contactValue && currentBudgetIdForEmail) {
            const contactData = JSON.parse(contactValue);
            sendEmailToBudget(currentBudgetIdForEmail, contactData, templateId);
        }
    };
}

// Função para enviar email
function sendEmailToBudget(budgetId, contactData, templateId) {
    const sendBtn = document.getElementById('sendEmailBtn');
    const originalText = sendBtn.innerHTML;
    
    // Mostrar loading
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...';
    sendBtn.disabled = true;
    
    const requestData = {
        contact_id: contactData.isClient ? null : contactData.id,
        is_client: contactData.isClient,
        template_id: templateId || null
    };
    
    fetch(`{{ url('/budgets') }}/${budgetId}/email-contact`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
            modal.hide();
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message || 'Email enviado com sucesso!',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
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


</script>
@endpush
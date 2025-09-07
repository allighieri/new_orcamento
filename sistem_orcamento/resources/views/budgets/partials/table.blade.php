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
        <h4 class="text-muted mt-3">Nenhum orçamento encontrado</h4>
        <p class="text-muted">Não há orçamentos que correspondam à sua pesquisa</p>
    </div>
@endif
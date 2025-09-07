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
    <div class="text-center py-5">
        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
        <h4 class="text-muted mt-3">Nenhum orçamento encontrado</h4>
        <p class="text-muted">Não há orçamentos que correspondam à sua pesquisa</p>
    </div>
@endif
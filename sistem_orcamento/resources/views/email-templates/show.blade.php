@extends('layouts.app')

@section('title', 'Visualizar Template de Email')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-envelope-paper-heart-fill"></i> {{ $emailTemplate->name }}</h3>
            </h1>
            
           <div>
                <a href="{{ route('email-templates.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('email-templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-envelope-paper-heart-fill"></i> Novo Template
                </a>
            </div>
        </div>
    </div>
</div>
<div class="container mx-auto row">
    <div class="col-8">
        <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Informações do Template -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nome do Template:</label>
                                        <p class="form-control-plaintext">{{ $emailTemplate->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status:</label>
                                        <p class="form-control-plaintext">
                                            @if($emailTemplate->is_active)
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Ativo</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="fas fa-times"></i> Inativo</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Assunto do Email:</label>
                                <p class="form-control-plaintext bg-light p-2 rounded">{{ $emailTemplate->subject }}</p>
                            </div>

                            @if($emailTemplate->description)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Descrição:</label>
                                <p class="form-control-plaintext">{{ $emailTemplate->description }}</p>
                            </div>
                            @endif

                            <!-- Conteúdo HTML -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Conteúdo HTML:</label>
                                <div class="position-relative">
                                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ $emailTemplate->html_content }}</code></pre>
                                    <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i> Copiar
                                    </button>
                                </div>
                            </div>

                            <!-- Informações de Data -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Criado em:</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-calendar"></i> {{ $emailTemplate->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Última atualização:</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-clock"></i> {{ $emailTemplate->updated_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>


    <div class="col-md-4">
        <!-- Ações Rápidas -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-gear"></i> Ações Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('email-templates.preview', $emailTemplate) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                    <a href="{{ route('email-templates.edit', $emailTemplate) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button class="btn btn-secondary" onclick="copyToClipboard()">
                        <i class="fas fa-copy"></i> Copiar HTML
                    </button>
                    <hr>
                    <form action="{{ route('email-templates.destroy', $emailTemplate) }}" method="POST" 
                            onsubmit="return confirm('Tem certeza que deseja excluir este template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Variáveis Disponíveis -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">📝 Variáveis Disponíveis</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">Variáveis que podem ser usadas no template:</small>
                <div class="mt-2">
                    <div class="mb-2">
                        <strong>Destinatário:</strong><br>
                        <code class="small">@{{recipientName}}</code>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <code class="small">@{{budgetNumber}}</code><br>
                <code class="small">@{{budgetValue}}</code><br>
                <code class="small">@{{budgetDate}}</code><br>
                <code class="small">@{{budgetValidity}}</code><br>
                <code class="small">@{{budgetStatus}}</code>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <code class="small">@{{companyName}}</code><br>
                <code class="small">@{{companyAddress}}</code><br>
                <code class="small">@{{companyCity}}</code><br>
                <code class="small">@{{companyState}}</code><br>
                <code class="small">@{{companyPhone}}</code><br>
                <code class="small">@{{companyEmail}}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard() {
    const htmlContent = @json($emailTemplate->html_content);
    
    navigator.clipboard.writeText(htmlContent).then(function() {
        // Criar notificação de sucesso
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check"></i> HTML copiado para a área de transferência!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remover o toast após 3 segundos
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }).catch(function(err) {
        alert('Erro ao copiar: ' + err);
    });
}
</script>
@endpush
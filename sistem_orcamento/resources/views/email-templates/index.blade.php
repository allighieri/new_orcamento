@extends('layouts.app')

@section('title', 'Templates de Email')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-envelope-paper-heart-fill"></i> Templates de Email</h3>
            </h1>
            
           <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
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
    <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @if($templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Assunto</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                        <tr>
                                            <td>
                                                <strong>{{ $template->name }}</strong>
                                            </td>
                                            <td>{{ $template->subject }}</td>
                                            <td>
                                                {{ Str::limit($template->description, 50) ?: 'Sem descrição' }}
                                            </td>
                                            <td>
                                                @if($template->is_active)
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-secondary">Inativo</span>
                                                @endif
                                            </td>
                                            <td>{{ $template->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('email-templates.show', $template) }}" 
                                                       class="btn btn-sm btn-info" title="Visualizar">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('email-templates.preview', $template) }}" 
                                                       class="btn btn-sm btn-success" title="Preview" target="_blank">
                                                        <i class="bi bi-pc-display"></i>
                                                    </a>
                                                    <a href="{{ route('email-templates.edit', $template) }}" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('email-templates.destroy', $template) }}" method="POST" class="d-inline" id="delete-form-template-{{ $template->id }}">
                                                   
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger" title="Excluir" onclick="confirmDeleteTemplate({{ $template->id }})">
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

                        <!-- Paginação -->
                        <div class="d-flex justify-content-center">
                            {{ $templates->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Nenhum template encontrado</h4>
                            <p class="text-muted">Crie seu primeiro template de email para começar.</p>
                            <a href="{{ route('email-templates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeiro Template
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

</div>
@endsection

@push('scripts')
<script>

    function confirmDeleteTemplate(templateId) {
        Swal.fire({
            title: 'Atenção!',
            text: 'Tem certeza que deseja excluir este template?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-template-' + templateId).submit();
            }
        });
    }


</script>
@endpush

@push('styles')
<style>
.table th {
    border-top: none;
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush
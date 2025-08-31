@extends('layouts.app')

@section('title', 'Formulários de Contato - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-telephone"></i> Formulários de Contato
            </h1>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
               <a href="{{ route('contact-forms.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Novo Contato
            </a>
            </div>
            
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($contactForms->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <th>Empresa</th>
                                    @endif
                                    <th class="text-end" style="width: 1%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contactForms as $contactForm)
                                <tr>
                                    <td>{{ $contactForm->id }}</td>
                                    <td>
                                        @switch($contactForm->type)
                                            @case('Telefone')
                                                <i class="bi bi-telephone"></i> Telefone
                                                @break
                                            @case('Celular')
                                                <i class="bi bi-phone"></i> Celular
                                                @break
                                            @case('WhatsApp')
                                                <i class="bi bi-whatsapp"></i> WhatsApp
                                                @break
                                            @case('Email')
                                                <i class="bi bi-envelope"></i> Email
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $contactForm->description }}</td>
                                    <td>
                                        @if($contactForm->active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>{{ $contactForm->company->fantasy_name ?? 'N/A' }}</td>
                                    @endif
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('contact-forms.show', $contactForm) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('contact-forms.edit', $contactForm) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDelete({{ $contactForm->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('contact-forms.destroy', $contactForm) }}" method="POST" class="d-none" id="delete-form-{{ $contactForm->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $contactForms->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-telephone fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum contato cadastrado</h4>
                        <p class="text-muted">Comece cadastrando seu primeiro contato</p>
                        <a href="{{ route('contact-forms.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Contato
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(contactFormId) {
    Swal.fire({
        title: 'Atenção!',
        text: 'Tem certeza que deseja excluir este contato?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + contactFormId).submit();
        }
    });
}
</script>

@endsection
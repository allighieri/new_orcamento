@extends('layouts.app')

@section('title', 'Visualizar Contato - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-telephone"></i> Detalhes do Contato
            </h1>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informações do Contato
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="fw-bold">Tipo:</span> 
                    @switch($contactForm->type)
                        @case('telefone')
                            <i class="bi bi-telephone"></i> Telefone
                            @break
                        @case('celular')
                            <i class="bi bi-phone"></i> Celular
                            @break
                        @case('whatsapp')
                            <i class="bi bi-whatsapp"></i> WhatsApp
                            @break
                        @case('email')
                            <i class="bi bi-envelope"></i> Email
                            @break
                    @endswitch
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Contato:</span> 
                    @if($contactForm->type === 'email')
                        <a href="mailto:{{ $contactForm->description }}" class="text-decoration-none">
                            {{ $contactForm->description }}
                        </a>
                    @elseif(in_array($contactForm->type, ['telefone', 'celular', 'whatsapp']))
                        <a href="tel:{{ preg_replace('/\D/', '', $contactForm->description) }}" class="text-decoration-none">
                            {{ $contactForm->description }}
                        </a>
                        @if($contactForm->type === 'whatsapp')
                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $contactForm->description) }}" target="_blank" class="btn btn-success btn-sm ms-2">
                                <i class="bi bi-whatsapp"></i> Abrir WhatsApp
                            </a>
                        @endif
                    @else
                        {{ $contactForm->description }}
                    @endif
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Status:</span> 
                    @if($contactForm->active)
                        <span class="badge bg-success">Ativo</span>
                    @else
                        <span class="badge bg-secondary">Inativo</span>
                    @endif
                </div>
                
                @if(auth()->guard('web')->user()->role === 'super_admin')
                <div class="mb-2">
                    <span class="fw-bold">Empresa:</span> {{ $contactForm->company->fantasy_name ?? $contactForm->company->corporate_name ?? 'N/A' }}
                </div>
                @endif
                
                <div class="mb-2">
                    <span class="fw-bold">Cadastrado em:</span> 
                    {{ $contactForm->created_at->format('d/m/Y H:i') }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Última atualização:</span> 
                    {{ $contactForm->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Ações
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('contact-forms.edit', $contactForm) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Contato
                    </a>
                    
                    @if($contactForm->type === 'whatsapp')
                        <a href="https://wa.me/55{{ preg_replace('/\D/', '', $contactForm->description) }}" target="_blank" class="btn btn-success">
                            <i class="bi bi-whatsapp"></i> Abrir WhatsApp
                        </a>
                    @endif
                    
                    @if($contactForm->type === 'email')
                        <a href="mailto:{{ $contactForm->description }}" class="btn btn-info">
                            <i class="bi bi-envelope"></i> Enviar Email
                        </a>
                    @endif
                    
                    @if(in_array($contactForm->type, ['telefone', 'celular']))
                        <a href="tel:{{ preg_replace('/\D/', '', $contactForm->description) }}" class="btn btn-info">
                            <i class="bi bi-telephone"></i> Ligar
                        </a>
                    @endif
                    
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $contactForm->id }})">
                        <i class="bi bi-trash"></i> Excluir Contato
                    </button>
                </div>
                
                <form action="{{ route('contact-forms.destroy', $contactForm) }}" method="POST" class="d-none" id="delete-form-{{ $contactForm->id }}">
                    @csrf
                    @method('DELETE')
                </form>
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
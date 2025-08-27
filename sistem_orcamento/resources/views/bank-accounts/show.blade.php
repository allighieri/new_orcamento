@extends('layouts.app')

@section('title', 'Visualizar Conta Bancária - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-bank"></i> Detalhes da Conta Bancária
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
                    <i class="bi bi-info-circle"></i> Informações da Conta
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="fw-bold">Tipo:</span> 
                    @if($bankAccount->type === 'PIX')
                        PIX
                    @else
                        Conta
                    @endif
                </div>
                
                @if(auth()->guard('web')->user()->role === 'super_admin')
                <div class="mb-2">
                    <span class="fw-bold">Empresa:</span> {{ $bankAccount->company->fantasy_name ?? $bankAccount->company->corporate_name ?? 'N/A' }}
                </div>
                @endif
                
                <div class="mb-2">
                    <span class="fw-bold">Banco:</span> 
                    @if($bankAccount->compe)
                        {{ $bankAccount->compe->code }} - {{ $bankAccount->compe->bank_name }}
                    @else
                        N/A
                    @endif
                </div>
                
                @if($bankAccount->type === 'Conta')
                <div class="mb-2">
                    <span class="fw-bold">Agência:</span> {{ $bankAccount->branch ?? '-' }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Conta:</span> {{ $bankAccount->account ?? '-' }}
                </div>
                @endif
                
                @if($bankAccount->type === 'PIX')
                <div class="mb-2">
                    <span class="fw-bold">Chave PIX:</span> {{ ucfirst($bankAccount->key) }} - {{ $bankAccount->key_desc }}
                </div>
                @endif
                
                <div class="mb-2">
                    <span class="fw-bold">Descrição:</span> {{ $bankAccount->description }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Status:</span> 
                    @if($bankAccount->active)
                        Ativo
                    @else
                        Inativo
                    @endif
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Informações Completas:</span> {{ $bankAccount->full_account_info }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Criado em:</span> 
                    {{ $bankAccount->created_at->format('d/m/Y H:i') }}
                </div>
                
                <div class="mb-2">
                    <span class="fw-bold">Última atualização:</span> 
                    {{ $bankAccount->updated_at->format('d/m/Y H:i') }}
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
                    <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Conta
                    </a>
                    
                    <form action="{{ route('bank-accounts.destroy', $bankAccount) }}" method="POST" id="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                            <i class="bi bi-trash"></i> Excluir Conta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    Swal.fire({
        title: 'Atenção!',
        html: 'Tem certeza que deseja excluir esta conta bancária?<br><br>Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}
</script>

@endsection
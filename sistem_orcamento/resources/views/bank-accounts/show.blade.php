@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-bank"></i> Detalhes da Conta Bancária</h4>
                    <div>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm me-2">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo:</label>
                                <p class="form-control-plaintext">
                                    @if($bankAccount->type === 'PIX')
                                        <span class="badge bg-info fs-6">PIX</span>
                                    @else
                                        <span class="badge bg-primary fs-6">Conta</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if(auth()->guard('web')->user()->role === 'super_admin')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Empresa:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->company->fantasy_name ?? $bankAccount->company->corporate_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Banco:</label>
                                <p class="form-control-plaintext">
                                    @if($bankAccount->compe)
                                        {{ $bankAccount->compe->code }} - {{ $bankAccount->compe->bank_name }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($bankAccount->type === 'Conta')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Agência:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->branch ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Conta:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->account ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Descrição:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->description }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext">
                                    @if($bankAccount->active)
                                        <span class="badge bg-success fs-6">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Inativo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Informações Completas:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->full_account_info }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Criado em:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Atualizado em:</label>
                                <p class="form-control-plaintext">{{ $bankAccount->updated_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('bank-accounts.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-list"></i> Listar Contas
                        </a>
                        <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="btn btn-warning me-2">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form action="{{ route('bank-accounts.destroy', $bankAccount) }}" method="POST" class="d-inline" id="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
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
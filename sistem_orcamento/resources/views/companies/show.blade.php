@extends('layouts.app')

@section('title', 'Visualizar Empresa - Sistema de Orçamento')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-building"></i> Detalhes da Empresa
            </h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informações da Empresa
                </h5>
            </div>
            <div class="card-body">
                @if($company->logo)
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <label class="form-label fw-bold d-block mb-2">Logomarca:</label>
                        <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo da {{ $company->corporate_name ?? $company->fantasy_name }}" class="img-fluid" style="max-width: 200px; max-height: 150px;">
                    </div>
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Razão Social:</label>
                        <p class="form-control-plaintext">{{ $company->corporate_name }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome Fantasia:</label>
                        <p class="form-control-plaintext">{{ $company->fantasy_name ?: 'Não informado' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">CNPJ:</label>
                        <p class="form-control-plaintext">{{ $company->cnpj }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Inscrição Estadual:</label>
                        <p class="form-control-plaintext">{{ $company->state_registration ?: 'Não informado' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Telefone:</label>
                        <p class="form-control-plaintext">
                            @if($company->phone)
                                <a href="tel:{{ $company->phone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone"></i> {{ $company->phone }}
                                </a>
                            @else
                                Não informado
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email:</label>
                        <p class="form-control-plaintext">
                            @if($company->email)
                                <a href="mailto:{{ $company->email }}" class="text-decoration-none">
                                    <i class="bi bi-envelope"></i> {{ $company->email }}
                                </a>
                            @else
                                Não informado
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Endereço:</label>
                    <p class="form-control-plaintext">{{ $company->address ?: 'Não informado' }}</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cadastrado em:</label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-calendar"></i> {{ $company->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Última atualização:</label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-clock"></i> {{ $company->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
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
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Empresa
                    </a>
                    
                    <form action="{{ route('companies.destroy', $company) }}" method="POST" 
                          onsubmit="return confirm('Tem certeza que deseja excluir esta empresa? Esta ação não pode ser desfeita.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Excluir Empresa
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contatos (apenas para super_admin) -->
        @if(auth()->user()->role === 'super_admin')
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people"></i> Contatos ({{ $company->contacts->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($company->contacts->count() > 0)
                    <div class="row">
                        @foreach($company->contacts as $contact)
                            <div class="col-md-12 mb-12">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td class="fw-bold" style="width: 30%;">Nome:</td>
                                                <td>
                                                    
                                                    {{ $contact->name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">CPF:</td>
                                                <td>
                                                    @if($contact->cpf)
                                                        {{ $contact->cpf }}
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Telefone:</td>
                                                <td>
                                                    @if($contact->phone)
                                                        <a href="tel:{{ $contact->phone }}" class="text-decoration-none">
                                                             {{ $contact->phone }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Email:</td>
                                                <td>
                                                    @if($contact->email)
                                                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                                             {{ $contact->email }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Não informado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">Nenhum contato cadastrado para esta empresa.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
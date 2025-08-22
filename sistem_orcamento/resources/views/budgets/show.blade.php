@extends('layouts.app')

@section('title', 'Orçamento #' . $budget->number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        {{ $budget->client->corporate_name ?? $budget->client->fantasy_name }} {{ $budget->number }}
                    </h3>
                    <div>
                        <a href="{{ route('budgets.pdf', $budget) }}" class="btn btn-secondary" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Seção Empresa (75%) e Informações do Orçamento (25%) -->
                    <div class="row mb-4">
                        <!-- Empresa - 75% -->
                        <div class="col-md-9">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Empresa</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Logo da Empresa -->
                                        @if($budget->company->logo)
                                        <div class="col-md-3 text-center mb-3">
                                            <img src="{{ asset('storage/' . $budget->company->logo) }}" alt="Logo da {{ $budget->company->corporate_name ?? $budget->company->fantasy_name }}" class="img-fluid" style="max-width: 120px; max-height: 100px;">
                                        </div>
                                        <div class="col-md-9">
                                        @else
                                        <div class="col-md-12">
                                        @endif
                                            <h6 class="fw-bold mb-3">{{ $budget->company->corporate_name }}</h6>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @if($budget->company->document_number)
                                                    <p class="mb-2"><strong>CNPJ:</strong> {{ $budget->company->document_number }}</p>
                                                    @endif
                                                    @if($budget->company->phone)
                                                    <p class="mb-2"><strong>Telefone:</strong> {{ $budget->company->phone }}</p>
                                                    @endif
                                                    @if($budget->company->email)
                                                    <p class="mb-2"><strong>Email:</strong> {{ $budget->company->email }}</p>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    @if($budget->company->address || $budget->company->city || $budget->company->state)
                                                    <p class="mb-2"><strong>Endereço:</strong><br>
                                                        {{ $budget->company->address }}@if($budget->company->address && ($budget->company->city || $budget->company->state)), @endif{{ $budget->company->city }}@if($budget->company->city && $budget->company->state) - @endif{{ $budget->company->state }}
                                                    </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações do Orçamento - 25% -->
                        <div class="col-md-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <h4 class="text-primary mb-3">{{ $budget->number }}</h4>
                                    <p class="mb-2"><strong>Data:</strong><br>{{ $budget->issue_date->format('d/m/Y') }}</p>
                                    <p class="mb-2"><strong>Validade:</strong><br>{{ $budget->valid_until->format('d/m/Y') }}</p>
                                    <p class="mb-2"><strong>Status:</strong><br>
                                        <span class="badge 
                                            @if($budget->status == 'Pendente') bg-warning
                                            @elseif($budget->status == 'Enviado') bg-info
                                            @elseif($budget->status == 'Em negociação') bg-primary
                                            @elseif($budget->status == 'Aprovado') bg-success
                                            @elseif($budget->status == 'Expirado') bg-danger
                                            @elseif($budget->status == 'Concluído') bg-secondary
                                            @else bg-light text-dark
                                            @endif">
                                            {{ $budget->status }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dados do Cliente -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Nome:</strong> 
                                                @if($budget->client->corporate_name)
                                                    {{ $budget->client->corporate_name }}
                                                @elseif($budget->client->fantasy_name)
                                                    {{ $budget->client->fantasy_name }}
                                                @else
                                                    <span class="text-muted">Nome não informado</span>
                                                @endif
                                            </p>
                                            @if($budget->client->document_number)
                                            <p class="mb-2"><strong>CPF/CNPJ:</strong> {{ $budget->client->document_number }}</p>
                                            @endif
                                            @if($budget->client->phone)
                                            <p class="mb-2"><strong>Telefone:</strong> {{ $budget->client->phone }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            @if($budget->client->email)
                                            <p class="mb-2"><strong>Email:</strong> {{ $budget->client->email }}</p>
                                            @endif
                                            @if($budget->client->address || $budget->client->city || $budget->client->state)
                                            <p class="mb-2"><strong>Endereço:</strong><br>
                                                {{ $budget->client->address }}@if($budget->client->address && ($budget->client->city || $budget->client->state)), @endif{{ $budget->client->city }}@if($budget->client->city && $budget->client->state) - @endif{{ $budget->client->state }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Itens do Orçamento -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Itens do Orçamento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Produto</th>
                                                    <th>Descrição</th>
                                                    <th class="text-end">Qtd</th>
                                                    <th class="text-end">Valor Unit.</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($budget->items as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($item->product)
                                                            <strong>{{ $item->product->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $item->product->category->name ?? 'Sem categoria' }}</small>
                                                        @else
                                                            <strong class="text-muted">Produto excluído</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $item->description ?: 'Produto não disponível' }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item->description)
                                                            {{ $item->description }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                                    <td class="text-end">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                                    <td class="text-end"><strong>R$ {{ number_format($item->total_price, 2, ',', '.') }}</strong></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Totais -->
                                    <div class="row">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <span>Subtotal:</span>
                                                        <span>R$ {{ number_format($budget->items->sum('total_price'), 2, ',', '.') }}</span>
                                                    </div>
                                                    @if($budget->total_discount > 0)
                                                    <div class="d-flex justify-content-between">
                                                        <span>Desconto:</span>
                                                        <span>R$ {{ number_format($budget->total_discount, 2, ',', '.') }}</span>
                                                    </div>
                                                    @endif
                                                    <hr>
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Total:</strong>
                                                        <strong class="text-success">R$ {{ number_format($budget->final_amount, 2, ',', '.') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($budget->observations)
                    <!-- Observações -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Observações</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{!! nl2br(e($budget->observations)) !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Linhas de Assinatura -->
                    <div class="row mb-4">
                        <div class="col-md-5">
                            
                                <div class="text-center">
                                    <div style="height: 80px; border-bottom: 2px solid #000; margin-bottom: 10px;"></div>
                                    <p class="mb-0"><strong>Assinatura do Cliente</strong></p>
                                    <small class="text-muted">{{ $budget->client->corporate_name ?? $budget->client->fantasy_name ?? 'Cliente' }}</small>
                                </div>
                            
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5">
                           
                                <div class="text-center">
                                    <div style="height: 80px; border-bottom: 2px solid #000; margin-bottom: 10px;"></div>
                                    <p class="mb-0"><strong>Assinatura da Empresa</strong></p>
                                    <small class="text-muted">{{ $budget->company->corporate_name }}</small>
                                </div>
                            
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('budgets.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar para Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@extends('layouts.app')

@section('title', 'Visualizar Produto - Sistema de Orçamento')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-box"></i> Detalhes do Produto
            </h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informações do Produto
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nome:</label>
                        <p class="form-control-plaintext">{{ $product->name }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Preço:</label>
                        <p class="form-control-plaintext text-success fw-bold">R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Categoria:</label>
                        <p class="form-control-plaintext">
                            @if($product->category)
                                <span class="badge bg-primary">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">Sem categoria</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Slug:</label>
                        <p class="form-control-plaintext">{{ $product->slug ?: 'Não informado' }}</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Descrição:</label>
                    <p class="form-control-plaintext">{{ $product->description ?: 'Não informado' }}</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cadastrado em:</label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-calendar"></i> {{ $product->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Última atualização:</label>
                        <p class="form-control-plaintext">
                            <i class="bi bi-clock"></i> {{ $product->updated_at->format('d/m/Y H:i') }}
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
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Produto
                    </a>
                    
                    <form action="{{ route('products.destroy', $product) }}" method="POST" 
                          onsubmit="return confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Excluir Produto
                        </button>
                    </form>
                    
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar à Lista
                    </a>
                </div>
            </div>
        </div>
        
        @if($product->category)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-tag"></i> Categoria
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">{{ $product->category->name }}</h6>
                        @if($product->category->description)
                            <small class="text-muted">{{ $product->category->description }}</small>
                        @endif
                    </div>
                    <a href="{{ route('categories.show', $product->category) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Ver Categoria
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
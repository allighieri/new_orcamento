@extends('layouts.app')

@section('title', 'Visualizar Produto - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-box"></i> Detalhes do Produto
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
                    <i class="bi bi-info-circle"></i> Informações do Produto
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-1">
                        <label class="form-label fw-bold">Produto:</label>
                        {{ $product->name }} -
                        <label class="form-label fw-bold">Categoria:</label>
                        @if($product->category)
                            {{ $product->category->name }}
                        @else
                            Sem categoria
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-1">
                        <label class="form-label fw-bold">Preço:</label>
                        <span class="text-success fw-bold">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                    </div>
                </div>
                
                @if($product->description)
                <div class="row">
                    <div class="col-12 mb-1">
                        <label class="form-label fw-bold">Descrição:</label>
                        {{ $product->description }}
                    </div>
                </div>
                @endif
                
                <div class="col-md-6 mb-1">
                    <label class="form-label fw-bold">Data de Criação:</label>
                    {{ $product->created_at->format('d/m/Y H:i') }}
                </div>
                
                <div class="col-md-6 mb-1">
                    <label class="form-label fw-bold">Última Atualização:</label>
                    {{ $product->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
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
                    
                    <form action="{{ route('products.destroy', $product) }}" method="POST" id="delete-form-product-{{ $product->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger w-100" onclick="confirmDeleteProduct({{ $product->id }})">
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

<script>
function confirmDeleteProduct(productId) {
    Swal.fire({
        title: 'Confirmação',
        text: 'Tem certeza de que quer excluir este produto?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-product-' + productId).submit();
        }
    });
}
</script>

@endsection
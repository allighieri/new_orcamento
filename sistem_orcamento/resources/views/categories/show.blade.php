@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="fas fa-tags me-2"></i>
                    Detalhes da Categoria
                </h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações da Categoria
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nome:</label>
                            <p class="form-control-plaintext">{{ $category->name }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Slug:</label>
                            <p class="form-control-plaintext">{{ $category->slug }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoria Pai:</label>
                            <p class="form-control-plaintext">
                                @if($category->parent)
                                    <a href="{{ route('categories.show', $category->parent) }}" class="text-decoration-none">
                                        {{ $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Categoria Principal</span>
                                @endif
                            </p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nível:</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info">{{ $category->level ?? 0 }}</span>
                            </p>
                        </div>
                    </div>
                    
                    @if($category->description)
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Descrição:</label>
                            <p class="form-control-plaintext">{{ $category->description }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data de Criação:</label>
                            <p class="form-control-plaintext">{{ $category->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Última Atualização:</label>
                            <p class="form-control-plaintext">{{ $category->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Subcategorias -->
            @if($category->children->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        Subcategorias ({{ $category->children->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($category->children as $child)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <a href="{{ route('categories.show', $child) }}" class="text-decoration-none">
                                    {{ $child->name }}
                                </a>
                                @if($child->description)
                                    <small class="text-muted d-block">{{ Str::limit($child->description, 50) }}</small>
                                @endif
                            </div>
                            <span class="badge bg-secondary">{{ $child->products->count() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Produtos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box me-2"></i>
                        Produtos ({{ $category->products->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($category->products->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($category->products->take(10) as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->description)
                                        <small class="text-muted d-block">{{ Str::limit($product->description, 50) }}</small>
                                    @endif
                                </div>
                                <span class="text-success fw-bold">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                            </div>
                            @endforeach
                            
                            @if($category->products->count() > 10)
                            <div class="list-group-item px-0 text-center">
                                <small class="text-muted">E mais {{ $category->products->count() - 10 }} produtos...</small>
                            </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0">Nenhum produto cadastrado nesta categoria.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>
                    Editar
                </a>
                
                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" 
                      onsubmit="return confirm('Tem certeza que deseja excluir esta categoria? Todos os produtos desta categoria também serão excluídos. Esta ação não pode ser desfeita.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Excluir
                    </button>
                </form>
                
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Voltar à Lista
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
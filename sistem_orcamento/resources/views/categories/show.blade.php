@extends('layouts.app')

@section('content')
<div class="container mx-auto row">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-tags"></i>
                    Detalhes da Categoria
                </h1>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
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
                        <div class="col-md-6 mb-1">
                            <label class="form-label fw-bold">Nome:</label>
                            {{ $category->name }}
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label fw-bold">Categoria Pai:</label>
                            
                                @if($category->parent)
                                    <a href="{{ route('categories.show', $category->parent) }}" class="text-decoration-none">
                                        {{ $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Categoria Principal</span>
                                @endif
                            
                        </div>
                        
                        
                    </div>
                    
                    @if($category->description)
                    <div class="row">
                        <div class="col-12 mb-1">
                            <label class="form-label fw-bold">Descrição:</label>
                            {{ $category->description }}
                        </div>
                    </div>
                    @endif
                    
                    
                    <div class="col-md-6 mb-1">
                        <label class="form-label fw-bold">Data de Criação:</label>
                        {{ $category->created_at->format('d/m/Y H:i') }}
                    </div>
                    
                    <div class="col-md-6 mb-1">
                        <label class="form-label fw-bold">Última Atualização:</label>
                        {{ $category->updated_at->format('d/m/Y H:i') }}
                    </div>
                    
                </div>
            </div>
        </div>
        
        <div class="col-md-4">

        
            
            <div class="card mb-3 mt-3 mt-lg-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> Ações
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" id="delete-form-category-{{ $category->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDeleteCategory({{ $category->id }})">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </form>
                        
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar à Lista
                        </a>
                    </div>
                </div>
            </div>

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
    
    
</div>

<script>
function confirmDeleteCategory(categoryId) {
    Swal.fire({
        title: 'Atenção!',
        text: 'Atenção, excluir uma Categoria excluirá também todos os produtos relacionados. Tem certeza de que deseja excluir esta Categoria?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-category-' + categoryId).submit();
        }
    });
}
</script>

@endsection
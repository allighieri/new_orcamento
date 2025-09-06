@extends('layouts.app')

@section('title', 'Categorias - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-tags"></i> Categorias
            </h1>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Nova Categoria
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <!-- Formulário de Pesquisa -->
        <div class="mb-4">
            <form method="GET" action="{{ route('categories.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nome da categoria">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-body">
                @if($categoriesTree->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Hierarquia</th>
                                    <th>Descrição</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <th>Empresa</th>
                                    @endif
                                    <th>Produtos</th>
                                    <th class="text-end" style="width: 1%;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoriesTree as $item)
                                <tr>
                                    <td>
                                        {!! str_replace(' ', '&nbsp;', $item->prefix) !!}
                                        <a href="{{ route('categories.products', $item->category) }}" class="text-decoration-none fw-bold">
                                            {{ $item->category->name }}
                                            @if($isSuperAdmin && !$item->category->parent_id && $item->category->company)
                                                <small class="text-muted"> ({{ $item->category->company->fantasy_name }})</small>
                                            @endif
                                        </a>
                                    </td>
                                    <td>{{ $item->category->description ? Str::limit($item->category->description, 50) : 'N/A' }}</td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>
                                            <span class="badge bg-secondary">{{ $item->category->company->fantasy_name ?? 'N/A' }}</span>
                                        </td>
                                    @endif
                                    <td>
                                        <a href="{{ route('categories.products', $item->category) }}" class="text-decoration-none">
                                            <span class="badge bg-info">{{ $item->category->products_count ?? 0 }}</span>
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('products.create', ['category_id' => $item->category->id]) }}" class="btn btn-sm btn-outline-primary" title="Cadastrar Produto">
                                                <i class="bi bi-plus-circle"></i>
                                            </a>
                                            <a href="{{ route('categories.show', $item->category) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('categories.edit', $item->category) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteCategory({{ $item->category->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('categories.destroy', $item->category) }}" method="POST" class="d-none" id="delete-form-category-{{ $item->category->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-tags fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma categoria cadastrada</h4>
                        <p class="text-muted">Comece cadastrando sua primeira categoria</p>
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Nova Categoria
                        </a>
                    </div>
                @endif
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
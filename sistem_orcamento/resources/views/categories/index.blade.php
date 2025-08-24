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
                                    <th>Ações</th>
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
                                    <td>
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
                                            <form action="{{ route('categories.destroy', $item->category) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria? Todos os produtos desta categoria também serão excluídos. Esta ação não pode ser desfeita.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
                            <i class="bi bi-plus"></i> Cadastrar Categoria
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Produtos - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-box"></i> Produtos
            </h1>
            
           <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Novo Produto
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <th>Empresa</th>
                                    @endif
                                    <th>Preço</th>
                                    
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->description ? Str::limit($product->description, 50) : 'N/A' }}</td>
                                    <td>
                                        @if($product->category)
                                           {{ $product->category->name }}
                                        @else
                                            Sem categoria
                                        @endif
                                    </td>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <td>{{ $product->company->fantasy_name ?? 'N/A' }}</td>
                                    @endif
                                    <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                    
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
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
                    
                    {{ $products->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-box fs-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhum produto cadastrado</h4>
                        <p class="text-muted">Comece cadastrando seu primeiro produto</p>
                        <a href="{{ route('products.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Cadastrar Produto
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
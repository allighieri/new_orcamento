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
                    
                    <th class="text-end" style="width: 1%;">Ações</th>
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
                    
                    <td class="text-end">
                        <div class="btn-group" role="group">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="confirmDeleteProduct({{ $product->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-none" id="delete-form-product-{{ $product->id }}">
                            @csrf
                            @method('DELETE')
                        </form>
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
        <h4 class="text-muted mt-3">Nenhum produto encontrado</h4>
        <p class="text-muted">Não há produtos que correspondam à sua pesquisa</p>
    </div>
@endif
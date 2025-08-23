@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Editar Orçamento #{{ $budget->number }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('budgets.update', $budget) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Cliente @if(Auth::check() && Auth::user()->role === 'super_admin')*@endif</label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" 
                                            id="client_id" name="client_id">
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" 
                                {{ old('client_id', $budget->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->corporate_name ?? $client->fantasy_name }}
                            </option>
                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                   
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa @if(Auth::check() && Auth::user()->role === 'super_admin')*@endif</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" 
                                            id="company_id" name="company_id">
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" 
                                                {{ old('company_id', $budget->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->corporate_name ?? $company->fantasy_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="issue_date" class="form-label">Data*</label>
                                    <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                           id="issue_date" name="issue_date" 
                                           value="{{ old('issue_date', $budget->issue_date ? $budget->issue_date->format('Y-m-d') : '') }}" required>
                                    @error('issue_date')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="valid_until" class="form-label">Validade*</label>
                                    <input type="date" class="form-control @error('valid_until') is-invalid @enderror" 
                                           id="valid_until" name="valid_until" 
                                           value="{{ old('valid_until', $budget->valid_until ? $budget->valid_until->format('Y-m-d') : '') }}" required>
                                    @error('valid_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                      
           

                        </div>

              
                        





                        <!-- Produtos -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-3">Produtos do Orçamento</h5>
                            </div>

                            <div id="productsContainer">
                                @php
                                    $hasValidItems = false;
                                @endphp
                                @foreach($budget->items as $index => $item)
                                    @php
                                        // Verificar se o item tem um produto válido
                                        $hasValidProduct = $item->product_id && $products->contains('id', $item->product_id);
                                        if ($hasValidProduct) {
                                            $hasValidItems = true;
                                        }
                                    @endphp
                                <div class="product-row mb-3" data-index="{{ $index }}">
                                    <div class="row align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label">Produto*</label>
                                            <div class="input-group">
                                                <select class="form-select product-select" name="items[{{ $index }}][product_id]" required>
                                                    <option value="">Selecione um produto</option>
                                                    @if($item->product_id && !$products->contains('id', $item->product_id))
                                                        <option value="{{ $item->product_id }}" selected disabled class="text-muted">
                                                            Produto excluído (ID: {{ $item->product_id }})
                                                        </option>
                                                    @endif
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}"
                                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} - {{ $product->category->name ?? 'Sem categoria' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addProductModal" title="Adicionar novo produto">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label">Desc.:</label>
                                            <textarea class="form-control" name="items[{{ $index }}][description]" rows="1">{{ $item->description ?? '' }}</textarea>
                                        </div>
                                        
                                        <div class="col-md-1">
                                            <label class="form-label">Qtde*</label>
                                            <input type="text" class="form-control quantity-input" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label" for="autoSizingInputGroup">Pç. Unit.</label>
                                            <div class="input-group">
                                                <div class="input-group-text">R$</div>
                                                <input type="text" class="form-control money unit-price-input" name="items[{{ $index }}][unit_price]" value="{{ number_format($item->unit_price, 2, ',', '.') }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Total</label>
                                            <div class="input-group">
                                                <div class="input-group-text">R$</div>
                                                <input type="text" class="form-control total-input" readonly value="{{ number_format($item->total_price, 2, ',', '.') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                
                                @if(!$hasValidItems)
                                    <!-- Se não há itens válidos, adicionar uma linha em branco -->
                                    <div class="product-row mb-3" data-index="0">
                                        <div class="row align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label">Produto*</label>
                                                <div class="input-group">
                                                    <select class="form-select product-select" name="items[0][product_id]" required>
                                                        <option value="">Selecione um produto</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}">
                                                                {{ $product->name }} - {{ $product->category->name ?? 'Sem categoria' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addProductModal" title="Adicionar novo produto">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <label class="form-label">Desc.:</label>
                                                <textarea class="form-control" name="items[0][description]" rows="1"></textarea>
                                            </div>
                                            
                                            <div class="col-md-1">
                                                <label class="form-label">Qtde*</label>
                                                <input type="text" class="form-control quantity-input" name="items[0][quantity]" value="1" required>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label" for="autoSizingInputGroup">Pç. Unit.</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" class="form-control money unit-price-input" name="items[0][unit_price]" value="0,00" required>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Total</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" class="form-control total-input" readonly value="0,00">
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto">
                                                        <i class="bi bi-plus-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Botão para adicionar produto quando não há produtos -->
                            <div id="addFirstProductBtn" class="text-center my-3" style="display: none;">
                                <button type="button" class="btn btn-success add-product">
                                    <i class="bi bi-plus-circle"></i> Adicionar Produto
                                </button>
                            </div>

                            <div class="my-3">
                                <label for="observations" class="form-label">Obs.:</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3">{{ old('observations', $budget->observations) }}</textarea>
                                @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8 d-flex justify-content-end">
                                     <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="total_discount" class="form-label">Desconto</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" placeholder="0,00" class="form-control money @error('total_discount') is-invalid @enderror" id="total_discount" name="total_discount" value="{{ old('total_discount', number_format($budget->total_discount, 2, ',', '.')) }}">
                                                    @error('total_discount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">

                                                        

                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <span>Subtotal:</span>
                                                <span id="subtotal">R$ 0,00</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Desconto:</span>
                                                <span id="discountDisplay">R$ 0,00</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Total:</strong>
                                                <strong id="total">R$ 0,00</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                        </div>

                        

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Orçamento
                            </button>
                            <a href="{{ route('budgets.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para adicionar produto -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">
                    <i class="bi bi-plus-circle"></i> Novo Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProductForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_name" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="modal_name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_price" class="form-label">Preço *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control money" id="modal_price" name="price" placeholder="0,00" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="modal_description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="modal_description" name="description" rows="3" placeholder="Descrição detalhada do produto"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="modal_category_id" class="form-label">Categoria *</label>
                                <div class="input-group">
                                    <select class="form-select" id="modal_category_id" name="category_id" required>
                                        <option value="">Selecione uma categoria</option>
                                        @php
                                            $categoriesTree = App\Models\Category::getTreeForSelect();
                                        @endphp
                                        @foreach($categoriesTree as $categoryId => $categoryName)
                                            <option value="{{ $categoryId }}">
                                                {!! $categoryName !!}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary" id="openCategoryModalBtn" title="Adicionar nova categoria">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveProductBtn">
                        <i class="bi bi-check-circle"></i> Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Adicionar Nova Categoria -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addCategoryForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="bi bi-tags"></i> Nova Categoria
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="category_name" name="name" placeholder="Digite o nome da categoria" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="category_parent_id" class="form-label">Categoria Pai</label>
                                <select class="form-select" id="category_parent_id" name="parent_id">
                                    <option value="">Categoria Principal</option>
                                    @php
                                        $categoriesTree = App\Models\Category::getTreeForSelect();
                                    @endphp
                                    @foreach($categoriesTree as $categoryId => $categoryName)
                                        <option value="{{ $categoryId }}">
                                            {!! $categoryName !!}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Deixe em branco para criar uma categoria principal. Subcategorias são indicadas por indentação.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="category_description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="category_description" name="description" rows="3" placeholder="Descreva a categoria..."></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveCategoryBtn">
                        <i class="bi bi-check-circle"></i> Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Máscara para campos monetários
    $('.money').mask('000.000.000.000.000,00', {
        reverse: true,
        placeholder: '0,00'
    });

    // Máscara para desconto
    $('#total_discount').mask('000.000.000.000.000,00', {
        reverse: true,
        placeholder: '0,00'
    });

    // Atualizar visibilidade dos botões no carregamento
    updateAddButtonVisibility();
    calculateTotals();

    // Adicionar produto (novo botão em cada linha)
    $(document).on('click', '.add-product', function() {
        let currentRow = $(this).closest('.product-row');
        let newIndex = $('.product-row').length;
        
        const productHtml = `
            <div class="product-row mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Produto *</label>
                        <div class="input-group">
                            <select class="form-select product-select" name="items[${newIndex}][product_id]" required>
                                <option value="">Selecione um produto</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}">
                                        {{ $product->name }} - {{ $product->category->name ?? 'Sem categoria' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addProductModal" title="Adicionar novo produto">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Desc.:</label>
                        <textarea class="form-control" name="items[${newIndex}][description]" rows="1"></textarea>
                    </div>
                    
                    <div class="col-md-1">
                        <label class="form-label">Qtde*</label>
                        <input type="text" class="form-control quantity-input" name="items[${newIndex}][quantity]" value="1" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label" for="autoSizingInputGroup">Pç. Unit.</label>
                        <div class="input-group">
                            <div class="input-group-text">R$</div>
                            <input type="text" class="form-control money unit-price-input" name="items[${newIndex}][unit_price]" value="0,00" required>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Total</label>
                        <div class="input-group">
                            <div class="input-group-text">R$</div>
                            <input type="text" class="form-control total-input" readonly value="0,00">
                        </div>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto">
                                <i class="bi bi-trash"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Adicionar nova linha após a atual
        currentRow.after(productHtml);
        
        // Aplicar máscara nos novos campos
        $('.money').mask('000.000.000.000.000,00', {
            reverse: true,
            placeholder: '0,00'
        });
        
        // Atualizar visibilidade dos botões
        updateAddButtonVisibility();
        
        // Calcular totais
        calculateTotals();
    });
    
    // Função para controlar a visibilidade dos botões de adicionar
    function updateAddButtonVisibility() {
        // Esconder todos os botões de adicionar
        $('.add-product').hide();
        
        // Verificar se há produtos
        if ($('.product-row').length === 0) {
            // Se não há produtos, mostrar o botão de adicionar primeiro produto
            $('#addFirstProductBtn').show();
        } else {
            // Se há produtos, esconder o botão de adicionar primeiro produto
            $('#addFirstProductBtn').hide();
            // Mostrar apenas o botão da última linha
            $('.product-row:last .add-product').show();
        }
    }
    
    // Remover produto
    $(document).on('click', '.remove-product-btn', function() {
        let rowToRemove = $(this).closest('.product-row');
        rowToRemove.remove();
        
        // Se não sobrou nenhuma linha, mostrar o botão de adicionar primeiro produto
        if ($('.product-row').length === 0) {
            $('#addFirstProductBtn').show();
        }
        
        updateAddButtonVisibility();
        calculateTotals();
    });
    
    // Atualizar preço e descrição quando produto é selecionado
    $(document).on('change', '.product-select', function() {
        const price = $(this).find(':selected').data('price');
        const description = $(this).find(':selected').data('description');
        const productRow = $(this).closest('.product-row');
        
        if (price !== undefined) {
            const formattedPrice = parseFloat(price).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            productRow.find('.unit-price-input').val(formattedPrice);
            calculateItemTotal(productRow);
        }
        
        // Preencher descrição adicional com a descrição do produto
        if (description !== undefined) {
            productRow.find('textarea[name*="[description]"]').val(description);
        }
    });
    
    // Calcular total do item quando quantidade ou preço mudar
    $(document).on('input', '.quantity-input, .unit-price-input', function() {
        calculateItemTotal($(this).closest('.product-row'));
    });
    
    // Calcular total do desconto
    $('#total_discount').on('input', function() {
        calculateTotals();
    });
    
    function calculateItemTotal(productRow) {
        const quantity = parseFloat(productRow.find('.quantity-input').val()) || 0;
        const unitPriceStr = productRow.find('.unit-price-input').val().replace(/\./g, '').replace(',', '.');
        const unitPrice = parseFloat(unitPriceStr) || 0;
        const total = quantity * unitPrice;
        
        productRow.find('.total-input').val(total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        calculateTotals();
    }
    
    function calculateTotals() {
        let subtotal = 0;
        
        $('.product-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const unitPriceStr = $(this).find('.unit-price-input').val().replace(/\./g, '').replace(',', '.');
            const unitPrice = parseFloat(unitPriceStr) || 0;
            subtotal += quantity * unitPrice;
        });
        
        const discountStr = $('#total_discount').val().replace(/\./g, '').replace(',', '.');
        const discount = parseFloat(discountStr) || 0;
        const total = subtotal - discount;
        
        $('#subtotal').text('R$ ' + subtotal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        $('#discountDisplay').text('R$ ' + discount.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        $('#total').text('R$ ' + total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }
    
    // Calcular totais iniciais ao carregar a página
    $('.product-row').each(function() {
        calculateItemTotal($(this));
    });
    
    // Atualizar visibilidade dos botões de adicionar no carregamento
    updateAddButtonVisibility();
    
    // Funcionalidade do modal de adicionar produto
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        
        // Limpar mensagens de erro anteriores
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Desabilitar botão de salvar
        $('#saveProductBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Salvando...');
        
        // Preparar dados do formulário
        let formData = {
            name: $('#modal_name').val(),
            price: $('#modal_price').val(),
            description: $('#modal_description').val(),
            category_id: $('#modal_category_id').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // Enviar requisição AJAX
        $.ajax({
            url: '{{ route("products.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Adicionar novo produto ao select de todas as linhas
                    let newOption = `<option value="${response.product.id}" data-price="${response.product.price}" data-description="${response.product.description}">
                        ${response.product.name} - ${response.product.category_name || 'Sem categoria'}
                    </option>`;
                    
                    $('.product-select').each(function() {
                        $(this).append(newOption);
                    });
                    
                    // Selecionar o novo produto na linha atual (última linha visível)
                    let lastProductSelect = $('.product-row:last .product-select');
                    lastProductSelect.val(response.product.id).trigger('change');
                    
                    // Preencher descrição automaticamente
                    let lastDescriptionField = $('.product-row:last textarea[name*="[description]"]');
                    lastDescriptionField.val(response.product.description);
                    
                    // Fechar modal e limpar formulário
                    $('#addProductModal').modal('hide');
                    $('#addProductForm')[0].reset();
                } else {
                    alert('Erro ao criar produto. Tente novamente.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Erros de validação
                    let errors = xhr.responseJSON.errors;
                    
                    $.each(errors, function(field, messages) {
                        let input = $('#modal_' + field);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    alert('Erro interno do servidor. Tente novamente.');
                }
            },
            complete: function() {
                // Reabilitar botão de salvar
                $('#saveProductBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Produto');
            }
        });
    });
    
    // Limpar formulário quando modal for fechado
    $('#addProductModal').on('hidden.bs.modal', function() {
        $('#addProductForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#saveProductBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Produto');
    });
    
    // Funcionalidade do modal de adicionar categoria
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        // Limpar mensagens de erro anteriores
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Desabilitar botão de salvar
        $('#saveCategoryBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Salvando...');
        
        // Preparar dados do formulário
        let formData = {
            name: $('#category_name').val(),
            description: $('#category_description').val(),
            parent_id: $('#category_parent_id').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // Enviar requisição AJAX
        $.ajax({
            url: '{{ route("categories.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Adicionar nova categoria ao select do modal de produto
                    let categorySelect = $('#modal_category_id');
                    let newOption = new Option(response.category.name, response.category.id, true, true);
                    categorySelect.append(newOption);
                    
                    // Atualizar também o select de categoria pai na modal de categoria
                    let parentSelect = $('#category_parent_id');
                    let parentOption = new Option(response.category.name, response.category.id);
                    parentSelect.append(parentOption);
                    
                    // Fechar modal e limpar formulário
                    $('#addCategoryModal').modal('hide');
                    $('#addCategoryForm')[0].reset();
                } else {
                    alert('Erro ao criar categoria. Tente novamente.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Erros de validação
                    let errors = xhr.responseJSON.errors;
                    
                    $.each(errors, function(field, messages) {
                        let input = $('#category_' + field);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    alert('Erro interno do servidor. Tente novamente.');
                }
            },
            complete: function() {
                // Reabilitar botão de salvar
                $('#saveCategoryBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Categoria');
            }
        });
    });
    
    // Abrir modal de categoria sem fechar modal de produto
    $('#openCategoryModalBtn').on('click', function() {
        $('#addCategoryModal').modal('show');
    });
    
    // Limpar formulário quando modal de categoria for fechado
    $('#addCategoryModal').on('hidden.bs.modal', function() {
        $('#addCategoryForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#saveCategoryBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Categoria');
    });
    
    // Aplicar máscara no campo de preço do modal
    $('#modal_price').mask('000.000.000.000.000,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    // Calcular automaticamente a data de validade (15 dias após a data de emissão)
    $('#issue_date').on('change', function() {
        const issueDate = $(this).val();
        if (issueDate) {
            const date = new Date(issueDate);
            date.setDate(date.getDate() + 15);
            
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            const validUntilDate = `${year}-${month}-${day}`;
            $('#valid_until').val(validUntilDate);
        }
    });
});
</script>
@endpush
@extends('layouts.app')

@section('title', 'Novo Orçamento')

@section('content')


    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Novo Orçamento</h5>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('budgets.store') }}" method="POST" id="budgetForm">
                        @csrf
                        
                        <div class="row">
                        
                        
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="company_id" class="form-label">Empresa*</label>
                                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id">
                                    <option value="">Selecione uma empresa</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->fantasy_name ?? $company->corporate_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @endif

                        @if(Auth::check() && Auth::user()->role === 'super_admin')
                            <div class="col-md-4">
                        @else
                            <div class="col-md-8">
                        @endif
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Cliente @if(Auth::check() && Auth::user()->role === 'super_admin')*@endif</label>
                                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id">
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->fantasy_name ?? $client->corporate_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                        <div class="mb-3">
                            <label for="issue_date" class="form-label">Data*</label>
                            <input type="date" class="form-control @error('issue_date') is-invalid @enderror" id="issue_date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required>
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
                                    value="{{ old('valid_until') }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                      
                       
                        
                        <!-- Produtos -->
                       


                            
                            
                        <div id="productsContainer">
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Produtos do Orçamento</h5>
                                    </div>

                                    <div id="productsContainer">
                                        @if(old('products'))
                                            @foreach(old('products') as $index => $productData)
                                                <div class="product-row mb-3 mt-3">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Produto*</label>
                                                            <div class="input-group">
                                                                <select class="form-select product-select @error('products.'.$index.'.product_id') is-invalid @enderror" name="products[{{ $index }}][product_id]" required>
                                                                    <option value="">Selecione um produto</option>
                                                                    @foreach($products as $product)
                                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}" {{ old('products.'.$index.'.product_id') == $product->id ? 'selected' : '' }}>
                                                                            {{ $product->name }} - {{ $product->category->name ?? 'Sem categoria' }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addProductModal" title="Adicionar novo produto">
                                                                    <i class="bi bi-plus"></i>
                                                                </button>
                                                                @error('products.'.$index.'.product_id')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-3">
                                                            <label class="form-label">Desc.:</label>
                                                            <textarea class="form-control" name="products[{{ $index }}][description]" rows="1">{{ old('products.'.$index.'.description') }}</textarea>
                                                        </div>
                                                        
                                                        <div class="col-md-1">
                                                            <label class="form-label">Qtde*</label>
                                                            <input type="text" class="form-control quantity-input" name="products[{{ $index }}][quantity]" value="{{ old('products.'.$index.'.quantity', 1) }}" required min="1">
                                                            @error('products.'.$index.'.quantity')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        
                                                        <div class="col-md-2">
                                                            <label class="form-label">Pç. Unit.</label>
                                                            <div class="input-group">
                                                                <div class="input-group-text">R$</div>
                                                                <input type="text" class="form-control money unit-price-input" name="products[{{ $index }}][unit_price]" value="{{ old('products.'.$index.'.unit_price') ? number_format(old('products.'.$index.'.unit_price'), 2, ',', '.') : '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label">Total</label>
                                                            <div class="input-group">
                                                                <div class="input-group-text">R$</div>
                                                                <input type="text" class="form-control total-price" placeholder="0,00" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-1">
                                                            <label class="form-label">&nbsp;</label>
                                                            <div class="d-flex gap-1">
                                                                <button type="button" class="btn btn-danger btn-sm remove-product">
                                                                    <i class="bi bi-trash3"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-success btn-sm add-product">
                                                                    <i class="bi bi-plus-circle"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="product-row mb-3 mt-3">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Produto*</label>
                                                        <div class="input-group">
                                                            <select class="form-select product-select" name="products[0][product_id]">
                                                                <option value="">Selecione um produto</option>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}" required>
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
                                                        <textarea class="form-control" name="products[0][description]" rows="1"></textarea>
                                                    </div>
                                                    
                                                    <div class="col-md-1">
                                                        <label class="form-label">Qtde*</label>
                                                        <input type="text" class="form-control quantity-input" name="products[0][quantity]" value="1" required min="1">
                                                    </div>
                                                    
                                                    <div class="col-md-2">
                                                        <label class="form-label">Pç. Unit.</label>
                                                        <div class="input-group">
                                                            <div class="input-group-text">R$</div>
                                                            <input type="text" class="form-control money unit-price-input" name="products[0][unit_price]">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label">Total</label>
                                                        <div class="input-group">
                                                            <div class="input-group-text">R$</div>
                                                            <input type="text" class="form-control total-price" placeholder="0,00" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-1">
                                                        <label class="form-label">&nbsp;</label>
                                                        <div class="d-flex gap-1">
                                                            <button type="button" class="btn btn-danger btn-sm remove-product">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-success btn-sm add-product">
                                                                <i class="bi bi-plus-circle"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="my-5">
                                    <label for="observations" class="form-label">Obs.:</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3">{{ old('observations') }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-8 d-flex justify-content-end">
                                         <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="total_discount" class="form-label">Desconto</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" class="form-control money" id="total_discount" name="total_discount" value="{{ old('total_discount') ? number_format(old('total_discount'), 2, ',', '.') : '' }}">
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

                         
                        
                        <hr class="my-4" />

                         <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="{{ route('budgets.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Orçamento
                            </button>
                        </div>

                        
                    </form>
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
                                <label for="modal_category_id" class="form-label">Categoria *</label>
                                <div class="input-group">
                                    <select class="form-select" id="modal_category_id" name="category_id" required>
                                        <option value="">Selecione uma categoria</option>
                                        @if(auth()->guard('web')->user()->role !== 'super_admin')
                                            @php
                                                $categoriesTree = App\Models\Category::getTreeForSelect(null, session('tenant_company_id'), false);
                                            @endphp
                                            @foreach($categoriesTree as $categoryId => $categoryName)
                                                <option value="{{ $categoryId }}">
                                                    {!! $categoryName !!}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" id="openCategoryModalBtn" title="Adicionar nova categoria">
                                        <i class="bi bi-plus"></i>
                                    </button>
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
                                        $showCompanyName = auth()->guard('web')->user()->role === 'super_admin';
                                        $categoriesTree = App\Models\Category::getTreeForSelect(null, session('tenant_company_id'), $showCompanyName);
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

<!-- Template para linha de produto -->
<template id="productRowTemplate">
    <div class="product-row mb-3 mt-3">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Produto*</label>
                <div class="input-group">
                    <select class="form-select product-select" name="products[INDEX][product_id]" id="product-select-INDEX" required>
                        <option value="">Selecione um produto</option>
                        @if(auth()->user()->profile !== 'super_admin')
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addProductModal" title="Adicionar novo produto">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Desc.:</label>
                <textarea class="form-control" name="products[INDEX][description]" rows="1"></textarea>
            </div>
            
            <div class="col-md-1">
                <label class="form-label">Qtde*</label>
                <input type="text" class="form-control quantity-input" name="products[INDEX][quantity]" value="1" required min="1">
            </div>

           
           
            <div class="col-md-2">
                <label class="form-label" for="autoSizingInputGroup">Pç. Unit.</label>
                <div class="input-group">
                    <div class="input-group-text">R$</div>
                    <input type="text" class="form-control money unit-price-input" name="products[INDEX][unit_price]">
                </div>
            </div>

             <div class="col-md-2">
                <label class="form-label" for="autoSizingInputGroup">Total</label>
                <div class="input-group">
                    <div class="input-group-text">R$</div>
                    <input type="text" class="form-control total-price" placeholder="0,00" readonly>
                </div>
            </div>

            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-danger btn-sm remove-product">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm add-product">
                        <i class="bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Definir o índice inicial para o próximo produto a ser adicionado
    let productIndex = $('#productsContainer .product-row').length;
    
    // Calcular totais iniciais se a página for recarregada com dados
    if (productIndex > 0) {
        // Recalcular os totais das linhas existentes
        $('#productsContainer .product-row').each(function() {
            calculateRowTotal($(this));
        });
        updateTotals();
    } else {
        // Se não houver produtos, adicionar a primeira linha
        let template = $('#productRowTemplate').html();
        template = template.replace(/INDEX/g, productIndex);
        $('#productsContainer').append(template);
        
        // Se temos opções de produto armazenadas, aplicá-las ao novo select
        if (window.currentProductOptions) {
            $('#productsContainer .product-row:last .product-select').html(window.currentProductOptions);
        }
        
        productIndex++;
    }
    
    // Máscara para valores monetários
    $('.money').mask('000.000.000.000.000,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    // Adicionar produto (novo botão em cada linha)
    $(document).on('click', '.add-product', function() {
        let template = $('#productRowTemplate').html();
        template = template.replace(/INDEX/g, productIndex);
        let currentRow = $(this).closest('.product-row');
        
        // Adicionar nova linha após a atual
        currentRow.after(template);
        
        // Se temos opções de produto armazenadas, aplicá-las ao novo select
        if (window.currentProductOptions) {
            currentRow.next().find('.product-select').html(window.currentProductOptions);
        }
        
        // Aplicar máscara nos novos campos com um pequeno delay
        setTimeout(function() {
            $('.money').mask('000.000.000.000.000,00', {
                reverse: true,
                placeholder: '0,00'
            });
        }, 10);
        
        // Atualizar visibilidade dos botões '+'
        updateAddButtons();
        
        productIndex++;
        updateTotals();
    });
    
    // Função para mostrar apenas o botão '+' da última linha
    function updateAddButtons() {
        $('.add-product').hide();
        $('#productsContainer .product-row:last .add-product').show();
    }
    
    // Inicializar botões
    updateAddButtons();
    
    // Remover produto
    $(document).on('click', '.remove-product', function() {
        let rowToRemove = $(this).closest('.product-row');
        
        rowToRemove.remove();
        
        // Se não sobrou nenhuma linha, adicionar uma nova
        if ($('#productsContainer .product-row').length === 0) {
            let template = $('#productRowTemplate').html();
            template = template.replace(/INDEX/g, 0); // O primeiro índice é sempre 0
            $('#productsContainer').append(template);
            
            // Se temos opções de produto armazenadas, aplicá-las ao novo select
            if (window.currentProductOptions) {
                $('#productsContainer .product-row:last .product-select').html(window.currentProductOptions);
            }
            
            setTimeout(function() {
                $('.money').mask('000.000.000.000.000,00', {
                    reverse: true,
                    placeholder: '0,00'
                });
            }, 10);
            productIndex = 1; // Resetar o índice
        }
        
        // Atualizar visibilidade dos botões '+'
        updateAddButtons();
        
        updateTotals();
    });
    
    // Quando selecionar um produto, preencher o preço e descrição
    $(document).on('change', '.product-select', function() {
        let price = $(this).find(':selected').data('price');
        let description = $(this).find(':selected').data('description');
        let row = $(this).closest('.product-row');
        
        if (price) {
            let formattedPrice = parseFloat(price).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            row.find('.unit-price-input').val(formattedPrice);
            calculateRowTotal(row);
        }
        
        // Preencher descrição adicional com a descrição do produto
        if (description) {
            row.find('textarea[name*="[description]"]').val(description);
        }
    });
    
    // Calcular total da linha quando quantidade ou preço mudar
    $(document).on('input', '.quantity-input, .unit-price-input', function() {
        calculateRowTotal($(this).closest('.product-row'));
    });
    
    // Calcular total quando desconto mudar
    $('#total_discount').on('input', function() {
        updateTotals();
    });
    
    function calculateRowTotal(row) {
        let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        let unitPrice = parseFloat(row.find('.unit-price-input').val().replace(/\./g, '').replace(',', '.')) || 0;
        let total = quantity * unitPrice;
        
        row.find('.total-price').val(total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        updateTotals();
    }
    
    function updateTotals() {
        let subtotal = 0;
        
        $('.product-row').each(function() {
            let quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            let unitPrice = parseFloat($(this).find('.unit-price-input').val().replace(/\./g, '').replace(',', '.')) || 0;
            subtotal += quantity * unitPrice;
        });
        
        let discount = parseFloat($('#total_discount').val().replace(/\./g, '').replace(',', '.')) || 0;
        let total = subtotal - discount;
        
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
    
    // Permitir que as validações do Laravel funcionem naturalmente
    
    // Funcionalidade do modal de adicionar produto
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        
        // Preparar para envio
        
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
        
        // Adicionar company_id se usuário for super_admin
        @if(Auth::check() && Auth::user()->role === 'super_admin')
        let companyId = $('#company_id').val();
        if (companyId) {
            formData.company_id = companyId;
        }
        @endif
        
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
                    
                    // Mostrar mensagem de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Produto criado com sucesso!',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao criar produto. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao criar produto. Verifique os dados e tente novamente.',
                    confirmButtonText: 'OK'
                });
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
        $('#saveProductBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Produto');
    });
    
    // Funcionalidade do modal de adicionar categoria
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        // Preparar para envio
        
        // Desabilitar botão de salvar
        $('#saveCategoryBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Salvando...');
        
        // Preparar dados do formulário
        let formData = {
            name: $('#category_name').val(),
            description: $('#category_description').val(),
            parent_id: $('#category_parent_id').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // Adicionar company_id se usuário for super_admin
        @if(auth()->user()->role === 'super_admin')
        let companyId = $('#company_id').val();
        if (companyId) {
            formData.company_id = companyId;
        }
        @endif
        
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
                    
                    // Mostrar mensagem de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Categoria criada com sucesso!',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao criar categoria. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao criar categoria. Verifique os dados e tente novamente.',
                    confirmButtonText: 'OK'
                });
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
    
    // Definir data de validade inicial se já houver data de emissão
    $(document).ready(function() {
        const initialIssueDate = $('#issue_date').val();
        if (initialIssueDate) {
            $('#issue_date').trigger('change');
        }
    });
    
    @if(auth()->guard('web')->user()->role === 'super_admin')
    // Carregamento dinâmico de categorias baseado na empresa selecionada
    function loadCategoriesByCompany(companyId) {
        if (!companyId) {
            // Se nenhuma empresa selecionada, limpar os selects de categoria
            $('#modal_category_id').html('<option value="">Selecione uma categoria</option>');
            $('#category_parent_id').html('<option value="">Categoria Principal</option>');
            return;
        }
        
        // Carregar categorias para o modal de produto
        $.get({
            url: '{{ route("categories.by-company") }}',
            data: { company_id: companyId },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let productCategoryOptions = '<option value="">Selecione uma categoria</option>';
                    let parentCategoryOptions = '<option value="">Categoria Principal</option>';
                    
                    response.categories.forEach(function(category) {
                        productCategoryOptions += `<option value="${category.id}">${category.name_with_indent}</option>`;
                        parentCategoryOptions += `<option value="${category.id}">${category.name_with_indent}</option>`;
                    });
                    
                    $('#modal_category_id').html(productCategoryOptions);
                    $('#category_parent_id').html(parentCategoryOptions);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar categorias:', error);
            }
        });
    }
    
    // Monitorar mudanças no select de empresa
    $('#company_id').on('change', function() {
        const companyId = $(this).val();
        loadCategoriesByCompany(companyId);
    });
    
    // Carregamento dinâmico de produtos baseado na empresa selecionada
    function loadProductsByCompany(companyId) {
        if (!companyId) {
            // Se nenhuma empresa selecionada, limpar os selects de produto
            const emptyOptions = '<option value="">Selecione um produto</option>';
            updateProductSelects(emptyOptions);
            return;
        }
        
        $.get({
            url: '{{ route("products.by-company") }}',
            data: { company_id: companyId },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                let productOptions = '<option value="">Selecione um produto</option>';
                
                response.forEach(function(product) {
                    productOptions += `<option value="${product.id}" data-price="${product.price}" data-description="${product.description}">${product.name} - ${product.category_name}</option>`;
                });
                
                updateProductSelects(productOptions);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar produtos:', error);
            }
        });
    }
    
    // Função para atualizar os selects de produto existentes e futuros
    function updateProductSelects(productOptions) {
        // Atualizar todos os selects de produto existentes
        $('.product-select').html(productOptions);
        
        // Armazenar as opções para uso em novos produtos
        window.currentProductOptions = productOptions;
    }
    
    // Carregamento dinâmico de clientes baseado na empresa selecionada
    function loadClientsByCompany(companyId) {
        if (!companyId) {
            // Se nenhuma empresa selecionada, limpar o select de cliente
            $('#client_id').html('<option value="">Selecione um cliente</option>');
            return;
        }
        
        $.get({
            url: '{{ route("clients.by-company") }}',
            data: { company_id: companyId },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                let clientOptions = '<option value="">Selecione um cliente</option>';
                
                response.forEach(function(client) {
                    clientOptions += `<option value="${client.id}">${client.display_name}</option>`;
                });
                
                $('#client_id').html(clientOptions);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar clientes:', error);
            }
        });
    }
    
    // Monitorar mudanças no select de empresa para carregar produtos e clientes
    $('#company_id').on('change', function() {
        const companyId = $(this).val();
        loadCategoriesByCompany(companyId);
        loadProductsByCompany(companyId);
        loadClientsByCompany(companyId);
    });
    
    // Carregar dados inicialmente se já houver empresa selecionada
    const initialCompanyId = $('#company_id').val();
    if (initialCompanyId) {
        loadCategoriesByCompany(initialCompanyId);
        loadProductsByCompany(initialCompanyId);
        loadClientsByCompany(initialCompanyId);
    }
      @endif
});
</script>
@endpush
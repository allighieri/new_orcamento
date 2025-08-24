@extends('layouts.app')

@section('content')
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Editar Orçamento {{ $budget->number }}</h5>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            
               
                <div class="card-body">
                    <form method="POST" action="{{ route('budgets.update', $budget) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            @if(auth()->user()->role === 'super_admin')
                                <div class="col-md-4">
                            @else
                                <div class="col-md-8">
                            @endif
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Cliente @if(Auth::check() && Auth::user()->role === 'super_admin')*@endif</label>
                                    <select class="form-select" 
                                            id="client_id" name="client_id" required>
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" 
                                                {{ old('client_id', $budget->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->corporate_name ?? $client->fantasy_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if(auth()->user()->role === 'super_admin')
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa*</label>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <!-- Campo desabilitado para super_admin -->
                                        <select class="form-select" id="company_id" disabled required>
                                            <option value="">Selecione uma empresa</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $budget->company_id) == $company->id ? 'selected' : '' }}>
                                                    {{ $company->fantasy_name ?? $company->corporate_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <!-- Campo hidden para enviar o valor no formulário -->
                                        <input type="hidden" name="company_id" value="{{ old('company_id', $budget->company_id) }}">
                                    @else
                                        <!-- Campo normal para outros usuários -->
                                        <select class="form-select" id="company_id" name="company_id" required>
                                            <option value="">Selecione uma empresa</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $budget->company_id) == $company->id ? 'selected' : '' }}>
                                                    {{ $company->fantasy_name ?? $company->corporate_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif

                                </div>
                            </div>
                            @endif
                            
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="issue_date" class="form-label">Data*</label>
                                    <input type="date" class="form-control" 
                                        id="issue_date" name="issue_date" 
                                        value="{{ old('issue_date', $budget->issue_date ? $budget->issue_date->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="valid_until" class="form-label">Validade*</label>
                                    <input type="date" class="form-control" 
                                        id="valid_until" name="valid_until" 
                                        value="{{ old('valid_until', $budget->valid_until ? $budget->valid_until->format('Y-m-d') : '') }}" required>
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
                                <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations', $budget->observations) }}</textarea>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8 d-flex justify-content-end">
                                     <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="total_discount" class="form-label">Desconto</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" placeholder="0,00" class="form-control money" id="total_discount" name="total_discount" value="{{ old('total_discount', number_format($budget->total_discount, 2, ',', '.')) }}">
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
                                        @if(auth()->guard('web')->user()->role !== 'super_admin')
                                            @php
                                                $categoriesTree = App\Models\Category::getTreeForSelect(null, null, false);
                                            @endphp
                                            @foreach($categoriesTree as $categoryId => $categoryName)
                                                <option value="{{ $categoryId }}">
                                                    {!! $categoryName !!}
                                                </option>
                                            @endforeach
                                        @endif
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
                                    @if(auth()->guard('web')->user()->role !== 'super_admin')
                                        @php
                                            $showCompanyName = auth()->guard('web')->user()->role === 'super_admin';
                                            $categoriesTree = App\Models\Category::getTreeForSelect(null, null, $showCompanyName);
                                        @endphp
                                        @foreach($categoriesTree as $categoryId => $categoryName)
                                            <option value="{{ $categoryId }}">
                                                {!! $categoryName !!}
                                            </option>
                                        @endforeach
                                    @endif
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
    // Armazena os dados do orçamento em uma variável JavaScript, se existirem
    let existingQuoteData = {!! json_encode($budget ?? null) !!};

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
    
    // Função para atualizar visibilidade dos botões de adicionar
    function updateAddButtonVisibility() {
        $('.add-product').hide();
        $('#productsContainer .product-row:last .add-product').show();
    }
    
    // Função para calcular total de um item
    function calculateItemTotal(row) {
        const quantity = parseFloat(row.find('.quantity-input').val().replace(',', '.')) || 0;
        const unitPrice = parseFloat(row.find('.unit-price-input').val().replace(/\./g, '').replace(',', '.')) || 0;
        const total = quantity * unitPrice;
        
        row.find('.total-input').val(total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        calculateTotals();
    }
    
    // Função para calcular totais gerais
    function calculateTotals() {
        let subtotal = 0;
        
        $('.product-row').each(function() {
            const total = parseFloat($(this).find('.total-input').val().replace(/\./g, '').replace(',', '.')) || 0;
            subtotal += total;
        });
        
        const discount = parseFloat($('#total_discount').val().replace(/\./g, '').replace(',', '.')) || 0;
        const finalTotal = subtotal - discount;
        
        $('#subtotal').text(subtotal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        $('#discountDisplay').text('R$ ' + discount.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        $('#total').text(finalTotal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }
    
    // Adicionar produto
    $(document).on('click', '.add-product', function() {
        const currentRow = $(this).closest('.product-row');
        const newIndex = $('.product-row').length;
        
        // Template da nova linha
        const newRowHtml = `
            <div class="row product-row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Produto*</label>
                    <div class="input-group">
                        <select class="form-select product-select" name="items[${newIndex}][product_id]" required>
                            <option value="">Selecione um produto</option>
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
                    <input type="text" class="form-control quantity-input" name="items[${newIndex}][quantity]" value="1" required min="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pç. Unit.</label>
                    <div class="input-group">
                        <div class="input-group-text">R$</div>
                        <input type="text" class="form-control money unit-price-input" name="items[${newIndex}][unit_price]" value="0,00" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <div class="input-group">
                        <div class="input-group-text">R$</div>
                        <input type="text" class="form-control total-input" placeholder="0,00" readonly value="0,00">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Adicionar nova linha após a atual
        currentRow.after(newRowHtml);
        
        // Copiar opções de produto para o novo select (sem valor selecionado)
        const newRow = currentRow.next();
        const originalSelect = currentRow.find('.product-select');
        const newSelect = newRow.find('.product-select');
        
        // Copiar apenas as opções, não o valor selecionado
        originalSelect.find('option').each(function() {
            const option = $(this).clone();
            option.removeAttr('selected'); // Remove seleção
            newSelect.append(option);
        });
        
        // Garantir que o primeiro option ("Selecione um produto") esteja selecionado
        newSelect.val('');
        
        // Limpar campos de descrição, quantidade e preço
        newRow.find('textarea[name*="[description]"]').val('');
        newRow.find('.quantity-input').val('1');
        newRow.find('.unit-price-input').val('');
        newRow.find('.total-input').val('');
        
        // Aplicar máscara nos novos campos monetários
        setTimeout(function() {
            newRow.find('.money').mask('000.000.000.000.000,00', {
                reverse: true,
                placeholder: '0,00'
            });
        }, 10);
        
        updateAddButtonVisibility();
        calculateTotals();
    });
    
    // Remover produto
     $(document).on('click', '.remove-product-btn', function() {
         const rowToRemove = $(this).closest('.product-row');
         
         // Não permitir remover se for a única linha
         if ($('.product-row').length > 1) {
             rowToRemove.remove();
             updateAddButtonVisibility();
             calculateTotals();
         }
     });
     
     // Event listeners para cálculos automáticos
     $(document).on('input', '.quantity-input, .unit-price-input', function() {
         calculateItemTotal($(this).closest('.product-row'));
     });
     
     $(document).on('change', '.product-select', function() {
         const selectedOption = $(this).find('option:selected');
         const price = selectedOption.data('price');
         const description = selectedOption.data('description');
         const row = $(this).closest('.product-row');
         
         if (price) {
             const formattedPrice = parseFloat(price).toLocaleString('pt-BR', {
                 minimumFractionDigits: 2,
                 maximumFractionDigits: 2
             });
             row.find('.unit-price-input').val(formattedPrice);
         }
         
         if (description) {
             row.find('textarea[name*="[description]"]').val(description);
         }
         
         calculateItemTotal(row);
     });
     
     $(document).on('input', '#total_discount', function() {
         calculateTotals();
     });
    
    // Funções de Modal
    // Mantenha todas as suas funções de modal de produto e categoria sem alterações aqui
    // ... $('#addProductForm').on('submit'), $('#addCategoryForm').on('submit') ...
    
    // NOVO: Função centralizada para popular as linhas de produtos
    function populateProductRows(productsFromApi, existingItems) {
        // Remove todas as linhas de produto existentes, exceto a primeira, se houver
        $('.product-row:not(:first)').remove();
        
        // Se não houver itens existentes, apenas inicializa a primeira linha e sai
        if (!existingItems || existingItems.length === 0) {
            $('.product-row:first').find('.product-select').html('<option value="">Selecione um produto</option>');
            // Adiciona as opções da API para a primeira linha
            productsFromApi.forEach(function(product) {
                const newOption = `<option value="${product.id}" data-price="${product.price}" data-description="${product.description}">${product.name} - ${product.category_name}</option>`;
                $('.product-row:first').find('.product-select').append(newOption);
            });
            updateAddButtonVisibility();
            calculateTotals();
            return;
        }

        // Caso de edição: itera sobre os itens do orçamento existentes
        existingItems.forEach(function(item, index) {
            let productRow;

            // Para o primeiro item, use a primeira linha existente. Para os demais, adicione uma nova.
            if (index === 0) {
                productRow = $('.product-row:first');
            } else {
                productRow = $('.product-row:last');
                // Adiciona uma nova linha para o próximo item
                $('.add-product', productRow).click();
                productRow = $('.product-row:last'); // Atualiza a referência para a nova linha
            }

            // Adiciona todas as opções da API ao select da linha atual
            let productOptions = '<option value="">Selecione um produto</option>';
            productsFromApi.forEach(function(product) {
                productOptions += `<option value="${product.id}" data-price="${product.price}" data-description="${product.description}">${product.name} - ${product.category_name}</option>`;
            });
            productRow.find('.product-select').html(productOptions);

            // Preenche os campos com os dados do item
            productRow.find('.product-select').val(item.product_id);
            productRow.find('.quantity-input').val(item.quantity);
            
            // Ajusta o preço unitário para o formato brasileiro
            const unitPriceFormatted = parseFloat(item.unit_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            productRow.find('.unit-price-input').val(unitPriceFormatted);
            
            productRow.find('textarea[name*="[description]"]').val(item.description);
            
            // Dispara o evento de mudança para que os totais sejam recalculados
            productRow.find('.product-select').trigger('change');
        });
        
        updateAddButtonVisibility();
        calculateTotals();
    }
    
    // NOVO: Carregamento dinâmico de clientes baseado na empresa selecionada
    function loadClientsByCompany(companyId, existingClientId = null) {
        if (!companyId) {
            $('#client_id').html('<option value="">Selecione um cliente</option>');
            return;
        }
        
        $.get({
            url: '{{ route("clients.by-company") }}',
            data: { company_id: companyId },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(response) {
                let clientOptions = '<option value="">Selecione um cliente</option>';
                response.forEach(function(client) {
                    clientOptions += `<option value="${client.id}">${client.display_name}</option>`;
                });
                $('#client_id').html(clientOptions);

                // Define o cliente selecionado APÓS o carregamento das opções
                if (existingClientId) {
                    $('#client_id').val(existingClientId);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar clientes:', error);
            }
        });
    }

    // NOVO: Carregamento dinâmico de produtos
    function loadProductsByCompany(companyId, existingItems = null) {
        if (!companyId) {
            $('.product-select').html('<option value="">Selecione um produto</option>');
            return;
        }
        
        $.get({
            url: '{{ route("products.by-company") }}',
            data: { company_id: companyId },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(response) {
                // Passa os produtos da API e os itens do orçamento para a função que preenche
                populateProductRows(response, existingItems);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar produtos:', error);
            }
        });
    }

    // Carregamento dinâmico de categorias
    function loadCategoriesByCompany(companyId) {
        if (!companyId) {
            $('#modal_category_id').html('<option value="">Selecione uma categoria</option>');
            $('#category_parent_id').html('<option value="">Categoria Principal</option>');
            return;
        }
        
        $.get({
            url: '{{ route("categories.by-company") }}',
            data: { company_id: companyId },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
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
    
    // Função para obter o ID da empresa (considerando campo desabilitado para super_admin)
    function getCompanyId() {
        @if(auth()->guard('web')->user()->role === 'super_admin')
            return $('input[name="company_id"]').val(); // Campo hidden para super_admin
        @else
            return $('#company_id').val(); // Campo select normal
        @endif
    }
    
    // Monitorar mudanças no select de empresa para carregar dados (apenas para não super_admin)
    @if(auth()->guard('web')->user()->role !== 'super_admin')
    $('#company_id').on('change', function() {
        const companyId = $(this).val();
        loadProductsByCompany(companyId);
        loadClientsByCompany(companyId);
    });
    @endif
    
    // Para super_admin, monitorar mudanças na empresa para carregar categorias nos modais
    @if(auth()->guard('web')->user()->role === 'super_admin')
    // Carregar categorias e produtos quando a página carrega
    const initialCompanyId = getCompanyId();
    if (initialCompanyId) {
        loadCategoriesByCompany(initialCompanyId);
        // Também carregar produtos para os modais
        loadProductsByCompany(initialCompanyId, existingQuoteData ? existingQuoteData.items : null);
    }
    @else
    // Lógica inicial para preencher os campos na página de edição (não super_admin)
    const initialCompanyId = getCompanyId();
    @endif
    if (initialCompanyId && existingQuoteData) {
        // Garante que o cliente e os produtos sejam carregados e preenchidos
        loadClientsByCompany(initialCompanyId, existingQuoteData.client_id);
        loadProductsByCompany(initialCompanyId, existingQuoteData.items);
        
        // Preenche o campo de desconto inicial
        $('#total_discount').val(parseFloat(existingQuoteData.total_discount).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        // Garante que o preço unitário e a quantidade da primeira linha estejam corretos
        if (existingQuoteData.items && existingQuoteData.items.length > 0) {
            let firstItem = existingQuoteData.items[0];
            $('.product-row:first').find('.quantity-input').val(firstItem.quantity);
            const unitPriceFormatted = parseFloat(firstItem.unit_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            $('.product-row:first').find('.unit-price-input').val(unitPriceFormatted);
            $('.product-row:first').find('textarea[name*="[description]"]').val(firstItem.description);
        }
    } else {
        // Se não houver dados, apenas inicializa
        updateAddButtonVisibility();
        calculateTotals();
    }
    
    // Inicialização da máscara para o campo de preço do modal
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

    // Script do modal de adicionar produto
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        
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
        @if(auth()->guard('web')->user()->role === 'super_admin')
        let companyId = getCompanyId();
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
                } else {
                    alert('Erro ao criar produto. Tente novamente.');
                }
            },
            error: function(xhr) {
                alert('Erro ao criar produto. Verifique os dados e tente novamente.');
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
    
    // Abrir modal de categoria
    $('#openCategoryModalBtn').on('click', function() {
        $('#addCategoryModal').modal('show');
    });
    
    // Script do modal de adicionar categoria
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
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
        @if(auth()->guard('web')->user()->role === 'super_admin')
        let companyId = getCompanyId();
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
                    let parentOption = new Option(response.category.name, response.category.id, false, false);
                    parentSelect.append(parentOption);
                    
                    // Fechar modal e limpar formulário
                    $('#addCategoryModal').modal('hide');
                    $('#addCategoryForm')[0].reset();
                } else {
                    alert('Erro ao criar categoria. Tente novamente.');
                }
            },
            error: function(xhr) {
                alert('Erro ao criar categoria. Verifique os dados e tente novamente.');
            },
            complete: function() {
                // Reabilitar botão de salvar
                $('#saveCategoryBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Categoria');
            }
        });
    });

    // Limpar formulário quando modal de categoria for fechado
    $('#addCategoryModal').on('hidden.bs.modal', function() {
        $('#addCategoryForm')[0].reset();
        $('#saveCategoryBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Categoria');
    });

    // Calcula os totais iniciais no carregamento da página
    $('.product-row').each(function() {
        calculateItemTotal($(this));
    });

    updateAddButtonVisibility();
    calculateTotals();
});
</script>
@endpush
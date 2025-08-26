@extends('layouts.app')

@section('title', 'Novo Orçamento')

@section('content')


    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Novo Orçamento</h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
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
                                <select class="form-select" id="company_id" name="company_id" required>
                                    <option value="">Selecione uma empresa</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->fantasy_name ?? $company->corporate_name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                <select class="form-select" id="client_id" name="client_id" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->fantasy_name ?? $client->corporate_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                        <div class="mb-3">
                            <label for="issue_date" class="form-label">Data*</label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                        </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label">Validade*</label>
                                <input type="date" class="form-control" 
                    id="valid_until" name="valid_until" 
                    value="{{ old('valid_until') }}" required>
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
                                                                <select class="form-select product-select" name="products[{{ $index }}][product_id]" required>
                                                                <option value="">Selecione um produto</option>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}" {{ old('products.'.$index.'.product_id') == $product->id ? 'selected' : '' }}>
                                                                        {{ $product->name }}
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
                                                            <textarea class="form-control" name="products[{{ $index }}][description]" rows="1">{{ old('products.'.$index.'.description') }}</textarea>
                                                        </div>
                                                        
                                                        <div class="col-md-1">
                                                            <label class="form-label">Qtde*</label>
                                                            <input type="number" class="form-control quantity-input" name="products[{{ $index }}][quantity]" value="{{ old('products.'.$index.'.quantity', 1) }}" required min="1" step="1">
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
                                                            <select class="form-select product-select" name="products[0][product_id]" required>
                                                                <option value="">Selecione um produto</option>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}">
                                                                        {{ $product->name }}
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
                                                        <input type="number" class="form-control quantity-input" name="products[0][quantity]" value="1" required min="1" step="1">
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
                                    <textarea class="form-control" id="observations" name="observations" rows="3" maxlength="1000">{{ old('observations') }}</textarea>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-8 d-flex justify-content-end">
                                         <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="total_discount" class="form-label">Desconto</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" class="form-control money" id="total_discount" name="total_discount" value="{{ old('total_discount') ? number_format(old('total_discount'), 2, ',', '.') : '' }}" min="0">
                                                </div>
                                                <div class="input-group mt-2">
                                                    <div class="input-group-text">&nbsp;%</div>
                                                    <input type="text" class="form-control perc" placeholder="0" id="total_discount_perc" name="total_discount_perc" value="{{ old('total_discount_perc') }}" min="0" max="100" step="0.01">
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

                        <!-- Seção de Métodos de Pagamento -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Métodos de Pagamento</h5>
                            </div>
                            <div class="card-body">
                                <!-- Radio buttons para controlar exibição dos métodos de pagamento -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Incluir forma de pagamento no orçamento?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="include_payment_methods" id="include_payment_no" value="no" checked>
                                        <label class="form-check-label" for="include_payment_no">
                                            Não
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="include_payment_methods" id="include_payment_yes" value="yes">
                                        <label class="form-check-label" for="include_payment_yes">
                                            Sim
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="payment-methods-container" style="display: none;">
                                    <div class="payment-method-row mb-3">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Método de Pagamento</label>
                                                <select class="form-select" name="payment_methods[0][payment_method_id]">
                                                    <option value="">Selecione um método</option>
                                                    @foreach($paymentMethods as $method)
                                                        <option value="{{ $method->id }}" data-allows-installments="{{ $method->allows_installments ? 'true' : 'false' }}">{{ $method->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 payment-amount-field" style="display: none;">
                                                <label class="form-label">Valor</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">R$</span>
                                                    <input type="text" class="form-control money" name="payment_methods[0][amount]" placeholder="0,00">
                                                </div>
                                            </div>
                                            <div class="col-md-2 payment-installments-field" style="display: none;">
                                                <label class="form-label">Parcelas</label>
                                                <input type="number" class="form-control" name="payment_methods[0][installments]" value="1" min="1">
                                            </div>
                                            <div class="col-md-2 payment-moment-field" style="display: none;">
                                                <label class="form-label">Momento</label>
                                                <select class="form-select" name="payment_methods[0][payment_moment]">
                                                    <option value="approval">Na Aprovação</option>
                                                    <option value="pickup">Na Retirada</option>
                                                    <option value="custom">Data Personalizada</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 custom-date-field" style="display: none;">
                                                <label class="form-label">Data Personalizada</label>
                                                <input type="date" class="form-control" name="payment_methods[0][custom_date]">
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-success btn-sm add-payment-method">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-11">
                                                <label class="form-label">Observações</label>
                                                <input type="text" class="form-control" name="payment_methods[0][notes]" placeholder="Observações sobre este pagamento">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <!-- Campo de Valor Restante -->
                        <div class="card mt-4" id="remainingAmountCard" style="border-left: 4px solid #28a745; display: none;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1"><i class="bi bi-calculator"></i> Valor Restante</h5>
                                                <small class="text-muted">Valor que ainda precisa ser pago</small>
                                            </div>
                                            <div class="text-end">
                                                <h3 class="mb-0" id="remainingAmount">R$ 0,00</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo de Valor Restante -->
                    

                        <hr class="my-4" />

                         <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="{{ route('budgets.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar
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

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_price" class="form-label">Preço *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control money" id="modal_price" name="price" placeholder="0,00" required>
                                </div>
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
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="modal_description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="modal_description" name="description" rows="3" placeholder="Descrição detalhada do produto"></textarea>
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
                                <div class="form-text">Deixe em branco para criar uma categoria principal. Subcategorias são indicadas por indentação.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="category_description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="category_description" name="description" rows="3" placeholder="Descreva a categoria..."></textarea>
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
                <input type="number" class="form-control quantity-input" name="products[INDEX][quantity]" value="1" required min="1" step="1">
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
    // --- Funções Auxiliares ---

    // Remove formatação de moeda e converte para float (ex: "1.234,56" -> 1234.56)
    function parseMoney(value) {
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Formata um número para o padrão monetário brasileiro (ex: 1234.56 -> "1.234,56")
    function formatMoney(value) {
        return (parseFloat(value) || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // --- Gerenciamento de Produtos e Totais ---

    // Definir o índice inicial para o próximo produto a ser adicionado
    let productIndex = $('#productsContainer .product-row').length;

    // Recalcula o total de uma linha específica
    function calculateRowTotal(row) {
        let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        let unitPrice = parseMoney(row.find('.unit-price-input').val());
        let total = quantity * unitPrice;
        
        row.find('.total-price').val(formatMoney(total));
        
        updateTotals();
    }

    // Calcula o subtotal de todos os produtos
    function calculateSubtotal() {
        let subtotal = 0;
        
        $('.product-row').each(function() {
            let quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            let unitPrice = parseMoney($(this).find('.unit-price-input').val());
            subtotal += quantity * unitPrice;
        });
        
        return subtotal;
    }

    // Atualiza os totais globais (subtotal, desconto e total final)
    function updateTotals() {
        let subtotal = calculateSubtotal();
        let discount = parseMoney($('#total_discount').val());
        
        // Recalcular desconto se o campo de porcentagem estiver preenchido
        let percentage = parseFloat($('#total_discount_perc').val()) || 0;
        if (percentage > 0 && $('#total_discount').val() === '') {
            discount = (subtotal * percentage) / 100;
            $('#total_discount').val(formatMoney(discount));
        } else if (percentage === 0) {
            // Se a porcentagem for zerada, garante que o valor também seja zerado
            discount = parseMoney($('#total_discount').val());
        }

        let total = subtotal - discount;
        
        $('#subtotal').text('R$ ' + formatMoney(subtotal));
        $('#discountDisplay').text('R$ ' + formatMoney(discount));
        $('#total').text('R$ ' + formatMoney(total));
        
        // Atualizar valor restante
        updateRemainingAmount(total);
    }
    
    // Calcula o total dos métodos de pagamento
    function calculatePaymentMethodsTotal() {
        let paymentTotal = 0;
        $('.payment-method-row input[name*="[amount]"]').each(function() {
            let amount = parseMoney($(this).val());
            paymentTotal += amount;
        });
        return paymentTotal;
    }
    
    // Função para atualizar valor restante
    function updateRemainingAmount() {
        const budgetTotal = calculateSubtotal() - parseMoney($('#total_discount').val());
        const paymentTotal = calculatePaymentMethodsTotal();
        const remaining = budgetTotal - paymentTotal;
        
        $('#remainingAmount').text('R$ ' + formatMoney(remaining));
        
        // Alterar cor baseado no valor
        const remainingElement = $('#remainingAmount');
        const cardElement = $('#remainingAmountCard');
        
        if (remaining < 0) {
            remainingElement.css('color', '#dc3545'); // Vermelho
            cardElement.css('border-left-color', '#dc3545');
        } else if (remaining === 0) {
            remainingElement.css('color', '#28a745'); // Verde
            cardElement.css('border-left-color', '#28a745');
        } else {
            remainingElement.css('color', '#ffc107'); // Amarelo
            cardElement.css('border-left-color', '#ffc107');
        }
    }

    // Função para mostrar apenas o botão '+' da última linha de produto
    function updateAddButtons() {
        $('.add-product').hide();
        $('#productsContainer .product-row:last .add-product').show();
    }

    // --- Iniciação do Formulário ---

    // Se a página for recarregada com produtos, recalcula os totais
    if (productIndex > 0) {
        $('#productsContainer .product-row').each(function() {
            calculateRowTotal($(this));
        });
        updateTotals();
    } else {
        // Se não houver produtos, adiciona a primeira linha
        let template = $('#productRowTemplate').html();
        template = template.replace(/INDEX/g, productIndex);
        $('#productsContainer').append(template);
        
        if (window.currentProductOptions) {
            $('#productsContainer .product-row:last .product-select').html(window.currentProductOptions);
        }
        
        productIndex++;
    }

    // Aplica máscara nos campos monetários (tanto os existentes quanto os novos)
    function applyMasks() {
        $('.money').mask('000.000.000.000.000,00', {
            reverse: true,
            placeholder: '0,00'
        });
    }
    applyMasks();
    updateAddButtons();

    // --- Eventos de Produto ---

    // Adicionar produto
    $(document).on('click', '.add-product', function() {
        let template = $('#productRowTemplate').html();
        template = template.replace(/INDEX/g, productIndex);
        let currentRow = $(this).closest('.product-row');
        
        currentRow.after(template);
        
        if (window.currentProductOptions) {
            currentRow.next().find('.product-select').html(window.currentProductOptions);
        }
        
        // Aplica máscaras nos novos campos
        applyMasks();
        updateAddButtons();
        productIndex++;
        updateTotals();
    });
    
    // Remover produto
    $(document).on('click', '.remove-product', function() {
        let rowToRemove = $(this).closest('.product-row');
        
        rowToRemove.remove();
        
        if ($('#productsContainer .product-row').length === 0) {
            // Se não sobrou nenhuma linha, adiciona uma nova
            let template = $('#productRowTemplate').html();
            template = template.replace(/INDEX/g, 0);
            $('#productsContainer').append(template);
            
            if (window.currentProductOptions) {
                $('#productsContainer .product-row:last .product-select').html(window.currentProductOptions);
            }
            
            applyMasks();
            productIndex = 1;
        } else {
            // Se uma linha foi removida, reindexa as restantes
            $('#productsContainer .product-row').each(function(index) {
                $(this).find('input, select, textarea').each(function() {
                    // Regex para substituir o índice no atributo 'name'
                    const newName = $(this).attr('name').replace(/\[\d+\]/g, `[${index}]`);
                    $(this).attr('name', newName);
                });
            });
            productIndex = $('#productsContainer .product-row').length;
        }

        updateAddButtons();
        updateTotals();
    });

    // Preencher preço e descrição ao selecionar um produto
    $(document).on('change', '.product-select', function() {
        let price = $(this).find(':selected').data('price');
        let description = $(this).find(':selected').data('description');
        let row = $(this).closest('.product-row');
        
        if (price) {
            row.find('.unit-price-input').val(formatMoney(price));
        }
        
        if (description) {
            row.find('textarea[name*="[description]"]').val(description);
        } else {
            row.find('textarea[name*="[description]"]').val('');
        }

        calculateRowTotal(row);
    });
    
    // Recalcular total da linha quando quantidade ou preço unitário mudam
    $(document).on('input', '.quantity-input, .unit-price-input', function() {
        calculateRowTotal($(this).closest('.product-row'));
    });
    
    // Recalcular totais quando o desconto muda
    $('#total_discount').on('input', function() {
        $('#total_discount_perc').val('');
        updateTotals();
    });
    
    // Recalcular desconto em reais quando a porcentagem muda
    $('#total_discount_perc').on('input', function() {
        $('#total_discount').val('');
        updateTotals();
    });
    
    // Monitorar mudanças nos valores dos métodos de pagamento
    $(document).on('input', 'input[name*="[amount]"]', function() {
        const currentValue = parseMoney($(this).val());
        const budgetTotal = calculateSubtotal() - parseMoney($('#total_discount').val());
        const otherPaymentsTotal = calculatePaymentMethodsTotal() - currentValue;
        const maxAllowed = budgetTotal - otherPaymentsTotal;
        
        if (currentValue > maxAllowed && maxAllowed >= 0) {
            // Valor ultrapassa o permitido
            Swal.fire({
                icon: 'warning',
                title: 'Valor Excedido!',
                text: `O valor máximo permitido para este pagamento é R$ ${formatMoney(maxAllowed)}.`,
                confirmButtonText: 'OK'
            });
            
            // Define o valor máximo permitido
            $(this).val(formatMoney(maxAllowed));
        }
        
        updateRemainingAmount();
    });

    // --- Lógica de Modais (Produto e Categoria) ---
    
    // Submissão do formulário do modal de produto
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#saveProductBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Salvando...');
        
        let formData = {
            name: $('#modal_name').val(),
            price: parseMoney($('#modal_price').val()),
            description: $('#modal_description').val(),
            category_id: $('#modal_category_id').val(),
            _token: $('input[name="_token"]').val()
        };
        
        @if(Auth::check() && Auth::user()->role === 'super_admin')
        let companyId = $('#company_id').val();
        if (companyId) {
            formData.company_id = companyId;
        }
        @endif
        
        $.ajax({
            url: '{{ route("products.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Adiciona a nova opção a todos os selects de produto e a armazena
                    let newOption = `<option value="${response.product.id}" data-price="${response.product.price}" data-description="${response.product.description}">
                        ${response.product.name} - ${response.product.category_name || 'Sem categoria'}
                    </option>`;
                    
                    $('.product-select').append(newOption);
                    
                    // Seleciona o novo produto na última linha
                    $('.product-row:last .product-select').val(response.product.id).trigger('change');
                    
                    $('#addProductModal').modal('hide');
                    showSuccessToast('Produto criado com sucesso!');
                } else {
                    showErrorAlert('Erro ao criar produto. Tente novamente.');
                }
            },
            error: function(xhr) {
                showErrorAlert('Erro ao criar produto. Verifique os dados e tente novamente.');
            },
            complete: function() {
                $('#saveProductBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Produto');
                $('#addProductForm')[0].reset();
            }
        });
    });

    // Submissão do formulário do modal de categoria
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#saveCategoryBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Salvando...');
        
        let formData = {
            name: $('#category_name').val(),
            description: $('#category_description').val(),
            parent_id: $('#category_parent_id').val(),
            _token: $('input[name="_token"]').val()
        };
        
        @if(auth()->user()->role === 'super_admin')
        let companyId = $('#company_id').val();
        if (companyId) {
            formData.company_id = companyId;
        }
        @endif
        
        $.ajax({
            url: '{{ route("categories.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    let newOption = new Option(response.category.name, response.category.id, true, true);
                    $('#modal_category_id').append(newOption);
                    $('#category_parent_id').append(new Option(response.category.name, response.category.id));
                    
                    $('#addCategoryModal').modal('hide');
                    showSuccessToast('Categoria criada com sucesso!');
                } else {
                    showErrorAlert('Erro ao criar categoria. Tente novamente.');
                }
            },
            error: function(xhr) {
                showErrorAlert('Erro ao criar categoria. Verifique os dados e tente novamente.');
            },
            complete: function() {
                $('#saveCategoryBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Categoria');
                $('#addCategoryForm')[0].reset();
            }
        });
    });

    // Limpar formulários ao fechar os modais
    $('#addProductModal').on('hidden.bs.modal', function() {
        $('#addProductForm')[0].reset();
        $('#saveProductBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Produto');
        applyMasks();
    });
    
    $('#addCategoryModal').on('hidden.bs.modal', function() {
        $('#addCategoryForm')[0].reset();
        $('#saveCategoryBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Salvar Categoria');
    });

    // Abrir modal de categoria sem fechar o de produto
    $('#openCategoryModalBtn').on('click', function() {
        $('#addCategoryModal').modal('show');
    });
    
    // --- Lógica de Datas ---

    // Calcula automaticamente a data de validade (15 dias após a data de emissão)
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
    const initialIssueDate = $('#issue_date').val();
    if (initialIssueDate) {
        $('#issue_date').trigger('change');
    }
    
    @if(auth()->guard('web')->user()->role === 'super_admin')
    // --- Lógica de Carregamento Dinâmico (Super Admin) ---

    // Carregamento dinâmico de categorias baseado na empresa selecionada
    function loadCategoriesByCompany(companyId) {
        if (!companyId) {
            $('#modal_category_id').html('<option value="">Selecione uma categoria</option>');
            $('#category_parent_id').html('<option value="">Categoria Principal</option>');
            return;
        }
        
        $.get({
            url: '{{ route("categories.by-company") }}',
            data: { company_id: companyId },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.categories) {
                    let productCategoryOptions = '<option value="">Selecione uma categoria</option>';
                    let parentCategoryOptions = '<option value="">Categoria Principal</option>';
                    
                    $.each(response.categories, function(categoryId, categoryName) {
                        productCategoryOptions += `<option value="${categoryId}">${categoryName}</option>`;
                        parentCategoryOptions += `<option value="${categoryId}">${categoryName}</option>`;
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
    
    // Carregamento dinâmico de produtos baseado na empresa selecionada
    function loadProductsByCompany(companyId) {
        if (!companyId) {
            updateProductSelects('<option value="">Selecione um produto</option>');
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
                // Armazena as opções para novas linhas
                window.currentProductOptions = productOptions;
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar produtos:', error);
            }
        });
    }

    // Atualiza todos os selects de produto na tela
    function updateProductSelects(options) {
        $('.product-select').html(options);
    }
    
    // Carregamento dinâmico de clientes baseado na empresa selecionada
    function loadClientsByCompany(companyId) {
        if (!companyId) {
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
    
    // Monitorar mudanças no select de empresa
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

    // --- Gerenciamento de Métodos de Pagamento ---
    
    let paymentMethodIndex = $('#payment-methods-container .payment-method-row').length;
    
    // Controle de exibição dos métodos de pagamento e card de valor restante
    $('input[name="include_payment_methods"]').on('change', function() {
        if ($(this).val() === 'yes') {
            $('#payment-methods-container').show();
            $('#remainingAmountCard').show();
        } else {
            $('#payment-methods-container').hide();
            $('#remainingAmountCard').hide();
        }
    });
    
    // Controle de exibição do campo Data Personalizada
    $(document).on('change', 'select[name*="[payment_moment]"]', function() {
        const customDateField = $(this).closest('.row').find('.custom-date-field');
        if ($(this).val() === 'custom') {
            customDateField.show();
            // Preenche com a data atual
            const today = new Date().toISOString().split('T')[0];
            customDateField.find('input[type="date"]').val(today);
        } else {
            customDateField.hide();
        }
    });
    
    // Controle de exibição dos campos baseado no método de pagamento selecionado
    $(document).on('change', 'select[name*="[payment_method_id]"]', function() {
        const paymentMethodId = $(this).val();
        const currentRow = $(this).closest('.payment-method-row');
        const amountField = currentRow.find('.payment-amount-field');
        const installmentsField = currentRow.find('.payment-installments-field');
        const momentField = currentRow.find('.payment-moment-field');
        
        if (paymentMethodId) {
            // Mostrar campos Valor e Momento quando método for selecionado
            amountField.show();
            momentField.show();
            
            // Buscar informações do método de pagamento para controlar Parcelas
            const paymentMethods = @json($paymentMethods);
            const selectedMethod = paymentMethods.find(method => method.id == paymentMethodId);
            
            if (selectedMethod && selectedMethod.allows_installments) {
                installmentsField.show();
                // Definir o valor máximo de parcelas baseado no método
                const installmentsInput = installmentsField.find('input');
                installmentsInput.attr('max', selectedMethod.max_installments);
                
                // Validar se o valor atual excede o máximo permitido
                const currentValue = parseInt(installmentsInput.val()) || 1;
                if (currentValue > selectedMethod.max_installments) {
                    installmentsInput.val(selectedMethod.max_installments);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Valor Ajustado',
                        text: `O número de parcelas foi ajustado para ${selectedMethod.max_installments}x (máximo permitido para este método).`,
                        confirmButtonText: 'OK'
                    });
                }
            } else {
                installmentsField.hide();
                // Definir parcelas como 1 se não permite parcelamento
                installmentsField.find('input').val(1).attr('max', 1);
            }
        } else {
            // Se nenhum método selecionado, esconder todos os campos
            amountField.hide();
            installmentsField.hide();
            momentField.hide();
            
            // Limpar valores dos campos
            amountField.find('input').val('');
            installmentsField.find('input').val(1);
            momentField.find('select').val('approval');
        }
    });
    
    // Verificar campos no carregamento da página
    $('select[name*="[payment_method_id]"]').each(function() {
        $(this).trigger('change');
    });
    
    // Validação em tempo real do campo parcelas
    $(document).on('input change', 'input[name*="[installments]"]', function() {
        const installmentsInput = $(this);
        const maxInstallments = parseInt(installmentsInput.attr('max')) || 1;
        const currentValue = parseInt(installmentsInput.val()) || 1;
        
        if (currentValue > maxInstallments) {
            installmentsInput.val(maxInstallments);
            Swal.fire({
                icon: 'warning',
                title: 'Valor Máximo Excedido',
                text: `O número máximo de parcelas para este método é ${maxInstallments}x.`,
                confirmButtonText: 'OK'
            });
        }
        
        if (currentValue < 1) {
            installmentsInput.val(1);
        }
    });

    function addPaymentRow() {
        const template = `
            <div class="payment-method-row mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Método de Pagamento</label>
                        <select class="form-select" name="payment_methods[${paymentMethodIndex}][payment_method_id]">
                            <option value="">Selecione um método</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" data-allows-installments="{{ $method->allows_installments ? 'true' : 'false' }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 payment-amount-field" style="display: none;">
                        <label class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control money" name="payment_methods[${paymentMethodIndex}][amount]" placeholder="0,00">
                        </div>
                    </div>
                    <div class="col-md-2 payment-installments-field" style="display: none;">
                        <label class="form-label">Parcelas</label>
                        <input type="number" class="form-control" name="payment_methods[${paymentMethodIndex}][installments]" value="1" min="1">
                    </div>
                    <div class="col-md-2 payment-moment-field" style="display: none;">
                        <label class="form-label">Momento</label>
                        <select class="form-select" name="payment_methods[${paymentMethodIndex}][payment_moment]">
                            <option value="approval">Na Aprovação</option>
                            <option value="pickup">Na Retirada</option>
                            <option value="custom">Data Personalizada</option>
                        </select>
                    </div>
                    <div class="col-md-2 custom-date-field" style="display: none;">
                         <label class="form-label">Data Personalizada</label>
                         <input type="date" class="form-control" name="payment_methods[${paymentMethodIndex}][custom_date]">
                     </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-payment-method me-1">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm add-payment-method">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-11">
                        <label class="form-label">Observações</label>
                        <input type="text" class="form-control" name="payment_methods[${paymentMethodIndex}][notes]" placeholder="Observações sobre este pagamento">
                    </div>
                </div>
            </div>
        `;
        $('#payment-methods-container').append(template);
        applyMasks();
        paymentMethodIndex++;
        updatePaymentAddButtons();
    }

    // Reindexa os métodos de pagamento após uma remoção
    function reindexPaymentMethods() {
        $('#payment-methods-container .payment-method-row').each(function(index) {
            $(this).find('select, input').each(function() {
                const newName = $(this).attr('name').replace(/\[\d+\]/g, `[${index}]`);
                $(this).attr('name', newName);
            });
        });
        paymentMethodIndex = $('#payment-methods-container .payment-method-row').length;
    }
    
    // Mostra apenas o botão de adicionar na última linha de pagamento
    function updatePaymentAddButtons() {
        $('.add-payment-method').hide();
        $('#payment-methods-container .payment-method-row:last .add-payment-method').show();
    }

    // Adiciona a primeira linha de pagamento se a container estiver vazia
    if ($('#payment-methods-container .payment-method-row').length === 0) {
        addPaymentRow();
    } else {
        // Reindexa as linhas existentes ao carregar a página
        reindexPaymentMethods();
    }
    
    $(document).on('click', '.add-payment-method', function() {
        addPaymentRow();
    });

    $(document).on('click', '.remove-payment-method', function() {
        const rowCount = $('.payment-method-row').length;
        if (rowCount > 1) {
            $(this).closest('.payment-method-row').remove();
            reindexPaymentMethods();
        } else {
            // Se for a última linha, apenas limpa os campos
            $(this).closest('.payment-method-row').find('input, select').val('');
            $(this).closest('.payment-method-row').find('input[type="number"]').val('1');
        }
        updatePaymentAddButtons();
    });

    // --- Validação do Formulário ---
    
    $('#budgetForm').on('submit', function(e) {
        const includePayments = $('input[name="include_payment_methods"]:checked').val();
        
        if (includePayments === 'yes') {
            const budgetTotal = calculateSubtotal() - parseMoney($('#total_discount').val());
            const paymentTotal = calculatePaymentMethodsTotal();
            const remaining = budgetTotal - paymentTotal;
            
            if (remaining !== 0) {
                e.preventDefault();
                
                let message = '';
                if (remaining > 0) {
                    message = `Ainda falta pagar R$ ${formatMoney(remaining)}. Os métodos de pagamento devem totalizar o valor do orçamento.`;
                } else {
                    message = `Os métodos de pagamento excedem o valor do orçamento em R$ ${formatMoney(Math.abs(remaining))}.`;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Valores Inconsistentes!',
                    text: message,
                    confirmButtonText: 'OK'
                });
                
                return false;
            }
        }
    });

    // --- Funções de Notificação (opcional, requer SweetAlert2) ---

    function showSuccessToast(text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: text,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }

    function showErrorAlert(text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: text,
                confirmButtonText: 'OK'
            });
        }
    }

});
</script>
@endpush
@extends('layouts.app')

@section('content')
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Editar Orçamento {{ $budget->number }}</h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
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
                                                    @if(($item->product_id && !$products->contains('id', $item->product_id)) || (!$item->product_id && $item->produto))
                                                        <option value="{{ $item->product_id }}" selected  class="text-muted">
                                                            @if($item->produto)
                                                                {{ $item->produto }}
                                                            @else
                                                                Produto excluído
                                                            @endif
                                                        </option>
                                                    @endif
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-description="{{ $product->description }}"
                                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addProductModal" title="Adicionar novo produto">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                            @if(!$item->product_id && $item->produto)
                                                <input type="hidden" name="items[{{ $index }}][produto_name]" value="{{ $item->produto }}">
                                            @endif
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
                                     <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="total_discount" class="form-label">Desconto</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" placeholder="0,00" class="form-control money" id="total_discount" name="total_discount" value="{{ old('total_discount', number_format($budget->total_discount, 2, ',', '.')) }}">
                                                </div>
                                                <div class="input-group mt-2">
                                                    <div class="input-group-text">&nbsp;%</div>
                                                    <input type="text" class="form-control perc" placeholder="0" id="total_discount_perc" name="total_discount_perc" value="{{ old('total_discount_perc') }}">
                                                    @error('total_discount_perc')
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
                                        <input class="form-check-input" type="radio" name="include_payment_methods" id="include_payment_no" value="no" {{ $budget->budgetPayments->count() == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_payment_no">
                                            Não
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="include_payment_methods" id="include_payment_yes" value="yes" {{ $budget->budgetPayments->count() > 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_payment_yes">
                                            Sim
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="payment-methods-container" style="{{ $budget->budgetPayments->count() == 0 ? 'display: none;' : '' }}">
                                    @if($budget->budgetPayments->count() > 0)
                                        @foreach($budget->budgetPayments as $index => $payment)
                                            <div class="payment-method-row mb-3">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Método de Pagamento</label>
                                                        <select class="form-select" name="payment_methods[{{ $index }}][payment_method_id]">
                                                            <option value="">Selecione um método</option>
                                                            @foreach($paymentMethods as $method)
                                                                <option value="{{ $method->id }}" data-allows-installments="{{ $method->allows_installments ? 'true' : 'false' }}" {{ $payment->payment_method_id == $method->id ? 'selected' : '' }}>
                                                                    {{ $method->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Valor</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">R$</span>
                                                            <input type="text" class="form-control money" name="payment_methods[{{ $index }}][amount]" 
                                                                   value="{{ number_format($payment->amount, 2, ',', '.') }}" placeholder="0,00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Parcelas</label>
                                                        <input type="number" class="form-control" name="payment_methods[{{ $index }}][installments]" 
                                                               value="{{ $payment->installments }}" min="1">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Momento</label>
                                                        <select class="form-select" name="payment_methods[{{ $index }}][payment_moment]">
                                                            <option value="approval" {{ $payment->payment_moment == 'approval' ? 'selected' : '' }}>Na Aprovação</option>
                                                            <option value="pickup" {{ $payment->payment_moment == 'pickup' ? 'selected' : '' }}>Na Retirada</option>
                                                            <option value="custom" {{ $payment->payment_moment == 'custom' ? 'selected' : '' }}>Data Personalizada</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Data Personalizada</label>
                                                        <input type="date" class="form-control" name="payment_methods[{{ $index }}][custom_date]" 
                                                               value="{{ $payment->custom_date }}">
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-end">
                                                        @if($index == 0)
                                                            <button type="button" class="btn btn-success btn-sm add-payment-method">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-danger btn-sm remove-payment-method me-1">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-success btn-sm add-payment-method">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-11">
                                                        <label class="form-label">Observações</label>
                                                        <input type="text" class="form-control" name="payment_methods[{{ $index }}][notes]" 
                                                               value="{{ $payment->notes }}" placeholder="Observações sobre este pagamento">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
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
                                                <div class="col-md-2">
                                                    <label class="form-label">Valor</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">R$</span>
                                                        <input type="text" class="form-control money" name="payment_methods[0][amount]" placeholder="0,00">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Parcelas</label>
                                                    <input type="number" class="form-control" name="payment_methods[0][installments]" value="1" min="1">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Momento</label>
                                                    <select class="form-select" name="payment_methods[0][payment_moment]">
                                                        <option value="approval">Na Aprovação</option>
                                                        <option value="pickup">Na Retirada</option>
                                                        <option value="custom">Data Personalizada</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
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
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Campo de Valor Restante -->
                        <div class="card mt-4" id="remainingAmountCard" style="border-left: 4px solid #28a745;">
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

                        <hr class="my-4" />

                         <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="{{ route('budgets.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar
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
                                                $categoriesTree = App\Models\Category::getTreeForSelect(null, session('tenant_company_id'), false);
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
                                            $categoriesTree = App\Models\Category::getTreeForSelect(null, session('tenant_company_id'), $showCompanyName);
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
    
    // Função para calcular subtotal
    function calculateSubtotal() {
        let subtotal = 0;
        
        $('.product-row').each(function() {
            const total = parseFloat($(this).find('.total-input').val().replace(/\./g, '').replace(',', '.')) || 0;
            subtotal += total;
        });
        
        return subtotal;
    }
    
    // Função para calcular totais gerais
    function calculateTotals() {
        let subtotal = calculateSubtotal();
        
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
        
        // Atualizar valor restante
        updateRemainingAmount();
    }
    
    // Função para calcular total dos métodos de pagamento
    function calculatePaymentMethodsTotal() {
        let total = 0;
        $('input[name*="[amount]"]').each(function() {
            const value = parseMoney($(this).val());
            total += value;
        });
        return total;
    }
    
    // Função para converter valor monetário para número
    function parseMoney(value) {
        if (!value) return 0;
        return parseFloat(value.toString().replace(/\./g, '').replace(',', '.')) || 0;
    }
    
    // Função para formatar número como moeda
    function formatMoney(value) {
        return value.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
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
        
        // Limpar o select e copiar apenas as opções de produtos (pular a primeira opção "Selecione um produto")
        newSelect.html('<option value="">Selecione um produto</option>');
        originalSelect.find('option:not(:first)').each(function() {
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
         // Limpar campo de porcentagem quando digitar valor em reais
         $('#total_discount_perc').val('');
         calculateTotals();
     });
     
     // Função para calcular e preencher a porcentagem de desconto automaticamente
     function calculateAndFillDiscountPercentage() {
         const discountValue = parseFloat($('#total_discount').val().replace(/\./g, '').replace(',', '.')) || 0;
         
         if (discountValue > 0) {
             const subtotal = calculateSubtotal();
             
             if (subtotal > 0) {
                 const percentage = (discountValue / subtotal) * 100;
                 $('#total_discount_perc').val(percentage.toFixed(2));
             }
         }
     }
     
     // Chamar a função quando a página carregar e após calcular os totais
     $(document).ready(function() {
         // Aguardar um pouco para garantir que todos os cálculos foram feitos
         setTimeout(function() {
             calculateAndFillDiscountPercentage();
         }, 500);
     });
     
     // Também chamar após mudanças nos produtos para recalcular a porcentagem
     $(document).on('input change', '.quantity-input, .unit-price-input, .product-select', function() {
         setTimeout(function() {
             calculateAndFillDiscountPercentage();
         }, 100);
     });
     
     // Calcular desconto em reais quando porcentagem mudar
     $(document).on('input', '#total_discount_perc', function() {
         let percentage = parseFloat($(this).val()) || 0;
         if (percentage > 0) {
             let subtotal = calculateSubtotal();
             let discountAmount = (subtotal * percentage) / 100;
             
             // Atualizar campo de desconto em reais
             $('#total_discount').val(discountAmount.toLocaleString('pt-BR', {
                 minimumFractionDigits: 2,
                 maximumFractionDigits: 2
             }));
         } else {
             $('#total_discount').val('');
         }
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
                const newOption = `<option value="${product.id}" data-price="${product.price}" data-description="${product.description}">${product.name}</option>`;
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
                productOptions += `<option value="${product.id}" data-price="${product.price}" data-description="${product.description}">${product.name}</option>`;
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

    // Carregar categorias quando a modal de produto for aberta
    $('#addProductModal').on('show.bs.modal', function() {
        const companyId = getCompanyId();
        if (companyId) {
            loadCategoriesByCompany(companyId);
        }
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

    // Gerenciamento de métodos de pagamento
    let paymentMethodIndex = {{ $budget->budgetPayments->count() > 0 ? $budget->budgetPayments->count() : 1 }};

    // Adicionar novo método de pagamento
    $(document).on('click', '.add-payment-method', function() {
        const newPaymentMethod = `
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
                    <div class="col-md-2">
                        <label class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control money" name="payment_methods[${paymentMethodIndex}][amount]" placeholder="0,00">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Parcelas</label>
                        <input type="number" class="form-control" name="payment_methods[${paymentMethodIndex}][installments]" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Momento</label>
                        <select class="form-select" name="payment_methods[${paymentMethodIndex}][payment_moment]">
                            <option value="approval">Na Aprovação</option>
                            <option value="pickup">Na Retirada</option>
                            <option value="custom">Data Personalizada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
        
        $('#payment-methods-container').append(newPaymentMethod);
        
        // Aplicar máscara de dinheiro ao novo campo
        $('.money').mask('#.##0,00', {reverse: true});
        
        paymentMethodIndex++;
    });

    // Controlar exibição dos métodos de pagamento com radio buttons
    $('input[name="include_payment_methods"]').change(function() {
        if ($(this).val() === 'yes') {
            $('#payment-methods-container').show();
        } else {
            $('#payment-methods-container').hide();
        }
    });

    // Controlar exibição do campo Data Personalizada
    $(document).on('change', 'select[name*="[payment_moment]"]', function() {
        const customDateField = $(this).closest('.payment-method-row').find('input[name*="[custom_date]"]').closest('.col-md-2');
        
        if ($(this).val() === 'custom') {
            customDateField.show();
            // Preencher com a data atual se estiver vazio
            const dateInput = customDateField.find('input[name*="[custom_date]"]');
            if (!dateInput.val()) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.val(today);
            }
        } else {
            customDateField.hide();
        }
    });

    // Verificar campos de data personalizada no carregamento da página
    $('select[name*="[payment_moment]"]').each(function() {
        const customDateField = $(this).closest('.payment-method-row').find('input[name*="[custom_date]"]').closest('.col-md-2');
        
        if ($(this).val() !== 'custom') {
            customDateField.hide();
        }
    });
    
    // Controle de exibição do campo Parcelas baseado no método de pagamento
    $(document).on('change', 'select[name*="[payment_method_id]"]', function() {
        const paymentMethodId = $(this).val();
        const installmentsField = $(this).closest('.row').find('input[name*="[installments]"]').closest('.col-md-2');
        
        if (paymentMethodId) {
            // Buscar informações do método de pagamento
            const paymentMethods = @json($paymentMethods);
            const selectedMethod = paymentMethods.find(method => method.id == paymentMethodId);
            
            if (selectedMethod && selectedMethod.allows_installments) {
                installmentsField.show();
            } else {
                installmentsField.hide();
                // Definir parcelas como 1 se não permite parcelamento
                installmentsField.find('input').val(1);
            }
        } else {
            // Se nenhum método selecionado, mostrar o campo
            installmentsField.show();
        }
    });
    
    // Verificar campos de parcelas no carregamento da página
    $('select[name*="[payment_method_id]"]').each(function() {
        const paymentMethodId = $(this).val();
        const installmentsField = $(this).closest('.row').find('input[name*="[installments]"]').closest('.col-md-2');
        
        if (paymentMethodId) {
            const paymentMethods = @json($paymentMethods);
            const selectedMethod = paymentMethods.find(method => method.id == paymentMethodId);
            
            if (selectedMethod && !selectedMethod.allows_installments) {
                installmentsField.hide();
                installmentsField.find('input').val(1);
            }
        }
    });

    // Remover método de pagamento
    $(document).on('click', '.remove-payment-method', function() {
        const paymentRows = $('.payment-method-row');
        if (paymentRows.length > 1) {
            $(this).closest('.payment-method-row').remove();
            updateRemainingAmount(); // Atualizar valor restante após remoção
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Deve haver pelo menos um método de pagamento.',
                confirmButtonText: 'OK'
            });
        }
    });
    
    // Validação dos valores dos métodos de pagamento
    $(document).on('input', 'input[name*="[amount]"]', function() {
        const currentValue = parseMoney($(this).val());
        const budgetTotal = calculateSubtotal() - parseMoney($('#total_discount').val());
        
        // Calcular total dos outros métodos de pagamento (excluindo o atual)
        let otherPaymentsTotal = 0;
        $('input[name*="[amount]"]').not(this).each(function() {
            otherPaymentsTotal += parseMoney($(this).val());
        });
        
        const maxAllowed = budgetTotal - otherPaymentsTotal;
        
        if (currentValue > maxAllowed && maxAllowed >= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Valor Excedido!',
                text: `O valor máximo permitido para este pagamento é R$ ${formatMoney(maxAllowed)}`,
                confirmButtonText: 'OK'
            });
            
            // Definir o valor máximo permitido
            $(this).val(maxAllowed.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
        
        updateRemainingAmount();
    });
    
    // Validação do formulário antes da submissão
    $('#budgetForm').on('submit', function(e) {
        const includePaymentMethods = $('input[name="include_payment_methods"]:checked').val();
        
        if (includePaymentMethods === 'yes') {
            const budgetTotal = calculateSubtotal() - parseMoney($('#total_discount').val());
            const paymentTotal = calculatePaymentMethodsTotal();
            const remaining = budgetTotal - paymentTotal;
            
            if (remaining !== 0) {
                e.preventDefault();
                
                let message = '';
                if (remaining > 0) {
                    message = `Ainda falta pagar R$ ${formatMoney(remaining)}. Os métodos de pagamento devem totalizar exatamente o valor do orçamento.`;
                } else {
                    message = `Os métodos de pagamento excedem o valor do orçamento em R$ ${formatMoney(Math.abs(remaining))}. Ajuste os valores.`;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro nos Métodos de Pagamento!',
                    text: message,
                    confirmButtonText: 'OK'
                });
                
                return false;
            }
        }
    });
});
</script>
@endpush
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
                    <form method="POST" action="{{ route('budgets.update', $budget) }}" id="budgetForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            @if(auth()->user()->role === 'super_admin')
                                <div class="col-md-4">
                            @else
                                <div class="col-md-6">
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

                            <div class="col-md-2">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="form-check form-switch me-2">
                                            <input class="form-check-input" type="checkbox" id="delivery_date_enabled" name="delivery_date_enabled" value="1" {{ old('delivery_date_enabled', $budget->delivery_date_enabled ?? true) ? 'checked' : '' }}>
                                        </div>
                                        <label for="delivery_date_enabled" class="form-label mb-0">Previsão de Entrega</label>
                                    </div>
                                    <div id="delivery_date_container">
                                        <input type="date" class="form-control" 
                                            id="delivery_date" name="delivery_date" 
                                            value="{{ old('delivery_date', $budget->delivery_date ? $budget->delivery_date->format('Y-m-d') : '') }}">
                                    </div>
                                    <div id="delivery_date_text" class="text-muted" style="display: none;">A combinar</div>
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
                                            <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" required min="1" step="1">
                                        </div>

                                        <div class="col-md-2">
                            <label class="form-label" for="autoSizingInputGroup">Pç. Unit.</label>
                            <div class="input-group">
                                <div class="input-group-text">R$</div>
                                <input type="text" class="form-control money unit-price-input" name="items[{{ $index }}][unit_price]" value="{{ number_format($item->unit_price, 2, ',', '.') }}" required>
                                <button type="button" class="btn btn-outline-info calculator-btn" data-bs-toggle="modal" data-bs-target="#calculatorModal" title="Calculadora de preço unitário">
                                    <i class="bi bi-calculator"></i>
                                </button>
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
                            <div class="d-flex gap-1 align-items-end" style="height: 38px;">
                                <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto" style="height: 38px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto" style="height: 38px;">
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
                                                <input type="number" class="form-control quantity-input" name="items[0][quantity]" value="1" required step="1">
                                            </div>

                                            <div class="col-md-2">
                                <label class="form-label" for="autoSizingInputGroup">Pç. Unit.</label>
                                <div class="input-group">
                                    <div class="input-group-text">R$</div>
                                    <input type="text" class="form-control money unit-price-input" name="items[0][unit_price]" value="0,00" required>
                                    <button type="button" class="btn btn-outline-info calculator-btn" data-bs-toggle="modal" data-bs-target="#calculatorModal" title="Calculadora de preço unitário">
                                        <i class="bi bi-calculator"></i>
                                    </button>
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
                                                <div class="d-flex gap-1 align-items-end" style="height: 38px;">
                                                    <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto" style="height: 38px;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto" style="height: 38px;">
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


                            <div class="row mt-4">
                                <div class="col-md-8 d-flex justify-content-end">
                                     <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="total_discount" class="form-label">Desconto</label>
                                               
                                                <div class="input-group">
                                                    <div class="input-group-text">&nbsp;%</div>
                                                    <input type="text" class="form-control perc" placeholder="0" id="total_discount_perc" name="total_discount_perc" value="{{ old('total_discount_perc') }}">
                                                    @error('total_discount_perc')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                 <div class="input-group mt-2">
                                                    <div class="input-group-text">R$</div>
                                                    <input type="text" placeholder="0,00" class="form-control money" id="total_discount" name="total_discount" value="{{ old('total_discount', number_format($budget->total_discount, 2, ',', '.')) }}">
                                                </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 my-2">
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
                                <!-- Checkbox tipo settings para controlar exibição dos métodos de pagamento -->
                                <div class="mb-3">
                                    @php
                                        // Verificar se há métodos de pagamento disponíveis
                                        $hasAvailablePaymentMethods = $paymentMethods->count() > 0;
                                        
                                        // Se há budgetPayments salvos, deve incluir métodos de pagamento
                                        $shouldIncludePayments = $hasAvailablePaymentMethods && $budget->budgetPayments->count() > 0;
                                    @endphp
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="include_payment_methods" 
                                               name="include_payment_methods" 
                                               value="1"
                                               {{ $shouldIncludePayments ? 'checked' : '' }}
                                               {{ !$hasAvailablePaymentMethods ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold" for="include_payment_methods">
                                            <i class="bi bi-credit-card"></i> Incluir Métodos de Pagamento
                                            {{ !$hasAvailablePaymentMethods ? ' (Nenhum método disponível)' : '' }}
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Quando ativado, permite adicionar formas de pagamento ao orçamento
                                    </small>
                                </div>
                                
                                <div id="payment-methods-container" style="{{ !$shouldIncludePayments ? 'display: none;' : '' }}">
                                    @if($budget->budgetPayments->count() > 0)
                                        @foreach($budget->budgetPayments as $index => $payment)
                                            <div class="payment-method-row mb-3">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Método de Pagamento</label>
                                                        <select class="form-select" name="payment_methods[{{ $index }}][payment_method_id]">
                                                            <option value="">Selecione um método</option>
                                                            
                                                            {{-- Incluir o método atual se foi excluído --}}
                                                            @if($payment->paymentMethod && $payment->paymentMethod->trashed())
                                                                <option value="{{ $payment->paymentMethod->id }}" data-allows-installments="{{ $payment->paymentMethod->allows_installments ? 'true' : 'false' }}" selected>
                                                                    {{ $payment->paymentMethod->paymentOptionMethod->method ?? 'N/A' }} (Excluído)
                                                                </option>
                                                            @endif
                                                            
                                                            {{-- Métodos disponíveis --}}
                                                            @foreach($paymentMethods as $method)
                                                                <option value="{{ $method->id }}" data-allows-installments="{{ $method->allows_installments ? 'true' : 'false' }}" {{ $payment->payment_method_id == $method->id ? 'selected' : '' }}>
                                                                    {{ $method->paymentOptionMethod->method ?? 'N/A' }}{{ !$method->is_active ? ' (Inativo)' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 payment-amount-field">
                                                        <label class="form-label">Valor</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">R$</span>
                                                            <input type="text" class="form-control money" name="payment_methods[{{ $index }}][amount]" 
                                                                   value="{{ number_format($payment->amount, 2, ',', '.') }}" placeholder="0,00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 payment-installments-field">
                                                        <label class="form-label">Parcelas</label>
                                                        <input type="number" class="form-control" name="payment_methods[{{ $index }}][installments]" 
                                                               value="{{ $payment->installments }}" min="1" 
                                                               max="{{ $payment->paymentMethod->max_installments ?? 1 }}">
                                                    </div>
                                                    <div class="col-md-2 payment-installment-value-field" style="{{ $payment->paymentMethod && $payment->paymentMethod->allows_installments ? '' : 'display: none;' }}">
                                                        <label class="form-label">Valor da Parcela</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">R$</span>
                                                            <input type="text" class="form-control" name="payment_methods[{{ $index }}][installment_value_display]" 
                                                                   value="{{ $payment->installments > 0 ? number_format($payment->amount / $payment->installments, 2, ',', '.') : '0,00' }}" 
                                                                   readonly style="background-color: #f8f9fa;">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 payment-moment-field">
                                                        <label class="form-label">Momento</label>
                                                        <select class="form-select" name="payment_methods[{{ $index }}][payment_moment]">
                                                            <option value="approval" {{ $payment->payment_moment == 'approval' ? 'selected' : '' }}>Na Aprovação</option>
                                                            <option value="pickup" {{ $payment->payment_moment == 'pickup' ? 'selected' : '' }}>Na Retirada</option>
                                                            <option value="custom" {{ $payment->payment_moment == 'custom' ? 'selected' : '' }}>Data Personalizada</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 custom-date-field" style="{{ $payment->payment_moment == 'custom' ? '' : 'display: none;' }}">
                                                        <label class="form-label">Data Personalizada</label>
                                                        <input type="date" class="form-control" name="payment_methods[{{ $index }}][custom_date]" 
                                                               value="{{ $payment->custom_date ? $payment->custom_date->format('Y-m-d') : '' }}">
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger btn-sm remove-payment-method me-1" style="height: 38px;">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                        @if($loop->last)
                                                            <button type="button" class="btn btn-success btn-sm add-payment-method" style="height: 38px;">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
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
                                                            <option value="{{ $method->id }}" data-allows-installments="{{ $method->allows_installments ? 'true' : 'false' }}">{{ $method->paymentOptionMethod->method ?? 'N/A' }}{{ !$method->is_active ? ' (Inativo)' : '' }}</option>
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
                                                    <input type="number" class="form-control" name="payment_methods[0][installments]" value="1" min="1" max="1">
                                                </div>
                                                <div class="col-md-2 payment-installment-value-field" style="display: none;">
                                                    <label class="form-label">Valor da Parcela</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">R$</span>
                                                        <input type="text" class="form-control" name="payment_methods[0][installment_value_display]" 
                                                               value="0,00" readonly style="background-color: #f8f9fa;">
                                                    </div>
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
                                                <div class="col-md-12">
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
                        <div class="card mt-4" id="remainingAmountCard" style="border-left: 4px solid #28a745;{{ $budget->budgetPayments->count() == 0 ? ' display: none;' : '' }}">
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

                        <!-- Seção de Dados Bancários -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-bank"></i> Dados Bancários</h5>
                            </div>
                            <div class="card-body">
                                <!-- Checkbox tipo settings para controlar exibição dos dados bancários -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="include_bank_data" 
                                               name="include_bank_data" 
                                               value="1"
                                               {{ $budget->bankAccounts->count() > 0 ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="include_bank_data">
                                            <i class="bi bi-bank"></i> Incluir Dados Bancários
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Quando ativado, permite adicionar contas bancárias ao orçamento
                                    </small>
                                </div>
                                
                                <div id="bank-data-container" style="{{ $budget->bankAccounts->count() == 0 ? 'display: none;' : '' }}">
                                    @if($budget->bankAccounts->count() > 0)
                                        @foreach($budget->bankAccounts as $index => $bankAccount)
                                            <div class="bank-account-row mb-3">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Conta Bancária</label>
                                                        <select class="form-select" name="bank_accounts[{{ $index }}][bank_account_id]">
                                                            <option value="">Selecione uma conta</option>
                                                            @foreach($bankAccounts as $account)
                                                                <option value="{{ $account->id }}" {{ $account->id == $bankAccount->id ? 'selected' : '' }}>
                                                                    {{ $account->type }} - ({{ $account->compe->code ?? '000' }}) {{ $account->compe->bank_name ?? 'Banco' }}
                                                                    @if($account->type === 'Conta' && $account->branch && $account->account)
                                                                        - Ag: {{ $account->branch }} Cc: {{ $account->account }}
                                                                    @elseif($account->type === 'PIX' && $account->key && $account->key_desc)
                                                                        - {{ ucfirst($account->key) }}: {{ $account->key_desc }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger btn-sm remove-bank-account me-1" style="height: 38px;">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                        @if($loop->last)
                                                            <button type="button" class="btn btn-success btn-sm add-bank-account" style="height: 38px;">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="bank-account-row mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Conta Bancária</label>
                                                    <select class="form-select" name="bank_accounts[0][bank_account_id]">
                                                        <option value="">Selecione uma conta</option>
                                                        @foreach($bankAccounts as $account)
                                                            <option value="{{ $account->id }}">
                                                                {{ $account->type }} - ({{ $account->compe->code ?? '000' }}) {{ $account->compe->bank_name ?? 'Banco' }}
                                                                @if($account->type === 'Conta' && $account->branch && $account->account)
                                                                    - Ag: {{ $account->branch }} Cc: {{ $account->account }}
                                                                @elseif($account->type === 'PIX' && $account->key && $account->key_desc)
                                                                    - {{ ucfirst($account->key) }}: {{ $account->key_desc }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-success btn-sm add-bank-account" style="height: 38px;">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                       <div class="my-3">
                            <label for="observations" class="form-label">Obs.:</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations', $budget->observations) }}</textarea>
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
        
        // Alterar cor baseado no valor e controlar visibilidade
        const remainingElement = $('#remainingAmount');
        const cardElement = $('#remainingAmountCard');
        
        // Verificar se a opção de incluir forma de pagamento está desmarcada
        const includePaymentMethods = $('input[name="include_payment_methods"]').is(':checked');
        
        if (!includePaymentMethods) {
            // Ocultar seção quando 'Incluir forma de pagamento no orçamento' estiver desmarcado
            cardElement.hide();
        } else if (remaining === 0) {
            // Ocultar quando valor for R$ 0,00
            cardElement.hide();
        } else {
            // Mostrar quando valor for diferente de R$ 0,00
            cardElement.show();
            
            if (remaining < 0) {
                remainingElement.css('color', '#dc3545'); // Vermelho
                cardElement.css('border-left-color', '#dc3545');
            } else {
                remainingElement.css('color', '#ffc107'); // Amarelo
                cardElement.css('border-left-color', '#ffc107');
            }
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
                    <input type="number" class="form-control quantity-input" name="items[${newIndex}][quantity]" value="1" required min="1" step="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pç. Unit.</label>
                    <div class="input-group">
                        <div class="input-group-text">R$</div>
                        <input type="text" class="form-control money unit-price-input" name="items[${newIndex}][unit_price]" value="0,00" required>
                        <button type="button" class="btn btn-outline-info calculator-btn" data-bs-toggle="modal" data-bs-target="#calculatorModal" title="Calculadora de preço unitário">
                            <i class="bi bi-calculator"></i>
                        </button>
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
                    <div class="d-flex gap-1 align-items-end" style="height: 38px;">
                        <button type="button" class="btn btn-danger btn-sm remove-product-btn" title="Remover produto" style="height: 38px;">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm add-product" title="Adicionar produto" style="height: 38px;">
                            <i class="bi bi-plus-circle"></i>
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
             // Usar o valor numérico diretamente, a máscara reverse formatará automaticamente
            row.find('.unit-price-input').val(parseFloat(price).toFixed(2).replace('.', ','));
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
            
            // Usar o valor numérico diretamente, a máscara reverse formatará automaticamente
            productRow.find('.unit-price-input').val(parseFloat(item.unit_price).toFixed(2).replace('.', ','));
            
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
            // Usar o valor numérico diretamente, a máscara reverse formatará automaticamente
            $('.product-row:first').find('.unit-price-input').val(parseFloat(firstItem.unit_price).toFixed(2).replace('.', ','));
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
    
    // Função para adicionar dias a uma data
    function addDays(dateString, days) {
        const date = new Date(dateString);
        date.setDate(date.getDate() + days);
        return date.toISOString().split('T')[0];
    }

    // Função para validar se uma data não é anterior à data do orçamento
    function validateDateNotBefore(dateToValidate, issueDate) {
        return new Date(dateToValidate) >= new Date(issueDate);
    }

    // Configurações da empresa
    const budgetValidityDays = {{ $settings->budget_validity_days }};
const budgetDeliveryDays = {{ $settings->budget_delivery_days }};
    
    // Quando a data do orçamento mudar, calcular automaticamente baseado nas configurações
    $('#issue_date').on('change', function() {
        const issueDate = $(this).val();
        if (issueDate) {
            const validUntilDate = addDays(issueDate, budgetValidityDays);
            const deliveryDate = addDays(issueDate, budgetDeliveryDays);
            
            $('#valid_until').val(validUntilDate);
            $('#delivery_date').val(deliveryDate);
        }
    });

    // Validação quando a data de validade for alterada manualmente
    $('#valid_until').on('change', function() {
        const validUntilDate = $(this).val();
        const issueDate = $('#issue_date').val();
        
        if (validUntilDate && issueDate && !validateDateNotBefore(validUntilDate, issueDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Data Inválida',
                text: 'A data de validade não pode ser anterior à data do orçamento.',
                confirmButtonText: 'OK'
            });
            $(this).val(addDays(issueDate, budgetValidityDays));
        }
    });

    // Validação quando a data de previsão de entrega for alterada manualmente
    $('#delivery_date').on('change', function() {
        const deliveryDate = $(this).val();
        const issueDate = $('#issue_date').val();
        
        if (deliveryDate && issueDate && !validateDateNotBefore(deliveryDate, issueDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Data Inválida',
                text: 'A data de previsão de entrega não pode ser anterior à data do orçamento.',
                confirmButtonText: 'OK'
            });
            $(this).val(addDays(issueDate, budgetDeliveryDays));
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
    
    // Configurar botões de adicionar no carregamento inicial
    updatePaymentAddButtons();
    updateAddBankAccountButton();

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
                                <option value="{{ $method->id }}" data-allows-installments="{{ $method->allows_installments ? 'true' : 'false' }}">{{ $method->paymentOptionMethod->method ?? 'N/A' }}{{ !$method->is_active ? ' (Inativo)' : '' }}</option>
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
                    <div class="col-md-2 payment-installment-value-field" style="display: none;">
                        <label class="form-label">Valor da Parcela</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" name="payment_methods[${paymentMethodIndex}][installment_value_display]" 
                                   value="0,00" readonly style="background-color: #f8f9fa;">
                        </div>
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
                        <button type="button" class="btn btn-danger btn-sm remove-payment-method me-1" style="height: 38px;">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm add-payment-method" style="height: 38px;">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
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
        updatePaymentAddButtons();
    });

    // Função para controlar exibição dos botões de adicionar métodos de pagamento
    function updatePaymentAddButtons() {
        $('.add-payment-method').hide();
        $('#payment-methods-container .payment-method-row:last .add-payment-method').show();
    }

    // Controlar exibição dos métodos de pagamento e card de valor restante com checkboxes
    $('input[name="include_payment_methods"]').change(function() {
        if ($(this).is(':checked')) {
            $('#payment-methods-container').show();
            $('#remainingAmountCard').show();
            updatePaymentAddButtons();
        } else {
            $('#payment-methods-container').hide();
            $('#remainingAmountCard').hide();
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
    
    // Controle de exibição dos campos baseado no método de pagamento selecionado
    $(document).on('change', 'select[name*="[payment_method_id]"]', function() {
        const paymentMethodId = $(this).val();
        const currentRow = $(this).closest('.payment-method-row');
        const amountField = currentRow.find('.payment-amount-field');
        const installmentsField = currentRow.find('.payment-installments-field');
        const installmentValueField = currentRow.find('.payment-installment-value-field');
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
                installmentValueField.show();
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
                installmentValueField.hide();
                // Definir parcelas como 1 se não permite parcelamento
                installmentsField.find('input').val(1).attr('max', 1);
                // Limpar valor da parcela
                installmentValueField.find('input').val('0,00');
            }
        } else {
            // Se nenhum método selecionado, esconder todos os campos
            amountField.hide();
            installmentsField.hide();
            installmentValueField.hide();
            momentField.hide();
            
            // Limpar valores dos campos
            amountField.find('input').val('');
            installmentsField.find('input').val(1);
            installmentValueField.find('input').val('0,00');
            momentField.find('select').val('approval');
        }
    });
    
    // Verificar campos no carregamento da página
    $('select[name*="[payment_method_id]"]').each(function() {
        $(this).trigger('change');
    });
    
    // Função para atualizar o valor da parcela
    function updateInstallmentValue(paymentRow) {
        const amountInput = paymentRow.find('input[name*="[amount]"]');
        const installmentsInput = paymentRow.find('input[name*="[installments]"]');
        const installmentValueInput = paymentRow.find('input[name*="[installment_value_display]"]');
        const installmentValueField = paymentRow.find('.payment-installment-value-field');
        
        // Só calcular se o campo estiver visível
        if (installmentValueField.is(':visible')) {
            const amount = parseMoney(amountInput.val());
            const installments = parseInt(installmentsInput.val()) || 1;
            
            if (amount > 0 && installments > 0) {
                const installmentValue = amount / installments;
                installmentValueInput.val(installmentValue.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                installmentValueInput.val('0,00');
            }
        }
    }
    
    // Event listeners para atualizar valor da parcela dinamicamente
    $(document).on('input keyup', 'input[name*="[amount]"]', function() {
        const paymentRow = $(this).closest('.payment-method-row');
        updateInstallmentValue(paymentRow);
    });
    
    $(document).on('input change', 'input[name*="[installments]"]', function() {
        const paymentRow = $(this).closest('.payment-method-row');
        updateInstallmentValue(paymentRow);
    });
    
    // Atualizar valores das parcelas no carregamento da página
    $('.payment-method-row').each(function() {
        updateInstallmentValue($(this));
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

    // Remover método de pagamento
    $(document).on('click', '.remove-payment-method', function() {
        const paymentRows = $('.payment-method-row');
        if (paymentRows.length > 1) {
            $(this).closest('.payment-method-row').remove();
            updateRemainingAmount(); // Atualizar valor restante após remoção
            updatePaymentAddButtons(); // Atualizar botões de adicionar
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
    
    // --- Validação de Duplicação de Dados Bancários ---
    
    function validateBankAccountDuplication() {
        const includeBankData = $('input[name="include_bank_data"]:checked').val();
        
        if (includeBankData !== '1') {
            return true; // Se não incluir dados bancários, não há duplicação
        }
        
        const selectedAccountIds = [];
        const duplicates = [];
        
        $('#bank-data-container .bank-account-row').each(function() {
            const selectElement = $(this).find('select[name*="bank_account_id"]');
            const selectedValue = selectElement.val();
            
            if (selectedValue) {
                if (selectedAccountIds.includes(selectedValue)) {
                    const selectedText = selectElement.find('option:selected').text();
                    duplicates.push({
                        id: selectedValue,
                        text: selectedText
                    });
                } else {
                    selectedAccountIds.push(selectedValue);
                }
            }
        });
        
        if (duplicates.length > 0) {
            const duplicate = duplicates[0];
            let message = `Não é possível incluir o mesmo dado bancário duas vezes no orçamento.\n\n`;
            message += `Dado bancário: ${duplicate.text}`;
            message += `\n\nPor favor, selecione dados bancários diferentes.`;
            
            Swal.fire({
                icon: 'error',
                title: 'Dados Bancários Duplicados!',
                text: message,
                confirmButtonText: 'OK'
            });
            
            return false;
        }
        
        return true;
    }
    
    // Validação do formulário antes da submissão
    $('#budgetForm').on('submit', function(e) {
        // Validar duplicação de dados bancários
        if (!validateBankAccountDuplication()) {
            e.preventDefault();
            return false;
        }
        
        const includePaymentMethods = $('input[name="include_payment_methods"]:checked').val();
        
        if (includePaymentMethods === '1') {
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

    // --- Controle de Dados Bancários ---
    // Controlar exibição do container de dados bancários
    $('input[name="include_bank_data"]').change(function() {
        const bankDataContainer = $('#bank-data-container');
        const addBankAccountBtn = $('.add-bank-account');
        
        if ($(this).is(':checked')) {
            bankDataContainer.show();
            addBankAccountBtn.show();
        } else {
            bankDataContainer.hide();
            addBankAccountBtn.hide();
            // Limpar todas as linhas de conta bancária exceto a primeira
            const bankRows = $('.bank-account-row');
            if (bankRows.length > 1) {
                bankRows.slice(1).remove();
            }
            // Limpar o select da primeira linha
            bankRows.first().find('select').val('');
        }
    });

    // Adicionar nova linha de conta bancária
    $(document).on('click', '.add-bank-account', function() {
        const bankContainer = $('#bank-data-container');
        const bankRows = $('.bank-account-row');
        const newIndex = bankRows.length;
        
        // Clonar a primeira linha
        const firstRow = bankRows.first();
        const newRow = firstRow.clone();
        
        // Atualizar os atributos name e id
        newRow.find('select').attr('name', `bank_accounts[${newIndex}][bank_account_id]`);
        newRow.find('select').attr('id', `bank_account_${newIndex}`);
        newRow.find('select').val(''); // Limpar seleção
        
        // Adicionar botão de remover se não existir
        if (newRow.find('.remove-bank-account').length === 0) {
            newRow.find('.col-md-10').removeClass('col-md-10').addClass('col-md-8');
            newRow.append(`
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-bank-account" title="Remover conta">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `);
        }
        
        // Adicionar a nova linha
        bankContainer.append(newRow);
        
        // Atualizar visibilidade do botão adicionar
        updateAddBankAccountButton();
    });

    // Remover linha de conta bancária
    $(document).on('click', '.remove-bank-account', function() {
        const bankRows = $('.bank-account-row');
        if (bankRows.length > 1) {
            $(this).closest('.bank-account-row').remove();
            reindexBankAccounts();
            updateAddBankAccountButton();
        }
    });

    // Reindexar campos de conta bancária após remoção
    function reindexBankAccounts() {
        $('.bank-account-row').each(function(index) {
            $(this).find('select').attr('name', `bank_accounts[${index}][bank_account_id]`);
            $(this).find('select').attr('id', `bank_account_${index}`);
        });
    }

    // Atualizar visibilidade do botão adicionar
    function updateAddBankAccountButton() {
        $('.add-bank-account').hide();
        $('#bank-data-container .bank-account-row:last .add-bank-account').show();
    }

    // Verificar estado inicial dos dados bancários
    const initialBankData = $('input[name="include_bank_data"]:checked').val();
    if (initialBankData === 'yes') {
        $('#bank-data-container').show();
        $('.add-bank-account').show();
        updateAddBankAccountButton();
    } else {
        $('#bank-data-container').hide();
        $('.add-bank-account').hide();
    }

    // === CALCULADORA DE PREÇO UNITÁRIO ===
    let currentCalculatorInput = null;
    
    // Máscara para valor total da calculadora
    $('#calc_total').mask('000.000.000.000.000,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    // Função para converter valor monetário brasileiro para número
    function parseMoney(value) {
        if (!value) return 0;
        return parseFloat(value.replace(/\./g, '').replace(',', '.'));
    }
    
    // Função para formatar número como moeda brasileira
    function formatMoney(value) {
        return value.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Função para encontrar o valor total mais próximo que resulte em preço com 2 casas decimais
    function findNearestExactTotal(quantity, originalTotal) {
        const originalUnitPrice = originalTotal / quantity;
        // Arredondar para 2 casas decimais
        const roundedUnitPrice = Math.round(originalUnitPrice * 100) / 100;
        const nearestExactTotal = roundedUnitPrice * quantity;
        
        return { total: nearestExactTotal, unitPrice: roundedUnitPrice };
    }
    
    // Abrir calculadora e armazenar referência do campo atual
    $(document).on('click', '.calculator-btn', function() {
        currentCalculatorInput = $(this).siblings('.unit-price-input');
        
        // Encontrar o campo de quantidade correspondente na mesma linha
        const productRow = $(this).closest('.row');
        window.currentQuantityInput = productRow.find('.quantity-input');
        
        // Limpar campos da calculadora
        $('#calc_quantity').val('');
        $('#calc_total').val('');
        $('#calculatorResult').hide();
        $('#resultSuccess').hide();
        $('#resultWarning').hide();
    });
    
    // Calculadora de preço unitário
    $('#calculateBtn').click(function() {
        const quantity = parseInt($('#calc_quantity').val());
        const totalValue = parseMoney($('#calc_total').val());
        
        // Validações
        if (!quantity || quantity <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Digite uma quantidade válida.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        if (!totalValue || totalValue <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Digite um valor total válido.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Calcular preço unitário
        const unitPrice = totalValue / quantity;
        
        // Verificar se o resultado tem mais de 2 casas decimais
        const roundedPrice = Math.round(unitPrice * 100) / 100;
        const hasMoreThanTwoDecimals = Math.abs(unitPrice - roundedPrice) > 0.001;
        
        // Mostrar resultado
        $('#calculatorResult').show();
        
        if (hasMoreThanTwoDecimals) {
            // Resultado com mais de 2 casas decimais - mostrar com fundo vermelho e até 4 casas decimais
            const unitPriceFormatted = unitPrice.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            });
            
            $('#unitPrice').text('R$ ' + unitPriceFormatted);
            $('#resultSuccess').removeClass('alert-success').addClass('alert-danger').show();
            $('#useCalculatedPrice').hide(); // Esconder botão "Usar este preço"
            
            // Mostrar valores sugeridos
            const suggestion = findNearestExactTotal(quantity, totalValue);
            $('#suggestedTotal').text('R$ ' + formatMoney(suggestion.total));
            $('#suggestedUnitPrice').text('R$ ' + formatMoney(suggestion.unitPrice));
            $('#resultWarning').show();
            
            // Armazenar valores para uso posterior
            window.calculatorSuggestion = suggestion;
        } else {
            // Resultado inteiro ou com 2 casas decimais - mostrar com fundo verde
            $('#unitPrice').text('R$ ' + formatMoney(unitPrice));
            $('#resultSuccess').removeClass('alert-danger').addClass('alert-success').show();
            $('#useCalculatedPrice').show(); // Mostrar botão "Usar este preço"
            
            // Esconder valores sugeridos
            $('#resultWarning').hide();
        }
    });
    
    // Usar preço calculado (resultado exato)
    $('#useCalculatedPrice').click(function() {
        if (currentCalculatorInput && window.currentQuantityInput) {
            const unitPrice = $('#unitPrice').text().replace('R$ ', '');
            const quantity = $('#calc_quantity').val();
            
            // Aplicar preço unitário e quantidade
            currentCalculatorInput.val(unitPrice).trigger('input');
            window.currentQuantityInput.val(quantity).trigger('input');
            
            // Fechar modal
            $('#calculatorModal').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Preço e quantidade aplicados com sucesso!',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    });
    
    // Usar preço sugerido (resultado aproximado)
    $('#useSuggestedPrice').click(function() {
        if (currentCalculatorInput && window.currentQuantityInput) {
            const suggestedPrice = $('#suggestedUnitPrice').text().replace('R$ ', '');
            const quantity = $('#calc_quantity').val();
            
            // Aplicar preço unitário sugerido e quantidade
            currentCalculatorInput.val(suggestedPrice).trigger('input');
            window.currentQuantityInput.val(quantity).trigger('input');
            
            // Fechar modal
            $('#calculatorModal').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Preço sugerido e quantidade aplicados com sucesso!',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    });
    
    // Limpar resultado quando campos da calculadora são alterados
    $('#calc_quantity, #calc_total').on('input', function() {
        $('#calculatorResult').hide();
        $('#resultSuccess').hide();
        $('#resultWarning').hide();
    });

    // --- Controle do Checkbox de Previsão de Entrega ---
    
    function toggleDeliveryDate() {
        const isEnabled = $('#delivery_date_enabled').is(':checked');
        
        if (isEnabled) {
            $('#delivery_date_container').show();
            $('#delivery_date_text').hide();
            $('#delivery_date').prop('required', true);
            
            // Se não há data definida, calcular baseado na data do orçamento e prazo das settings
            if (!$('#delivery_date').val()) {
                const issueDate = $('#issue_date').val();
                if (issueDate) {
                    const deliveryDate = addDays(issueDate, budgetDeliveryDays);
                    $('#delivery_date').val(deliveryDate);
                }
            }
        } else {
            $('#delivery_date_container').hide();
            $('#delivery_date_text').show();
            $('#delivery_date').prop('required', false);
            $('#delivery_date').val('');
        }
    }
    
    // Inicializar estado do checkbox
    toggleDeliveryDate();
    
    // Event listener para mudanças no checkbox
    $('#delivery_date_enabled').on('change', function() {
        toggleDeliveryDate();
    });

});
</script>

<!-- Modal da Calculadora -->
<div class="modal fade" id="calculatorModal" tabindex="-1" aria-labelledby="calculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calculatorModalLabel">
                    <i class="bi bi-calculator"></i> Calculadora de Preço Unitário
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="calc_quantity" class="form-label">Quantidade</label>
                    <input type="number" class="form-control" id="calc_quantity" placeholder="Ex: 10" min="1">
                </div>
                <div class="mb-3">
                    <label for="calc_total" class="form-label">Valor Total</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control" id="calc_total" placeholder="0,00">
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="button" class="btn btn-info" id="calculateBtn">
                        <i class="bi bi-calculator"></i> Calcular
                    </button>
                </div>
                
                <div id="calculatorResult" style="display: none;">
                    <div class="alert alert-success" id="resultSuccess" style="display: none;">
                        <strong>Preço unitário:</strong> <span id="unitPrice"></span><br>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="useCalculatedPrice">
                            <i class="bi bi-check"></i> Usar este preço
                        </button>
                    </div>
                    <div class="alert alert-warning" id="resultWarning" style="display: none;">
                        <strong>Atenção!</strong> O resultado não é exato.<br>
                        <strong>Sugestão:</strong> Use R$ <span id="suggestedTotal"></span> como valor total.<br>
                        <strong>Preço unitário:</strong> <span id="suggestedUnitPrice"></span><br>
                        <button type="button" class="btn btn-warning btn-sm mt-2" id="useSuggestedPrice">
                            <i class="bi bi-check"></i> Usar preço sugerido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endpush
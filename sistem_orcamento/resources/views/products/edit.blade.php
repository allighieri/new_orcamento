@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box"></i> Editar Produto
                    </h5>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('products.update', $product->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Preço *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price', number_format($product->price, 2, ',', '.')) }}" placeholder="0,00" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        
                        
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Selecione uma empresa</option>
                                        @foreach(App\Models\Company::orderBy('fantasy_name')->get() as $company)
                                            <option value="{{ $company->id }}" {{ (old('company_id', $product->company_id) == $company->id) ? 'selected' : '' }}>
                                                {{ $company->fantasy_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Categoria *</label>
                                    <div class="input-group">
                                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">Selecione uma categoria</option>
                            @php
                                $categoriesTree = App\Models\Category::getTreeForSelect(null, $product->company_id, false);
                            @endphp
                            @foreach($categoriesTree as $categoryId => $categoryName)
                                <option value="{{ $categoryId }}" {{ (old('category_id', $product->category_id) == $categoryId) ? 'selected' : '' }}>
                                    {!! $categoryName !!}
                                </option>
                            @endforeach
                        </select>
                                        <button type="button" class="btn btn-outline-primary" id="openCategoryModalBtn" title="Nova Categoria">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Selecione a empresa para exibir categorias</div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Categoria *</label>
                                    <div class="input-group">
                                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                            <option value="">Selecione uma categoria</option>
                                            @php
                                                $categoriesTree = App\Models\Category::getTreeForSelect(null, $product->company_id, false);
                                            @endphp
                                            @foreach($categoriesTree as $categoryId => $categoryName)
                                                <option value="{{ $categoryId }}" {{ (old('category_id', $product->category_id) == $categoryId) ? 'selected' : '' }}>
                                                    {!! $categoryName !!}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" id="openCategoryModalBtn" title="Nova Categoria">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Subcategorias são indicadas por indentação</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" placeholder="Descrição detalhada do produto">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                     
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar Produto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                        <div class="invalid-feedback" id="category_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="category_description" name="description" rows="3" placeholder="Descrição da categoria"></textarea>
                        <div class="invalid-feedback" id="category_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="parent_category_id" class="form-label">Categoria Pai</label>
                        <select class="form-select" id="parent_category_id" name="parent_id">
                            <option value="">Categoria Principal</option>
                            @php
                                $categoriesTree = App\Models\Category::getTreeForSelect(null, $product->company_id, false);
                            @endphp
                            @foreach($categoriesTree as $categoryId => $categoryName)
                                <option value="{{ $categoryId }}">
                                    {!! $categoryName !!}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Deixe em branco para criar uma categoria principal</div>
                        <div class="invalid-feedback" id="parent_id_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveCategoryBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscara para preço em formato brasileiro
    $('#price').mask('000.000.000.000.000,00', {
        reverse: true,
        placeholder: '0,00'
    });
    
    // Abrir modal de categoria
    $('#openCategoryModalBtn').on('click', function() {
        $('#categoryModal').modal('show');
    });
    
    // Limpar formulário ao fechar modal
    $('#categoryModal').on('hidden.bs.modal', function() {
        $('#categoryForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    });
    
    // Submeter formulário de categoria
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#saveCategoryBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        // Mostrar loading
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        // Limpar erros anteriores
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let formData = $(this).serialize();
        
        // Adicionar company_id do produto sendo editado
         const companyId = '{{ $product->company_id }}';
         if (companyId) {
             formData += '&company_id=' + companyId;
         }
        
        $.ajax({
            url: '{{ route("categories.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Adicionar nova categoria ao select
                    const newOption = new Option(response.category.name, response.category.id, true, true);
                    $('#category_id').append(newOption);
                    
                    // Fechar modal
                     $('#categoryModal').modal('hide');
                    
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
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Erros de validação
                    const errors = xhr.responseJSON.errors;
                    
                    Object.keys(errors).forEach(function(field) {
                        const input = $('#category_' + field);
                        const errorDiv = $('#category_' + field + '_error');
                        
                        input.addClass('is-invalid');
                        errorDiv.text(errors[field][0]);
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
            complete: function() {
                // Esconder loading
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
    
    @if(auth()->guard('web')->user()->role === 'super_admin')
    // Função para carregar categorias por empresa
    function loadCategoriesByCompany(companyId) {
        const categorySelect = $('#category_id');
        const parentCategorySelect = $('#parent_category_id');
        
        if (!companyId) {
            categorySelect.empty().append('<option value="">Selecione uma categoria</option>').prop('disabled', true);
            parentCategorySelect.empty().append('<option value="">Categoria Principal</option>');
            return;
        }
        
        $.ajax({
            url: '{{ route('categories.by-company') }}',
            method: 'GET',
            data: { company_id: companyId },
            success: function(response) {
                // Atualizar select principal de categoria
                categorySelect.empty().append('<option value="">Selecione uma categoria</option>');
                
                // Atualizar select de categoria pai no modal
                parentCategorySelect.empty().append('<option value="">Categoria Principal</option>');
                
                if (response.success && response.categories) {
                    $.each(response.categories, function(categoryId, categoryName) {
                        const isSelected = categoryId == {{ $product->category_id ?? 'null' }} ? 'selected' : '';
                        categorySelect.append('<option value="' + categoryId + '" ' + isSelected + '>' + categoryName + '</option>');
                        parentCategorySelect.append('<option value="' + categoryId + '">' + categoryName + '</option>');
                    });
                }
                
                categorySelect.prop('disabled', false);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao carregar categorias',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
    
    // Monitorar mudanças no select de empresa
    $('#company_id').on('change', function() {
        const companyId = $(this).val();
        loadCategoriesByCompany(companyId);
    });
    
    // Carregar categorias iniciais se uma empresa já estiver selecionada
    const initialCompanyId = $('#company_id').val();
    if (initialCompanyId) {
        loadCategoriesByCompany(initialCompanyId);
    }
    @endif
});
</script>
@endpush
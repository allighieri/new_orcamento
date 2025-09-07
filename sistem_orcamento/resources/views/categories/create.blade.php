@extends('layouts.app')

@section('title', 'Nova Categoria - Sistema de Orçamento')

@section('content')
<div class="container mx-auto row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-tags"></i> Nova Categoria
                </h5>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
            
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Digite o nome da categoria" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->fantasy_name ?: $company->corporate_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Categoria Pai</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id" @if(auth()->guard('web')->user()->role === 'super_admin') disabled @endif>
                                    @if(auth()->guard('web')->user()->role === 'super_admin')
                                        <option value="">Selecione uma empresa primeiro</option>
                                    @else
                                        <option value="">Sem categoria</option>
                                        @foreach($categoriesTree as $categoryId => $categoryName)
                                            <option value="{{ $categoryId }}" {{ old('parent_id') == $categoryId ? 'selected' : '' }}>
                                                {!! $categoryName !!}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(auth()->guard('web')->user()->role === 'super_admin')
                                    <div class="form-text">Selecione a empresa para exibir categorias</div>
                                @else
                                    <div class="form-text">Escolha sem categoria para categorias principais.</div>
                                @endif
                            </div>
                        </div>


                    </div>
                    
                   
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Descreva a categoria...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Converter campos de texto para maiúsculo durante a digitação
    $('#name, #description').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    @if(auth()->guard('web')->user()->role === 'super_admin')
    // Carregamento dinâmico de categorias pai baseado na empresa selecionada
    $('#company_id').on('change', function() {
        const companyId = $(this).val();
        const parentSelect = $('#parent_id');
        
        // Limpar opções atuais
        parentSelect.empty().append('<option value="">Carregando...</option>');
        parentSelect.prop('disabled', true);
        
        if (companyId) {
            // Fazer requisição AJAX para carregar categorias
            $.ajax({
                url: '{{ route("categories.by-company") }}',
                type: 'GET',
                data: { company_id: companyId },
                success: function(response) {
                    parentSelect.empty().append('<option value="">Sem categoria</option>');
                    
                    // Adicionar categorias da empresa
                    if (response.success && response.categories) {
                        $.each(response.categories, function(categoryId, categoryName) {
                            parentSelect.append('<option value="' + categoryId + '">' + categoryName + '</option>');
                        });
                    }
                    
                    parentSelect.prop('disabled', false);
                },
                error: function() {
                    parentSelect.empty().append('<option value="">Erro ao carregar categorias</option>');
                    parentSelect.prop('disabled', false);
                }
            });
        } else {
            parentSelect.empty().append('<option value="">Selecione uma empresa primeiro</option>');
            parentSelect.prop('disabled', true);
        }
    });
    @endif
});
</script>
@endpush
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-telephone"></i> Novo Contato</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('contact-forms.store') }}">
                        @csrf
                        
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
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
                        </div>
                        @endif

                        <!-- Seção de Contatos -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Contatos</h5>
                            </div>

                            <div id="contactsContainer">
                                @if(old('contacts'))
                                    @foreach(old('contacts') as $index => $contactData)
                                        <div class="contact-row mb-3 mt-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Tipo *</label>
                                                    <select class="form-select contact-type-select" name="contacts[{{ $index }}][type]" required>
                                                        <option value="">Selecione o tipo</option>
                                                        <option value="telefone" {{ $contactData['type'] == 'telefone' ? 'selected' : '' }}>Telefone</option>
                                                        <option value="celular" {{ $contactData['type'] == 'celular' ? 'selected' : '' }}>Celular</option>
                                                        <option value="whatsapp" {{ $contactData['type'] == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                                        <option value="email" {{ $contactData['type'] == 'email' ? 'selected' : '' }}>Email</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-md-4 contact-field" style="{{ $contactData['type'] ? 'display: block;' : 'display: none;' }}">
                                                    <label class="form-label">Contato *</label>
                                                    <input type="text" class="form-control contact-input" name="contacts[{{ $index }}][description]" value="{{ $contactData['description'] }}" required>
                                                </div>
                                                
                                                <div class="col-md-2">
                                                    <label class="form-label">Status</label>
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" name="contacts[{{ $index }}][active]" value="1" {{ isset($contactData['active']) && $contactData['active'] ? 'checked' : '' }}>
                                                        <label class="form-check-label">Ativo</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">&nbsp;</label>
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-danger btn-sm remove-contact">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-success btn-sm add-contact">
                                                            <i class="bi bi-plus-circle"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Campo inicial quando não há dados antigos -->
                                    <div class="contact-row mb-3 mt-3">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Tipo *</label>
                                                <select class="form-select contact-type-select" name="contacts[0][type]" required>
                                                    <option value="">Selecione o tipo</option>
                                                    <option value="telefone">Telefone</option>
                                                    <option value="celular">Celular</option>
                                                    <option value="whatsapp">WhatsApp</option>
                                                    <option value="email">Email</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4 contact-field" style="display: none;">
                                                 <label class="form-label">Contato *</label>
                                                 <input type="text" class="form-control contact-input" name="contacts[0][description]" required>
                                             </div>
                                            
                                            <div class="col-md-2">
                                                <label class="form-label">Status</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" name="contacts[0][active]" value="1" checked>
                                                    <label class="form-check-label">Ativo</label>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-danger btn-sm remove-contact">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm add-contact">
                                                        <i class="bi bi-plus-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('contact-forms.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template para linha de contato -->
<template id="contactRowTemplate">
    <div class="contact-row mb-3 mt-3">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Tipo *</label>
                <select class="form-select contact-type-select" name="contacts[INDEX][type]" required>
                    <option value="">Selecione o tipo</option>
                    <option value="telefone">Telefone</option>
                    <option value="celular">Celular</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>
            
            <div class="col-md-4 contact-field" style="display: none;">
                <label class="form-label">Contato *</label>
                <input type="text" class="form-control contact-input" name="contacts[INDEX][description]" required>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="contacts[INDEX][active]" value="1" checked>
                    <label class="form-check-label">Ativo</label>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-danger btn-sm remove-contact">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm add-contact">
                        <i class="bi bi-plus-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
    let contactIndex = $('.contact-row').length;
    
    // Função para aplicar máscaras baseadas no tipo
    function applyMask(input, type) {
        // Remove máscaras anteriores
        input.unmask();
        
        switch(type) {
            case 'telefone':
                input.mask('(00) 0000-0000');
                break;
            case 'celular':
            case 'whatsapp':
                input.mask('(00) 00000-0000');
                break;
            case 'email':
                // Para email, não aplicamos máscara, apenas validação
                input.attr('type', 'email');
                break;
            default:
                input.attr('type', 'text');
        }
    }
    
    // Adicionar contato
    $(document).on('click', '.add-contact', function() {
        let template = $('#contactRowTemplate').html();
        template = template.replace(/INDEX/g, contactIndex);
        $('#contactsContainer').append(template);
        contactIndex++;
    });
    
    // Remover contato
    $(document).on('click', '.remove-contact', function() {
        if ($('.contact-row').length > 1) {
            $(this).closest('.contact-row').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Deve haver pelo menos um contato.',
                confirmButtonText: 'OK'
            });
        }
    });
    
    // Quando mudar o tipo de contato, aplicar máscara correspondente
    $(document).on('change', '.contact-type-select', function() {
        const type = $(this).val();
        const contactRow = $(this).closest('.contact-row');
        const input = contactRow.find('.contact-input');
        const contactField = contactRow.find('.contact-field');
        
        if (type) {
            // Mostrar o campo de contato
            contactField.show();
            
            // Limpar o campo
            input.val('');
            
            // Aplicar nova máscara
            applyMask(input, type);
            
            // Atualizar placeholder
            switch(type) {
                case 'telefone':
                    input.attr('placeholder', '(11) 3333-4444');
                    break;
                case 'celular':
                case 'whatsapp':
                    input.attr('placeholder', '(11) 99999-8888');
                    break;
                case 'email':
                    input.attr('placeholder', 'exemplo@email.com');
                    break;
                default:
                    input.attr('placeholder', '');
            }
        } else {
            // Ocultar o campo de contato se nenhum tipo for selecionado
            contactField.hide();
            input.val('');
        }
    });
    
    // Aplicar máscaras nos campos existentes ao carregar a página
    $('.contact-type-select').each(function() {
        const type = $(this).val();
        const input = $(this).closest('.contact-row').find('.contact-input');
        if (type) {
            applyMask(input, type);
        }
    });
});
</script>

@endsection
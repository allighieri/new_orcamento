@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-telephone"></i> Editar Contato
                    </h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('contact-forms.update', $contactForm->id) }}">
                        @csrf
                        @method('PUT')
                        
                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $contactForm->company_id) == $company->id ? 'selected' : '' }}>
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

                        <!-- Dados do Contato -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipo *</label>
                                    <select class="form-select contact-type-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="telefone" {{ old('type', $contactForm->type) == 'telefone' ? 'selected' : '' }}>Telefone</option>
                                        <option value="celular" {{ old('type', $contactForm->type) == 'celular' ? 'selected' : '' }}>Celular</option>
                                        <option value="whatsapp" {{ old('type', $contactForm->type) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                        <option value="email" {{ old('type', $contactForm->type) == 'email' ? 'selected' : '' }}>Email</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 contact-field" style="{{ old('type', $contactForm->type) ? 'display: block;' : 'display: none;' }}">
                <div class="mb-3">
                    <label for="description" class="form-label">Contato *</label>
                    <input type="text" class="form-control contact-input @error('description') is-invalid @enderror" 
                           id="description" name="description" value="{{ old('description', $contactForm->description) }}" required>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
                            
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="active" class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input @error('active') is-invalid @enderror" type="checkbox" 
                                               id="active" name="active" value="1" 
                                               {{ old('active', $contactForm->active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">Ativo</label>
                                    </div>
                                    @error('active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('contact-forms.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
    // Função para aplicar máscaras baseadas no tipo
    function applyMask(input, type) {
        // Remove máscaras anteriores
        input.unmask();
        
        switch(type) {
            case 'telefone':
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
    
    // Aplicar máscara inicial baseada no tipo selecionado
    const initialType = $('#type').val();
    const input = $('#description');
    if (initialType) {
        applyMask(input, initialType);
    }
    
    // Quando mudar o tipo de contato, aplicar máscara correspondente
    $('#type').on('change', function() {
        const type = $(this).val();
        const contactField = $('.contact-field');
        
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
});
</script>

@endsection
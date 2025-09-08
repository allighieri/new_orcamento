@extends('layouts.app')

@section('content')
<div class="container mx-auto row">
    
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-building-add"></i> Editar Empresa</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="corporate_name" class="form-label">Razão Social</label>
                                    <input type="text" class="form-control @error('corporate_name') is-invalid @enderror" 
                                           id="corporate_name" name="corporate_name" value="{{ old('corporate_name', $company->corporate_name) }}">
                                    @error('corporate_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fantasy_name" class="form-label">Nome Fantasia</label>
                                    <input type="text" class="form-control @error('fantasy_name') is-invalid @enderror" 
                                           id="fantasy_name" name="fantasy_name" value="{{ old('fantasy_name', $company->fantasy_name) }}">
                                    @error('fantasy_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document_number" class="form-label">CNPJ *</label>
                                    <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                                           id="document_number" name="document_number" value="{{ old('document_number', $company->document_number) }}" required>
                                    @error('document_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state_registration" class="form-label">Inscrição Estadual</label>
                                    <input type="text" class="form-control @error('state_registration') is-invalid @enderror" 
                                           id="state_registration" name="state_registration" value="{{ old('state_registration', $company->state_registration) }}">
                                    @error('state_registration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefone *</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $company->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $company->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Endereço *</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                           id="address" name="address" value="{{ old('address', $company->address) }}" required>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="district" class="form-label">Bairro</label>
                                    <input type="text" class="form-control @error('district') is-invalid @enderror" 
                                           id="district" name="district" value="{{ old('district', $company->district) }}">
                                    @error('district')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Cidade *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $company->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="state" class="form-label">UF *</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $company->state) }}" maxlength="2" required>
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="cep" class="form-label">CEP</label>
                                    <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                           id="cep" name="cep" value="{{ old('cep', $company->cep) }}">
                                    @error('cep')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logomarca</label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                           id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div id="logo-preview" class="border rounded p-3 text-center" style="min-height: 150px; background-color: #f8f9fa;">
                                            @if($company->logo)
                                                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo atual" 
                                                    class="img-fluid" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                                <p class="text-muted mt-2 mb-0 small">Logo atual</p>
                                            @else
                                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2 mb-0">Nenhuma imagem selecionada</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar Empresa</button>
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
    /// Máscara dinâmica para CPF/CNPJ
    var documentInput = $('#document_number');
    documentInput.on('input', function() {
        var cleanValue = $(this).val().replace(/\D/g, '');

        if (cleanValue.length > 11) {
            $(this).mask('00.000.000/0000-00', {clearIfNotMatch: true});
        } else {
            $(this).mask('000.000.000-009', {clearIfNotMatch: true});
        }
    }).trigger('input'); // O trigger('input') já aplica a máscara inicial
    
    // Máscara dinâmica para telefone/celular
    var phoneOptions = {
        onKeyPress: function(phone, e, field, options) {
            var masks = ['(00) 0000-00009', '(00) 00000-0000'];
            var mask = (phone.length > 14) ? masks[1] : masks[0];
            $('#phone').mask(mask, options);
        }
    };
    $('#phone').mask('(00) 0000-00009', phoneOptions);
    
    // Máscara para CEP
    $('#cep').mask('99.999-999');
    
    // Máscara para UF (maiúscula)
    $('#state').on('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Converter campos de texto para maiúsculo durante a digitação (exceto email)
    $('#corporate_name, #fantasy_name, #state_registration, #address, #district, #city').on('input', function() {
        this.value = this.value.toUpperCase();
    });
});

// Função para preview da logo
function previewLogo(event) {
    const file = event.target.files[0];
    const previewDiv = document.getElementById('logo-preview');
    
    if (file) {
        // Verificar se é uma imagem
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Preview da Logo" 
                         class="img-fluid" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                    <p class="text-muted mt-2 mb-0 small">${file.name}</p>
                `;
            };
            
            reader.readAsDataURL(file);
        } else {
            // Se não for uma imagem, mostrar erro
            previewDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <p class="text-warning mt-2 mb-0">Arquivo inválido</p>
                <p class="text-muted small mb-0">Selecione uma imagem válida</p>
            `;
        }
    } else {
        // Se nenhum arquivo foi selecionado, voltar ao estado inicial
        previewDiv.innerHTML = `
            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2 mb-0">Nenhuma imagem selecionada</p>
        `;
    }
}
</script>
@endpush
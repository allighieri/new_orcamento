@extends('layouts.app')

@section('content')
<div class="container mx-auto row">
    
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-building-add"></i> Nova Empresa</h4>
                    <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="corporate_name" class="form-label">Razão Social</label>
                                    <input type="text" class="form-control @error('corporate_name') is-invalid @enderror" 
                                           id="corporate_name" name="corporate_name" value="{{ old('corporate_name') }}">
                                    @error('corporate_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fantasy_name" class="form-label">Nome Fantasia</label>
                                    <input type="text" class="form-control @error('fantasy_name') is-invalid @enderror" 
                                           id="fantasy_name" name="fantasy_name" value="{{ old('fantasy_name') }}">
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
                                           id="document_number" name="document_number" value="{{ old('document_number') }}" required>
                                    @error('document_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state_registration" class="form-label">Inscrição Estadual</label>
                                    <input type="text" class="form-control @error('state_registration') is-invalid @enderror" 
                                           id="state_registration" name="state_registration" value="{{ old('state_registration') }}">
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
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
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
                                           id="address" name="address" value="{{ old('address') }}" required>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Cidade *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="state" class="form-label">UF *</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state') }}" maxlength="2" required>
                                    @error('state')
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
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2 mb-0">Nenhuma imagem selecionada</p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar
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
    // Máscara para CNPJ
    $('#document_number').mask('00.000.000/0000-00');
    
    // Máscara para telefone
    $('#phone').mask('(00) 00000-0000');
    
    // Máscara para UF (maiúscula)
    $('#state').on('input', function() {
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
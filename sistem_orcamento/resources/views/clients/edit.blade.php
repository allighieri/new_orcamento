@extends('layouts.app')

@section('content')
<div class="container">
    <div class="container mx-auto row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                     <h5 class="mb-0">
                        <i class="bi bi-people"></i> Editar Cliente
                     </h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('clients.update', $client->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fantasy_name" class="form-label">Nome Fantasia</label>
                                    <input type="text" class="form-control @error('fantasy_name') is-invalid @enderror" 
                                           id="fantasy_name" name="fantasy_name" value="{{ old('fantasy_name', $client->fantasy_name) }}">
                                    @error('fantasy_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="corporate_name" class="form-label">Razão Social</label>
                                    <input type="text" class="form-control @error('corporate_name') is-invalid @enderror" 
                                           id="corporate_name" name="corporate_name" value="{{ old('corporate_name', $client->corporate_name) }}">
                                    @error('corporate_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if(auth()->guard('web')->user()->role === 'super_admin')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                        <option value="">Selecione uma empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $client->company_id) == $company->id ? 'selected' : '' }}>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="document_number" class="form-label">CPF/CNPJ *</label>
                                    <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                                           id="document_number" name="document_number" value="{{ old('document_number', $client->document_number) }}" required>
                                    @error('document_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state_registration" class="form-label">Inscrição Estadual</label>
                                    <input type="text" class="form-control @error('state_registration') is-invalid @enderror" 
                                           id="state_registration" name="state_registration" value="{{ old('state_registration', $client->state_registration) }}">
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
                                           id="phone" name="phone" value="{{ old('phone', $client->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $client->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="cep" class="form-label">CEP</label>
                                    <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                           id="cep" name="cep" value="{{ old('cep', $client->cep) }}" maxlength="10">
                                    @error('cep')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Endereço *</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                        id="address" name="address" value="{{ old('address', $client->address) }}" required>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="address_line_2" class="form-label">Complemento</label>
                                    <input type="text" class="form-control @error('address_line_2') is-invalid @enderror" 
                                           id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $client->address_line_2) }}">
                                    @error('address_line_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="district" class="form-label">Bairro</label>
                                    <input type="text" class="form-control @error('district') is-invalid @enderror" 
                                           id="district" name="district" value="{{ old('district', $client->district) }}">
                                    @error('district')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Cidade *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $client->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="state" class="form-label">UF *</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $client->state) }}" maxlength="2" required>
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('clients.index') }}" class="btn btn-secondary me-md-2">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Atualizar
                            </button>
                        </div>
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
    // Máscara dinâmica para telefone usando keyup para evitar problemas de cursor
    var phoneOptions = {
        onKeyPress: function(phone, e, field, options) {
            var masks = ['(00) 0000-00009', '(00) 00000-0000'];
            var mask = (phone.length > 14) ? masks[1] : masks[0];
            $('#phone').mask(mask, options);
        }
    };
    $('#phone').mask('(00) 0000-00009', phoneOptions);
    
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
    
    // Máscara para CEP
    $('#cep').mask('99.999-999');
    
    // Busca automática de endereço por CEP
    $('#cep').on('input', function() {
        var cep = $(this).val().replace(/\D/g, '');
        
        if (cep.length === 8) {
            // Exibe loading nos campos
            $('#address, #district, #city, #state').prop('disabled', true).val('Carregando...');
            
            $.ajax({
                url: 'https://viacep.com.br/ws/' + cep + '/json/',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (!data.erro) {
                        $('#address').val(data.logradouro.toUpperCase());
                        $('#district').val(data.bairro.toUpperCase());
                        $('#city').val(data.localidade.toUpperCase());
                        $('#state').val(data.uf.toUpperCase());
                    } else {
                        Swal.fire({
                             icon: 'warning',
                             title: 'CEP não encontrado',
                             text: 'O CEP informado não foi encontrado. Verifique se está correto.',
                             toast: true,
                             position: 'bottom-start',
                             showConfirmButton: false,
                             timer: 3000
                         });
                        $('#address, #district, #city, #state').val('');
                    }
                },
                error: function() {
                    Swal.fire({
                         icon: 'error',
                         title: 'Erro de Conexão',
                         text: 'Não foi possível conectar ao serviço de CEP. Verifique sua conexão com a internet.',
                         toast: true,
                         position: 'bottom-start',
                         showConfirmButton: false,
                         timer: 4000
                     });
                    $('#address, #district, #city, #state').val('');
                },
                complete: function() {
                    $('#address, #district, #city, #state').prop('disabled', false);
                }
            });
        } else if (cep.length < 8) {
             // Limpa os campos quando CEP tem menos de 8 dígitos
             $('#address').val('');
             $('#district').val('');
             $('#city').val('');
             $('#state').val('');
        }
    });
    
    // Máscara para UF (maiúscula)
    $('#state').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Converter campos de texto para maiúsculo durante a digitação (exceto email)
    $('#fantasy_name, #corporate_name, #state_registration, #address, #address_line_2, #district, #city').on('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
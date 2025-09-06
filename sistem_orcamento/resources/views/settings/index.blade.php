@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
<div class="container mx-auto">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Configurações da Empresa</h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                
                <div class="card-body">
                   

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Configurações de Orçamento -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-1">
                                    <i class="bi bi-file-earmark-text"></i> Configurações de Orçamento
                                </h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="budget_validity_days" class="form-label fw-bold">
                                    <i class="bi bi-calendar-check"></i> Dias de Validade do Orçamento
                                </label>
                                <select class="form-select @error('budget_validity_days') is-invalid @enderror" 
                                        id="budget_validity_days" 
                                        name="budget_validity_days" 
                                        required>
                                    <option value="5" {{ old('budget_validity_days', $settings->budget_validity_days) == 5 ? 'selected' : '' }}>5 dias</option>
                                    <option value="10" {{ old('budget_validity_days', $settings->budget_validity_days) == 10 ? 'selected' : '' }}>10 dias</option>
                                    <option value="15" {{ old('budget_validity_days', $settings->budget_validity_days) == 15 ? 'selected' : '' }}>15 dias</option>
                                    <option value="20" {{ old('budget_validity_days', $settings->budget_validity_days) == 20 ? 'selected' : '' }}>20 dias</option>
                                    <option value="25" {{ old('budget_validity_days', $settings->budget_validity_days) == 25 ? 'selected' : '' }}>25 dias</option>
                                    <option value="30" {{ old('budget_validity_days', $settings->budget_validity_days) == 30 ? 'selected' : '' }}>30 dias</option>
                                    <option value="60" {{ old('budget_validity_days', $settings->budget_validity_days) == 60 ? 'selected' : '' }}>60 dias</option>
                                    <option value="90" {{ old('budget_validity_days', $settings->budget_validity_days) == 90 ? 'selected' : '' }}>90 dias</option>
                                    <option value="120" {{ old('budget_validity_days', $settings->budget_validity_days) == 120 ? 'selected' : '' }}>120 dias</option>
                                </select>
                                @error('budget_validity_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Define quantos dias após a criação o orçamento será válido
                                </small>
                            </div>

                            <div class="col-md-3">
                                <label for="budget_delivery_days" class="form-label fw-bold">
                                    <i class="bi bi-truck"></i> Dias de Previsão de Entrega
                                </label>
                                <select class="form-select @error('budget_delivery_days') is-invalid @enderror" 
                                        id="budget_delivery_days" 
                                        name="budget_delivery_days" 
                                        required>
                                    <option value="5" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 5 ? 'selected' : '' }}>5 dias</option>
                                    <option value="10" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 10 ? 'selected' : '' }}>10 dias</option>
                                    <option value="15" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 15 ? 'selected' : '' }}>15 dias</option>
                                    <option value="20" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 20 ? 'selected' : '' }}>20 dias</option>
                                    <option value="25" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 25 ? 'selected' : '' }}>25 dias</option>
                                    <option value="30" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 30 ? 'selected' : '' }}>30 dias</option>
                                    <option value="60" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 60 ? 'selected' : '' }}>60 dias</option>
                                    <option value="90" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 90 ? 'selected' : '' }}>90 dias</option>
                                    <option value="120" {{ old('budget_delivery_days', $settings->budget_delivery_days) == 120 ? 'selected' : '' }}>120 dias</option>
                                </select>
                                @error('budget_delivery_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Define a previsão de entrega padrão dos orçamentos
                                </small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Configurações de PDF -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="bi bi-file-earmark-pdf"></i> Configurações de PDF
                                </h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="enable_pdf_watermark" 
                                           name="enable_pdf_watermark" 
                                           value="1"
                                           {{ old('enable_pdf_watermark', $settings->enable_pdf_watermark) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="enable_pdf_watermark">
                                        <i class="bi bi-droplet"></i> Ativar Marca d'Água nos PDFs
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Quando ativado, os PDFs dos orçamentos terão uma marca d'água de fundo
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="show_validity_as_text" 
                                           name="show_validity_as_text" 
                                           value="1"
                                           {{ old('show_validity_as_text', $settings->show_validity_as_text) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_validity_as_text">
                                        <i class="bi bi-calendar-check"></i> Exibir Validade por Extenso
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Quando ativado, exibe "Validade: X dias" em vez da data específica no PDF
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="border" 
                                           name="border" 
                                           value="1"
                                           {{ old('border', $settings->border) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="border">
                                        <i class="bi bi-border-all"></i> Ativar Bordas nas Tabelas do Orçammento em PDF
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Quando ativado, as tabelas do PDF terão bordas visíveis nos cabeçalhos da empresa e do cliente
                                </small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Configurações de Tema -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-1">
                                    <i class="bi bi-palette"></i> Configurações de Tema
                                </h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-brush"></i> Selecionar Tema
                                </label>
                                <div class="theme-color-picker">
                                    <div class="color-options d-flex flex-wrap gap-2 mb-2">
                                        @php
                                            $currentTheme = App\Http\Controllers\SettingsController::getCurrentTheme();
                                        @endphp
                                        <div class="color-option {{ $currentTheme == 'blue' ? 'active' : '' }}" data-theme="blue" data-color="#0d6efd" title="Azul (Padrão)">
                                            <div class="color-circle" style="background-color: #0d6efd;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'green' ? 'active' : '' }}" data-theme="green" data-color="#198754" title="Verde">
                                            <div class="color-circle" style="background-color: #198754;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'teal' ? 'active' : '' }}" data-theme="teal" data-color="#20c997" title="Teal">
                                            <div class="color-circle" style="background-color: #20c997;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'cyan' ? 'active' : '' }}" data-theme="cyan" data-color="#0dcaf0" title="Ciano">
                                            <div class="color-circle" style="background-color: #0dcaf0;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'purple' ? 'active' : '' }}" data-theme="purple" data-color="#6f42c1" title="Roxo">
                                            <div class="color-circle" style="background-color: #6f42c1;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'indigo' ? 'active' : '' }}" data-theme="indigo" data-color="#6610f2" title="Índigo">
                                            <div class="color-circle" style="background-color: #6610f2;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'pink' ? 'active' : '' }}" data-theme="pink" data-color="#e83e8c" title="Rosa">
                                            <div class="color-circle" style="background-color: #e83e8c;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'red' ? 'active' : '' }}" data-theme="red" data-color="#dc3545" title="Vermelho">
                                            <div class="color-circle" style="background-color: #dc3545;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'orange' ? 'active' : '' }}" data-theme="orange" data-color="#fd7e14" title="Laranja">
                                            <div class="color-circle" style="background-color: #fd7e14;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'yellow' ? 'active' : '' }}" data-theme="yellow" data-color="#ffc107" title="Amarelo">
                                            <div class="color-circle" style="background-color: #ffc107;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'lime' ? 'active' : '' }}" data-theme="lime" data-color="#32cd32" title="Lima">
                                            <div class="color-circle" style="background-color: #32cd32;"></div>
                                        </div>
                                        <div class="color-option {{ $currentTheme == 'dark' ? 'active' : '' }}" data-theme="dark" data-color="#495057" title="Escuro">
                                            <div class="color-circle" style="background-color: #495057;"></div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="theme_selector" name="theme" value="{{ $currentTheme }}">
                                </div>
                                <small class="form-text text-muted">
                                    Clique em uma cor para selecionar o tema
                                </small>
                            </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Botões de Ação -->
                        <div class="row">
                            <div class="col-12 pb-4 px-4">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-0">
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salvar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeSelector = document.getElementById('theme_selector');
    const colorOptions = document.querySelectorAll('.color-option');
    
    // Aplicar tema atual na inicialização
    const currentTheme = '{{ session("theme", "blue") }}';
    if (currentTheme !== 'blue') {
        document.documentElement.setAttribute('data-theme', currentTheme);
    }
    
    // Adicionar event listeners para as opções de cor
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            
            console.log('Clicou no tema:', theme); // Debug
            
            // Remover classe active de todas as opções
            colorOptions.forEach(opt => opt.classList.remove('active'));
            
            // Adicionar classe active à opção selecionada
            this.classList.add('active');
            
            // Atualizar o valor do input hidden
            if (themeSelector) {
                themeSelector.value = theme;
            }
            
            // Aplicar tema imediatamente
            applyTheme(theme);
        });
    });
    
    function applyTheme(theme) {
        console.log('Aplicando tema:', theme); // Debug
        
        // Fazer requisição AJAX
        fetch('{{ route("settings.theme") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ theme: theme })
        })
        .then(response => {
            console.log('Resposta recebida:', response.status); // Debug
            return response.json();
        })
        .then(data => {
            console.log('Dados da resposta:', data); // Debug
            if (data.success) {
                // Aplicar tema imediatamente
                if (theme === 'blue') {
                    document.documentElement.removeAttribute('data-theme');
                } else {
                    document.documentElement.setAttribute('data-theme', theme);
                }
                
                console.log('Tema aplicado com sucesso! Atributo data-theme:', document.documentElement.getAttribute('data-theme'));
            } else {
                console.error('Erro ao aplicar tema - resposta não foi sucesso');
            }
        })
        .catch(error => {
            console.error('Erro na requisição AJAX:', error);
        });
    }
    


});
</script>
@endsection
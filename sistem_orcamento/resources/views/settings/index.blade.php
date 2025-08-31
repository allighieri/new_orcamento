@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
<div class="container mx-auto">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-gear"></i> Configurações da Empresa
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Configurações de Orçamento -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="bi bi-file-earmark-text"></i> Configurações de Orçamento
                                </h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
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

                            <div class="col-md-6">
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

                        <!-- Botões de Ação -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Salvar Configurações
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de Informações -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informações
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Dias de Validade</h6>
                            <p class="small text-muted mb-3">
                                Define por quantos dias o orçamento será válido após sua criação. 
                                Esta data aparecerá no PDF e na visualização do orçamento.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Previsão de Entrega</h6>
                            <p class="small text-muted mb-3">
                                Define a previsão padrão de entrega dos produtos/serviços. 
                                Esta informação será exibida no orçamento.
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary">Marca d'Água</h6>
                            <p class="small text-muted mb-0">
                                Quando ativada, adiciona uma marca d'água sutil no fundo dos PDFs dos orçamentos, 
                                ajudando a identificar documentos da sua empresa.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
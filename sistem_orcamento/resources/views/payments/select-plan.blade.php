@extends('layouts.app')

@section('title', 'Escolher Plano')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Escolher Plano de Assinatura</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Escolher Plano</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="text-center mb-4">
                <h2 class="text-dark">Escolha o Plano Ideal para Sua Empresa</h2>
                <p class="text-muted">Selecione o plano que melhor atende às necessidades do seu negócio</p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        @foreach($plans as $plan)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card pricing-card {{ $plan->name === 'Prata' ? 'border-primary' : '' }}">
                @if($plan->name === 'Prata')
                <div class="ribbon ribbon-top-right"><span class="bg-primary">Mais Popular</span></div>
                @endif
                
                <div class="card-body text-center">
                    <div class="pricing-header">
                        <h3 class="fw-bold text-uppercase">{{ $plan->name }}</h3>
                        <div class="pricing-price">
                            <span class="price-currency">R$</span>
                            <span class="price-amount">{{ number_format($plan->price, 2, ',', '.') }}</span>
                            <span class="price-period">/mês</span>
                        </div>
                    </div>
                    
                    <div class="pricing-features mt-4">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                <strong>{{ $plan->budget_limit }}</strong> orçamentos por mês
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Suporte por email
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Relatórios básicos
                            </li>
                            @if($plan->name !== 'Bronze')
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Integração com Google Drive
                            </li>
                            @endif
                            @if($plan->name === 'Ouro')
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Suporte prioritário
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Relatórios avançados
                            </li>
                            @endif
                        </ul>
                    </div>
                    
                    <div class="pricing-action mt-4">
                        <a href="{{ route('payments.checkout', $plan) }}" 
                           class="btn {{ $plan->name === 'Prata' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg w-100">
                            Escolher {{ $plan->name }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($company->subscription && $company->subscription->status === 'active')
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="alert alert-info text-center">
                <i class="mdi mdi-information me-2"></i>
                <strong>Plano Atual:</strong> {{ $company->subscription->plan->name }} 
                (válido até {{ $company->subscription->ends_at->format('d/m/Y') }})
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.pricing-card {
    border: 2px solid #e3e6f0;
    border-radius: 15px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.pricing-card.border-primary {
    border-color: #5a67d8;
    box-shadow: 0 5px 15px rgba(90, 103, 216, 0.2);
}

.pricing-header {
    padding: 1rem 0;
}

.pricing-price {
    margin: 1rem 0;
}

.price-currency {
    font-size: 1.2rem;
    vertical-align: top;
    color: #6c757d;
}

.price-amount {
    font-size: 3rem;
    font-weight: bold;
    color: #2d3748;
}

.price-period {
    font-size: 1rem;
    color: #6c757d;
}

.pricing-features ul li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.pricing-features ul li:last-child {
    border-bottom: none;
}

.ribbon {
    position: absolute;
    right: -5px;
    top: -5px;
    z-index: 1;
    overflow: hidden;
    width: 75px;
    height: 75px;
    text-align: right;
}

.ribbon span {
    font-size: 10px;
    font-weight: bold;
    color: #fff;
    text-transform: uppercase;
    text-align: center;
    line-height: 20px;
    transform: rotate(45deg);
    -webkit-transform: rotate(45deg);
    width: 100px;
    display: block;
    position: absolute;
    top: 19px;
    right: -21px;
}

.ribbon span.bg-primary {
    background: linear-gradient(45deg, #5a67d8, #667eea);
}
</style>
@endsection
@extends('layouts.app')

@section('title', 'Escolher Plano')

@section('content')
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>
                    <i class="bi bi-credit-card"></i> 
                    Escolher Plano de Assinatura
                </h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i> Selecione o plano que melhor atende às necessidades do seu negócio
                </p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto row">

    <div class="row justify-content-center">
        @foreach($plans as $plan)
        @php
            $isCurrentPlan = $currentSubscription && $currentSubscription->plan_id == $plan->id;
        @endphp
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card pricing-card {{ $plan->name === 'Prata' ? 'border-primary' : '' }} {{ $isCurrentPlan ? 'current-plan border-success' : '' }}">
                @if($isCurrentPlan)
                <div class="ribbon ribbon-top-right"><span class="bg-success">Plano Atual</span></div>
                @elseif($plan->name === 'Prata')
                <div class="ribbon ribbon-top-right"><span class="bg-primary">Mais Popular</span></div>
                @endif
                
                <div class="card-body text-center">
                    <div class="pricing-header">
                        <h3 class="fw-bold text-uppercase">{{ $plan->name }}</h3>
                        <div class="pricing-price">
                             <span class="monthly-price" style="display: none;">
                                 <span class="price-currency">R$</span>
                                 <span class="price-amount">{{ number_format($plan->monthly_price, 2, ',', '.') }}</span>
                                 <span class="price-period">/mês</span>
                             </span>
                             <span class="yearly-price">
                                 <span class="price-currency">R$</span>
                                 <span class="price-amount">{{ number_format($plan->annual_price, 2, ',', '.') }}</span>
                                 <span class="price-period">/mês</span>
                             </span>
                         </div>
                         <div class="yearly-info text-muted small">
                             Valor para o plano anual
                         </div>
                         <div class="yearly-savings text-success small">
                            Economize R$ {{ number_format(($plan->monthly_price * 12) - ($plan->annual_price * 12), 2, ',', '.') }} por ano!
                        </div>
                    </div>
                    
                    <div class="pricing-features mt-4">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                @if($plan->name === 'Ouro')
                                    <strong>Orçamentos ilimitados</strong>
                                @else
                                    <strong>{{ $plan->budget_limit }}</strong> orçamentos por mês
                                @endif
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Suporte por email
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Envio de e-mails integrado com Gmail
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check text-success me-2"></i>
                                Templates de Email personalizados
                            </li>
                        </ul>
                    </div>
                    
                    <div class="pricing-action mt-4">
                        @if($isCurrentPlan)
                             <div class="text-center">
                                 <button class="btn btn-success px-3 py-2 w-100 mb-2" disabled>
                                     <i class="bi bi-check-circle me-2"></i>
                                     <div class="fw-bold">
                                         @if($currentSubscription->ends_at)
                                             Válido até {{ $currentSubscription->ends_at->format('d/m/Y') }}
                                         @else
                                             Plano Ativo
                                         @endif
                                     </div>
                                 </button>
                             </div>
                        @else
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('payments.checkout', $plan->id) }}?period=monthly" class="btn btn-outline-primary px-3 py-2 w-100 mb-2">
                                    <div class="small">Mensal</div>
                                    <div class="fw-bold">R$ {{ number_format($plan->monthly_price, 2, ',', '.') }}</div>
                                </a>
                                <a href="{{ route('payments.checkout', $plan->id) }}?period=yearly" class="btn btn-primary px-3 py-2 w-100 mb-2">
                                    <div class="small">Anual</div>
                                    <div class="fw-bold">R$ {{ number_format($plan->annual_price, 2, ',', '.') }}</div>
                                </a>
                            </div>
                        @endif
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

.pricing-card.current-plan {
    border-color: #28a745;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
}

.pricing-card.current-plan .card-body {
    position: relative;
}

.pricing-card.current-plan::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #28a745, #20c997);
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

.ribbon span.bg-success {
    background: linear-gradient(45deg, #28a745, #20c997);
}
</style>


@endsection
@extends('layouts.app')

@section('title', 'Escolher Plano')

@section('content')
@php
    $plans = \App\Models\Plan::where('active', true)->orderBy('monthly_price')->get();
@endphp
<div class="container mx-auto row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-left mb-4">
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
                $features = json_decode($plan->description, true) ?? [];
                $yearlySavings = ($plan->monthly_price * 12) - $plan->yearly_price;
            @endphp
            <div class="col-md-4 mb-4">
                 <div class="card plan-card h-100 d-flex flex-column">
                     <div class="card-body text-center flex-grow-1">
                         <h3 class="plan-title fs-1 fw-bold">{{ strtoupper($plan->name) }}</h3>
                         <div class="price-section mb-5">
                             <div class="monthly-price">
                                 <span class="price-currency">R$</span>
                                 <span class="price-value">{{ number_format($plan->yearly_price, 2, ',', '.') }}</span>
                                 <span class="price-period">/ano</span>
                             </div>
                             <div class="annual-info mt-2">
                                 <small class="text-muted">Valor para o plano anual</small>
                                 <div class="annual-savings text-success">
                                     <strong>Economize R$ {{ number_format($yearlySavings, 2, ',', '.') }} por ano!</strong>
                                 </div>
                             </div>
                         </div>
                         
                         <ul class="plan-features-list list-unstyled mb-4 text-start">
                              @foreach($features as $feature)
                              <li class="mb-2">
                                  <i class="bi bi-check-circle text-success me-2"></i>
                                  {{ $feature }}
                              </li>
                              @endforeach
                          </ul>
                     </div>
                     
                     <div class="card-footer bg-transparent border-0 p-3">
                          <div class="pricing-buttons d-flex gap-2">
                              <button class="btn btn-outline-primary flex-fill monthly-btn" 
                                      data-plan="{{ $plan->slug }}" data-cycle="monthly" data-price="{{ $plan->monthly_price }}">
                                  Mensal<br>
                                  <strong>R$ {{ number_format($plan->monthly_price, 2, ',', '.') }}</strong>
                              </button>
                              <button class="btn btn-primary flex-fill annual-btn" 
                                      data-plan="{{ $plan->slug }}" data-cycle="yearly" data-price="{{ $plan->yearly_price }}">
                                  Anual<br>
                                  <strong>R$ {{ number_format($plan->yearly_price, 2, ',', '.') }}</strong>
                              </button>
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
    border-width: 2px;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
}

.pricing-card.current-plan .card-body {
    position: relative;
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para os botões de plano
    document.querySelectorAll('.monthly-btn, .annual-btn').forEach(button => {
        button.addEventListener('click', function() {
            const planSlug = this.getAttribute('data-plan');
            const cycle = this.getAttribute('data-cycle');
            
            // Redirecionar para a página de checkout com os parâmetros
            window.location.href = `/payments/checkout/${planSlug}?period=${cycle}`;
        });
    });
});
</script>
@endpush
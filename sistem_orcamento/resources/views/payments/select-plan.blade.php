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
                $isCurrentPlan = $currentSubscription && $currentSubscription->plan_id == $plan->id;
                $isPrata = strtolower($plan->name) === 'prata';
            @endphp
            <div class="col-md-4 mb-4">
                 <div class="card plan-card h-100 d-flex flex-column {{ $isCurrentPlan ? 'current-plan' : '' }} position-relative">
                     @if($isPrata && !$isCurrentPlan)
                         <div class="ribbon">
                             <span class="bg-warning text-dark">MELHOR ESCOLHA</span>
                         </div>
                     @endif
                     @if($isCurrentPlan)
                         <div class="ribbon">
                             <span class="bg-success">PLANO ATUAL</span>
                         </div>
                     @endif
                     <div class="card-body text-center flex-grow-1">
                         <h3 class="plan-title fs-1 fw-bold">{{ strtoupper($plan->name) }}</h3>
                         <div class="price-section mb-5">
                             <div class="monthly-price">
                                 <span class="price-currency">R$</span>
                                 <span class="price-value">{{ number_format($plan->yearly_price, 2, ',', '.') }}</span>
                                 <span class="price-period">/ano</span>
                             </div>
                             <div class="yearly-info mt-2">
                                 <small class="text-muted">Valor para o plano anual</small>
                                 <div class="yearly-savings text-success">
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
                              @php
                                  $isCurrentMonthly = $isCurrentPlan && $currentSubscription->billing_cycle === 'monthly';
                                  $isCurrentYearly = $isCurrentPlan && $currentSubscription->billing_cycle === 'yearly';
                              @endphp
                              <button class="btn {{ $isCurrentMonthly ? 'btn-success' : 'btn-outline-primary' }} flex-fill monthly-btn" 
                                      data-plan="{{ $plan->slug }}" data-cycle="monthly" data-price="{{ $plan->monthly_price }}"
                                      {{ $isCurrentMonthly ? 'disabled' : '' }}>
                                  @if($isCurrentMonthly)
                                  <i class="bi bi-check-circle-fill"></i> Mensal<br>
                                        <strong>R$ {{ number_format($plan->monthly_price, 2, ',', '.') }}</strong>
                                  @else
                                        <strong>R$ {{ number_format($plan->monthly_price, 2, ',', '.') }}</strong>
                                  @endif
                              </button>
                              <button class="btn {{ $isCurrentYearly ? 'btn-success' : 'btn-primary' }} flex-fill yearly-btn" 
                                      data-plan="{{ $plan->slug }}" data-cycle="yearly" data-price="{{ $plan->yearly_price }}"
                                      {{ $isCurrentYearly ? 'disabled' : '' }}>
                                       @if($isCurrentYearly)
                                            <i class="bi bi-check-circle-fill"></i> Anual<br>
                                            <strong>R$ {{ number_format($plan->yearly_price, 2, ',', '.') }}</strong>
                                        @else
                                            Anual<br>
                                            <strong>R$ {{ number_format($plan->yearly_price, 2, ',', '.') }}</strong>
                                        @endif
                              </button>
                          </div>
                     </div>
                 </div>
             </div>
            @endforeach
        </div>

    @if($currentSubscription && $currentSubscription->status === 'active')
    <div class="row justify-content-center mt-4">
        <div class="col-lg-12">
            <div class="alert alert-info text-center">
                <i class="mdi mdi-information me-2"></i>
                <strong>Plano Atual:</strong> {{ $currentSubscription->plan->name }} 
                (válido até {{ $currentSubscription->ends_at->format('d/m/Y') }})
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

.plan-card.current-plan {
    border-color: #28a745;
    border-width: 1px;
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
}

.plan-card {
    position: relative;
    overflow: hidden;
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
    width: 90px;
    height: 90px;
    text-align: right;
}

.ribbon span {
    font-size: 11px;
    font-weight: bold;
    color: #fff;
    text-transform: uppercase;
    text-align: center;
    line-height: 22px;
    transform: rotate(45deg);
    -webkit-transform: rotate(45deg);
    width: 120px;
    display: block;
    position: absolute;
    top: 22px;
    right: -25px;
}

.ribbon span.bg-primary {
    background: linear-gradient(45deg, #5a67d8, #667eea);
}

.ribbon span.bg-success {
    background: linear-gradient(45deg, #28a745, #20c997);
}

.ribbon span.bg-warning {
    background: linear-gradient(45deg, #ffc107, #ffca2c);
    color: #212529 !important;
}
</style>


@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para os botões de plano
    document.querySelectorAll('.monthly-btn, .yearly-btn').forEach(button => {
        button.addEventListener('click', function() {
            const planSlug = this.getAttribute('data-plan');
            const cycle = this.getAttribute('data-cycle');
            
            // Redirecionar para a página de checkout com os parâmetros
            window.location.href = `/payments/checkout/${planSlug}?period=${cycle}`;
        });
    });
    
    // Configurar escuta de eventos de pagamento em tempo real
    if (window.Echo) {
        window.Echo.channel('payments')
            .listen('.payment.confirmed', (e) => {
                console.log('Evento de pagamento recebido em select-plan:', e);
                
                // Mostrar notificação de sucesso
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pagamento Confirmado!',
                        text: 'Seu plano foi ativado com sucesso.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
                
                // Redirecionar para payments após confirmação
                setTimeout(() => {
                    window.location.href = '{{ route("payments.index") }}';
                }, 2000);
            });
    }
});
</script>
@endpush
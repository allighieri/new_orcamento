@extends('layouts.app')

@section('title', 'Comprar Orçamentos Extras')

@section('content')
<div class="container mx-auto mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                   <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Adicionar Orçamentos Extras
                    </h5>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-body">
                    @php
                        // Verificar se usuário está logado e tem empresa
                        $user = auth()->user();
                        $company = $user ? $user->company : null;
                        $activeSubscription = $company ? $company->activeSubscription() : null;
                        $currentPlan = $activeSubscription ? $activeSubscription->plan : null;
                        $usageControl = null;
                        
                        if ($activeSubscription && $currentPlan) {
                            $usageControl = \App\Models\UsageControl::getOrCreateForCurrentMonth(
                                $company->id,
                                $activeSubscription->id,
                                $currentPlan->budget_limit ?? 0
                            );
                        }
                        
                        // Garantir que sempre temos um valor válido
                        $planPrice = ($currentPlan && $currentPlan->monthly_price > 0) ? $currentPlan->monthly_price : 29.90;
                        $planName = $currentPlan ? $currentPlan->name : 'Nenhum plano ativo';
                        $budgetsLimit = $currentPlan ? $currentPlan->budget_limit : 0;
                        $budgetsUsed = $usageControl ? $usageControl->budgets_used : 0;
                        $extraBudgetsPurchased = $usageControl ? $usageControl->extra_budgets_purchased : 0;
                        $budgetsRemaining = $usageControl ? $usageControl->getRemainingBudgets() : 0;
                        $totalLimit = $budgetsLimit + $extraBudgetsPurchased;
                    @endphp

                    @if($currentPlan)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Seu plano atual:</strong> {{ $planName }}
                            <br>
                            <strong>Orçamentos usados este mês:</strong> {{ $budgetsUsed }}
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Você não possui um plano ativo.
                            <br>
                            Para comprar orçamentos extras, você precisa primeiro assinar um plano.
                            <br>
                            <a href="{{ route('payments.select-plan') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-circle"></i>
                                Escolher Plano
                            </a>
                        </div>
                    @endif

                    @if($currentPlan)
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-success mb-3">
                                            <i class="bi bi-file-earmark-plus"></i>
                                        </div>
                                        <h5 class="card-title">{{ $budgetsLimit }} Orçamentos Extras</h5>
                                        <p class="card-text text-muted">
                                            Adicione <strong>{{ $budgetsLimit }} orçamentos extras</strong> ao seu limite atual
                                        </p>
                                        <div class="h3 text-success mb-3">
                                            R$ {{ number_format($planPrice, 2, ',', '.') }}
                                            <small class="text-muted d-block fs-6">Por {{ $budgetsLimit }} orçamentos extras</small>
                                        </div>
                                        <ul class="list-unstyled text-start">
                                            <li><i class="bi bi-check-circle text-success"></i> +{{ $budgetsLimit }} orçamentos adicionais</li>
                                            <li><i class="bi bi-check-circle text-success"></i> Válido até o final do mês atual</li>
                                            <li><i class="bi bi-check-circle text-success"></i> Mantém seu plano atual</li>
                                            <li><i class="bi bi-check-circle text-success"></i> Ativação imediata</li>
                                        </ul>
                                        
                                        <div>
                                            <div class="d-grid">
                                                <a href="{{ route('payments.extra-budgets-checkout') }}" class="btn btn-primary btn-lg">
                                                    <i class="bi bi-plus-circle"></i>
                                                    Adicionar Orçamentos Extras
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
                            <div class="col-6 text-center">
                                <div class="card border-warning h-100">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-warning">
                                            <i class="bi bi-arrow-up-circle"></i>
                                            Ou faça upgrade do seu plano
                                        </h5>
                                        <p class="card-text">
                                            Tenha mais orçamentos mensais e recursos adicionais com um plano superior.
                                        </p>
                                        <a href="{{ route('payments.change-plan') }}" class="btn btn-warning">
                                            <i class="bi bi-arrow-up"></i>
                                            Ver Planos Disponíveis
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.display-4 {
    font-size: 3rem;
}

@media (max-width: 768px) {
    .col-md-6:first-child {
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar escuta de eventos de pagamento em tempo real
    if (window.Echo) {
        window.Echo.channel('payments')
            .listen('.payment.confirmed', (e) => {
                console.log('Evento de pagamento recebido em extra-budgets:', e);
                
                // Mostrar notificação de sucesso
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pagamento Confirmado!',
                        text: 'Seus orçamentos extras foram adicionados com sucesso.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
                
                // Recarregar a página após um pequeno delay para mostrar os novos orçamentos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            });
    }
});
</script>
@endpush
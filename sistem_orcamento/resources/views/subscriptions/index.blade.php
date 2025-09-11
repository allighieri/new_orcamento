@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Gerenciar Plano da Empresa</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Plano Atual -->
                    @if($currentSubscription)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Plano Atual</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><strong>{{ $currentSubscription->plan->name }}</strong></h6>
                                                <p class="text-muted mb-1">Status: 
                                                    <span class="badge bg-{{ $currentSubscription->status === 'active' ? 'success' : ($currentSubscription->status === 'cancelled' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($currentSubscription->status) }}
                                                    </span>
                                                </p>
                                                <p class="text-muted mb-1">Ciclo: {{ $currentSubscription->billing_cycle === 'monthly' ? 'Mensal' : 'Anual' }}</p>
                                                <p class="text-muted mb-1">Preço: R$ {{ number_format($currentSubscription->amount_paid, 2, ',', '.') }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="text-muted mb-1">Início: {{ $currentSubscription->start_date->format('d/m/Y') }}</p>
                                                <p class="text-muted mb-1">Vencimento: {{ $currentSubscription->end_date->format('d/m/Y') }}</p>
                                                @if($currentSubscription->isInGracePeriod())
                                                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Em período de carência</p>
                                                @endif
                                                @if($currentSubscription->isExpired())
                                                    <p class="text-danger"><i class="fas fa-times-circle"></i> Expirado</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            @if($currentSubscription->status === 'active')
                                                <form action="{{ route('subscriptions.cancel') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Tem certeza que deseja cancelar sua assinatura?')">
                                                        <i class="fas fa-times"></i> Cancelar Assinatura
                                                    </button>
                                                </form>
                                            @elseif($currentSubscription->status === 'cancelled' && !$currentSubscription->isExpired())
                                                <form action="{{ route('subscriptions.reactivate') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Reativar Assinatura
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Sua empresa não possui um plano ativo. Escolha um plano abaixo para começar.
                        </div>
                    @endif

                    <!-- Planos Disponíveis -->
                    <h5 class="mb-3">Planos Disponíveis</h5>
                    <div class="row">
                        @foreach($plans as $plan)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 {{ $currentSubscription && $currentSubscription->plan_id === $plan->id ? 'border-primary' : '' }}">
                                    <div class="card-header text-center {{ $plan->slug === 'ouro' ? 'bg-warning' : ($plan->slug === 'prata' ? 'bg-secondary text-white' : 'bg-light') }}">
                                        <h5 class="mb-0">{{ $plan->name }}</h5>
                                        @if($currentSubscription && $currentSubscription->plan_id === $plan->id)
                                            <small class="badge bg-primary">Plano Atual</small>
                                        @endif
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="text-center mb-3">
                                            <h6 class="text-muted">Mensal</h6>
                                            <h4 class="text-primary">R$ {{ number_format($plan->monthly_price, 2, ',', '.') }}</h4>
                                            <h6 class="text-muted mt-2">Anual</h6>
                                            <h4 class="text-success">R$ {{ number_format($plan->annual_price, 2, ',', '.') }}</h4>
                                            <small class="text-muted">Economia de {{ number_format((($plan->monthly_price * 12) - $plan->annual_price), 2, ',', '.') }}</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <h6>Recursos:</h6>
                                            <ul class="list-unstyled">
                                                @if($plan->features)
                                                    @foreach($plan->features as $feature)
                                                        <li><i class="fas fa-check text-success"></i> {{ $feature }}</li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            @if(!$currentSubscription || $currentSubscription->plan_id !== $plan->id)
                                                <div class="row">
                                                    <div class="col-6">
                                                        <form action="{{ route('subscriptions.store') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                                            <input type="hidden" name="billing_cycle" value="monthly">
                                                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                                                Mensal
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="col-6">
                                                        <form action="{{ route('subscriptions.store') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                                            <input type="hidden" name="billing_cycle" value="annual">
                                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                                Anual
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @else
                                                <button class="btn btn-secondary w-100" disabled>
                                                    <i class="fas fa-check"></i> Plano Atual
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
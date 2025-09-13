<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;

class TestPaymentRedirect extends Command
{
    protected $signature = 'test:payment-redirect {user_id=1}';
    protected $description = 'Testar redirecionamento da rota de pagamentos';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário ID {$userId} não encontrado!");
            return 1;
        }
        
        if (!$user->company) {
            $this->error("Usuário não possui empresa associada!");
            return 1;
        }
        
        $this->info("Testando redirecionamento para usuário: {$user->name}");
        $this->info("Empresa: {$user->company->name}");
        
        // Verificar se tem subscription ativa
        $activeSubscription = $user->company->activeSubscription();
        
        if ($activeSubscription) {
            $this->info("✅ Usuário tem subscription ativa (ID: {$activeSubscription->id})");
            $this->info("Deveria acessar a página de pagamentos normalmente.");
        } else {
            $this->info("❌ Usuário NÃO tem subscription ativa");
            $this->info("Deveria ser redirecionado para /payments/plans");
        }
        
        return 0;
    }
}
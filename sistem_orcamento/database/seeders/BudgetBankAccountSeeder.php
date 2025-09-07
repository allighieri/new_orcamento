<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\BankAccount;
use Faker\Factory as Faker;

class BudgetBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        // Verificar se existem orçamentos
        $budgets = Budget::all();
        if ($budgets->isEmpty()) {
            $this->command->warn('Nenhum orçamento encontrado. Execute o BudgetSeeder primeiro.');
            return;
        }
        
        // Verificar se existem contas bancárias
        $bankAccounts = BankAccount::all();
        if ($bankAccounts->isEmpty()) {
            $this->command->warn('Nenhuma conta bancária encontrada. Execute o BankAccountSeeder primeiro.');
            return;
        }
        
        foreach ($budgets as $budget) {
            // Buscar contas bancárias da mesma empresa do orçamento
            $companyBankAccounts = BankAccount::where('company_id', $budget->company_id)->get();
            
            if ($companyBankAccounts->isNotEmpty()) {
                // Associar 1-2 contas bancárias por orçamento
                $numContas = $faker->numberBetween(1, min(2, $companyBankAccounts->count()));
                $contasSelecionadas = $companyBankAccounts->random($numContas);
                
                foreach ($contasSelecionadas as $index => $conta) {
                    $budget->bankAccounts()->attach($conta->id, [
                        'order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        $this->command->info('Associações entre orçamentos e contas bancárias criadas com sucesso!');
    }
}

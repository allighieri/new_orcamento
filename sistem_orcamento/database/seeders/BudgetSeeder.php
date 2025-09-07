<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $clients = Client::with('company')->get();
        
        if ($clients->isEmpty()) {
            $this->command->warn('Nenhum cliente encontrado. Execute ClientSeeder primeiro.');
            return;
        }
        
        // Criar 50 orçamentos para testar paginação (25 por empresa)
        $budgetCount = 0;
        $targetBudgets = 50;
        
        while ($budgetCount < $targetBudgets) {
            foreach ($clients as $client) {
                if ($budgetCount >= $targetBudgets) break;
                
                $company = $client->company;
                $companyId = $company->id;
                
                // Gerar número do orçamento no formato 0000-0/YYYY
                $year = $faker->dateTimeBetween('-2 years', 'now')->format('Y');
                $existingBudgets = Budget::where('company_id', $companyId)
                                       ->whereYear('created_at', $year)
                                       ->count();
                $nextNumber = $existingBudgets + 1;
                $budgetNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . '-' . $companyId . '/' . $year;
                
                // Datas do orçamento
                $issueDate = $faker->dateTimeBetween('-2 years', 'now');
                $deliveryDate = $faker->dateTimeBetween($issueDate, '+3 months');
                $validUntil = $faker->dateTimeBetween($issueDate, '+2 months');
                
                // Status possíveis (conforme enum da migração)
                $statuses = ['Pendente', 'Enviado', 'Em negociação', 'Aprovado', 'Expirado', 'Concluído'];
                $status = $faker->randomElement($statuses);
                
                // Criar orçamento
                $budget = Budget::create([
                    'number' => $budgetNumber,
                    'client_id' => $client->id,
                    'company_id' => $companyId,
                    'issue_date' => $issueDate,
                    'delivery_date' => $deliveryDate,
                    'valid_until' => $validUntil,
                    'status' => $status,
                    'total_discount' => $faker->randomFloat(2, 0, 500),
                    'observations' => $faker->optional(0.7)->paragraph(),
                    'total_amount' => 0, // Será calculado depois
                    'final_amount' => 0, // Será calculado depois
                    'created_at' => $issueDate,
                    'updated_at' => $issueDate
                ]);
                
                // Buscar produtos da empresa
                $products = Product::where('company_id', $companyId)->get();
                
                if ($products->isNotEmpty()) {
                    // Criar entre 2 a 8 itens por orçamento
                    $itemCount = $faker->numberBetween(2, 8);
                    $totalAmount = 0;
                    
                    for ($i = 0; $i < $itemCount; $i++) {
                        $product = $faker->randomElement($products);
                        $quantity = $faker->numberBetween(1, 10);
                        $unitPrice = $product->price * $faker->randomFloat(2, 0.8, 1.3); // Variação de preço
                        $itemTotal = $quantity * $unitPrice;
                        $totalAmount += $itemTotal;
                        
                        BudgetItem::create([
                            'budget_id' => $budget->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total_price' => $itemTotal,
                            'description' => $product->description
                        ]);
                    }
                    
                    // Atualizar totais do orçamento
                    $finalAmount = $totalAmount - $budget->total_discount;
                    $budget->update([
                        'total_amount' => $totalAmount,
                        'final_amount' => max(0, $finalAmount)
                    ]);
                }
                
                $budgetCount++;
                
                // Criar alguns orçamentos adicionais para clientes aleatórios
                if ($budgetCount < $targetBudgets && $faker->boolean(30)) {
                    // 30% de chance de criar outro orçamento para o mesmo cliente
                    continue;
                }
            }
        }
        
        $this->command->info("Criados {$budgetCount} orçamentos para testar paginação.");
    }
}
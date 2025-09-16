<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $clients = Client::all();
        
        if ($clients->isEmpty()) {
            $this->command->warn('Nenhum cliente encontrado. Execute ClientSeeder primeiro.');
            return;
        }
        
        foreach ($clients as $client) {
            // Criar 2-3 contatos por cliente
            $contactCount = $faker->numberBetween(2, 3);
            
            for ($i = 0; $i < $contactCount; $i++) {
                Contact::create([
                    'name' => $faker->name,
                    'cpf' => $faker->cpf(false), // CPF sem formatação
                    'phone' =>  '(61) 99253-0902',//$faker->cellphone(false), // Celular sem formatação
                    'email' =>  'agenciaolhardigital@gmail.com',//$faker->unique()->safeEmail,
                    'company_id' => $client->company_id,
                    'client_id' => $client->id
                ]);
            }
        }
    }
}
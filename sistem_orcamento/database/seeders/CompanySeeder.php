<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        // Empresa 1: Construtora
        Company::create([
            'fantasy_name' => 'KARAOKÊ CLUBE',
            'corporate_name' => 'KARAOKÊ CLUBE LTDA',
            'document_number' => '975.026.851-20',
            'state_registration' => '70680200',
            'phone' => '(61) 99253-0902',
            'email' => 'agenciaolhardigital@gmail.com',
            'address' => 'QMSW 2',
            'address_line_2' => 'CONJ D LOJA 13A',
            'district' => 'SUDOESTE',
            'city' => 'BRASÍLIA',
            'state' => 'DF',
            'cep' => '70.680-200'
        ]);
        
        
    }
}
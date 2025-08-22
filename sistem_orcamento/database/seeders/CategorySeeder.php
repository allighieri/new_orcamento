<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = \App\Models\Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Execute CompanySeeder primeiro.');
            return;
        }
        
        $categories = [
            [
                'name' => 'Eletrônicos',
                'slug' => 'eletronicos',
                'description' => 'Produtos eletrônicos em geral'
            ],
            [
                'name' => 'Informática',
                'slug' => 'informatica',
                'description' => 'Produtos de informática e tecnologia'
            ],
            [
                'name' => 'Móveis',
                'slug' => 'moveis',
                'description' => 'Móveis e decoração'
            ]
        ];

        // Criar categorias para cada empresa
        foreach ($companies as $company) {
            foreach ($categories as $categoryData) {
                $categoryData['company_id'] = $company->id;
                $categoryData['slug'] = $categoryData['slug'] . '-' . $company->id; // Tornar slug único por empresa
                Category::create($categoryData);
            }
        }
    }
}
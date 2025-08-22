<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
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
        
        $products = [
            [
                'name' => 'Notebook Dell Inspiron',
                'slug' => 'notebook-dell-inspiron',
                'price' => 2500.00,
                'description' => 'Notebook Dell Inspiron 15 com processador Intel i5',
                'category_slug_base' => 'informatica'
            ],
            [
                'name' => 'Mouse Logitech',
                'slug' => 'mouse-logitech',
                'price' => 89.90,
                'description' => 'Mouse óptico Logitech com fio',
                'category_slug_base' => 'informatica'
            ],
            [
                'name' => 'Teclado Mecânico',
                'slug' => 'teclado-mecanico',
                'price' => 299.90,
                'description' => 'Teclado mecânico RGB para games',
                'category_slug_base' => 'informatica'
            ],
            [
                'name' => 'Smartphone Samsung',
                'slug' => 'smartphone-samsung',
                'price' => 1200.00,
                'description' => 'Smartphone Samsung Galaxy A54',
                'category_slug_base' => 'eletronicos'
            ],
            [
                'name' => 'Mesa de Escritório',
                'slug' => 'mesa-escritorio',
                'price' => 450.00,
                'description' => 'Mesa de escritório em MDF com gavetas',
                'category_slug_base' => 'moveis'
            ]
        ];

        // Criar produtos para cada empresa
        foreach ($companies as $company) {
            $categories = Category::where('company_id', $company->id)->get();
            
            foreach ($products as $productData) {
                $categorySlug = $productData['category_slug_base'] . '-' . $company->id;
                $category = $categories->where('slug', $categorySlug)->first();
                
                if ($category) {
                    $productData['company_id'] = $company->id;
                    $productData['category_id'] = $category->id;
                    $productData['slug'] = $productData['slug'] . '-' . $company->id; // Tornar slug único por empresa
                    unset($productData['category_slug_base']); // Remove campo auxiliar
                    
                    Product::create($productData);
                }
            }
        }
    }
}
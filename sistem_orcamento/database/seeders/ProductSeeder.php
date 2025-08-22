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
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->warn('Nenhuma categoria encontrada. Execute CategorySeeder primeiro.');
            return;
        }

        $products = [
            [
                'name' => 'Notebook Dell Inspiron',
                'slug' => 'notebook-dell-inspiron',
                'price' => 2500.00,
                'description' => 'Notebook Dell Inspiron 15 com processador Intel i5',
                'category_id' => $categories->where('slug', 'informatica')->first()->id ?? $categories->first()->id
            ],
            [
                'name' => 'Mouse Logitech',
                'slug' => 'mouse-logitech',
                'price' => 89.90,
                'description' => 'Mouse óptico Logitech com fio',
                'category_id' => $categories->where('slug', 'informatica')->first()->id ?? $categories->first()->id
            ],
            [
                'name' => 'Teclado Mecânico',
                'slug' => 'teclado-mecanico',
                'price' => 299.90,
                'description' => 'Teclado mecânico RGB para games',
                'category_id' => $categories->where('slug', 'informatica')->first()->id ?? $categories->first()->id
            ],
            [
                'name' => 'Smartphone Samsung',
                'slug' => 'smartphone-samsung',
                'price' => 1200.00,
                'description' => 'Smartphone Samsung Galaxy A54',
                'category_id' => $categories->where('slug', 'eletronicos')->first()->id ?? $categories->first()->id
            ],
            [
                'name' => 'Mesa de Escritório',
                'slug' => 'mesa-escritorio',
                'price' => 450.00,
                'description' => 'Mesa de escritório em MDF com gavetas',
                'category_id' => $categories->where('slug', 'moveis')->first()->id ?? $categories->first()->id
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
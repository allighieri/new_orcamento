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

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}
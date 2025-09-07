<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $companies = \App\Models\Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Execute CompanySeeder primeiro.');
            return;
        }
        
        foreach ($companies as $index => $company) {
            if ($index === 0) {
                // Categorias para Construtora Alvorada
                $constructionCategories = [
                    [
                        'name' => 'Materiais de Construção',
                        'slug' => 'materiais-construcao',
                        'description' => 'Cimento, areia, brita, tijolos e outros materiais básicos'
                    ],
                    [
                        'name' => 'Acabamentos',
                        'slug' => 'acabamentos',
                        'description' => 'Pisos, azulejos, tintas e materiais de acabamento'
                    ],
                    [
                        'name' => 'Instalações Hidráulicas',
                        'slug' => 'instalacoes-hidraulicas',
                        'description' => 'Tubos, conexões, registros e acessórios hidráulicos'
                    ],
                    [
                        'name' => 'Instalações Elétricas',
                        'slug' => 'instalacoes-eletricas',
                        'description' => 'Fios, cabos, disjuntores e materiais elétricos'
                    ],
                    [
                        'name' => 'Ferragens',
                        'slug' => 'ferragens',
                        'description' => 'Parafusos, pregos, dobradiças e ferragens em geral'
                    ],
                    [
                        'name' => 'Mão de Obra',
                        'slug' => 'mao-de-obra',
                        'description' => 'Serviços de pedreiros, eletricistas, encanadores'
                    ]
                ];
                
                foreach ($constructionCategories as $categoryData) {
                    $categoryData['company_id'] = $company->id;
                    Category::create($categoryData);
                }
            } else {
                // Categorias para TechnoInfo
                $techCategories = [
                    [
                        'name' => 'Computadores e Notebooks',
                        'slug' => 'computadores-notebooks',
                        'description' => 'Desktops, notebooks e workstations'
                    ],
                    [
                        'name' => 'Periféricos',
                        'slug' => 'perifericos',
                        'description' => 'Mouses, teclados, monitores e impressoras'
                    ],
                    [
                        'name' => 'Componentes',
                        'slug' => 'componentes',
                        'description' => 'Processadores, memórias, placas de vídeo'
                    ],
                    [
                        'name' => 'Redes e Conectividade',
                        'slug' => 'redes-conectividade',
                        'description' => 'Roteadores, switches, cabos de rede'
                    ],
                    [
                        'name' => 'Software e Licenças',
                        'slug' => 'software-licencas',
                        'description' => 'Sistemas operacionais, antivírus, aplicativos'
                    ],
                    [
                        'name' => 'Serviços Técnicos',
                        'slug' => 'servicos-tecnicos',
                        'description' => 'Manutenção, instalação e suporte técnico'
                    ]
                ];
                
                foreach ($techCategories as $categoryData) {
                    $categoryData['company_id'] = $company->id;
                    Category::create($categoryData);
                }
            }
        }
    }
}
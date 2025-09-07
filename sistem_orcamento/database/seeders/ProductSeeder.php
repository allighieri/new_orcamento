<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->warn('Nenhuma categoria encontrada. Execute CategorySeeder primeiro.');
            return;
        }
        
        // Produtos para Construtora Alvorada
        $constructionProducts = [
            // Materiais de Construção
            'materiais-construcao' => [
                ['name' => 'Cimento Portland CP II-E-32', 'description' => 'Saco de cimento Portland 50kg', 'price' => 32.50],
                ['name' => 'Areia Média Lavada', 'description' => 'Metro cúbico de areia média lavada', 'price' => 45.00],
                ['name' => 'Brita 1', 'description' => 'Metro cúbico de brita número 1', 'price' => 55.00],
                ['name' => 'Tijolo Cerâmico 6 Furos', 'description' => 'Milheiro de tijolos cerâmicos 6 furos', 'price' => 850.00],
                ['name' => 'Bloco de Concreto 14x19x39', 'description' => 'Bloco estrutural de concreto', 'price' => 4.20]
            ],
            // Acabamentos
            'acabamentos' => [
                ['name' => 'Piso Cerâmico 60x60', 'description' => 'Piso cerâmico esmaltado 60x60cm', 'price' => 28.90],
                ['name' => 'Azulejo Branco 20x30', 'description' => 'Azulejo branco brilhante 20x30cm', 'price' => 12.50],
                ['name' => 'Tinta Acrílica Premium', 'description' => 'Tinta acrílica premium 18L branca', 'price' => 185.00],
                ['name' => 'Rejunte Flexível', 'description' => 'Rejunte flexível 1kg cinza', 'price' => 15.80],
                ['name' => 'Argamassa Colante AC-I', 'description' => 'Argamassa colante 20kg', 'price' => 22.90]
            ],
            // Instalações Hidráulicas
            'instalacoes-hidraulicas' => [
                ['name' => 'Tubo PVC 100mm', 'description' => 'Tubo PVC esgoto 100mm x 6m', 'price' => 45.60],
                ['name' => 'Registro de Gaveta 3/4"', 'description' => 'Registro de gaveta bronze 3/4 polegada', 'price' => 35.90],
                ['name' => 'Joelho PVC 90° 32mm', 'description' => 'Joelho PVC soldável 90° 32mm', 'price' => 2.80],
                ['name' => 'Caixa D\'Água 1000L', 'description' => 'Caixa d\'água polietileno 1000 litros', 'price' => 320.00],
                ['name' => 'Torneira de Mesa Cromada', 'description' => 'Torneira de mesa cromada 1/4 volta', 'price' => 89.90]
            ],
            // Instalações Elétricas
            'instalacoes-eletricas' => [
                ['name' => 'Fio Flexível 2,5mm²', 'description' => 'Fio flexível 2,5mm² rolo 100m', 'price' => 180.00],
                ['name' => 'Disjuntor Bipolar 25A', 'description' => 'Disjuntor termomagnético bipolar 25A', 'price' => 28.50],
                ['name' => 'Tomada 2P+T 10A', 'description' => 'Tomada 2P+T 10A com placa branca', 'price' => 12.90],
                ['name' => 'Interruptor Simples', 'description' => 'Interruptor simples com placa branca', 'price' => 8.50],
                ['name' => 'Eletroduto Flexível 3/4"', 'description' => 'Eletroduto flexível corrugado 3/4" rolo 50m', 'price' => 85.00]
            ],
            // Ferragens
            'ferragens' => [
                ['name' => 'Parafuso Fenda 6x60mm', 'description' => 'Parafuso fenda zincado 6x60mm caixa 100un', 'price' => 25.00],
                ['name' => 'Prego 18x27', 'description' => 'Prego comum 18x27 kg', 'price' => 8.90],
                ['name' => 'Dobradiça 3.1/2"', 'description' => 'Dobradiça ferro oxidado 3.1/2 polegadas', 'price' => 15.60],
                ['name' => 'Fechadura Externa', 'description' => 'Fechadura externa cromada com cilindro', 'price' => 125.00],
                ['name' => 'Chave Philips', 'description' => 'Chave philips cabo isolado nº2', 'price' => 18.50]
            ],
            // Mão de Obra
            'mao-de-obra' => [
                ['name' => 'Serviço de Pedreiro', 'description' => 'Diária de pedreiro especializado', 'price' => 180.00],
                ['name' => 'Serviço de Eletricista', 'description' => 'Diária de eletricista residencial', 'price' => 200.00],
                ['name' => 'Serviço de Encanador', 'description' => 'Diária de encanador especializado', 'price' => 190.00],
                ['name' => 'Serviço de Pintor', 'description' => 'Diária de pintor profissional', 'price' => 150.00],
                ['name' => 'Serviço de Azulejista', 'description' => 'Diária de azulejista especializado', 'price' => 170.00]
            ]
        ];
        
        // Produtos para TechnoInfo
        $techProducts = [
            // Computadores e Notebooks
            'computadores-notebooks' => [
                ['name' => 'Notebook Dell Inspiron 15 3000', 'description' => 'Intel Core i5, 8GB RAM, SSD 256GB', 'price' => 2850.00],
                ['name' => 'Desktop Gamer RGB', 'description' => 'AMD Ryzen 5, 16GB RAM, RTX 3060, SSD 500GB', 'price' => 4200.00],
                ['name' => 'Notebook Lenovo IdeaPad', 'description' => 'Intel Core i3, 4GB RAM, HD 1TB', 'price' => 1950.00],
                ['name' => 'All-in-One HP 24"', 'description' => 'Intel Core i5, 8GB RAM, SSD 256GB, Tela 24"', 'price' => 3200.00],
                ['name' => 'Workstation Dell Precision', 'description' => 'Intel Xeon, 32GB RAM, Quadro P2000, SSD 1TB', 'price' => 8500.00]
            ],
            // Periféricos
            'perifericos' => [
                ['name' => 'Mouse Gamer Logitech G502', 'description' => 'Mouse gamer RGB 25.600 DPI', 'price' => 285.00],
                ['name' => 'Teclado Mecânico Redragon', 'description' => 'Teclado mecânico RGB switch blue', 'price' => 189.90],
                ['name' => 'Monitor LG 24" Full HD', 'description' => 'Monitor LED 24 polegadas 1920x1080', 'price' => 650.00],
                ['name' => 'Impressora HP LaserJet', 'description' => 'Impressora laser monocromática', 'price' => 890.00],
                ['name' => 'Webcam Logitech C920', 'description' => 'Webcam Full HD 1080p com microfone', 'price' => 320.00]
            ],
            // Componentes
            'componentes' => [
                ['name' => 'Processador AMD Ryzen 7', 'description' => 'AMD Ryzen 7 5700X 8-core 3.4GHz', 'price' => 1450.00],
                ['name' => 'Memória RAM 16GB DDR4', 'description' => 'Memória RAM 16GB DDR4 3200MHz', 'price' => 380.00],
                ['name' => 'Placa de Vídeo RTX 4060', 'description' => 'NVIDIA GeForce RTX 4060 8GB GDDR6', 'price' => 2200.00],
                ['name' => 'SSD NVMe 1TB', 'description' => 'SSD NVMe M.2 1TB 3500MB/s', 'price' => 420.00],
                ['name' => 'Placa Mãe ASUS B550', 'description' => 'Placa mãe ASUS B550M-A WiFi AM4', 'price' => 650.00]
            ],
            // Redes e Conectividade
            'redes-conectividade' => [
                ['name' => 'Roteador TP-Link Archer C6', 'description' => 'Roteador wireless dual band AC1200', 'price' => 180.00],
                ['name' => 'Switch 24 Portas Gigabit', 'description' => 'Switch gerenciável 24 portas gigabit', 'price' => 850.00],
                ['name' => 'Cabo de Rede Cat6 305m', 'description' => 'Cabo UTP Cat6 caixa 305 metros', 'price' => 420.00],
                ['name' => 'Access Point Ubiquiti', 'description' => 'Access Point UniFi AC Lite', 'price' => 380.00],
                ['name' => 'Patch Panel 24 Portas', 'description' => 'Patch panel Cat6 24 portas 19"', 'price' => 120.00]
            ],
            // Software e Licenças
            'software-licencas' => [
                ['name' => 'Windows 11 Pro', 'description' => 'Licença Windows 11 Professional', 'price' => 890.00],
                ['name' => 'Office 365 Business', 'description' => 'Microsoft Office 365 Business anual', 'price' => 420.00],
                ['name' => 'Antivírus Kaspersky', 'description' => 'Kaspersky Total Security 5 dispositivos', 'price' => 180.00],
                ['name' => 'Adobe Creative Suite', 'description' => 'Adobe Creative Cloud anual', 'price' => 1200.00],
                ['name' => 'AutoCAD LT', 'description' => 'Licença AutoCAD LT anual', 'price' => 1850.00]
            ],
            // Serviços Técnicos
            'servicos-tecnicos' => [
                ['name' => 'Formatação Completa', 'description' => 'Formatação e instalação do sistema operacional', 'price' => 80.00],
                ['name' => 'Manutenção Preventiva', 'description' => 'Limpeza interna e externa do computador', 'price' => 60.00],
                ['name' => 'Instalação de Rede', 'description' => 'Instalação e configuração de rede cabeada', 'price' => 150.00],
                ['name' => 'Recuperação de Dados', 'description' => 'Recuperação de dados de HD danificado', 'price' => 250.00],
                ['name' => 'Suporte Técnico Remoto', 'description' => 'Suporte técnico remoto por hora', 'price' => 45.00]
            ]
        ];
        
        foreach ($categories as $category) {
            $companyId = $category->company_id;
            $categorySlug = $category->slug;
            
            // Determinar quais produtos usar baseado na empresa
            if ($companyId == 1) { // Construtora Alvorada
                $productsToCreate = $constructionProducts[$categorySlug] ?? [];
            } else { // TechnoInfo
                $productsToCreate = $techProducts[$categorySlug] ?? [];
            }
            
            foreach ($productsToCreate as $productData) {
                Product::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'category_id' => $category->id,
                    'company_id' => $companyId
                ]);
            }
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'budget_counter',
                'value' => '0',
                'description' => 'Contador automático para numeração de orçamentos'
            ],
            [
                'key' => 'company_name',
                'value' => 'Minha Empresa',
                'description' => 'Nome da empresa padrão'
            ],
            [
                'key' => 'budget_validity_days',
                'value' => '30',
                'description' => 'Dias de validade padrão para orçamentos'
            ]
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

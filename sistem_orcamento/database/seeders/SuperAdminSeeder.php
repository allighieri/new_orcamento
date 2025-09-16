<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se já existe um super_admin
        if (!User::where('role', 'super_admin')->exists()) {
            $user =User::create([
                'name' => 'Super Administrador',
                'email' => 'agenciaolhardigital@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('@Gencia5859262'),
                'role' => 'super_admin',
                'active' => 1, // Super admin deve estar ativo
            ]);


            $this->command->info('Super Admin criado com sucesso!');
            $this->command->info('Email: agenciaolhardigital@gmail.com');
            $this->command->info('Senha: @Gencia5859262');
        } else {
            $this->command->info('Super Admin já existe no sistema.');
        }
    }
}

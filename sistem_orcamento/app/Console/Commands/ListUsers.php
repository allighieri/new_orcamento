<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'list:users';
    protected $description = 'Listar usuários e suas empresas';

    public function handle()
    {
        $users = User::with('company')->get();
        
        $this->info('Listando usuários:');
        $this->line('');
        
        foreach ($users as $user) {
            $companyName = $user->company ? $user->company->name : 'Nenhuma empresa';
            $this->info("User ID: {$user->id} - Nome: {$user->name} - Company: {$companyName}");
        }
        
        return 0;
    }
}
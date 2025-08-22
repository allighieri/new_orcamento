<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Product;
use App\Models\Company;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    /**
     * Dashboard padrão para todos os usuários
     */
    public function index()
    {
        $user = Auth::user();
        
        // Estatísticas básicas
        $stats = [
            'budgets_count' => Budget::count(),
            'clients_count' => Client::count(),
            'products_count' => Product::count(),
        ];
        
        return view('dashboard', compact('user', 'stats'));
    }

    /**
     * Define os middlewares para este controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }
}
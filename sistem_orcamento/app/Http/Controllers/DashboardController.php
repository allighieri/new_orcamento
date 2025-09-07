<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Product;
use App\Models\Company;
use App\Models\Category;
use App\Models\Contact;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    /**
     * Dashboard padrão para todos os usuários
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = session('tenant_company_id');
        
        // Estatísticas básicas filtradas por empresa (ou todas para super_admin)
        if ($companyId) {
            $stats = [
                'budgets_count' => Budget::where('company_id', $companyId)->count(),
                'clients_count' => Client::where('company_id', $companyId)->count(),
                'products_count' => Product::where('company_id', $companyId)->count(),
                'categories_count' => Category::where('company_id', $companyId)->count(),
                'contacts_count' => Contact::where('company_id', $companyId)->count(),
            ];
            
            // Query base para orçamentos filtrados por empresa
            $budgetsQuery = Budget::with(['client', 'company', 'pdfFiles'])
                ->where('company_id', $companyId)
                ->orderBy('created_at', 'desc');
        } else {
            // Super admin vê dados de todas as empresas
            $stats = [
                'budgets_count' => Budget::count(),
                'clients_count' => Client::count(),
                'products_count' => Product::count(),
                'categories_count' => Category::count(),
                'contacts_count' => Contact::count(),
            ];
            
            // Query base para orçamentos de todas as empresas
            $budgetsQuery = Budget::with(['client', 'company', 'pdfFiles'])
                ->orderBy('created_at', 'desc');
        }
        
        // Aplicar pesquisa se fornecida
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $budgetsQuery->where(function($q) use ($searchTerm) {
                $q->where('number', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('client', function($clientQuery) use ($searchTerm) {
                      $clientQuery->where('corporate_name', 'LIKE', '%' . $searchTerm . '%')
                                  ->orWhere('fantasy_name', 'LIKE', '%' . $searchTerm . '%');
                  });
            });
        }
        
        // Aplicar paginação (5 itens por página na dashboard)
        $recentBudgets = $budgetsQuery->paginate(10)->appends($request->query());
        
        return view('dashboard', compact('user', 'stats', 'recentBudgets'));
    }

    /**
     * Define os middlewares para este controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('tenant'),
        ];
    }
}
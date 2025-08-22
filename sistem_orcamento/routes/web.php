<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

// Rotas de Autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Redireciona a raiz para o dashboard
Route::get('/', [DashboardController::class, 'index'])->middleware('auth')->name('home');

// Dashboard Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Rotas para Empresas (apenas admin e super_admin podem criar/editar/deletar)
    Route::resource('companies', CompanyController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('companies', CompanyController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('role:admin,super_admin');

    // Rotas para Categorias (apenas admin e super_admin podem criar/editar/deletar)
    Route::resource('categories', CategoryController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('categories', CategoryController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('role:admin,super_admin');
    Route::get('categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');

    // Rotas para Produtos (apenas admin e super_admin podem criar/editar/deletar)
    Route::resource('products', ProductController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('role:admin,super_admin');

    // Rotas para Clientes (todos os usuários autenticados)
    Route::resource('clients', ClientController::class);

    // Rotas para Contatos (todos os usuários autenticados)
    Route::resource('contacts', ContactController::class);

    // Rotas para Orçamentos (todos os usuários autenticados)
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{budget}/pdf', [BudgetController::class, 'generatePdf'])->name('budgets.pdf');
});

// Rotas exclusivas para Super Admin
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    // Aqui podem ser adicionadas rotas específicas para super admin
    // Como gerenciamento de usuários, configurações do sistema, etc.
});

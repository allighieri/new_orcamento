<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

// Rotas de Autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



// Redireciona a raiz para o dashboard
Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'user.active'])->name('home');

// Dashboard Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'user.active'])->name('dashboard');

// Rotas protegidas por autenticação
Route::middleware(['auth', 'user.active', 'tenant'])->group(function () {
    // Rotas para Empresas - visualização para todos, criação/edição apenas para admin e super_admin
    Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
    
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
    });
    
    Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });
});

// Rotas com filtro de tenant (empresa) e verificação de empresa obrigatória
Route::middleware(['auth', 'user.active', 'tenant', 'require.company'])->group(function () {
    // Rotas para Categorias - visualização para todos, criação/edição apenas para admin e super_admin
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    });
    
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');
    
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Rotas para Produtos - visualização para todos, criação/edição apenas para admin e super_admin
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });
    
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Rotas para Clientes (todos os usuários autenticados)
    Route::resource('clients', ClientController::class);

    // Rotas para Contatos (todos os usuários autenticados)
    Route::resource('contacts', ContactController::class);

    // Rotas para Orçamentos (todos os usuários autenticados)
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{budget}/pdf', [BudgetController::class, 'generatePdf'])->name('budgets.pdf');
});

// Rotas para gerenciamento de usuários (Super Admin e Admin)
Route::middleware(['auth', 'user.active', 'role:admin,super_admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
});

// Rotas exclusivas para Super Admin
Route::middleware(['auth', 'user.active', 'role:super_admin'])->group(function () {
    // Aqui podem ser adicionadas rotas específicas para super admin
    // Como configurações do sistema, etc.
});

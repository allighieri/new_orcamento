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
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\BankAccountController;

// Rotas de Autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



// Redireciona a raiz para o dashboard
Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'user.active', 'tenant'])->name('home');

// Dashboard Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'user.active', 'tenant'])->name('dashboard');

// Rotas protegidas por autenticação
Route::middleware(['auth', 'user.active', 'tenant'])->group(function () {
    // Rotas para Empresas - apenas para admin e super_admin
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });
});

// Rotas com filtro de tenant (empresa) e verificação de empresa obrigatória
Route::middleware(['auth', 'user.active', 'tenant', 'require.company'])->group(function () {
    // Rotas para Categorias - todos os usuários autenticados podem criar, editar e excluir
    Route::resource('categories', CategoryController::class);
    Route::get('categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');
    Route::get('api/categories/by-company', [CategoryController::class, 'getCategoriesByCompany'])->name('categories.by-company');

    // Rotas para Produtos - todos os usuários autenticados podem criar, editar e excluir
    Route::resource('products', ProductController::class);
    Route::get('api/products/by-company', [ProductController::class, 'getProductsByCompany'])->name('products.by-company');

    // Rotas para Clientes (todos os usuários autenticados)
    Route::resource('clients', ClientController::class);
    Route::get('api/clients/by-company', [ClientController::class, 'getClientsByCompany'])->name('clients.by-company');

    // Rotas para Contatos (todos os usuários autenticados)
    Route::resource('contacts', ContactController::class);

    // Rotas para Orçamentos (todos os usuários autenticados)
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{budget}/pdf', [BudgetController::class, 'generatePdf'])->name('budgets.pdf');
    Route::get('budgets/{budget}/whatsapp', [BudgetController::class, 'sendWhatsApp'])->name('budgets.whatsapp');
    Route::post('budgets/{budget}/whatsapp-contact', [BudgetController::class, 'sendWhatsAppToContact'])->name('budgets.whatsapp-contact');
    Route::get('budgets/{budget}/email', [BudgetController::class, 'sendEmail'])->name('budgets.email');
    Route::post('budgets/{budget}/email-contact', [BudgetController::class, 'sendEmailToContact'])->name('budgets.email-contact');
    
    // Rotas para Templates de Email (todos os usuários autenticados)
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    
    // Rotas para Métodos de Pagamento - admin e super_admin podem gerenciar
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::resource('payment-methods', PaymentMethodController::class);
    });
    
    // Rotas para Contas Bancárias - todos os usuários autenticados podem gerenciar
    Route::resource('bank-accounts', BankAccountController::class);
    
    // Rota para autocomplete de bancos
    Route::get('compes/autocomplete', [App\Http\Controllers\CompeController::class, 'autocomplete'])->name('compes.autocomplete');
    
    // Rotas para autenticação com Google
    Route::get('/google/settings', function() { return view('google.settings'); })->name('google.settings');
    Route::get('/google/auth', [App\Http\Controllers\GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
    Route::get('/google/callback', [App\Http\Controllers\GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/google/status', [App\Http\Controllers\GoogleAuthController::class, 'checkStatus'])->name('google.status');
    Route::post('/google/disconnect', [App\Http\Controllers\GoogleAuthController::class, 'disconnect'])->name('google.disconnect');
});

// Rota de atualização de status do orçamento (sem middlewares extras que podem interferir)
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::put('budgets/{budget}/status', [BudgetController::class, 'updateStatus'])->name('budgets.update-status');
});

// Rota para perfil do usuário (todos os usuários autenticados)
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::get('profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');
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

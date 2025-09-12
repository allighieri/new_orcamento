<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PaymentOptionMethodController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\SettingsController;


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
    
    // Rotas para Formulários de Contato (todos os usuários autenticados)
    Route::resource('contact-forms', ContactFormController::class);

    // Rotas para Orçamentos (todos os usuários autenticados)
    // Aplicar middleware de verificação de limites apenas na criação e armazenamento
    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('budgets/create', [BudgetController::class, 'create'])->middleware('plan.limits')->name('budgets.create');
    Route::post('budgets', [BudgetController::class, 'store'])->middleware('plan.limits')->name('budgets.store');
    Route::get('budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::get('budgets/{budget}/edit', [BudgetController::class, 'edit'])->name('budgets.edit');
    Route::put('budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    Route::get('budgets/{budget}/pdf', [BudgetController::class, 'generatePdf'])->name('budgets.pdf');
    Route::get('budgets/{budget}/serve-pdf/{filename}', [BudgetController::class, 'servePdf'])->name('budgets.serve-pdf');
    Route::get('budgets/{budget}/whatsapp', [BudgetController::class, 'sendWhatsApp'])->name('budgets.whatsapp');
    Route::post('budgets/{budget}/whatsapp-contact', [BudgetController::class, 'sendWhatsAppToContact'])->name('budgets.whatsapp-contact');
    Route::get('budgets/{budget}/email', [BudgetController::class, 'sendEmail'])->name('budgets.email');
    Route::post('budgets/{budget}/email-contact', [BudgetController::class, 'sendEmailToContact'])->name('budgets.email-contact');
    
    // Rotas para Templates de Email (todos os usuários autenticados)
    Route::get('email-templates/builder', [EmailTemplateController::class, 'builder'])->name('email-templates.builder');
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    
    // Rotas para Métodos de Pagamento - admin e super_admin podem gerenciar
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::resource('payment-methods', PaymentMethodController::class);
    });
    
    // Rotas para Contas Bancárias - todos os usuários autenticados podem gerenciar
    Route::resource('bank-accounts', BankAccountController::class);
    
    // Rotas para Configurações - admin e super_admin podem gerenciar
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
    });
    
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
    // Rotas para Métodos de Opção de Pagamento - apenas super_admin
    Route::resource('payment-option-methods', PaymentOptionMethodController::class);
    Route::patch('payment-option-methods/{paymentOptionMethod}/toggle-active', [PaymentOptionMethodController::class, 'toggleActive'])->name('payment-option-methods.toggle-active');
});



// Rotas para Sistema de Pagamentos
Route::middleware(['auth', 'user.active', 'tenant', 'require.company'])->group(function () {
    Route::get('payments/plans', [App\Http\Controllers\PaymentController::class, 'selectPlan'])->name('payments.select-plan');
Route::get('payments/change-plan', [App\Http\Controllers\PaymentController::class, 'changePlan'])->name('payments.change-plan');
Route::get('payments/checkout/{plan}', [App\Http\Controllers\PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('payments/pix/{plan}', [App\Http\Controllers\PaymentController::class, 'processPixPayment'])->name('payments.process-pix');
    // Rota de teste para debug
    Route::any('payments/test-debug', function() {
        \Log::info('TESTE DE ROTA - Requisição chegou', [
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'data' => request()->all()
        ]);
        return response()->json(['success' => true, 'message' => 'Teste OK']);
    })->name('payments.test-debug');
    Route::post('payments/credit-card/{plan}', [App\Http\Controllers\PaymentController::class, 'processCreditCardPayment'])->name('payments.process-credit-card');
    Route::get('payments/{payment}/check-status', [App\Http\Controllers\PaymentController::class, 'checkPaymentStatus'])->name('payments.check-status');
    Route::get('payments/check-status/{payment}', [App\Http\Controllers\PaymentController::class, 'checkPaymentStatus'])->name('payments.ajax-check-status');
    Route::get('payments/{payment}/details', [App\Http\Controllers\PaymentController::class, 'details'])->name('payments.details');
    Route::get('payments/{payment}/status', [App\Http\Controllers\PaymentController::class, 'status'])->name('payments.status');
Route::get('payments/{payment}/invoice', [App\Http\Controllers\PaymentController::class, 'invoice'])->name('payments.invoice');
Route::get('payments/{payment}/receipt', [App\Http\Controllers\PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('payments/extra-budgets', [App\Http\Controllers\PaymentController::class, 'extraBudgets'])->name('payments.extra-budgets');
Route::get('payments/extra-budgets/checkout', [App\Http\Controllers\PaymentController::class, 'extraBudgetsCheckout'])->name('payments.extra-budgets-checkout');
Route::post('payments/extra-budgets/purchase', [App\Http\Controllers\PaymentController::class, 'purchaseExtraBudgets'])->name('payments.purchase-extra-budgets');
    Route::get('payments/{payment}/pix', [App\Http\Controllers\PaymentController::class, 'pixPayment'])->name('payments.pix-payment');
    Route::get('payments', [App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
});

// Webhook do Asaas (sem middleware de autenticação)
Route::post('webhook/asaas', [App\Http\Controllers\WebhookController::class, 'handleAsaasWebhook'])->name('webhook.asaas');

// Rota de teste para debug (sem middleware)
Route::any('payments/test-debug-public', function() {
    \Log::info('TESTE DE ROTA PÚBLICA - Requisição chegou', [
        'method' => request()->method(),
        'url' => request()->fullUrl(),
        'data' => request()->all()
    ]);
    return response()->json(['success' => true, 'message' => 'Teste OK']);
})->name('payments.test-debug-public');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BudgetController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Rotas para Empresas
Route::resource('companies', CompanyController::class);

// Rotas para Categorias
Route::resource('categories', CategoryController::class);
Route::get('categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');

// Rotas para Produtos
Route::resource('products', ProductController::class);

// Rotas para Clientes
Route::resource('clients', ClientController::class);

// Rotas para Contatos
Route::resource('contacts', ContactController::class);

// Rotas para Orçamentos
Route::resource('budgets', BudgetController::class);
Route::get('budgets/{budget}/pdf', [BudgetController::class, 'generatePdf'])->name('budgets.pdf');

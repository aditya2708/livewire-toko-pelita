<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Products;
use App\Livewire\Categories;
use App\Livewire\Packages;

use App\Livewire\Transactions;

use App\Livewire\SalesReport;
use App\Livewire\AprioriAnalysis;
use App\Livewire\SavedAnalyses;
use App\Livewire\AnalysisDetail;

use App\Http\Controllers\AprioriReportController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/products', Products::class)->name('products');
    Route::get('/categories', Categories::class)->name('categories');
    Route::get('/packages', Packages::class)->name('packages');
    
   
    
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/sales-report', SalesReport::class)->name('sales.report');
    

    // Main Apriori Analysis page
    Route::get('/apriori', AprioriAnalysis::class)->name('apriori');

     // New and updated routes for Saved Analyses
     Route::prefix('apriori/saved')->group(function () {
          Route::get('/', SavedAnalyses::class)->name('apriori.saved');
          Route::get('/analysis/{analysisId}', AnalysisDetail::class)->name('apriori.detail');
          Route::get('/{analysis}/pdf', [SavedAnalyses::class, 'generatePDF'])->name('apriori.pdf');
          Route::get('/{analysis}/excel', [SavedAnalyses::class, 'generateExcel'])->name('apriori.excel');

          // New routes for Apriori Analysis components
    Route::get('/itemset', App\Livewire\ItemsetTable::class)->name('apriori.itemset');
    Route::get('/restock', App\Livewire\RestockRecommendationTable::class)->name('apriori.restock');
    Route::get('/rules', App\Livewire\AssociationRuleTable::class)->name('apriori.rules');
      });

    
});
Route::get('/', function () {
    return redirect()->route('login');
});


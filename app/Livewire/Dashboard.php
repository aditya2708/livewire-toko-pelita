<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\AprioriAnalysis;
use Carbon\Carbon;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $startDate;
    public $endDate;

    public $totalSales = 0;
    public $totalTransactions = 0;
    public $averageTransactionValue = 0;
    public $topProducts = [];
    public $lowStockProducts = [];
    public $topAssociationRules = [];
    public $totalPackages = 0; // New property for total packages
    
    // New metrics
    public $totalProducts = 0;
    public $totalCategories = 0;

    public function mount()
    {
        $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadDashboardData();
    }

    public function updatedStartDate()
    {
        $this->loadDashboardData();
    }

    public function updatedEndDate()
    {
        $this->loadDashboardData();
    }

    private function loadDashboardData()
    {
        $this->calculateMetrics();
        $this->loadTopProducts();
        $this->loadLowStockProducts();
        $this->loadTopAssociationRules();
        $this->loadTotalProducts(); // New method
        $this->loadTotalCategories(); // New method
        $this->loadTotalPackages();
    }

    private function calculateMetrics()
    {
        $transactions = Transaction::whereBetween('transaction_date', [$this->startDate, $this->endDate]);
        
        $this->totalSales = $transactions->sum('total_amount');
        $this->totalTransactions = $transactions->count();
    }

    private function loadTotalPackages()
    {
        $this->totalPackages = Package::count();
    }

    private function loadTopProducts()
    {
        $this->topProducts = Product::select('products.id', 'products.name')
            ->selectRaw('SUM(transaction_items.quantity) as total_sold')
            ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereBetween('transactions.transaction_date', [$this->startDate, $this->endDate])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();
    }

    private function loadLowStockProducts()
    {
        $this->lowStockProducts = Product::where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity')
            ->get();
    }

    private function loadTopAssociationRules()
    {
        // Get the latest Apriori analysis within the selected date range
        $latestAnalysis = AprioriAnalysis::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->latest()
            ->first();

        if ($latestAnalysis) {
            // Fetch top 5 association rules based on lift
            $this->topAssociationRules = $latestAnalysis->rules()
                ->orderByDesc('lift')
                ->take(5)
                ->get()
                ->map(function ($rule) {
                    return [
                        'antecedents' => $rule->antecedents,
                        'consequents' => $rule->consequents,
                        'confidence' => $rule->confidence,
                        'lift' => $rule->lift
                    ];
                });
        } else {
            $this->topAssociationRules = [];
        }
    }

    // New method to load total products count
    private function loadTotalProducts()
    {
        $this->totalProducts = Product::count();
    }

    // New method to load total categories count
    private function loadTotalCategories()
    {
        $this->totalCategories = Category::count();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
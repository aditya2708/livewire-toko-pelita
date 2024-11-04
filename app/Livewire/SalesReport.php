<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class SalesReport extends Component
{
    use WithPagination;

    // Date range filters
    public $startDate;
    public $endDate;

    // Other filters
    public $selectedProduct = '';
    public $selectedCategory = '';

    // Sorting
    public $sortField = 'transaction_date';
    public $sortDirection = 'desc';

    // Summary data
    public $totalSales = 0;
    public $totalTransactions = 0;
    public $averageTransactionValue = 0;

    // Flag to check if report has been generated
    public $reportGenerated = false;

    // Top products
    public $topProducts = [];

    protected $rules = [
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
    ];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();
    }

    public function updatedStartDate()
    {
        $this->validateOnly('startDate');
        $this->reportGenerated = false;
    }

    public function updatedEndDate()
    {
        $this->validateOnly('endDate');
        $this->reportGenerated = false;
    }

    public function generateReport()
    {
        $this->validate();

        $this->fetchReportData();
        $this->calculateSummary();
        $this->fetchTopProducts();

        $this->reportGenerated = true;
    }

    private function fetchReportData()
    {
        $query = Transaction::query()
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->when($this->selectedProduct, function ($query) {
                $query->whereHas('items', function ($q) {
                    $q->where('product_id', $this->selectedProduct)
                      ->orWhereHas('package.items', function ($pq) {
                          $pq->where('product_id', $this->selectedProduct);
                      });
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('items.product', function ($q) {
                    $q->where('category_id', $this->selectedCategory);
                });
            });

        $this->totalTransactions = $query->count();
        $this->totalSales = $query->sum('total_amount');
    }

    private function calculateSummary()
    {
        $this->averageTransactionValue = $this->totalTransactions > 0 
            ? $this->totalSales / $this->totalTransactions 
            : 0;
    }

    private function fetchTopProducts()
{
    $topProducts = collect();

    Transaction::whereBetween('transaction_date', [$this->startDate, $this->endDate])
        ->with('items.product', 'items.package.items.product')
        ->get()
        ->each(function ($transaction) use (&$topProducts) {
            $transaction->items->each(function ($item) use (&$topProducts) {
                $products = $item->getAllProducts();
                $products->each(function ($productData) use (&$topProducts) {
                    if (!isset($productData['product']) || !$productData['product']) {
                        return; // Skip this iteration if product is null
                    }
                    $product = $productData['product'];
                    $quantity = $productData['quantity'];
                    
                    $topProducts[$product->id] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'total_sold' => ($topProducts[$product->id]['total_sold'] ?? 0) + $quantity
                    ];
                });
            });
        });

    $this->topProducts = $topProducts->sortByDesc('total_sold')->take(10)->values();
}
    public function getReportDataProperty()
    {
        if (!$this->reportGenerated) {
            return collect();
        }

        return Transaction::query()
            ->with(['items.product', 'items.package.items.product', 'user'])
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->when($this->selectedProduct, function ($query) {
                $query->whereHas('items', function ($q) {
                    $q->where('product_id', $this->selectedProduct)
                      ->orWhereHas('package.items', function ($pq) {
                          $pq->where('product_id', $this->selectedProduct);
                      });
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('items.product', function ($q) {
                    $q->where('category_id', $this->selectedCategory);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function exportPDF()
    {
        $data = [
            'reportData' => $this->reportData,
            'totalSales' => $this->totalSales,
            'totalTransactions' => $this->totalTransactions,
            'averageTransactionValue' => $this->averageTransactionValue,
            'startDate' => Carbon::parse($this->startDate)->isoFormat('D MMMM Y'),
            'endDate' => Carbon::parse($this->endDate)->isoFormat('D MMMM Y'),
            'topProducts' => $this->topProducts,
        ];
    
        $pdf = PDF::loadView('exports.sales-report-pdf', $data);
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'sales_report.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new SalesReportExport($this->reportData, $this->totalSales, $this->totalTransactions, $this->averageTransactionValue, $this->topProducts), 'sales_report.xlsx');
    }

    public function render()
    {
        return view('livewire.sales-report', [
            'reportData' => $this->reportGenerated ? $this->reportData : collect(),
            'products' => Product::all(),
            'categories' => Category::all(),
        ]);
    }
}
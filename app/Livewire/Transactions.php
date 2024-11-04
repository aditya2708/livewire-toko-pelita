<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Package;
use App\Models\AprioriAnalysis;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Transactions extends Component
{
    use WithPagination;

    public $confirmingDeletion = false;
    public $transactionToDelete;

    // Date range filters
    public $dateFrom;
    public $dateTo;

    // Sorting
    public $sortField = 'transaction_date';
    public $sortDirection = 'desc';

    // Transaction items
    public $items = [];
    public $totalAmount = 0;
    public $paidAmount = 0;
    public $changeAmount = 0;

    // Item selection
    public $selectedItemId = '';
    public $quantity = 1;

    // Available products and packages
    public $availableProducts = [];
    public $availablePackages = [];

    // Modal control
    public $showModal = false;
    public $showDetailModal = false;
    public $selectedTransaction;

    public $itemType = 'product';
    

    protected $listeners = ['deleteConfirmed' => 'deleteTransaction'];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadAvailableProductsAndPackages();
    }

    private function checkPackageStock($package, $quantity)
    {
        foreach ($package->items as $item) {
            $totalRequired = $item->quantity * $quantity;
            if ($item->product->stock_quantity < $totalRequired) {
                return false;
            }
        }
        return true;
    }

    public function loadAvailableProductsAndPackages()
    {
        $this->availableProducts = Product::where('stock_quantity', '>', 0)
            ->select('id', 'name', 'unit_price', 'stock_quantity')
            ->get();
        $this->availablePackages = Package::all();
    }

    #[Computed]
    public function transactions()
    {
        return Transaction::whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
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

    public function createTransaction()
    {
        $this->resetValidation();
        $this->reset(['items', 'totalAmount', 'paidAmount', 'changeAmount', 'selectedItemId', 'quantity']);
        $this->showModal = true;
    }

    public function addItem()
    {
        $this->validate([
            'itemType' => 'required|in:product,package',
            'selectedItemId' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $isPackage = $this->itemType === 'package';
        $itemId = $this->selectedItemId;

        $item = $isPackage
            ? Package::with('items.product')->findOrFail($itemId)
            : Product::findOrFail($itemId);

        if ($isPackage) {
            if (!$this->checkPackageStock($item, $this->quantity)) {
                $this->addError('quantity', 'Not enough stock available for one or more products in this package.');
                return;
            }
        } else {
            if ($item->stock_quantity < $this->quantity) {
                $this->addError('quantity', 'Not enough stock available.');
                return;
            }
        }

        $this->items[] = [
            'id' => $item->id,
            'name' => $item->name,
            'type' => $isPackage ? 'package' : 'product',
            'quantity' => $this->quantity,
            'price' => $isPackage ? $item->package_price : $item->unit_price,
            'subtotal' => ($isPackage ? $item->package_price : $item->unit_price) * $this->quantity,
        ];

        $this->calculateTotals();
        $this->reset(['selectedItemId', 'quantity']);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalAmount = array_sum(array_column($this->items, 'subtotal'));
        $this->calculateChange();
    }

    public function updatedPaidAmount()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->validate([
            'paidAmount' => 'required|numeric|min:' . $this->totalAmount,
        ]);
        $this->changeAmount = $this->paidAmount - $this->totalAmount;
    }

    public function processTransaction()
    {
        if (empty($this->items)) {
            $this->addError('items', 'You must add at least one item to the transaction.');
            return;
        }

        $this->validate([
            'paidAmount' => 'required|numeric|min:' . $this->totalAmount,
        ]);

        // Recheck stock for all items
        foreach ($this->items as $item) {
            if ($item['type'] === 'product') {
                $product = Product::find($item['id']);
                if ($product->stock_quantity < $item['quantity']) {
                    $this->addError('items', "Insufficient stock for {$product->name}.");
                    return;
                }
            } elseif ($item['type'] === 'package') {
                $package = Package::with('items.product')->find($item['id']);
                if (!$this->checkPackageStock($package, $item['quantity'])) {
                    $this->addError('items', "Insufficient stock for one or more products in package {$package->name}.");
                    return;
                }
            }
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total_amount' => $this->totalAmount,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->changeAmount,
                'transaction_date' => now(),
            ]);

            foreach ($this->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'package_id' => $item['type'] === 'package' ? $item['id'] : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'is_package' => $item['type'] === 'package',
                ]);

                if ($item['type'] === 'product') {
                    $this->decreaseProductStock($item['id'], $item['quantity']);
                } elseif ($item['type'] === 'package') {
                    $this->decreasePackageStock($item['id'], $item['quantity']);
                }
            }

            DB::commit();

            $this->reset(['items', 'totalAmount', 'paidAmount', 'changeAmount', 'selectedItemId', 'quantity', 'itemType']);
            $this->showModal = false;
            $this->dispatch('transactionCompleted');
            $this->loadAvailableProductsAndPackages();
            session()->flash('message', 'Transaction processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction processing error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing the transaction.');
        }
    }

    private function decreaseProductStock($productId, $quantity)
    {
        $product = Product::findOrFail($productId);
        if ($product->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock for product {$product->name}.");
        }
        $product->decrement('stock_quantity', $quantity);
    }

    private function decreasePackageStock($packageId, $quantity)
    {
        $package = Package::with('items.product')->findOrFail($packageId);
        foreach ($package->items as $item) {
            $totalQuantity = $item->quantity * $quantity;
            $this->decreaseProductStock($item->product_id, $totalQuantity);
        }
    }

    public function viewTransactionDetails($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['items.product', 'items.package', 'user'])
            ->findOrFail($transactionId);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['items', 'totalAmount', 'paidAmount', 'changeAmount', 'selectedItemId', 'quantity']);
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function confirmDelete($transactionId)
    {
        $this->transactionToDelete = $transactionId;
        $this->confirmingDeletion = true;
    }

    private function restoreProductStock($productId, $quantity)
    {
        $product = Product::findOrFail($productId);
        $product->increment('stock_quantity', $quantity);
    }

    private function restorePackageStock($packageId, $quantity)
    {
        $package = Package::with('items.product')->find($packageId);
        if ($package) {
            foreach ($package->items as $item) {
                $this->restoreProductStock($item->product_id, $item->quantity * $quantity);
            }
        } else {
            // Log informasi bahwa paket tidak ditemukan
            Log::warning("Package with ID {$packageId} not found when restoring stock");
        }
    }
    public function deleteTransaction()
{
    try {
        $transaction = Transaction::findOrFail($this->transactionToDelete);
        
        DB::beginTransaction();

        foreach ($transaction->items as $item) {
            if ($item->is_package) {
                // Cek apakah paket masih ada
                $package = Package::find($item->package_id);
                if ($package) {
                    $this->restorePackageStock($item->package_id, $item->quantity);
                } else {
                    // Jika paket sudah dihapus, log informasi atau tangani sesuai kebutuhan
                    Log::warning("Package with ID {$item->package_id} not found when deleting transaction {$transaction->id}");
                }
            } else {
                $this->restoreProductStock($item->product_id, $item->quantity);
            }
        }

        $transaction->delete();

        DB::commit();

        session()->flash('message', 'Transaction deleted successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Transaction deletion error: ' . $e->getMessage());
        session()->flash('error', 'An error occurred while deleting the transaction.');
    }

    $this->confirmingDeletion = false;
    $this->transactionToDelete = null;
}

    public function formatToRupiah($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatDate($date)
    {
        return Carbon::parse($date)->format('d M Y H:i');
    }

 

    

    public function render()
    {
        return view('livewire.transactions', [
            'transactions' => $this->transactions,
            'availableProducts' => $this->availableProducts,
            'availablePackages' => $this->availablePackages,
        ]);
    }
}
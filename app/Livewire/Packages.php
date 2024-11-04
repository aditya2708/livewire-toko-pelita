<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Package;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PackagesExport;
use Illuminate\Support\Facades\Log;
class Packages extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortField = 'name';

    #[Url(history: true)]
    public $sortDirection = 'asc';

    public $showModal = false;
    public $modalMode = 'create';
    public $deleteId = null;
    // Add a property for the number of items per page
    public $perPage = 10;
    public $package = [
        'id' => null,
        'name' => '',
        'description' => '',
        'items' => [],
        'total_normal_price' => 0,
        'package_price' => 0,
        'discount_percentage' => 0,
    ];

    public $selectedProduct = '';
    public $selectedQuantity = 1;

    protected $listeners = ['deleteConfirmed' => 'deletePackage'];

    public function rules()
    {
        return [
'package.name' => [
            'required',
            'string',
            'min:3',
            Rule::unique('packages', 'name')->ignore($this->package['id'] ?? null)
        ],
            'package.description' => 'nullable|string',
            'package.package_price' => 'required|numeric|min:0',
            'package.items' => 'required|array|min:1',
            'package.items.*.product_id' => 'required|exists:products,id',
            'package.items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function packages()
    {
        return Package::query()
            ->withCount('items')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function products()
    {
        return Product::all();
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

    public function createPackage()
    {
        $this->resetValidation();
        $this->package = [
            'id' => null,
            'name' => '',
            'description' => '',
            'items' => [],
            'total_normal_price' => 0,
            'package_price' => 0,
            'discount_percentage' => 0,
        ];
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function editPackage(Package $package)
    {
        $this->resetValidation();
        $this->package = $package->toArray();
        $this->package['items'] = $package->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'unit_price' => $item->product->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->product->unit_price * $item->quantity,
            ];
        })->toArray();
        $this->calculateTotals();
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function addProductToPackage()
    {
        $product = Product::find($this->selectedProduct);
        if ($product) {
            $this->package['items'][] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->unit_price,
                'quantity' => $this->selectedQuantity,
                'subtotal' => $product->unit_price * $this->selectedQuantity,
            ];
            $this->calculateTotals();
            $this->selectedProduct = '';
            $this->selectedQuantity = 1;
        }
    }

    public function removeProductFromPackage($index)
    {
        unset($this->package['items'][$index]);
        $this->package['items'] = array_values($this->package['items']);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->package['total_normal_price'] = array_sum(array_column($this->package['items'], 'subtotal'));
        $this->calculateDiscount();
    }

    public function calculateDiscount()
    {
        if ($this->package['total_normal_price'] > 0 && $this->package['package_price'] > 0) {
            $this->package['discount_percentage'] = (1 - ($this->package['package_price'] / $this->package['total_normal_price'])) * 100;
        } else {
            $this->package['discount_percentage'] = 0;
        }
    }

    public function updatedPackagePackagePrice()
    {
        $this->calculateDiscount();
    }

    public function savePackage()
    {
        $this->validate([
            'package.name' => 'required|string|min:3',
            'package.description' => 'nullable|string',
            'package.package_price' => 'required|numeric|min:0',
            'package.items' => 'required|array|min:1',
            'package.items.*.product_id' => 'required|exists:products,id',
            'package.items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $packageData = [
                'name' => $this->package['name'],
                'description' => $this->package['description'],
                'package_price' => $this->package['package_price'],
            ];

            if ($this->package['id']) {
                $package = Package::findOrFail($this->package['id']);
                $package->update($packageData);
                $package->items()->delete(); // Remove existing items
            } else {
                $package = Package::create($packageData);
                $this->js('window.location.reload()'); 
            }

            foreach ($this->package['items'] as $item) {
                $package->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            DB::commit();

            $this->showModal = false;
            $this->reset(['package', 'selectedProduct', 'selectedQuantity']);
            session()->flash('message', $this->package['id'] ? 'Package updated successfully.' : 'Package created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving package: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while saving the package. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('showDeleteConfirmation');
    }

    public function deletePackage()
    {
        try {
            $package = Package::findOrFail($this->deleteId);
            $package->items()->delete();
            $package->delete();
            session()->flash('message', 'Package deleted successfully.');
            $this->js('window.location.reload()'); 
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the package: ' . $e->getMessage());
        }
        $this->deleteId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function exportPDF()
    {
        $packages = Package::with('items.product')->get();
        $pdf = Pdf::loadView('exports.packages-pdf', ['packages' => $packages]);
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'packages.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new PackagesExport, 'packages.xlsx');
    }

    // Add a new method to format currency to Rupiah
    private function formatToRupiah($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

   
    public function render()
    {
        $packages = $this->packages;

        // Format the price for each package
        $packages->getCollection()->transform(function ($package) {
            $package->formatted_price = $this->formatToRupiah($package->package_price);
            return $package;
        });

        return view('livewire.packages', [
            'packages' => $packages,
            'products' => $this->products,
        ]);
    }
}
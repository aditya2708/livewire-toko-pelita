<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Excel as ExcelType;
use Illuminate\Support\Facades\DB;

class Products extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortField = 'created_at'; // Changed default sort field to 'created_at'

    #[Url(history: true)]
    public $sortDirection = 'desc'; // Changed default sort direction to 'desc'

    public $product = [
        'id' => null,
        'name' => '',
        'description' => '',
        'unit_price' => 0,
        'stock_quantity' => 0,
        'category_id' => null,
        'barcode' => '',
    ];

    public $photo;
    public $showModal = false;
    public $modalMode = 'create';
    public $deleteId = null;
    public $isLoading = false;

    
    public $exportFormat = 'pdf';

    public function exportProducts()
    {
        $products = Product::all();

        if ($this->exportFormat === 'pdf') {
            $pdf = PDF::loadView('exports.products', ['products' => $products]);
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'products.pdf');
        } elseif ($this->exportFormat === 'csv') {
            return Excel::download(new ProductsExport, 'products.csv', ExcelType::CSV);
        } elseif ($this->exportFormat === 'excel') {
            return Excel::download(new ProductsExport, 'products.xlsx', ExcelType::XLSX);
        }
    }
// Helper method to format price in Rupiah
private function formatToRupiah($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}
    public function rules()
    {
        return [
            'product.name' => 'required|min:3',
            'product.description' => 'nullable',
            'product.unit_price' => 'required|numeric|min:0',
            'product.stock_quantity' => 'required|integer|min:0',
            'product.category_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|max:2024', // max 1MB
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

        #[Computed]
    public function products()
    {
        return Product::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }
    #[Computed]
    public function categories()
    {
        return Category::all();
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

    public function createProduct()
    {
        $this->resetValidation();
        $this->product = [
            'id' => null,
            'name' => '',
            'description' => '',
            'unit_price' => 0,
            'stock_quantity' => 0,
            'category_id' => null,
            'barcode' => '',
        ];
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function editProduct(Product $product)
    {
        $this->resetValidation();
        $this->product = $product->toArray();
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function saveProduct()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();

            if ($this->product['id']) {
                $product = Product::findOrFail($this->product['id']);
                $product->update($this->product);
                $message = 'Product successfully updated.';
            } else {
                $product = Product::create($this->product);
                $message = 'Product successfully created.';
                $this->js('window.location.reload()'); 
            }

            if ($this->photo) {
                $path = $this->photo->store('product-photos', 'public');
                if ($product->photo_path) {
                    Storage::disk('public')->delete($product->photo_path);
                }
                $product->update(['photo_path' => $path]);
            }

            DB::commit();

            $this->showModal = false;
            $this->reset(['product', 'photo']);
            $this->dispatch('productSaved'); // Dispatch an event after saving
            session()->flash('message', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while saving the product: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }
    public function deleteProduct()
{
    try {
        // Start a database transaction to ensure data integrity
        DB::beginTransaction();

        $product = Product::findOrFail($this->deleteId);

        // Check if the product is part of any package
        $packageItems = $product->packageItems;
        if ($packageItems->isNotEmpty()) {
            // If the product is part of packages, collect the package names
            $packageNames = $packageItems->pluck('package.name')->unique()->implode(', ');
            // Throw an exception with a detailed error message
            throw new \Exception("Cannot delete this product as it is part of the following package(s): $packageNames");
        }

        // If the product is not part of any package, proceed with deletion
        if ($product->photo_path) {
            Storage::disk('public')->delete($product->photo_path);
        }
        $product->delete();

        // If everything is successful, commit the transaction
        DB::commit();
        session()->flash('message', 'Product successfully deleted.');
        $this->js('window.location.reload()');
    } catch (\Exception $e) {
        // If an error occurs, rollback the transaction
        DB::rollBack();
        // Flash the error message to the session
        session()->flash('error', 'An error occurred while deleting the product: ' . $e->getMessage());
    }
    $this->deleteId = null;
}

    

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function render()
    {
        $productsQuery = $this->products;
        
        $products = $productsQuery->paginate(10);
        
        $products->getCollection()->transform(function ($product) {
            $product->formatted_price = $this->formatToRupiah($product->unit_price);
            return $product;
        });

        return view('livewire.products', [
            'products' => $products,
            'categories' => $this->categories,
        ]);
    }
        // Add a new method to handle re-rendering
        public function refreshProducts()
        {
            $this->render();
        }
}
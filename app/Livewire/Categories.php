<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class Categories extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortField = 'name';

    #[Url(history: true)]
    public $sortDirection = 'asc';

    public $category = [
        'id' => null,
        'name' => '',
        'description' => ''
    ];

    public $showModal = false;
    public $modalMode = 'create';
    public $showDeleteModal = false;
    public $showDeleteWarningModal = false;
    public $categoryToDelete;
    public $newCategoryId;

    protected $listeners = ['showDeleteWarning'];

    public function rules()
    {
        return [
            'category.name' => [
                'required',
                'min:3',
                Rule::unique('categories', 'name')->ignore($this->category['id'] ?? null)
            ],
            'category.description' => 'nullable',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
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

    public function createCategory()
    {
        $this->resetValidation();
        $this->category = [
            'id' => null,
            'name' => '',
            'description' => ''
        ];
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function editCategory(Category $category)
    {
        $this->resetValidation();
        $this->category = $category->toArray();
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function saveCategory()
    {
        $this->validate();
        
        if ($this->category['id']) {
            $category = Category::find($this->category['id']);
            $category->update($this->category);
            $message = 'Category updated successfully.';
        } else {
            Category::create($this->category);
            $message = 'Category created successfully.';
        }
        
        $this->showModal = false;
        $this->reset(['category']);
        session()->flash('message', $message);
    }

    public function confirmCategoryDeletion($categoryId)
    {
        Log::info('Confirming deletion for category: ' . $categoryId);
        $this->categoryToDelete = Category::findOrFail($categoryId);
        if ($this->categoryToDelete->hasProducts()) {
            Log::info('Category has products, showing warning modal');
            $this->showDeleteWarningModal = true;
        } else {
            Log::info('Category has no products, showing delete modal');
            $this->showDeleteModal = true;
        }
    }

    public function deleteCategory()
    {
        $this->categoryToDelete->delete();
        $this->resetState();
        session()->flash('message', 'Category deleted successfully.');
    }

    public function deleteWithProducts()
    {
        $this->categoryToDelete->products()->delete();
        $this->categoryToDelete->delete();
        $this->resetState();
        session()->flash('message', 'Category and associated products have been deleted.');
    }

    public function moveProductsAndDelete()
    {
        $this->validate([
            'newCategoryId' => 'required|exists:categories,id'
        ]);

        $this->categoryToDelete->products()->update(['category_id' => $this->newCategoryId]);
        $this->categoryToDelete->delete();
        $this->resetState();
        session()->flash('message', 'Products moved and category deleted successfully.');
    }

    public function getOtherCategories()
    {
        if ($this->categoryToDelete) {
            return Category::where('id', '!=', $this->categoryToDelete->id)->get();
        }
        return collect();
    }

    public function resetState()
    {
        $this->categoryToDelete = null;
        $this->newCategoryId = null;
        $this->showDeleteModal = false;
        $this->showDeleteWarningModal = false;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['category']);
    }

    public function closeDeleteModal()
    {
        $this->resetState();
    }

    public function render()
    {
        return view('livewire.categories', [
            'categories' => $this->categories,
        ]);
    }
}
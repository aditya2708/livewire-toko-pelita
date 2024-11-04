<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-white">Manajemen Produk</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-center">
        <div class="w-full sm:w-1/3 mb-2 sm:mb-0">
            <input wire:model.live="search" type="text" placeholder="Cari Produk..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div class="flex space-x-2">
            <button wire:click="createProduct" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Tambah Produk
            </button>
            <select wire:model="exportFormat" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="pdf">PDF</option>
                <option value="csv">CSV</option>
                <option value="excel">Excel</option>
            </select>
            <button wire:click="exportProducts" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Export
            </button>
        </div>

    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FOTO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('name')" class="text-gray-500 hover:text-gray-700">
                            Nama
                            @if ($sortField === 'name')
                                <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('unit_price')" class="text-gray-500 hover:text-gray-700">
                            Harga
                            @if ($sortField === 'unit_price')
                                <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('stock_quantity')" class="text-gray-500 hover:text-gray-700">
                            Stock
                            @if ($sortField === 'stock_quantity')
                                <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $index => $product)
                    <tr class="hover:bg-gray-50">
                        <!-- Add a new column for numbering -->
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-full">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                        <!-- Format the price in Rupiah -->
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->formatted_price }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->stock_quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="editProduct({{ $product->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button wire:click="confirmDelete({{ $product->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    @if($showModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full md:max-w-2xl">
                <form wire:submit.prevent="saveProduct">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                       
                        <div class="col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <input type="text" id="name" wire:model="product.name"
                                   class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            @error('product.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea id="description" wire:model="product.description"
                                      class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500" rows="3"></textarea>
                            @error('product.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                            <input type="number" id="unit_price" wire:model="product.unit_price" step="0.01"
                                   class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            @error('product.unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah stock</label>
                            <input type="number" id="stock_quantity" wire:model="product.stock_quantity"
                                   class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            @error('product.stock_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select id="category_id" wire:model="product.category_id"
                                    class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500" required>
                                <option value="">Pilih Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('product.category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        {{-- <div>
                            <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <input type="text" id="barcode" wire:model="product.barcode" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500" readonly>
                        </div> --}}
                        
                        <div class="col-span-2">
                            <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                            <input type="file" id="photo" wire:model="photo" accept="image/*"
                                   class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:border-blue-500">
                            @error('photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            
                            <div class="mt-2 flex items-center">
                                @if ($photo)
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-16 h-16 object-cover rounded-full mr-2" alt="Product preview">
                                    <span class="text-sm text-gray-500">New photo preview</span>
                                @elseif ($product['id'] && $product['photo_path'])
                                    <img src="{{ Storage::url($product['photo_path']) }}" class="w-16 h-16 object-cover rounded-full mr-2" alt="Current product photo">
                                    <span class="text-sm text-gray-500">Current photo</span>
                                @else
                                    <span class="text-sm text-gray-500">No photo uploaded</span>
                                @endif
                            </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($deleteId)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center" x-data>
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="mb-4 text-sm text-gray-600">Apakah anda yakin ingin menghapus produk ini? </p>
            <div class="flex justify-end">
                <button wire:click="$set('deleteId', null)" class="px-4 py-2 text-gray-500 rounded-lg mr-2 hover:bg-gray-100">Batal</button>
                <button wire:click="deleteProduct" class="px-4 py-2 text-white bg-red-500 rounded-lg hover:bg-red-600">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('productSaved', () => {
            Livewire.dispatch('refreshProducts');
        });
    });
</script>
@endpush



<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-white">Manajemen Paket</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-4 flex flex-col sm:flex-row justify-between items-center">
        <div class="w-full sm:w-1/3 mb-2 sm:mb-0">
            <input wire:model.live="search" type="text" placeholder="Cari Paket..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div class="flex space-x-2">
            <button wire:click="createPackage" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Tambah Paket
            </button>
            <button wire:click="exportPDF" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Export PDF
            </button>
            <button wire:click="exportExcel" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Export Excel
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr class="text-left">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('name')" class="text-gray-500 hover:text-gray-700">
                            Nama
                            @if ($sortField === 'name')
                                <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('package_price')" class="text-gray-500 hover:text-gray-700">
                            Harga Paket
                            @if ($sortField === 'package_price')
                                <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Terhitung</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($packages as $pkg)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $pkg->name }}</td>
                        <td class="px-6 py-4">{{ Str::limit($pkg->description, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $pkg->formatted_price }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $pkg->items_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="editPackage({{ $pkg->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button wire:click="confirmDelete({{ $pkg->id }})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $packages->links() }}
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center overflow-y-auto">
        <div class="bg-white p-6 rounded-lg w-3/4 max-w-4xl">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ $modalMode === 'create' ? 'Add New Package' : 'Edit Package' }}
            </h3>
            <form wire:submit.prevent="savePackage">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-bold mb-2">Nama</label>
                    <input type="text" id="name" wire:model="package.name"
                           class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none" required>
                    @error('package.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
                    <textarea id="description" wire:model="package.description"
                              class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none" rows="3"></textarea>
                    @error('package.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <h4 class="font-bold mb-2">Tambah Produk ke Paket</h4>
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <select wire:model="selectedProduct" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none">
                                <option value="">Pilih Produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->formatted_price }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-1/4">
                            <input type="number" wire:model="selectedQuantity" min="1" placeholder="Quantity"
                                   class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none">
                        </div>
                        <button type="button" wire:click="addProductToPackage" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                            Tambah 
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="font-bold mb-2">Produk dalam Paket</h4>
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Produk</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Kuantitas</th>
                                <th class="text-right">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($package['items'] as $index => $item)
                                <tr>
                                    <td>{{ $item['product_name'] }}</td>
                                    <td class="text-right">{{ $this->formatToRupiah($item['unit_price']) }}</td>
                                    <td class="text-right">{{ $item['quantity'] }}</td>
                                    <td class="text-right">{{ $this->formatToRupiah($item['subtotal']) }}</td>
                                    <td class="text-right">
                                        <button type="button" wire:click="removeProductFromPackage({{ $index }})" class="text-red-500">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @error('package.items') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4 flex justify-between">
                    <div>
                        <span class="font-bold">Total Harga Normal:</span>
                        <span>{{ $this->formatToRupiah($package['total_normal_price']) }}</span>
                    </div>
                    <div>
                        <label for="package_price" class="font-bold mr-2">Harga Paket:</label>
                        <input type="number" id="package_price" wire:model.live="package.package_price" step="1000"
                               class="px-3 py-2 text-gray-700 border rounded-lg focus:outline-none" required>
                        @error('package.package_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-4 text-right">
                    <span class="font-bold">Diskon:</span>
                    <span>{{ number_format($package['discount_percentage'], 2) }}%</span>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-white bg-gray-500 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($deleteId)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
            <p class="mb-4 text-sm text-gray-600">Are you sure you want to delete this package? This action cannot be undone.</p>
            <div class="flex justify-end space-x-2">
                <button wire:click="$set('deleteId', null)" class="px-4 py-2 text-gray-500 rounded-lg hover:bg-gray-100">Cancel</button>
                <button wire:click="deletePackage" class="px-4 py-2 text-white bg-red-500 rounded-lg hover:bg-red-600">Delete</button>
            </div>
        </div>
    </div>
    @endif
</div>
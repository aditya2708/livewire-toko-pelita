<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <h2 class="text-2xl font-semibold mb-4 text-white">Manajemen Transaksi</h2>

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

    <!-- Date range filter -->
    <div class="mb-4 flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
        <div class="w-full sm:w-auto flex items-center">
            <label for="dateFrom" class="mr-2 text-sm font-medium text-white">From:</label>
            <input type="date" id="dateFrom" wire:model.lazy="dateFrom" class="border rounded px-2 py-1 focus:ring-green-500 focus:border-green-500">
        </div>
        <div class="w-full sm:w-auto flex items-center">
            <label for="dateTo" class="mr-2 text-sm font-medium text-white">To:</label>
            <input type="date" id="dateTo" wire:model.lazy="dateTo" class="border rounded px-2 py-1 focus:ring-green-500 focus:border-green-500">
        </div>

        <button wire:click="createTransaction" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
            Tambah Transaksi
        </button>
    </div>

    <!-- Transactions list -->
    <div class="mb-8 overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                 
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('transaction_date')" class="font-bold">
                            Tanggal
                            @if ($sortField === 'transaction_date')
                                @if ($sortDirection === 'asc') ↑ @else ↓ @endif
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('total_amount')" class="font-bold">
                            Jumlah Total
                            @if ($sortField === 'total_amount')
                                @if ($sortDirection === 'asc') ↑ @else ↓ @endif
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($transactions as $transaction)
                    <tr class="hover:bg-gray-50" wire:key="transaction-{{ $transaction->id }}">
                       
                        <td class="py-4 px-6">{{ $this->formatDate($transaction->transaction_date) }}</td>
                        <td class="py-4 px-6">{{ $this->formatToRupiah($transaction->total_amount) }}</td>
                        <td class="py-4 px-6">
                            <button wire:click="viewTransactionDetails({{ $transaction->id }})" class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out mr-2">View Details</button>
                            <button wire:click="confirmDelete({{ $transaction->id }})" class="text-red-600 hover:text-red-800 transition duration-150 ease-in-out">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- New Transaction Modal -->
    @if($showModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">New Transaction</h3>
                    <div class="mb-4">
                        <label for="itemType" class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                        <select wire:model.live="itemType" id="itemType" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="product">Product</option>
                            <option value="package">Package</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="selectedItemId" class="block text-sm font-medium text-gray-700 mb-2">Select Item</label>
                        <select wire:model.live="selectedItemId" id="selectedItemId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Select an item</option>
                            @if($itemType === 'product')
                                @foreach($availableProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - {{ $this->formatToRupiah($product->unit_price) }} (Stock: {{ $product->stock_quantity }})</option>
                                @endforeach
                            @else
                                @foreach($availablePackages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }} - {{ $this->formatToRupiah($package->package_price) }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('selectedItemId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input type="number" wire:model.live="quantity" id="quantity" min="1" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        @error('quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                

                    <button wire:click="addItem" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">Add to Transaction</button>
                    
                    <!-- Transaction Items List -->
                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Items in Transaction</h4>
                        <ul class="space-y-2">
                            @foreach($items as $index => $item)
                                <li class="flex justify-between items-center">
                                    <span>{{ $item['name'] }} ({{ $item['type'] }}) (x{{ $item['quantity'] }}) - {{ $this->formatToRupiah($item['subtotal']) }}</span>
                                    <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 transition duration-150 ease-in-out">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                        @error('items') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Transaction Summary -->
                    <div class="mt-4">
                        <div class="flex justify-between font-semibold mb-2">
                            <span>Jumlah Total:</span>
                            <span>{{ $this->formatToRupiah($totalAmount) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span>Bayar:</span>
                            <input wire:model.live="paidAmount" type="number" step="1000" class="w-32 border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        @error('paidAmount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        <div class="flex justify-between font-semibold mb-4">
                            <span>Kembali:</span>
                            <span>{{ $this->formatToRupiah($changeAmount) }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="processTransaction" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Complete Transaction
                    </button>
                    <button wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Transaction Details Modal -->
    @if($showDetailModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Detail Transaksi</h3>
                    <div class="bg-gray-100 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-2 gap-4">
                           
                            <div>
                                <p class="text-sm font-medium text-gray-500">Tanggal</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $this->formatDate($selectedTransaction->transaction_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Jumlah Total</p>
                                <p class="text-lg font-semibold text-green-600">{{ $this->formatToRupiah($selectedTransaction->total_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Bayar</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $this->formatToRupiah($selectedTransaction->paid_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Kembalian</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $this->formatToRupiah($selectedTransaction->change_amount) }}</p>
                            </div>
                        </div>
                    </div>
                    <h4 class="font-semibold text-lg mb-2">Item yang dibeli</h4>
                    <div class="overflow-y-auto max-h-60">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedTransaction->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->is_package ? $item->package->name : $item->product->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $this->formatToRupiah($item->unit_price) }}
                                        </td>
                                        
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeDetailModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeletion)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Hapus Transaksi
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah anda yakin ingin menghapus transaksi? 
                                    Note: Jika transaksi ini terdapat paket yang sudah dihapus, maka stock tidak akan berkurang.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="deleteTransaction" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button type="button" wire:click="$set('confirmingDeletion', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('transactionCompleted', () => {
            // You can add any additional client-side logic here if needed
            console.log('Transaction completed successfully');
        });
    });
</script>
@endpush
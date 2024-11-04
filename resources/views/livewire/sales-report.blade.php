<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <h2 class="text-2xl font-semibold mb-4 text-white">Laporan Penjualan</h2>

    @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

    <div class="mb-6 bg-white shadow-md rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" id="startDate" wire:model="startDate" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700">Tanggal Berhenti</label>
                <input type="date" id="endDate" wire:model="endDate" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <div>
                <label for="selectedProduct" class="block text-sm font-medium text-gray-700">Produk</label>
                <select id="selectedProduct" wire:model="selectedProduct" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Semua Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="selectedCategory" class="block text-sm font-medium text-gray-700">Kategori</label>
                <select id="selectedCategory" wire:model="selectedCategory" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="generateReport" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Generate Laporan
            </button>
        </div>
    </div>

    @if($reportGenerated)
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Penjualan</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            Rp{{ number_format($totalSales, 0, ',', '.') }}
                        </dd>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Transaksi</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $totalTransactions }}
                        </dd>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Rata - Rata penjualan</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            Rp{{ number_format($averageTransactionValue, 0, ',', '.') }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Top 10 Produk</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topProducts as $index => $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product['total_sold'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Transaksi</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('transaction_date')" class="text-gray-500 hover:text-gray-700">
                                    Tanggal
                                    @if ($sortField === 'transaction_date')
                                        @if ($sortDirection === 'asc')
                                            &#8593;
                                        @else
                                            &#8595;
                                        @endif
                                    @endif
                                </button>
                            </th>
                            
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('total_amount')" class="text-gray-500 hover:text-gray-700">
                                    Total 
                                    @if ($sortField === 'total_amount')
                                        @if ($sortDirection === 'asc')
                                            &#8593;
                                        @else
                                            &#8595;
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transaction->transaction_date->isoFormat('D MMMM Y') }}
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <ul>
                                    @foreach($transaction->items as $item)
                                        <li>
                                            @if($item->is_package)
                                                Package: {{ $item->package->name }} ({{ $item->quantity }})
                                                <ul class="ml-4">
                                                @foreach($item->package->items as $packageItem)
                                                    <li>{{ $packageItem->product->name }} ({{ $packageItem->quantity * $item->quantity }})</li>
                                                @endforeach
                                                </ul>
                                            @else
                                                {{ $item->product->name }} ({{ $item->quantity }})
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $reportData->links() }}
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <button wire:click="exportPDF" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Export PDF
            </button>
            <button wire:click="exportExcel" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Export Excel
            </button>
        </div>
    @else
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Belum ada generate laporan</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Tekan tombol "Generate Laporan" jika ingin melihat Laporan</p>
                </div>
            </div>
        </div>
    @endif
</div>
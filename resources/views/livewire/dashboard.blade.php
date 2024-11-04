<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-white">Dashboard</h2>
  
    <!-- Date Range Selector -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2">Date Range</h3>
        <div class="flex flex-wrap items-center space-x-4">
            <div class="w-full sm:w-auto mb-2 sm:mb-0">
                <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" wire:model.live="startDate" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </div>
            <div class="w-full sm:w-auto">
                <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" wire:model.live="endDate" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </div>
        </div>
    </div>
  
    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Total Penjualan</h3>
            <p class="text-3xl font-bold text-green-600">Rp{{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Transaksi</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $totalTransactions }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Total Produk</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $totalProducts }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Total Kategori</h3>
            <p class="text-3xl font-bold text-indigo-600">{{ $totalCategories }}</p>
        </div>
        <!-- New metric: Total Packages -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Total Paket</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $totalPackages }}</p>
        </div>
    </div>
  
    <!-- Top Products and Low Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top 10 Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Top 10 Produk</h3>
            <ul class="space-y-2">
                @foreach($topProducts as $index => $product)
                    <li class="flex justify-between items-center">
                        <span class="text-sm">{{ $index + 1 }}. {{ $product->name }}</span>
                        <span class="text-sm font-semibold">{{ $product->total_sold }} Terjual</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Peringatan Stock Rendah</h3>
            <ul class="space-y-2">
                @foreach($lowStockProducts as $product)
                    <li class="flex justify-between items-center">
                        <span class="text-sm">{{ $product->name }}</span>
                        <span class="text-sm font-semibold @if($product->stock_quantity <= 5) text-red-600 @endif">
                            {{ $product->stock_quantity }} Tersisa
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Top Association Rules -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h3 class="text-lg font-semibold mb-4">Top Rekomendasi Restock</h3>
        @if(count($topAssociationRules) > 0)
            <ul class="space-y-2">
                @foreach($topAssociationRules as $rule)
                    <li class="text-sm">
                        <span class="font-medium">{{ $rule['antecedents'] }}</span> →
                        <span class="font-medium">{{ $rule['consequents'] }}</span>
                        <span class="text-gray-600">
                            (Confidence: {{ number_format($rule['confidence'] * 100, 2) }}%, 
                            Lift: {{ number_format($rule['lift'], 2) }})
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-600">No association rules available for the selected date range. Run Apriori analysis to see insights.</p>
        @endif
    </div>
</div>
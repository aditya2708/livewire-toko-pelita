<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    @if (!$analysisLoaded)
    <div class="text-center py-4">
        <p>Pilih analisis untuk melihat detailnya.</p>
    </div>
@else
    <h2 class="text-2xl font-semibold mb-4 text-white">Detail Analisis Apriori</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Analisis</h3>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Tanggal Analisis</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $analysis->formatted_date }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Periode</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($analysis->start_date)->format('d M Y') }} - 
                        {{ \Carbon\Carbon::parse($analysis->end_date)->format('d M Y') }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Minimum Support</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $analysis->min_support }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Minimum Confidence</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $analysis->min_confidence }}</dd>
                </div>
                @if($analysis->description)
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $analysis->description }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- 1-Itemset Table -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Hasil 1-Itemset</h3>
        <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
            <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                <thead>
                    <tr class="text-left">
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Product Name</th>
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Support</th>
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Transaction Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis->itemsets as $itemset)
                        <tr>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ $itemset->product->name }}</td>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ number_format($itemset->support, 4) }}</td>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ $itemset->transaction_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Restock Recommendations -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Rekomendasi Restock</h3>
        @if($analysis->restockRecommendations->isNotEmpty())
            <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                    <thead>
                        <tr class="text-left">
                            <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Produk</th>
                            <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Jumlah Transaksi</th>
                            <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Support</th>
                            <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Produk Terkait</th>
                            <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Skor Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysis->restockRecommendations as $recommendation)
                            <tr>
                                <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ $recommendation->product->name }}</td>
                                <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ $recommendation->transaction_count }}</td>
                                <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ number_format($recommendation->support, 4) }}</td>
                                <td class="border-dashed border-t border-gray-200 px-6 py-4">
                                    {{ implode(', ', array_slice(json_decode($recommendation->related_products, true), 0, 3)) }}
                                </td>
                                <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ number_format($recommendation->recommendation_score, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">Tidak ada rekomendasi restock untuk analisis ini.</p>
        @endif
    </div>

    <!-- Association Rules -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Association Rules</h3>
        <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
            <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                <thead>
                    <tr class="text-left">
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Antecedents</th>
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Consequents</th>
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Support</th>
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Confidence</th>
                        <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs">Lift</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis->rules as $rule)
                        <tr>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ $rule->antecedents }}</td>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ $rule->consequents }}</td>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ number_format($rule->support, 4) }}</td>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ number_format($rule->confidence, 4) }}</td>
                            <td class="border-dashed border-t border-gray-200 px-6 py-4">{{ number_format($rule->lift, 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
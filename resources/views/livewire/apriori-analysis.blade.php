<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold mb-4 text-white">Analisa Algoritma Apriori</h2>
        <a href="{{ route('apriori.saved') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Lihat Riwayat Analisis
        </a>
    </div>

    @if ($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @endif

    @if ($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ $successMessage }}</span>
        </div>
    @endif

    <form wire:submit.prevent="runAnalysis" class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" id="startDate" wire:model="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700">Tanggal Berhenti</label>
                <input type="date" id="endDate" wire:model="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="minSupport" class="block text-sm font-medium text-gray-700">Minimum Support</label>
                <input type="number" id="minSupport" wire:model="minSupport" step="0.01" min="0.01" max="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('minSupport') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="minConfidence" class="block text-sm font-medium text-gray-700">Minimum Confidence</label>
                <input type="number" id="minConfidence" wire:model="minConfidence" step="0.01" min="0.1" max="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('minConfidence') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="maxRules" class="block text-sm font-medium text-gray-700">Maximum Rules</label>
                <input type="number" id="maxRules" wire:model="maxRules" min="1" max="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('maxRules') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Jalankan Analisis
            </button>
        </div>
    </form>

    @if($isAnalysisComplete && $aprioriAnalysisId)
        <!-- 1-Itemset Table -->
        {{-- <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-white">Hasil Analisa 1 Itemset</h2>
            
           <livewire:itemset-table :analysis-id="$aprioriAnalysisId" />
        </div> --}}

       

        <!-- Association Rules Table -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-white">Hasil Association Rule</h2>
            <livewire:association-rule-table :analysis-id="$aprioriAnalysisId" />
        </div>

         <!-- Restock Recommendations Table -->
         <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-white">Hasil Rekomendasi Restock</h2>
            <livewire:restock-recommendation-table :analysis-id="$aprioriAnalysisId" />
        </div>

        <div class="mt-6 bg-gray-100 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Rangkuman Analisis</h3>
            <p><strong>Range Tanggal:</strong> {{ $startDate }} to {{ $endDate }}</p>
            <p><strong>Minimum Support:</strong> {{ $minSupport }}</p>
            <p><strong>Minimum Confidence:</strong> {{ $minConfidence }}</p>
            <p><strong>Maximum Rules:</strong> {{ $maxRules }}</p>
        </div>
    @elseif($isAnalysisComplete && !$aprioriAnalysisId)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mt-4" role="alert">
            <p class="font-bold">No Results</p>
            <p>The analysis did not produce any results. Try adjusting your parameters and run the analysis again.</p>
        </div>
    @endif

    <div wire:loading wire:target="runAnalysis" class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
        <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
        <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('analysisComplete', () => {
            console.log('Analysis completed');
        });
    });
</script>
@endpush
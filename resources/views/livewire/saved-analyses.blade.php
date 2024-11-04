<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-bone">
    <h2 class="text-2xl font-semibold mb-4 text-white">Riwayat Analisa</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-end">
        <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
            <label for="dateFrom" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
            <input wire:model="dateFrom" type="date" id="dateFrom" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
        </div>
        <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
            <label for="dateTo" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
            <input wire:model="dateTo" type="date" id="dateTo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
        </div>
        <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
            <button wire:click="applyDateFilter" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Terapkan Filter
            </button>
        </div>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                    <button wire:click="sortBy('created_at')" class="text-gray-500 hover:text-gray-700">
                        Tanggal
                        @if ($sortField === 'created_at')
                            @if ($sortDirection === 'asc')
                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            @else
                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($analyses as $analysis)
                <tr>
                    <td class="px-6 py-4 whitespace-no-wrap">{{ $analysis->formatted_date }}</td>
                    <td class="px-6 py-4 whitespace-no-wrap">
                        {{ \Carbon\Carbon::parse($analysis->start_date)->format('d M Y') }} - 
                        {{ \Carbon\Carbon::parse($analysis->end_date)->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-no-wrap">
                        <a href="{{ route('apriori.detail', $analysis) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        <button wire:click="confirmDelete({{ $analysis->id }})" class="ml-2 text-red-600 hover:text-red-900">Delete</button>
                        <button wire:click="generatePDF({{ $analysis->id }})" class="ml-2 text-green-600 hover:text-green-900">PDF</button>
                        <button wire:click="generateExcel({{ $analysis->id }})" class="ml-2 text-blue-600 hover:text-blue-900">Excel</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $analyses->links() }}
    </div>

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
                                <!-- Heroicon name: outline/exclamation -->
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Hapus
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus riwayat analisis? 
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteAnalysis" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
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
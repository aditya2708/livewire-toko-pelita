<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900">
    <!-- Removed dark:bg-gray-900 to disable dark mode -->
    <div>
        {{ $logo }}
    </div>
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <!-- Removed dark:bg-gray-800 to disable dark mode -->
        {{ $slot }}
    </div>
</div>
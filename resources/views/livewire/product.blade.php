<div class="mt-16 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <livewire:product-table lazy>
                    <x-slot:placeholder>
                        <!-- Breeze-style Skeleton Table Placeholder -->
                        <div class="loading-placeholder space-y-4 animate-pulse">
                            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"></div>
                            <div class="space-y-2">
                                <div class="h-10 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="h-10 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="h-10 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="h-10 bg-gray-100 dark:bg-gray-800 rounded"></div>
                            </div>
                        </div>
                    </x-slot:placeholder>
                </livewire:product-table>
            </div>
        </div>
    </div>
</div>
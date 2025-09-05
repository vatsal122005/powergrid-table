<div class="mt-16 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                {{-- Flash Error Message --}}
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.500ms
                        x-init="setTimeout(() => show = false, 5000)"
                        class="mb-4 rounded px-4 py-3 border bg-red-100 border-red-400 text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Product Table --}}
                <div wire:init="loadProducts">
                    @if($readyToLoad)
                        <livewire:product-table />
                    @else
                        <div class="rounded-lg border border-gray-200 overflow-hidden shadow-sm animate-pulse">
                            {{-- Table Header --}}
                            <div class="bg-gray-100 px-4 py-3 flex space-x-6">
                                <div class="h-4 bg-gray-300 rounded w-full"></div>
                                <div class="h-4 bg-gray-300 rounded w-full"></div>
                                <div class="h-4 bg-gray-300 rounded w-full"></div>
                                <div class="h-4 bg-gray-300 rounded w-full"></div>
                                <div class="h-4 bg-gray-300 rounded w-full"></div>
                            </div>

                            {{-- Table Rows --}}
                            <div class="divide-y divide-gray-200">
                                @for ($i = 0; $i < 6; $i++)
                                    <div class="px-4 py-3 flex space-x-6">
                                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <p class="text-gray-500 text-sm mt-3 text-center">Loading products...</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
                <livewire:product-table lazy />
                    <x-slot:placeholder>
                        <!-- Skeleton Loader -->
                        <p class="text-red-600">Loading Table Placeholder...</p>
                    </x-slot:placeholder>
            </div>
        </div>
    </div>
</div>
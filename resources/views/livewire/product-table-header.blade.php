<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Products</h2>
        <p class="text-gray-600">Manage your product inventory</p>
    </div>
    <div class="flex space-x-3">
        <a 
            href="{{ route('products.create') }}" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Product
        </a>
    </div>
</div>

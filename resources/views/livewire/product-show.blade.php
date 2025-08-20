<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white shadow rounded-lg p-8">
        @if(session()->has('message'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('message') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row gap-8">
            @if($product->image_url)
                <div class="flex-shrink-0">
                    <img src="{{ asset('storage/' . $product->image_url) }}" alt="{{ $product->name }}" class="w-64 h-64 object-cover rounded">
                </div>
            @else
                <div class="flex-shrink-0">
                    <div class="w-64 h-64 bg-gray-200 rounded flex items-center justify-center">
                        <span class="text-gray-500">No Image</span>
                    </div>
                </div>
            @endif
            <div class="flex-1">
                <h1 class="text-3xl font-bold mb-2">{{ $product->name }}</h1>
                <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                <div class="mb-2">
                    <span class="text-lg font-semibold text-blue-700">${{ number_format($product->price, 2) }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-sm text-gray-500">SKU:</span>
                    <span class="text-sm">{{ $product->sku ?? 'N/A' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-sm text-gray-500">Stock:</span>
                    <span class="text-sm">{{ $product->stock_quantity }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-sm text-gray-500">Status:</span>
                    <span class="text-sm capitalize">{{ $product->status }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-sm text-gray-500">Category:</span>
                    <span class="text-sm">
                        {{ $product->category ? $product->category->name : 'Uncategorized' }}
                    </span>
                </div>
                @if($product->meta_title || $product->meta_description)
                    <div class="mt-4">
                        <h2 class="text-md font-semibold mb-1 text-gray-700">SEO Information</h2>
                        @if($product->meta_title)
                            <div class="text-sm text-gray-600"><span class="font-medium">Meta Title:</span> {{ $product->meta_title }}</div>
                        @endif
                        @if($product->meta_description)
                            <div class="text-sm text-gray-600"><span class="font-medium">Meta Description:</span> {{ $product->meta_description }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <a href="{{ route('products.edit', $product->id) }}" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Edit Product</a>
        </div>
    </div>
</div>

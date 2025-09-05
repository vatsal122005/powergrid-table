<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white shadow rounded-lg p-8">
        @cannot('view', $product)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') ?? __('messages.unauthorized') }}
                <div class="text-sm mt-2">
                    Redirecting to products table in <span id="redirect-timer">5</span> seconds...
                </div>
            </div>
            <script>
                let seconds = 5;
                const timer = document.getElementById('redirect-timer');
                const interval = setInterval(() => {
                    seconds--;
                    if (timer) timer.textContent = seconds;
                    if (seconds <= 0) {
                        clearInterval(interval);
                        window.location.href = "{{ route('products') }}";
                    }
                }, 1000);
            </script>
        @else
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
            <div class="flex-shrink-0 w-64 h-64 flex items-center justify-center bg-white">
                @if($product->image_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_url))
                    <img 
                        src="{{ asset('storage/' . $product->image_url) }}" 
                        alt="{{ $product->name }}" 
                        class="w-64 h-64 object-cover rounded shadow-md border border-gray-200 transition-transform duration-200 hover:scale-105"
                        loading="lazy"
                    >
                @else
                    <div class="w-64 h-64 bg-gradient-to-br from-gray-100 to-gray-300 rounded flex flex-col items-center justify-center border border-dashed border-gray-300">
                        <svg class="w-16 h-16 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V7a2 2 0 012-2h14a2 2 0 012 2v9.5M3 16.5l4.5-4.5a2 2 0 012.828 0L17 16.5M3 16.5l2.5-2.5a2 2 0 012.828 0L17 16.5M21 16.5l-4.5-4.5a2 2 0 00-2.828 0L7 16.5"></path>
                        </svg>
                        <span class="text-gray-500 text-sm">No Image Available</span>
                    </div>
                @endif
            </div>
            <div class="flex-1 space-y-2">
                <h1 class="text-3xl font-extrabold mb-2 flex items-center gap-2">
                    {{ $product->name }}
                </h1>
                @if(!empty($product->description))
                    <div class="mb-4">
                        <p class="text-gray-600 whitespace-pre-line">{{ $product->description }}</p>
                    </div>
                @else
                    <div class="mb-4">
                        <span class="text-gray-400 italic">No description available.</span>
                    </div>
                @endif
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">Price:</span>
                    <span class="text-lg font-bold text-blue-800 bg-blue-50 px-3 py-1 rounded shadow-sm">
                         ₹{{ number_format($product->price, 2) }}
                    </span>
                    @if($product->price <= 0)
                        <span class="text-xs text-red-600 bg-red-50 px-2 py-0.5 rounded ml-2">Free</span>
                    @endif
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">SKU:</span>
                    @if(!empty($product->sku))
                        <span class="text-sm px-2 py-1 bg-gray-100 text-gray-800 rounded font-mono tracking-wider">
                            {{ $product->sku }}
                        </span>
                    @else
                        <span class="text-sm px-2 py-1 bg-gray-50 text-gray-400 rounded italic">
                            Not assigned
                        </span>
                    @endif
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">Stock:</span>
                    @if($product->stock_quantity > 10)
                        <span class="text-sm px-2 py-1 bg-green-100 text-green-800 rounded font-semibold">
                            {{ $product->stock_quantity }} in stock
                        </span>
                    @elseif($product->stock_quantity > 0)
                        <span class="text-sm px-2 py-1 bg-yellow-100 text-yellow-800 rounded font-semibold">
                            Only {{ $product->stock_quantity }} left
                        </span>
                    @else
                        <span class="text-sm px-2 py-1 bg-red-100 text-red-700 rounded font-semibold">
                            Out of stock
                        </span>
                    @endif
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">Status:</span>
                    @php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'inactive' => 'bg-yellow-100 text-yellow-800',  
                        ];
                        $status = strtolower($product->status ?? 'unknown');
                        $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <span class="text-sm px-2 py-1 rounded capitalize {{ $colorClass }}">
                        {{ $status ?: 'Unknown' }}
                    </span>
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">Category:</span>
                    @if($product->category)
                        <span class="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            {{ $product->category->name }}
                        </span>
                    @else
                        <span class="text-sm px-2 py-1 bg-gray-100 text-gray-500 rounded">
                            Uncategorized
                        </span>
                    @endif
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">Sub Category:</span>
                    @if($product->subCategory)
                        <span class="text-sm px-2 py-1 bg-green-100 text-green-800 rounded">
                            {{ $product->subCategory->name }}
                        </span>
                    @else
                        <span class="text-sm px-2 py-1 bg-gray-100 text-gray-500 rounded">
                            Uncategorized
                        </span>
                    @endif
                </div>
                @if($product->meta_title || $product->meta_description)
                    <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h2 class="text-md font-semibold mb-2 text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path></svg>
                            SEO Information
                        </h2>
                        <ul class="space-y-1">
                            @if($product->meta_title)
                                <li class="flex items-start">
                                    <span class="font-medium text-gray-700 w-32">Meta Title:</span>
                                    <span class="text-gray-600 flex-1">{{ $product->meta_title }}</span>
                                </li>
                            @endif
                            @if($product->meta_description)
                                <li class="flex items-start">
                                    <span class="font-medium text-gray-700 w-32">Meta Description:</span>
                                    <span class="text-gray-600 flex-1">{{ $product->meta_description }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <a 
                href="{{ route('products.edit', $product->id) }}" 
                class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 hover:shadow-lg transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                title="Edit this product"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H7v-3a2 2 0 01.586-1.414z"></path>
                </svg>
                <span>Edit Product</span>
            </a>
        </div>
        @endcannot
    </div>
</div>

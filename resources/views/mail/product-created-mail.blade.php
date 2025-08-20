<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Product Created</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind Play CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 py-8">
    <div class="font-sans text-gray-800 p-4 sm:p-6 max-w-2xl mx-auto bg-white rounded-lg shadow-sm">
        <h2 class="text-2xl sm:text-3xl font-bold text-green-600 mb-4 flex items-center gap-2">
            <span>🎉</span>
            <span>New Product Created!</span>
        </h2>

        <p class="text-base sm:text-lg mb-6 leading-relaxed">
            A new product has been added to the catalog. Here are the details:
        </p>

        <div class="mb-8">
            <h4 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 border-b pb-1 border-gray-200">
                Product Details:
            </h4>
            <ul class="space-y-3">
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">ID:</strong>
                    <span class="ml-1">{{ $product->id ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Name:</strong>
                    <span class="ml-1">{{ $product->name ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">SKU:</strong>
                    <span class="ml-1">{{ $product->sku ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Price:</strong>
                    <span class="ml-1">${{ number_format($product->price, 2) }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Stock Quantity:</strong>
                    <span class="ml-1">{{ $product->stock_quantity ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Status:</strong>
                    <span class="ml-1">{{ ucfirst($product->status) ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Category:</strong>
                    <span class="ml-1">{{ $product->category->name ?? 'N/A' }}</span>
                </li>
                @if(!empty($product->image_url))
                    <li class="pt-2">
                        <strong class="font-medium text-gray-700 block mb-1">Image:</strong>
                        <img src="{{ asset('storage/' . $product->image_url) }}" alt="Product Image"
                            class="max-w-xs sm:max-w-sm rounded-lg border border-gray-200 shadow-sm">
                    </li>
                @endif
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Description:</strong>
                    <span class="ml-1">{{ $product->description ?? 'N/A' }}</span>
                </li>
            </ul>
        </div>

        <div class="mb-8">
            <h4 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 border-b pb-1 border-gray-200">
                Created By:
            </h4>
            <ul class="space-y-3">
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">User ID:</strong>
                    <span class="ml-1">{{ $user->id ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Name:</strong>
                    <span class="ml-1">{{ $user->name ?? 'N/A' }}</span>
                </li>
                <li class="text-sm sm:text-base">
                    <strong class="font-medium text-gray-700">Email:</strong>
                    <span class="ml-1">{{ $user->email ?? 'N/A' }}</span>
                </li>
            </ul>
        </div>

        <p class="text-sm sm:text-base text-gray-600 mt-6 pt-4 border-t border-gray-200">
            Thank you,<br>
            The {{ config('app.name') }} Team
        </p>
    </div>
</body>

</html>
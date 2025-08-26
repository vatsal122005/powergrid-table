@component('mail::message')
# 🎉 New Product Created!

A new product has been added to the catalog. Here are the details:

---

## 🛒 Product Details

<div class="p-4 bg-gray-50 rounded-lg mb-5">

    - **ID:** {{ $product->id ?? 'N/A' }}
    - **Name:** <span class="font-semibold text-gray-800">{{ $product->name ?? 'N/A' }}</span>
    - **SKU:** {{ $product->sku ?? 'N/A' }}
    - **Price:** <span class="text-green-600 font-semibold">${{ number_format($product->price ?? 0, 2) }}</span>
    - **Stock Quantity:** {{ $product->stock_quantity ?? 'N/A' }}
    - **Status:** <span class="capitalize">{{ $product->status ?? 'N/A' }}</span>
    - **Category:** {{ $product->category->name ?? 'N/A' }}

    @if(!empty($product->image_url))
        <div class="mt-3 text-center">
            <img src="{{ $product->image_url }}" alt="Product Image"
                class="max-w-xs rounded-lg shadow-md" />
        </div>
    @endif

    - **Description:**
    <p class="mt-2 text-gray-700">{{ $product->description ?? 'N/A' }}</p>
</div>

---

## 👤 Created By

<div class="p-4 bg-gray-100 rounded-lg">

    - **User ID:** {{ $user->id ?? 'N/A' }}
    - **Name:** {{ $user->name ?? 'N/A' }}
    - **Email:** {{ $user->email ?? 'N/A' }}

</div>

@component('mail::button', ['url' => route('products.show', $product->id), 'color' => 'success'])
View Product
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
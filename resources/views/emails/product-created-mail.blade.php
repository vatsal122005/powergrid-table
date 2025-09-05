@component('mail::message')
# 🎉 New Product Created!

A new product has been added to the catalog. Here are the details:

---

## 🛒 Product Details

- **ID:** {{ $product->id ?? 'N/A' }}
- **Name:** **{{ $product->name ?? 'N/A' }}**
- **SKU:** {{ $product->sku ?? 'N/A' }}
- **Price:** **${{ number_format($product->price ?? 0, 2) }}**
- **Stock Quantity:** {{ $product->stock_quantity ?? 'N/A' }}
- **Status:** {{ ucfirst($product->status ?? 'N/A') }}
- **Category:** {{ $product->category->name ?? 'N/A' }}

@if(!empty($product->image_url))
![Product Image]({{ $message->embed(public_path('storage/' . $product->image_url)) }})
@endif

- **Description:**
{{ $product->description ?? 'N/A' }}

---

## 👤 Created By

- **User ID:** {{ $user->id ?? 'N/A' }}
- **Name:** {{ $user->name ?? 'N/A' }}
- **Email:** {{ $user->email ?? 'N/A' }}

@component('mail::button', ['url' => route('products.show', $product->id), 'color' => 'success'])
View Product
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent

<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Product</h2>

        @cannot('update', $product)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') ?? __('messages.unauthorized') }}
            </div>
            <script>
                window.location.href = "{{ route('products') }}";
            </script>
        @else
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit.prevent="update" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="name" class="block text-sm font-medium text-gray-700 mb-2" :value="__('Product Name *')" />
                        <x-text-input
                            type="text"
                            id="name"
                            wire:model.lazy="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter product name"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="sku" :value="__('SKU')" class="mb-2" />
                        <x-text-input
                            type="text"
                            id="sku"
                            wire:model.live.debounce.300ms="sku"
                            class="w-full px-3 py-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Stock Keeping Unit"
                        />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" :value="__('Description')" class="mb-2" />
                    <x-textarea
                        id="description"
                        wire:model="description"
                        rows="4"
                        class="w-full px-3 py-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter product description"
                    />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <!-- Pricing and Stock -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="price" :value="__('Price *')" class="mb-2" />
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">₹</span>
                            <x-text-input
                                type="number"
                                id="price"
                                wire:model="price"
                                step="0.01"
                                min="0"
                                class="w-full pl-8 pr-3 py-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="stock_quantity" :value="__('Stock Quantity *')" class="mb-2" />
                        <x-text-input
                            type="number"
                            id="stock_quantity"
                            wire:model="stock_quantity"
                            min="0"
                            class="w-full px-3 py-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0"
                        />
                        <x-input-error :messages="$errors->get('stock_quantity')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status *')" class="mb-2" />
                        <select
                            id="status"
                            wire:model="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                    <!-- Category -->
                    <div wire:ignore>
                        <x-input-label for="category_id" :value="__('Category')" class="mb-2" />
                        <x-select-2
                            id="category_id"
                            name="category_id"
                            wireModel="category_id"
                            :options="$categories->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()"
                            placeholder="Select a Category"
                            class="w-full"
                        />
                        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                    </div>
                    
                    <!-- Sub Category -->
                    <div wire:ignore.self>
                        <x-input-label for="sub_category_id" :value="__('Sub Category')" class="mb-2" />
                        <x-select-2
                            id="sub_category_id"
                            name="sub_category_id"
                            wireModel="sub_category_id"
                            :options="$subCategories->map(fn($c) => ['id' => $c->id, 'text' => $c->name])->toArray()"
                            placeholder="Select a Sub Category"
                            class="w-full"
                        />
                        <x-input-error :messages="$errors->get('sub_category_id')" class="mt-1" />
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <x-input-label for="image" :value="__('Product Image')" class="mb-2" />
                    <input
                        type="file"
                        id="image"
                        wire:model="image"
                        accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    @if($image)
                        <div class="mt-2">
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-32 h-32 object-cover rounded">
                        </div>
                    @elseif($image_url)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $image_url) }}" alt="Current Image" class="w-32 h-32 object-cover rounded">
                            <p class="text-sm text-gray-500 mt-1">Current image</p>
                        </div>
                    @endif
                </div>

                <!-- SEO Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="meta_title" :value="__('Meta Title')" class="mb-2" />
                        <x-text-input
                            type="text"
                            id="meta_title"
                            wire:model="meta_title"
                            class="w-full px-3 py-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="SEO title for search engines"
                        />
                        <x-input-error :messages="$errors->get('meta_title')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="meta_description" :value="__('Meta Description')" class="mb-2" />
                        <x-textarea
                            id="meta_description"
                            wire:model="meta_description"
                            rows="3"
                            class="w-full px-3 py-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="SEO description for search engines"
                        />
                        <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <x-secondary-button
                        type="button"
                        onclick="if(confirm('Are you sure you want to cancel? Changes will not be saved.')) window.location='{{ route('products') }}'"
                    >
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-primary-button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed" wire:target="update">
                        <span wire:loading.remove wire:target="update">{{ __('Update Product') }}</span>
                        <span wire:loading wire:target="update">
                            <svg class="animate-spin h-5 w-5 inline-block mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            {{ __('Updating...') }}
                        </span>
                    </x-primary-button>
                </div>
            </form>
        @endcannot
    </div>
</div>

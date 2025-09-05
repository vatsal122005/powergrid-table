<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductEdit extends Component
{
    use WithFileUploads;

    public $product;

    public $name;

    public $description;

    public $price;

    public $stock_quantity;

    public $category_id;

    public $sub_category_id;

    public $status = 'active';

    public $sku;

    public $image_url;

    public $meta_title;

    public $meta_description;

    public $image;

    public $categories;
    public $subCategories;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'status' => 'required|in:active,inactive,draft',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $this->product->id,
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:1024',
        ];
    }

    public function mount($id)
    {
        Log::debug('Mounting ProductEdit with id: ' . $id);
        // $this->authorize('update', Product::find($id));
        $this->categories = Category::all();
        Log::debug('Retrieved categories: ', $this->categories->toArray());
        $this->product = Product::findOrFail($id);
        Log::debug('Retrieved product: ', $this->product->toArray());
        $this->name = $this->product->name;
        $this->description = $this->product->description;
        $this->price = $this->product->price;
        $this->stock_quantity = $this->product->stock_quantity;
        $this->category_id = $this->product->category_id;
        $this->sub_category_id = $this->product->sub_category_id;
        $this->status = $this->product->status;
        $this->sku = $this->product->sku;
        $this->image_url = $this->product->image_url;
        $this->meta_title = $this->product->meta_title;
        $this->meta_description = $this->product->meta_description;

        $this->subCategories = SubCategory::where('category_id', $this->category_id)->get();
        Log::debug('Retrieved filtered sub categories: ', $this->subCategories->toArray());
    }

    public function updatedCategoryId($value)
    {
        Log::debug('Updated category_id: ' . $value);
        $this->sub_category_id = null; // Reset
        $this->subCategories = SubCategory::where('category_id', $value)->get();
        Log::debug('Filtered sub categories: ', $this->subCategories->toArray());
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function update()
    {
        $validatedData = $this->validate();

        try {
            // Handle image upload
            if ($this->image) {
                $imagePath = $this->image->store('products', 'public');
                $validatedData['image_url'] = $imagePath;
            } else {
                $validatedData['image_url'] = $this->image_url;
            }

            $this->product->update($validatedData);

            session()->flash('message', __('messages.product_updated'));
        } catch (Exception $e) {
            session()->flash('error', __('messages.product_update_failed'));
        }
    }

    public function updatedName($value)
    {
        Log::info('Updated name: ' . $value);
        // Generate SKU only if SKU is empty or not manually set
        if (empty($this->sku)) {
            Log::info('Generating SKU because it is empty');
            // Take the first 3 letters of each word in the name, uppercase, and join with '-'
            $words = preg_split('/\s+/', trim($value));
            $parts = array_map(function ($word) {
                return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $word), 0, 3));
            }, $words);

            // Add a random 4-digit number to ensure uniqueness
            $random = mt_rand(1000, 9999);

            $sku = implode('-', array_filter($parts)) . '-' . $random;

            $this->sku = $sku;
            Log::info('Generated SKU: ' . $sku);
        }

        // Generate a description if it is empty or not manually set
        if (empty($this->description)) {
            // Simple auto-generated description based on the name
            $this->description = 'Introducing ' . $value . ', a new product in our catalog. Discover its features and benefits today!';
            Log::info('Generated description: ' . $this->description);
        }

        if (empty($this->meta_title)) {
            $this->meta_title = $this->name;
        }
    }

    public function render()
    {
        return view('livewire.product-edit');
    }
}

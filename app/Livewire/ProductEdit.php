<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
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
    public $status = 'active';
    public $sku;
    public $image_url;
    public $meta_title;
    public $meta_description;
    public $image;
    public $categories;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:active,inactive,draft',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $this->product->id,
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:1024',
        ];
    }


    public function mount($id)
    {
        $this->categories = Category::all();
        $this->product = Product::findOrFail($id);
        $this->name = $this->product->name;
        $this->description = $this->product->description;
        $this->price = $this->product->price;
        $this->stock_quantity = $this->product->stock_quantity;
        $this->category_id = $this->product->category_id;
        $this->status = $this->product->status;
        $this->sku = $this->product->sku;
        $this->image_url = $this->product->image_url;
        $this->meta_title = $this->product->meta_title;
        $this->meta_description = $this->product->meta_description;
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
        } catch (\Exception $e) {
            session()->flash('error', __('messages.product_update_failed'));
        }
    }

    public function render()
    {
        return view('livewire.product-edit');
    }
}

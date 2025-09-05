<?php

namespace App\Livewire;

use App\Jobs\SendProductAddedMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    #[Validate(['name' => 'required|string|min:3|max:255'])]
    public $name;

    #[Validate(['description' => 'required|string|min:3|max:500'])]
    public $description;

    #[Validate(['price' => 'required|numeric|min:0'])]
    public $price;

    #[Validate(['stock_quantity' => 'required|integer|min:0'])]
    public $stock_quantity;

    #[Validate(['category_id' => 'required|exists:categories,id'])]
    public $category_id;

    #[Validate(['sub_category_id' => 'required|exists:sub_categories,id'])]
    public $sub_category_id;

    #[Validate(['status' => 'required|in:active,inactive'])]
    public $status = 'active';

    #[Validate(['sku' => 'required|string|min:3|max:100|unique:products,sku'])]
    public $sku;

    public $image_url;

    #[Validate(['meta_title' => 'nullable|string|max:255'])]
    public $meta_title;

    #[Validate(['meta_description' => 'nullable|string|max:500'])]
    public $meta_description;

    #[Validate(rule: ['image' => 'required|image|max:2048'])] // 2MB max
    public $image;

    public $categories;
    public $subCategories;
    public $filteredSubCategories;

    public function mount()
    {
        Log::debug('Mounting ProductForm');
        $this->categories = Category::select('id', 'name')->get();
        $this->subCategories = SubCategory::select('id', 'name')->get();
        $this->filteredSubCategories = collect();
    }

    public function updatedCategoryId($value)
    {
        Log::debug('Updated category_id: ' . $value);

        $this->filteredSubCategories = SubCategory::where('category_id', $value)
            ->select('id', 'name')
            ->get();

        Log::debug('Filtered sub categories: ', $this->filteredSubCategories->toArray());

        $this->price = 0;
    }

    

    public function updated($propertyName)
    {
        Debugbar::info('Validated ProductForm');
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $this->validate();

        try {
            // Get user_id from session (authenticated user)
            $user = auth()->guard()->user();
            if (! $user) {
                session()->flash('error', __('messages.product_create_failed') . ' ' . __('messages.unauthenticated'));

                return;
            }

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'stock_quantity' => $this->stock_quantity,
                'category_id' => $this->category_id,
                'sub_category_id' => $this->sub_category_id,
                'status' => $this->status,
                'sku' => $this->sku,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'user_id' => $user->id, // Store user_id from session
            ];

            // Handle image upload
            if ($this->image) {
                $imagePath = $this->image->store('products', 'public');
                $data['image_url'] = $imagePath;
            }

            $product = Product::create($data);

            // Send ProductCreatedMail after successful product creation
            try {
                SendProductAddedMail::dispatch(
                    $product,
                    $user,
                    [$user->email],
                    []
                );
                Log::info('ProductCreatedMail sent successfully to ' . $user->email);
                Log::info('Data', $data);
            } catch (\Exception $mailException) {
                // Optionally log or handle mail sending failure
                Log::error('Failed to send ProductCreatedMail: ' . $mailException->getMessage());
            }

            session()->flash('message', __('messages.product_created'));
            $this->resetForm();
        } catch (\Exception $e) {
            session()->flash('error', __('messages.product_create_failed') . ' ' . $e->getMessage());
        }
    }

    /**
     * This function is automatically called by Livewire when the 'name' property is updated.
     * 
     * Example: 
     * - If you have <input wire:model="name" ...> in your Blade view,
     *   then whenever the user types in the input, this method will be triggered with the new value.
     * 
     * You do not need to call this function manually.
     */
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

    public function resetForm()
    {
        $this->reset([
            'name',
            'description',
            'price',
            'stock_quantity',
            'category_id',
            'sub_category_id',
            'status',
            'sku',
            'image_url',
            'meta_title',
            'meta_description',
            'image',
        ]);
        $this->status = 'active';
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}

<?php

namespace App\Livewire;

use App\Jobs\SendProductAddedMail;
use App\Models\Category;
use App\Models\Product;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function mount()
    {
        $this->categories = Category::all();
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

    public function resetForm()
    {
        $this->reset([
            'name',
            'description',
            'price',
            'stock_quantity',
            'category_id',
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

<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductShow extends Component
{
    public $product;

    public function mount($id)
    {
        try {
            $this->product = Product::findOrFail($id);
        } catch (\Exception $e) {
            // Optionally log the error
            report($e);
            // Optionally set a flash message or redirect
            session()->flash('error', __('messages.not_found'));

            // Redirect to a safe page, e.g., product list
            return redirect()->route('products');
        }
    }

    public function render()
    {
        return view('livewire.product-show');
    }
}

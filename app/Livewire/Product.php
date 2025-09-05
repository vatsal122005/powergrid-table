<?php

namespace App\Livewire;

use App\Models\Product as ModelsProduct;
use Livewire\Component;

class Product extends Component
{
    public $products = [];
    public $readyToLoad = false;
    public function loadProducts()
    {
        $this->readyToLoad = true;
    }


    public function render()
    {
        return view('livewire.product', [
            'products' => $this->readyToLoad
                ? ModelsProduct::latest()->paginate(10)
                : []
        ]);
    }
}

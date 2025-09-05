<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        "name",
        "slug",
        "description",
        "category_id",
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class ProductCategory extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];
    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

}

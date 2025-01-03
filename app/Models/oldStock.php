<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class oldStock extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
   
}

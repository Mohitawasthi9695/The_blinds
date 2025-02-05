<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class Godown extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [''];
    protected $hidden = ['created_at', 'updated_at'];


    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    public function gatepasses()
    {
        return $this->belongsTo(GatePass::class, 'gate_pass_id');
    }
}

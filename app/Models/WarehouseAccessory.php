<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class WarehouseAccessory extends Model
{
    use HasFactory,HasApiTokens;
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];

}

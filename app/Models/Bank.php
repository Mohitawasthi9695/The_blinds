<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Bank extends Model
{
    use HasApiTokens, HasApiTokens;
    protected $guarded = [];
}

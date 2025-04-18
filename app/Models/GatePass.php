<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class GatePass extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [''];
    protected $hidden = ['created_at', 'updated_at'];

    public function warehouse_supervisors()
    {
        return $this->belongsTo(User::class, 'warehouse_supervisor_id');
    }
    public function godown_supervisors()
    {
        return $this->belongsTo(User::class, 'godown_supervisor_id');
    }
    public function godown_roller_stock()
    {
        return $this->hasMany(GodownRollerStock::class);
    }
    public function godown_accessories()
    {
        return $this->hasMany(GodownAccessory::class, 'gate_pass_id', 'id');
    }
    
}

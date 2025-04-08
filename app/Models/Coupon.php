<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;


    public function drivers()
    {
        return $this->belongsToMany(Driver::class, 'coupon_driver_id', 'coupon_id', 'driver_id');
    }
}

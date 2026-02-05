<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBooking extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'order_id',
        'status',
        'data',

    ];

     public function trip()
    {
        return $this->hasOne(
            Trip::class,
            'order_id',   // foreign key on trips table
            'order_id'    // local key on customer_bookings table
        );
    }
}

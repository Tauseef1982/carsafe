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
}

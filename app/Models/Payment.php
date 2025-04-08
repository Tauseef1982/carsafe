<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;


    protected $fillable = [
        'prev_am',
        'amount',
        'driver_id',
        'trip_id',
        'payment_date',
        'user_id',
        'user_type',
        'type',
    ];



    public function trip(){

        return $this->belongsTo(Trip::class,'trip_id','trip_id');
    }
}

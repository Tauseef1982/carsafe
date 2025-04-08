<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchPayment extends Model
{
    use HasFactory;


    protected $fillable = [
        'driver_id',
        'account_id',
        'from',
        'amount',
    ];
    public $appends = ['PTrips'];

    public function payments(){

        return $this->hasMany(Payment::class,'batch_id');
    }
    public function getPTripsAttribute()
    {
        return $this->trips();
    }
    public function trips(){

        $payments = $this->payments->pluck('trip_id');
        return Trip::whereIn('trip_id',$payments)->get();
    }

    public function accountPay(){

        return $this->hasone(AccountPayment::class,'batch_id');

    }
}

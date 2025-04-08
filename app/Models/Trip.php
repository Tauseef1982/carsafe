<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;
    public $appends = ['ExtraDescription','TotalCostDiscounted'];
    protected $fillable = [
        'location_from',
        'location_to',
        'duration',
        'date',
        'time',
        'status',
        'complaint',
        'reason',
        'is_auto_paid_as_adjustment',
        'is_complaint',
        'trip_cost',
        'gocab_payment_id',
        'driver_paid',
        'gocab_paid',
        'cube_pin',
        'cube_pin_status',
        'payment_method',
        'driver_id',
        'account_number',
        'strip_id',
        'estimated_cost',
        'extra_charges',
        'extra_stop_amount',
        'stop_location',
        'extra_wait_amount',
        'extra_round_trip',
        'passenger_phone',
        'trip_id',
        'dev_status',
        'is_auto_paid_as_adjustment',
        'discount_perc',
        'discount_amount',
        'is_from_api',
        'is_manuall',
        'is_delete',
        'dev_status',
        'temp_data',
        'payment_id',
        'ts_delivered',
        'accepted_by',
        'icked_up'

    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'trip_id', 'trip_id');
    }

//    public function paidPayments()
//    {
//        return $this->hasMany(Payment::class, 'trip_id', 'trip_id')->where();
//    }
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }
    public function paidAgianstTripByAdmin(){

        return Payment::where('trip_id',$this->trip_id)->where('is_delete',0)->where('user_type','admin')->where('type','debit')->sum('amount');
    }

    public function adjustment_amount(){

        return Adjustment::where('trip_id',$this->trip_id)->where('type','admin_paid_auto')->sum('amount');
    }

    public function paidAgianstTripByAdminQuery(){

        return Payment::where('trip_id',$this->trip_id)->where('is_delete',0)->where('user_type','admin')->where('type','debit');
    }


    public function totalPaidAmountByCustomerFromAccountCard(){

        return Payment::where('trip_id',$this->trip_id)->where('is_delete',0)->where('user_type','customer')->where('type','debit');
    }


    public function getExtraDescriptionAttribute()
    {

        $description = 'Stop =$'.$this->extra_stop_amount .',Stop Location ='.$this->stop_location. ',Wait =$'.$this->extra_wait_amount. ',Round Trip = $'.$this->extra_round_trip.' .';
        return $description;

    }
    public function getTotalCostDiscountedAttribute()
    {

        return $this->trip_cost - $this->discount_amount;

    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Trip extends Model
{
    use HasFactory;
    use LogsActivity;
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
        'icked_up',
        'first_destination',
        'payper_trip',
        'order_id',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'gocab_card_number',
        'gocab_status',
        'gocab_reason',
        'gocab_response',
    ];

    protected $casts = [
        'gocab_response' => 'array',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'gocab_paid' => 'float',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()
            ->logOnlyDirty()->useLogName('Trip');

    }
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

        $description = 'Stop =$'.$this->extra_stop_amount .',Stop Location ='.$this->stop_location. ',Wait =$'.$this->extra_wait_amount. ' .';
        return $description;

    }
    public function getTotalCostDiscountedAttribute()
    {

        return $this->trip_cost - $this->discount_amount;

    }

     public function customerBooking()
    {
        return $this->belongsTo(
            CustomerBooking::class,
            'order_id',   // foreign key on trips table
            'order_id'    // owner key on customer_bookings table
        );
    }

    /**
     * Check if the trip was approved and charged via GoCab SCN.
     */
    public function isGoCabCovered(): bool
    {
        return $this->gocab_status === 'COVERED' && (float) $this->gocab_paid > 0;
    }

    /**
     * Check if the trip was submitted to GoCab but declined (not covered).
     */
    public function isGoCabUncovered(): bool
    {
        return $this->gocab_status === 'UNCOVERED';
    }

    /**
     * Check if the trip was voided on GoCab.
     */
    public function isGoCabVoided(): bool
    {
        return $this->gocab_status === 'VOID';
    }

    /**
     * Determine if this trip can be voided via GoCab SCN API.
     */
    public function canVoidGoCab(): bool
    {
        return !empty($this->gocab_payment_id) && $this->gocab_status === 'COVERED';
    }
}


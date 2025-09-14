<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory;
    use LogsActivity;


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


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()
            ->logOnlyDirty()->useLogName('Payment');

    }
    public function trip(){

        return $this->belongsTo(Trip::class,'trip_id','trip_id');
    }
}

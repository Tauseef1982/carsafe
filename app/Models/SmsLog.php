<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;
    protected $table = 'sms_logs';
    protected $fillable = [
        'trip_id',
        'driver_id',
        'to_phone',
        'message',
        'status',
        'response'
        
    ];
}

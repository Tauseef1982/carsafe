<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class Driver extends Authenticatable
{
    use Notifiable;

    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'driver_id',
        'phone',
        'role',
        'username',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public $appends = ['DriverBalance'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function fee_amount()
    {

        return $this->hasOne(DriverFee::class, 'driver_id', 'driver_id')->withDefault(['fee' => 80]);
    }


    public function weeklyFeeBalance()
    {
        // Define the start and end of the week
        $start = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        $end = now()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');

        // Log the start and end dates for debugging
//        \Log::info('Start of week: ' . $start);
//        \Log::info('End of week: ' . $end);

        // Get the total fee amount
        $total = $this->fee_amount->fee;

        // Calculate the total payment within the week for this driver
        $payment = Payment::where('is_delete', 0)
            ->where('driver_id', $this->driver_id)
            ->whereNull('trip_id')
            ->whereBetween('payment_date', [$start, $end])  // Fix date comparison to be a range
            ->where('user_type', 'driver')
            ->where('type', 'debit')
            ->sum('amount');

        // Calculate the remaining balance
        $remaining = $total - $payment;

        return $remaining;
    }
    public function weeklyPaid()
    {

        $start = now()->startOfWeek()->format('Y-m-d');
        $today = now()->format('Y-m-d');
        $payment = Payment::where('is_delete',0)->where('driver_id',$this->driver_id)->whereNull('trip_id')->where('payment_date', '>=', $start)->where('payment_date', $today)->sum('amount');

        return $payment;
    }

    public function balance_details($from = null,$to =null,$apply = false){


        $balance_credit = Payment::where('is_delete', 0)->where('driver_id',$this->driver_id)->where('type', 'credit');
        if ($apply == true) {
            $balance_credit = $balance_credit->whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to);
        }
        $balance_credit = $balance_credit->sum('amount');

        $balance_debit = Payment::where('is_delete', 0)->where('user_type','!=','customer')->where('driver_id',$this->driver_id)->where('type', 'debit');
        if ($apply == true) {
            $balance_debit = $balance_debit->whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to);
        }
        $balance_debit = $balance_debit->sum('amount');

        $adjust = Adjustment::where('driver_id',$this->driver_id)->where('type','debit_driver_balance');
        if ($apply == true) {
            $adjust = $adjust->whereDate('date', '>=', $from)->whereDate('date', '<=', $to);
        }
        $adjust = $adjust->sum('amount');

        $adjust_debit = Adjustment::where('driver_id',$this->driver_id)->where('type','admin_paid_auto');
        if ($apply == true) {
            $adjust_debit = $adjust_debit->whereDate('date', '>=', $from)->whereDate('date', '<=', $to);
        }
        $adjust_debit = $adjust_debit->sum('amount');


        $balance = (float)$balance_credit - (float)$balance_debit;
        $balance = $balance - $adjust;
//        $balance = $balance - $adjust_debit;

         return $balance;

    }
    public function balance()
    {

        $balance = Payment::where('is_delete',0)->where('driver_id',$this->driver_id)->where('type','credit')->sum('amount');
        $balance_debit = Payment::where('is_delete',0)->where('user_type','!=','customer')->where('driver_id',$this->driver_id)->where('type','debit')->sum('amount');
        $adjust = Adjustment::where('driver_id',$this->driver_id)->where('type','debit_driver_balance')->sum('amount');
        $adjust_debit = Adjustment::where('driver_id',$this->driver_id)->where('type','admin_paid_auto')->sum('amount');

        $balance = ((float)$balance - (float)$balance_debit);
        $balance =  $balance - $adjust;
//        $balance = $balance - $adjust_debit;
        return $balance;
    }

    public function trips()
    {

        return $this->hasMany(Trip::class, 'driver_id', 'driver_id');

    }

    public function getDriverBalanceAttribute()
    {

        $this->balance();
    }

    public function complaints()
{
    return $this->hasMany(driverComplaint::class);
}

public function documents()
{
    return $this->hasMany(Document::class);
}

}

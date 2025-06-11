<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    use HasFactory;

    protected $table = 'accounts';

    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'password' => 'hashed',
    ];
    public function trips()
    {
        return $this->hasMany(Trip::class, 'account_number', 'account_id');
    }

    public function totalcost($to_date = null)
    {
        $return =  $this->trips()
            ->where(function($query) {
                $query->where('payment_method','account');
            })
            ->where('is_delete', 0)
            ->where('date', '>', '2024-09-05');
            if($to_date != null){
                $return = $return->where('date','<=',$to_date);
            }
        //  $return = $return->sum('trip_cost');
             $return = $return->sum('trip_cost');
            return $return;
    }



    public function card(){

        return  $this->hasOne(CreditCard::class,'account_id','account_id')->where('is_deleted', 0);
    }

    public function cards(){

        return  $this->hasMany(CreditCard::class,'account_id','account_id');
    }

    public function totalPaidAmountByCustomerFromAccount(){

        $ids = $this->trips->pluck('trip_id');
        $return  = Payment::whereIn('trip_id',$ids)->where('is_delete',0)->where('user_type','customer')->where('type','debit')->sum('amount');
        return $return;
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_client', 'account_id', 'discount_id');
    }

public function allowedAddresses()
{
    return $this->hasMany(AllowedAddress::class);
}

}

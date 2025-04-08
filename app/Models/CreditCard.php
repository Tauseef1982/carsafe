<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    use HasFactory;


    protected $fillable = [
        'id',
        'account_id',
        'account_number',
        'account_name',
        'type',
        'card_number',
        'cvc',
        'expiry',
        'card_zip',
        'cardnox_token',
        'created_at',
        'updated_at',
        'is_deleted',
    ];


    public function account(){
        return $this->belongsTo(Account::class,'account_id','account_id');
    }
}

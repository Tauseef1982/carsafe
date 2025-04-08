<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPayment extends Model
{
    use HasFactory;
    protected $table = 'account_payments';
    protected $fillable = [
        'account_id',
        'account_type',
        'trip_id',
        'batch_id',
        'amount',
        'payment_date',
        'transaction_id',
        'payment_type',
        'ref_no',
        'hash_id',
        'invoice_from_date',
        'invoice_to_date',
        'status',
        'try',
        'email_sends',
        'due_date',
        'trip_ids'
        ];


    public function account(){


        return $this->belongsTo(Account::class,'account_id','account_id');
    }
}

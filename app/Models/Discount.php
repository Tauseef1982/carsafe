<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'discount_client', 'discount_id', 'account_id');
    }
    
}

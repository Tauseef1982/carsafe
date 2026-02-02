<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QrCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'code',
        'expires_at',
        'used_at',
    ];

     public function isExpired()
    {
        // OPTION A: expires_at column
        if ($this->expires_at) {
            return Carbon::now()->greaterThan($this->expires_at);
        }

        // OPTION B: time-based (e.g. valid for 5 minutes)
        // return $this->created_at->addMinutes(5)->isPast();

        return false;
    }
}

<?php

namespace App\Services;
use App\Models\Discount;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\SmsLog;


class DiscountService
{


    public static function AvailableDiscount()
    {

        $today = now()->toDateString();
        $Discounts = Discount::where('start_date','<=',$today)->where('end_date','>=',$today)->where('status',1)->max('percentage');

        return $Discounts;
    }


}

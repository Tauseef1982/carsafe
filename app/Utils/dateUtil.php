<?php

namespace App\Utils;



use Carbon\Carbon;

class dateUtil
{

    public function format_date($date)
    {

        $date = Carbon::parse($date)->format('m/d/Y');
        return $date;

    }

    public function time_format($time)
    {

        $date = Carbon::parse(now()->format('Y-m-d').' '.$time)->format('g:i A');
        return $date;

    }
    public function time_format2($time)
    {
        if (empty($time)) {
            return 'Invalid time';
        }
    
        try {
            // Match the correct format of the provided datetime string
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $time)->format('g:i A');
            return $date;
        } catch (\Exception $e) {
            return 'Invalid time format';
        }
    }
    
    

}

<?php

use Illuminate\Support\Carbon;

if (!function_exists('format_date')) {
    function format_date($date)
    {
        $date = Carbon::parse($date)->format('m/d/Y');
        return $date;
    }
}

if (!function_exists('time_format')) {
    function time_format($time)
    {
        $date = Carbon::parse(now()->format('Y-m-d').' '.$time)->format('g:i A');
        return $date;
    }
}

if (!function_exists('date_time_formate')) {
    function date_time_formate($data)
    {
        $date = Carbon::parse($data)->format('m/d/Y g:i A');
        return $date;
    }
}

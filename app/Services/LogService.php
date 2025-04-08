<?php

namespace App\Services;
use App\Models\Log;
use App\Models\TripEditHistory;
use Illuminate\Support\Facades\Auth;



class LogService
{


    public static function saveLog($data)
    {
        $log = new Log();
        $log->from = $data['from'];
        if (Auth::guard('admin')->check()) {
            $log->loger_id = Auth::guard('admin')->user()->id;
        } elseif (Auth::guard('driver')->check()) {
            $log->loger_id = Auth::guard('driver')->user()->id;
        } else {
            // Handle case when no user is authenticated
            $log->loger_id = 0; // or some default value
        }
        $log->message = $data['message'];
        $log->data = json_encode($data);
        $log->save();

    }


    public static function historyEditTripIfAdminPaymentExists($data_c){

        $trip = $data_c['trip'];
        $payment = $data_c['payment'];
        $diff = $data_c['diff'];
        $type = $data_c['type'];

        $reason = '';
        if(isset($data_c['reason'])){
            $reason = $data_c['reason'];
        }

        if($diff != ""){
            $data = new TripEditHistory();
            $data->amount = $diff;
            $data->trip_id = $trip->trip_id;
            $data->driver_id = $trip->driver_id;
            $data->date = now()->toDateString();
            $data->time = now()->toTimeString();
            $data->type = $type;
            $data->old_data = json_encode($data_c);
            $data->description = $reason.' , when tripCost was '.$trip->trip_cost.' including extras '.$trip->extra_charges;
            $data->save();

        }else{
            $data = new TripEditHistory();
            $data->amount = $data_c['amount'] ? $data_c['amount'] : $payment->amount;
            $data->trip_id = $trip->trip_id;
            $data->driver_id = $trip->driver_id;
            $data->date = now()->toDateString();
            $data->time = now()->toTimeString();
            $data->type = $payment->type;
            $data->old_data = json_encode($data_c);
            $data->description = $reason.'when tripCost was '.$trip->trip_cost.' including extras '.$trip->extra_charges;
            $data->save();

        }




    }



}

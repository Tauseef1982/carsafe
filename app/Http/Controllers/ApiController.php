<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\CreditCard;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
use App\Services\LogService;
use App\Services\TokenService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    public function createTrip(Request $request)
    {

        if (!isset($request->apiKey) || !isset($request->driverId) || !isset($request->pickupAddress)) {

            return response()->json(['status' => false, 'msg' => 'Please Provide Proper Data. apiKey,pickupAddress or driverId missing']);

        }

        if (!isset($request->amount)) {
            return response()->json(['status' => false, 'msg' => 'Please Provide Proper Data. amount missing']);

        }

        $apikey = $request->apiKey;
        if ($apikey != 'iekkdiakczggmieikdlaiejkdk') {
            return response()->json(['status' => false, 'msg' => 'Unauthorized Invalid apiKey']);

        }

        if(!Driver::where('driver_id',$request->driverId)->exists()){
            return response()->json(['status' => false, 'msg' => 'Driver not found']);

        }
        $tripPresent = false;
        if (isset($request->tripId)) {

            $tripid = $request->tripId;
            $tripcheck = Trip::where('trip_id',$tripid)->where('driver_id',$request->driverId)->where('payment_method','cash')->first();
            if($tripcheck){
                $tripPresent = true;

            }else{

                return response()->json(['status' => false, 'msg' => 'Driver is not matched with trip In cash']);

            }

        }else{

            $tripPresent = false;
            $tripid = random_int(10000000000,99999999999);

        }



        try {

            if($tripPresent == true){


                $tripcheck->update([

//                        'trip_id' => (int)$tripid,
                        'location_from' => $request->pickupAddress,
//                        'location_to' => null,
//                        'date' => now()->toDateString(),
//                        'time' => now()->toTimeString(),
                        'trip_cost' => $request->amount,
                        'gocab_paid' => $request->amount,
//                        'driver_id' => $request->driverId,
                        'payment_method' => 'card',
                    ]
                );

                $paycheck = Payment::where('trip_id',$tripid)->where('driver_id',$request->driverId)->sum('amount');

                if($paycheck == 0){

                    $new = new Payment();
                    $new->driver_id = $request->driverId;
                    $new->trip_id = $tripid;
                    $new->payment_date = now()->toDateString();
                    $new->amount = (float)$request->amount;
                    $new->user_id = 0;
                    $new->user_type = 'driver';
                    $new->type = 'credit';
                    $new->save();


                }
                elseif($paycheck < $request->amount){

                    $paid = $request->amount - $paycheck;

                    $new = new Payment();
                    $new->driver_id = $request->driverId;
                    $new->trip_id = $tripid;
                    $new->payment_date = now()->toDateString();
                    $new->amount = (float)$paid;
                    $new->user_id = 0;
                    $new->user_type = 'driver';
                    $new->type = 'credit';
                    $new->save();


                }

                return response()->json(['status' => true, 'msg' => 'Updated']);


            }else{

                // todo payment add
                if(Trip::where('trip_id',$tripid)->exists()){
                    return response()->json(['status' => false, 'msg' => 'trip id found but have payment method not as cash']);

                }
                Trip::create([
                    'trip_id' => (int)$tripid,
                    'location_from' => $request->pickupAddress,
                    'location_to' => null,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'trip_cost' => (float)$request->amount,
                    'driver_id' => $request->driverId,
                    'payment_method' => 'card',
                    'estimated_cost' => (float)$request->amount,
                    'is_from_api' => 1,
                    'is_manuall' => 1

                ]);


                $new = new Payment();
                $new->driver_id = $request->driverId;
                $new->trip_id = $tripid;
                $new->payment_date = now()->toDateString();
                $new->amount = (float)$request->amount;
                $new->user_id = 0;
                $new->user_type = 'driver';
                $new->type = 'credit';
                $new->save();
            }


            return response()->json(['status' => true, 'msg' => 'Created']);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);

        }

    }

     public function getWebHookTrip(Request $request){


        $tripContent = $request->getContent();
         Log::info($tripContent);
        $trip = json_decode( $tripContent, true);


        // Check if 'start' is valid
                if ($trip['start'] != '-' && $trip['start'] != '') {

                    if (isset($trip['driverId'])) {
                        if ($trip['driverId'] != '') {


                            $dateTime = Carbon::createFromFormat('m/d/Y h:i A', $trip['start']);
                            $date = $dateTime->format('Y-m-d'); // e.g., '2024-09-01'
                            $time = $dateTime->format('H:i:s'); // e.g., '12:25:00'

                            $existingTrip = Trip::where('trip_id', (int)$trip['id'])->first();


                                $to_location = $trip['route.drop_off_text'];




                            if ($existingTrip) {
                                // Check if the payment method is cash
                                if ($existingTrip->payment_method === 'cash') {
                                    if ($existingTrip->temp_data == null || $existingTrip->temp_data == '') {
                                $tsDelivered = (!empty($trip['ts.delivered']) && $trip['ts.delivered'] !== '-')
                                    ? date("Y-m-d H:i:s", strtotime($trip['ts.delivered']))
                                    : null;

                                        $ickedup = !empty($trip['icked up']) ? date("Y-m-d H:i:s", strtotime($trip['icked up'])) : null;
                                        $existingTrip->update([
                                            'location_from' => $trip['route.pick_up_text'],
                                            'location_to' => $to_location,
                                            'date' => $date,
                                            'time' => $time,
                                            'trip_cost' => !empty($trip['fx.trip_base']) && $trip['fx.trip_base'] != 0 ? $trip['fx.trip_base'] : $trip['estimatedPrice'],
                                            'driver_id' => $trip['driverId'],
                                            'account_number' => $trip['account.name'],
                                            'passenger_phone' => $trip['passenger.phone'].'',
                                            'estimated_cost' => !empty($trip['fx.trip_base']) && $trip['fx.trip_base'] != 0 ? $trip['fx.trip_base'] : $trip['estimatedPrice'],
                                            'status' => $trip['event'],
                                            'ts_delivered' => $tsDelivered,
                                            'icked_up' => $ickedup,

                                        ]);
                                        return response()->json('updated');

                                    }
                                }
                            } else {

                        $ickedup = !empty($trip['icked up']) ? date("Y-m-d H:i:s", strtotime($trip['icked up'])) : null;
                        $tsDelivered = (!empty($trip['ts.delivered']) && $trip['ts.delivered'] !== '-')
                            ? date("Y-m-d H:i:s", strtotime($trip['ts.delivered']))
                            : null;

                                Trip::create([
                                    'trip_id' => (int)$trip['id'],
                                    'location_from' => $trip['route.pick_up_text'],
                                    'location_to' => $to_location,
                                    'date' => $date,
                                    'time' => $time,
                                    'trip_cost' => !empty($trip['fx.grand_total']) && $trip['fx.grand_total'] != 0 ? $trip['fx.grand_total'] : $trip['estimatedPrice'],
                                    'trip_extras' => !empty($trip['fx.trip_extras']) && $trip['fx.trip_extras'] != 0 ? $trip['fx.trip_extras'] : 0,
                                    'driver_id' => $trip['driverId'],
                                    'account_number' => $trip['account.name'],
                                    'passenger_phone' => $trip['passenger.phone'],
                                    'estimated_cost' => !empty($trip['fx.trip_base']) && $trip['fx.trip_base'] != 0 ? $trip['fx.trip_base'] : $trip['estimatedPrice'],
                                    'status' => $trip['event'],
                                    'ts_delivered' => $tsDelivered,
                                    'icked_up' => $ickedup,

                                ]);

                              return response()->json('created');

                            }



                        }
                    }

                }
            }


}

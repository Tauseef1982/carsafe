<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\CreditCard;

use App\Models\Discount;

use App\Models\CustomerBooking;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
use App\Services\EmailService;
use App\Services\LogService;
use App\Services\PaymentSaveService;
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
          $data = [
            'from' => 'taxicaller',
            'message' => 'Webhook received from taxicaller -' . $request->comments,
            'data' => $tripContent,
        ];

        $logdata = LogService::saveLog($data);
        $trip = json_decode( $tripContent, true);

        if(!isset($trip['start']) || $trip == null){

            $logdata = \App\Models\Log::find(2);
            $tripdata = json_decode($logdata->data, true);
            $tripdata = str_replace("\t".'','',$tripdata['data']);
            $tripdata = json_decode($tripdata,true);
            $trip = $tripdata;
//            $this->fetchtrip($tripId);
        }


        try {

        // Check if 'start' is valid
                if ($trip['start'] != '-' && $trip['start'] != '') {

                    if (isset($trip['driverId'])) {
                        if ($trip['driverId'] != '') {


                            $dateTime = Carbon::createFromFormat('m/d/Y h:i A', $trip['start']);
                            $date = $dateTime->format('Y-m-d'); // e.g., '2024-09-01'
                            $time = $dateTime->format('H:i:s'); // e.g., '12:25:00'

                            $existingTrip = Trip::where('trip_id', $trip['id'])->first();

                            $to_location = $trip['route.drop_off_text'];
                            $order_id = null;
                            if(isset($trip['OrderId'])){
                                $customer_order = CustomerBooking::where('order_id',$trip['OrderId'])->first();
                                if($customer_order){
                                    $customer_order->status = 'web_hook_recieved';
                                    $customer_order->save();
                                    $order_id = $customer_order->order_id;
                                }
                            }

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
                                            'order_id' => $order_id,

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
                                    'extra_charges' => !empty($trip['fx.trip_extras']) && $trip['fx.trip_extras'] != 0 ? $trip['fx.trip_extras'] : 0,
                                    'driver_id' => $trip['driverId'],
                                    'account_number' => $trip['account.name'],
                                    'passenger_phone' => $trip['passenger.phone'],
                                    'estimated_cost' => !empty($trip['fx.trip_base']) && $trip['fx.trip_base'] != 0 ? $trip['fx.trip_base'] : $trip['estimatedPrice'],
                                    'status' => $trip['event'],
                                    'ts_delivered' => $tsDelivered,
                                    'icked_up' => $ickedup,
                                    'order_id' => $order_id,


                                ]);

                              return response()->json('created');

                            }



                        }
                    }

                }
        } catch (\Exception $e) {


            Log::Info('trip-web-hook-error');
        }



            }

   public function voiceCall(Request $request){


        $account = Account::where('account_id',$request->account)->first();
        if($account){
            return response()->json([
                'valid' => true,
                'balance' => $account->balance
            ]);
        }else{
            return response()->json([
                'valid' => false,
                'balance' => 0
            ]);
        }



    }

    public function fetchtrip($tripid){


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.taxicaller.net/api/v1/reports/typed/generate',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    "company_id" => 57068,
                    "report_type" => "jobs",
                    "output_format" => "json",
                    "template_id" => 12528,
                    "search_query" => [
                        "period" => [
                            "@type" => "custom",
                            "start" => "2025-03-01T00:00:00",
                            "end" => "2025-03-02T00:00:00",
//                    "end" => "".$from."T00:00:00",
//                    "start" => "".$to."T00:00:00"
                        ]
                    ]
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . TokenService::token(),
                    'Content-Type: application/json'
                ),
            ));


        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        $trips = $response->rows;
        $trips = collect($trips)->where('id',$tripid);

        foreach ($trips as $trip) {


        if ($trip->{'start'} != '-' && $trip->{'start'} != '') {
            if (isset($trip->{'driverId'})) {
                if ($trip->{'driverId'} != '') {


                    $dateTime = Carbon::createFromFormat('m/d/Y h:i A', $trip->{'start'});
                    $date = $dateTime->format('Y-m-d'); // e.g., '2024-09-01'
                    $time = $dateTime->format('H:i:s'); // e.g., '12:25:00'

                    $existingTrip = Trip::where('trip_id', (int)$trip->{'id'})->first();

                    if($trip->{'stops'} != '' && $trip->{'stops'} != null){
                        $to_location = $trip->{'stops'};

                    }else{
                        $to_location = $trip->{'route.drop_off_text'};

                    }


                    //todo confirm
                    if ($existingTrip) {
                        // Check if the payment method is cash
                        if ($existingTrip->payment_method === 'cash') {
                            if ($existingTrip->temp_data == null || $existingTrip->temp_data == '') {
                                $tsDelivered = !empty($trip->{'ts.delivered'}) ? date("Y-m-d H:i:s", strtotime($trip->{'ts.delivered'})) : null;
                                $ickedup = !empty($trip->{'icked up'}) ? date("Y-m-d H:i:s", strtotime($trip->{'icked up'})) : null;
                                $existingTrip->update([
                                    'location_from' => $trip->{'route.pick_up_text'},
                                    'location_to' => $to_location,
                                    'date' => $date,
                                    'time' => $time,
                                    'trip_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                                    'driver_id' => $trip->{'driverId'},
                                    'account_number' => $trip->{'account.name'},
                                    'passenger_phone' => $trip->{'passenger.phone'},
                                    'estimated_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                                    'status' => $trip->{'job.state.status_localized'},
                                    'ts_delivered' => $tsDelivered,
                                    'icked_up' => $ickedup,
                                ]);
                            }
                        }
                    } else {
                        $ickedup = !empty($trip->{'icked up'}) ? date("Y-m-d H:i:s", strtotime($trip->{'icked up'})) : null;
                        $tsDelivered = !empty($trip->{'ts.delivered'}) ? date("Y-m-d H:i:s", strtotime($trip->{'ts.delivered'})) : null;
                        Trip::create([
                            'trip_id' => (int)$trip->{'id'},
                            'location_from' => $trip->{'route.pick_up_text'},
                            'location_to' => $to_location,
                            'date' => $date,
                            'time' => $time,
                            'trip_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                            'driver_id' => $trip->{'driverId'},
                            'account_number' => $trip->{'account.name'},
                            'passenger_phone' => $trip->{'passenger.phone'},
                            'estimated_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                            'status' => $trip->{'job.state.status_localized'},
                            'ts_delivered' => $tsDelivered,
                            'icked_up' => $ickedup,
                            'first_destination'=>$trip->{'route.drop_off_text'}
                        ]);
                    }



                }
            }

        }



        }

    }

    public function tryrecharge(Request $request){

        Log::info('first-trips');

        $account_id = $request->account;

        $uaccount = Account::where('account_id',$account_id)->first();
        $trips = $uaccount->trips->where('date', '>=', '2024-10-15')->where('payment_method', 'account');

        $filteredTrips = $trips->filter(function ($trip) {
            $paid = $trip->TripPaidByCustomerFromAccount->sum('amount');
            return $trip->trip_cost > $paid;
        });
        $tids = $filteredTrips->pluck('trip_id')->toArray();
        Log::info($tids);
        return response()->json([
            'valid' => true,
            'trip_ids' => json_encode($tids)
        ]);


    }

    public function tryrechargefinal(Request $request){

        Log::info($request->all());

        $to_refill = $request->amount;
        $account_id = $request->account;

        $uaccount = Account::where('account_id',$account_id)->first(); // Retrieve the account

        $cardDetails = CreditCard::where('account_id',$account_id)->where('charge_priority',1)->where('is_deleted', 0)->first();

        if (empty($cardDetails)) {
            // if no primary then secondary
            $cardDetails = CreditCard::where('account_id',$account_id)->where('charge_priority',0)->where('is_deleted', 0)->first();
            if (empty($cardDetails)) {

                return response()->json([
                    'valid' => false,
                    'new_balace' => 'No credit card details found for Account'
                ]);


            }
        }

        $trip_ids = json_decode($request->trip_ids[0]);
        $cardknoxToken = $cardDetails->cardnox_token;
        $cardknoxResponse = CardKnoxService::processCardknoxPaymentRefill($cardknoxToken, $to_refill, $account_id);

        if ($cardknoxResponse['status'] == 'approved') {

            $account_payment = new AccountPayment();
            $account_payment->account_id = $uaccount->account_id;
            $account_payment->account_type = $uaccount->account_type;
            $account_payment->amount = $to_refill;
            $account_payment->transaction_id = $cardknoxResponse['transaction_id'];
            $account_payment->payment_date = Carbon::today();
            $account_payment->payment_type = 'card';
            $account_payment->save();
            if($uaccount->account_type == 'postpaid'){

                $trips_to_be_paid = Trip::whereIn('trip_id',$trip_ids)->get();
                Log::info('inside');
                $total_payments = 0;
                $batch_p = new BatchPayment();
                $batch_p->account_id = $uaccount->account_id;
                $batch_p->from = 'trips_paid_with_upfront_credit';
                $batch_p->amount = $total_payments;
                $batch_p->save();
                Log::info('Batch='.$batch_p->id);
                Log::info('refill='.$to_refill);

                foreach ($trips_to_be_paid as $paytrip) {

                    $unpaid_amount = $paytrip->trip_cost;
                    Log::info($paytrip->trip_id);

                    // Only pay if unpaid amount is <= available to_refill
                    if ($unpaid_amount > 0 && $unpaid_amount <= $to_refill) {
                        //$pay_data = $this->addpay_customer($paytrip, $request,  $batch_p->id);
                        $new = new Payment();
                        $new->driver_id = $paytrip->driver_id;
                        $new->trip_id = $paytrip->trip_id;
                        $new->payment_date = now()->toDateString();
                        $new->amount = (float)$paytrip->trip_cost;
                        $new->user_id = 0;
                        $new->user_type = 'customer';
                        $new->type = 'debit';
                        $new->batch_id = $batch_p->id;
                        $new->description = 'payment_added_from_twilio_' . $request['account_id'];
                        $new->account_id = $paytrip->account_number;
                        $new->save();
                        $total_payments += $unpaid_amount;
                        $to_refill -= $unpaid_amount; // update to_refill after payment
                        Log::info($unpaid_amount);

                    }

                }

                $batch_p->amount = $total_payments;
                $batch_p->save();
                $account_payment->batch_id = $batch_p->id;
                $account_payment->save();

            }

                $uaccount->balance += $to_refill;
                $uaccount->save();


                if($uaccount->account_type == 'prepaid') {

                    if ($uaccount->balance > 0) {
                        $uaccount->status = 1;
                        if ($uaccount->cube_id == null || $uaccount->cube_id == '') {
                          //  CubeContact::createAccount($uaccount->account_id);
                        }
                        // CubeContact::updateCubeAccount($uaccount->account_id,null,'active');

                        $uaccount->save();
                    }
                }



            $logdata = [
                'from' => 'customer',
                'payment' => $to_refill,
                'cardknox_response' => $cardknoxResponse,
                'message' => 'Refill Payment added using Cardknox for Account#' . $account_id . ' Amount: ' . $to_refill
            ];
            LogService::saveLog($logdata);
            Log::info('finallllllll');


            DB::commit();
        } elseif ($cardknoxResponse['status'] == 'declined') {

            return response()->json([
                'valid' => false,
                'message' => 'Card Decline'
            ]);

        } else {
            return response()->json([
                'valid' => false,
                'message' => 'Payment Failed'
            ]);

        }

        return response()->json([
            'valid' => true,
            'message' => 'Charge Successfully'
        ]);


    }

    public function addcard(Request $request){

        Log::info($request->all());
          $checkCard = CreditCard::where('account_id', $request->account_id)->first();
        $creditCard = new CreditCard;
        $creditCard->account_id = $request->account_id;

        if ($checkCard) {

            $creditCard->charge_priority = 0;

        }
         $cardNumber = $request->card_number;
            $maskedCard = substr($cardNumber, 0, 1) . str_repeat('*', strlen($cardNumber) - 5) . substr($cardNumber, -4);

            $creditCard->card_number = $maskedCard;
            $creditCard->cvc = $request->cvc;
            // Process expiry date

                $month = $request->expiry_month;
                $year = $request->expiry_year;
                $expiryWithoutSlash = $month.$year;


            $fullYear = '20' . $year;
            $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();
            $creditCard->expiry = $expiryDate;

            // Remove the slash from expiry for CardKnoxService

            // Call CardKnoxService to save the card
            $cardResponse = CardKnoxService::saveCard(
                $request->account_id,
                'credit',
                $request->card_number,
                $expiryWithoutSlash,
                $request->card_zip
            );
             if ($cardResponse['status']) {
            $creditCard->cardnox_token = $cardResponse['data']['xToken'];
            $creditCard->save();



                 return response()->json([
            'valid' => true,
            'message' => 'Charge Successfully'
        ]);

        } else {

                 return response()->json([
            'valid' => false,
            'message' => 'Error in proccessing please try again'
        ]);

        }


    }

    public function driverByVehicle(Request $request)
    {

        Log::info('finddriver');
        Log::info($request);

        $current_time = Carbon::now();
        $vehicle_number = $request->v_number;

        // Find driver by last name pattern
        $driver = Driver::where('last_name', 'LIKE', '%' . $vehicle_number . '%')->first();

        if ($driver) {
            $driver_v_number = explode('-', $driver->last_name);

            if (isset($driver_v_number[1]) && $driver_v_number[1] == $vehicle_number) {

                $trip = Trip::where('trips.is_delete', 0)
                    ->where('trips.driver_id', $driver->driver_id)
                    ->where('trips.date', '>', now()->subDays(3))
                    ->where('trips.payment_method', 'cash')
                    ->where('status', 'NOT LIKE', '%Cancelled%')
                    ->where('status', 'NOT LIKE', '%Client canceled%')
                    ->whereNotNull('icked_up') // Fixed typo
                    ->where('icked_up', '!=', '')
                    ->where(function ($query) {
                        $query->whereNull('ts_delivered')
                            ->orWhereRaw("COALESCE(ts_delivered, '') = ''")
                            ->orWhereBetween('ts_delivered', [
                                now()->subMinutes(15)->format('Y-m-d H:i:s'),
                                now()->format('Y-m-d H:i:s')
                            ]);
                    })
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('trips as future_trips')
                            ->whereColumn('future_trips.driver_id', 'trips.driver_id')
                            ->where('future_trips.icked_up', '>', DB::raw('trips.icked_up'));
                    })
                    ->select('trips.*')
                    ->orderBy('trips.date', 'desc')
                    ->orderBy('trips.time', 'desc')
                    ->limit(1)
                    ->first();

                if ($trip) {
                    $trip_time = Carbon::parse($trip->time);

                    $diffInMinutes = $trip_time->diffInMinutes($current_time, false); // false = signed diff

                    if ($diffInMinutes >= -30 && $diffInMinutes <= 30) {
                        return response()->json([
                            'valid' => true,
                            'trip_id' => $trip->trip_id,
                            'driver_id' => $driver->driver_id
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'valid' => false,
            'trip_id' => 0,
            'driver_id' => 0
        ]);
    }

    public function payTripAccount(Request $request)
    {
        Log::info($request);
        $account_number = $request->account_number;
        $account_pin = $request->account_pin;
        $trip_id = $request->trip_id;

        $account = Account::where('account_id',$account_number)->first();
        if(!$account){
            return response()->json([
                'move_to' => 2,
                'msg' => ''
            ]);
        }
        if($account->status == 0){

            return response()->json([
                'move_to' => 4,
                'msg' => ''
            ]);

        }

        $pins = explode(',',$account->pins);
        if(!in_array($account_pin,$pins)){
            return response()->json([
                'move_to' => 3,
                'msg' => ''
            ]);
        }

        DB::beginTransaction();
        $trip = Trip::where('trip_id',$trip_id)->first();

        try {

                        $cost = (float) $trip->trip_cost;

                        $discount = Discount::select('discounts.*')
                            ->join('discount_client', 'discounts.id', '=', 'discount_client.discount_id')
                            ->where('discount_client.account_id', $account->id)
                            ->where('discounts.start_date', '<=', now())
                            ->where('discounts.end_date', '>=', now())
                            ->where('discounts.status', 1)
                            ->orderBy('discounts.created_at', 'desc')
                            ->first();


                        if ($discount) {
                            $disc_amount = $cost * ($discount->percentage / 100);
                            $trip->discount_perc = $discount;
                            $trip->discount_amount = $disc_amount;
                        }

                        $trip_cost = $cost;
                        $trip->trip_cost = $trip_cost;
                        $trip->gocab_paid = $trip_cost;
                        $trip->payment_method = 'account';
                        $trip->cube_pin_status = $request->account_pin;
                        $trip->extra_charges = 0;

                        $trip->account_number = $account_number;

                        if ($account->account_type == 'prepaid') {

                            if ($account->balance >= $cost) {

                                $trip->update();
                                $this->prepaidAccountDeduction($trip, $account);
                                $account->balance = $account->balance - $cost;
                                $account->save();
                            } else {

                                return response()->json([
                                    'move_to' => 5,
                                    'msg' => ''
                                ]);

                            }
                        }


                        $trip->update();
                        $pay_data = $this->addpay($trip, $request);
                        $tripId = $trip->trip_id;
                        $driverId = $trip->driver_id;
                        $extra_message = null;


                        $logdata = array();
                        $logdata['from'] = 'driver';
                        $logdata['payment'] = $pay_data;
                        $logdata['trip'] = $trip;
                        $logdata['message'] = 'from twillio call';


                        LogService::saveLog($logdata);
//                        $this->ifBalanceMinusAutoPaidAsAdmin($trip);

//                        DB::rollBack();
                        DB::commit();
            return response()->json([
                'move_to' => 1,
                'msg' => ''
            ]);


        } catch (\Exception $e) {

            DB::rollBack();
            Log::Info('twilio'.$e->getMessage());
        }
        DB::rollBack();

    }

    public function payTripCard(Request $request)
    {

        Log::info($request);

        $month = $request->expiry_month;
        $year = $request->expiry_year;
        $expiryWithoutSlash = $month.$year;

        // Remove the slash from expiry for CardKnoxService
        // Call CardKnoxService to save the card
        $cardResponse = CardKnoxService::saveCard(
            $request->account_id,
            'credit',
            $request->card_number,
            $expiryWithoutSlash,
            $request->card_zip
        );
        if ($cardResponse['status']) {

            return response()->json([
                'valid' => true,
                'tokenn' => base64_encode($cardResponse['data']['xToken'])
            ]);

        } else {

            return response()->json([
                'valid' => false,
                'message' => 'Error in proccessing please try again'
            ]);

        }


    }

    public function payTripCard2(Request $request)
    {

        Log::info($request);

        $trip_id = $request->trip_id;
//        $trip_id = '277151862';
//        $request->tokenn = 'MzU3cTM3Z20ybTA1OXE2OTkzODI2Mm01N3FxMzg5NTI=';
        $cardknoxToken = base64_decode($request->tokenn);

        DB::beginTransaction();
        $trip = Trip::where('trip_id', $trip_id)->first();

        $originalAmount = $trip->trip_cost;

        $fee = ((float)$originalAmount * 0.03333333333) + .3;
        $cardknoxAmount = $originalAmount + $fee;

        $desc = 'Twilio:Carsafe Payment Trip#' . $trip->trip_id . ' driver#' . $trip->trip_id . ' Total Amount=' . $cardknoxAmount . ' , without Fee' . $originalAmount;
        $charge = CardKnoxService::processPayment($cardknoxToken, $cardknoxAmount, $desc);

        if ($charge['status'] == 'approved') {

            $data['trip_cost'] = $originalAmount;
            $data['gocab_paid'] = $originalAmount;
            $data['payment_method'] = 'card';
            $data['stripe_id'] = $charge['transaction_id'];

            $trip->update($data);

            $pay_data = $this->addpay($trip, $request);

            $logdata = array();
            $logdata['from'] = 'driver';
            $logdata['payment'] = $pay_data;
            $logdata['trip'] = $trip;
            $logdata['strip'] = $charge;

            $logdata['message'] = 'Twillio:Trip Payment Added By Driver Using Method Card#' . $charge['transaction_id'] . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

            LogService::saveLog($logdata);
            // $this->ifBalanceMinusAutoPaidAsAdmin($trip);
            DB::commit();

            return response()->json([
                'valid' => true,
                'message' => ''
            ]);

        } else {

            DB::rollBack();
            return response()->json([
                'valid' => false,
                'message' => 'Card Decline'
            ]);

        }
    }

    public function prepaidAccountDeduction($trip, $account)
    {
        $new = new Payment();
        $new->driver_id = $trip->driver_id;
        $new->trip_id = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float) $trip->trip_cost - $trip->discount_amount;
        $new->user_id = $account->id;
        $new->user_type = 'customer';
        $new->type = 'debit';
        $new->description = 'deduct_from_customer_prepaid_account_against_trip';
        $new->account_id = $account->account_id;
        $new->save();

        return $new;
    }

    public function addpay($trip, $request)
    {

        $new = new Payment();
        $new->driver_id = $trip->driver_id;
        $new->trip_id = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float) $trip->gocab_paid;
        $new->user_id = $trip->driver_id;
        $new->user_type = 'driver';
        $new->type = 'credit';
        if (isset($request->is_admin)) {
            $new->description = 'admin_acceptt';
        } elseif (isset($request->is_driver)) {
            $new->description = 'driver_acceptt';
        } else {
            $new->description = 'no_auth';
        }

        $new->save();

        return $new;
    }


}

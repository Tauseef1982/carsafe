<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\CreditCard;
use App\Models\CustomerBooking;
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
            }

    public function voiceCall(Request $request){

        Log::info($request->all());

        $account = Account::where('account_id',$request->account)->first();
        if($account){
            return response()->json([
                'valid' => true,
                'balance' => 25.75
            ]);
        }else{
            return response()->json([
                'valid' => false,
                'balance' => 0
            ]);
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
            $expiry = $request->input('expiry');

            if(isset($request->month) && isset($request->year)){
                $month = $request->month;
                $year = $request->year;
                $expiryWithoutSlash = $month.$year;

            }else{
                list($month, $year) = explode('/', $expiry);
                $expiryWithoutSlash = str_replace('/', '', $expiry);

            }

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


}

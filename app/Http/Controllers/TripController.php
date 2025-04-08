<?php

namespace App\Http\Controllers;

use App\Console\Commands\SyncContacts;
use App\Models\Account_Complaint;
use App\Models\Adjustment;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\Account;
use App\Models\Discount;
use App\Models\TripEditHistory;
use App\Services\AccountService;
use App\Services\CardKnoxService;
use App\Services\CustomPagination;
use App\Services\DiscountService;
use App\Services\EmailService;
use App\Services\LogService;
use App\Services\CubeContact;
use App\Services\PaymentSaveService;
use App\Services\TokenService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\CardException;
use App\Services\TwilioService;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isFalse;


class TripController extends Controller
{
    public function latestThree(Request $request)
    {

        $driverId = Auth::guard('driver')->user()->driver_id;

        // Check if the request is AJAX
        if ($request->ajax()) {

            $trips = Trip::where('trips.is_delete', 0)
                ->where('trips.driver_id', $driverId)
                ->where('trips.date', '>', now()->subDays(3))
                ->where('trips.payment_method', 'cash')
                ->where('status', 'NOT LIKE', '%Cancelled%')
                ->where('status','NOT LIKE', '%canceled%')
                ->select('trips.*')
                ->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc')
                ->limit(2)
                ->get();

            $discount = DiscountService::AvailableDiscount();

            return response()->json([
                'trips' => $trips,
                'discount' => $discount
            ]);
        }

        // If not an AJAX request, return the full view
        return view('driver.payment'); // Adjust this to your actual view file
    }


    public function latestThreeNew()
    {

        $driverId = Auth::guard('driver')->user()->driver_id;

        //sleep(1);
        $driver = Driver::where('driver_id',$driverId)->first();
        if (empty($driver->cube_id)) {
            return redirect()->to('dashboard')->with('error', 'Contact is not available in cube.');
        }
        // $trips = Trip::where('trips.is_delete', 0)
        //     ->where('trips.driver_id', $driverId)
        //     ->where('trips.date', '>', now()->subDays(3))
        //     ->where('trips.payment_method', 'cash')
        //     ->where('status', 'NOT LIKE', '%Cancelled%')
        //    ->where('status', 'NOT LIKE', '%Client canceled%')
        // ->whereNotNull('icked_up')
        //     ->where('icked_up', '!=', '')
        //     ->where(function ($query) {
        //         $query->whereNull('ts_delivered')
        //             ->orWhereRaw("COALESCE(ts_delivered, '') = ''")
        //             ->orWhereBetween('ts_delivered', [now()->subMinutes(15)->format('Y-m-d H:i:s'), now()->format('Y-m-d H:i:s')]);
        //     })
        //     ->select('trips.*')
        //     ->orderBy('trips.date', 'desc')
        //     ->orderBy('trips.time', 'desc')
        //     ->limit(1)
        //     ->get();
        $trips = Trip::where('trips.is_delete', 0)
            ->where('trips.driver_id', $driverId)
            ->where('trips.date', '>', now()->subDays(3))
            ->where('trips.payment_method', 'cash')
            ->where('status', 'NOT LIKE', '%Cancelled%')
            ->where('status', 'NOT LIKE', '%Client canceled%')
            ->whereNotNull('icked_up')
            ->where('icked_up', '!=', '')
            ->where(function ($query) {
                $query->whereNull('ts_delivered')
                    ->orWhereRaw("COALESCE(ts_delivered, '') = ''")
                    ->orWhereBetween('ts_delivered', [now()->subMinutes(15)->format('Y-m-d H:i:s'), now()->format('Y-m-d H:i:s')]);
            })
            ->whereNotExists(function ($query) use ($driverId) {
                $query->select(DB::raw(1))
                    ->from('trips as future_trips')
                    ->whereColumn('future_trips.driver_id', 'trips.driver_id')
                    ->where('future_trips.icked_up', '>', DB::raw('trips.icked_up'));

            })
            ->select('trips.*')
            ->orderBy('trips.date', 'desc')
            ->orderBy('trips.time', 'desc')
            ->limit(1)
            ->get();

        return view('driver.payment_new', compact('trips'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function alltrips(Request $request)
    {

        sleep(1);


        if (request()->ajax()) {


            $from = Carbon::now()->toDateString();
            $to = Carbon::now()->toDateString();
            $offset = $request->offset;

            if ($request->has('from_date') && $request->has('to_date')) {

                if ($request->from_date != '' && $request->to_date != '') {
                    $from = $request->from_date;
                    $to = $request->to_date;

                }
            }

            $driver_id = Auth::guard('driver')->user()->driver_id;
            $trips = Trip::where('is_delete', 0)->where('driver_id', $driver_id)
                ->where(function ($query) {
                    $query->where('status', 'not like', '%Cancelled%')
                        ->orWhere('status', 'not like', '%canceled%')
                        ->orWhereNull('status');
                })->where(function ($query) {
                    $query->where('payment_method', '!=', 'card') // Include trips where payment method is not card
                    ->orWhere(function ($subQuery) { // Nested condition for card payment method
                        $subQuery->where('payment_method', 'card')
                            ->where('trip_cost', '>', 0); // Include trips with card only if trip_cost > 0
                    });
                })
//                ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->offset($offset)
                ->limit(10)
                ->get();

            return view('driver.load_history', compact('trips'));

        }
        return view('driver.history');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $trip = Trip::find($id);
        return view('driver.trip-details', compact('trip'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        // $discount = DiscountService::AvailableDiscount();

        DB::beginTransaction();
        if ($request->has('trip')) {

            $trip = Trip::where('trip_id',$request->trip)->first();
            $existingPayment = Payment::where('trip_id', $trip->trip_id)->where('user_type', 'driver')
                ->where('type','credit')->where('is_delete',0)->first();

            if ($existingPayment) {
                return redirect()->back()->with('error', 'Payment already exists for this trip.');
            }

        }

        if(isset($request->accept_by)) {
            $trip->accepted_by = $request->accept_by;
        }

        if ($request->has('trip') && $request->payment_method == 'account') {

            // if cron has empty account says not found
            // if ($trip->account_number == null || $trip->account_number == '') {

            //     return redirect()->back()->with('error', 'Trip Account Not Match');

            // }

            if (!empty($trip->account_number) && $request->account !== $trip->account_number) {
                return redirect()->back()->with('error', 'Trip Account Not Match');
            }

            $account = Account::where('account_id', $request->account)->first();

            if ($account) {
                if ($account->status == 1) {
                    if ($trip->trip_cost == 0) {
                        $cost = (float)$request->amount;
                    } else {
                        $cost = (float)$trip->trip_cost;
                    }


                    $extraCharges = $request->extra_charges;
                    $cost = $cost + (float)$extraCharges;

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
                    $trip->extra_charges = $request->extra_charges;

                    if (isset($request->stop_amount)) {
                        $trip->extra_stop_amount = $request->stop_amount;
                        $trip->stop_location = $request->stop_location;
                    }

                    if (isset($request->wait_amount)) {
                        $trip->extra_wait_amount = $request->wait_amount;
                    }
                    if (isset($request->round_trip)) {
                        $trip->extra_round_trip = $request->round_trip;
                    }


                    $trip->account_number = $request->account;
                    if (isset($request->complaint)) {
                        $trip->complaint = $request->complaint;
                        $trip->is_complaint = '1';
                    }


                    if ($account->account_type == 'prepaid') {

                        if ($account->balance < 20) {
                            if ($account->autofill == "on") {

                                $account_responce = PaymentSaveService::prepPaidRefill($account,"single");

                                if($account_responce != false){
                                    $account = $account_responce;
                                    Log::info("prepaid refill suddenly");


                                }
                            }
                        }
                        if ($account->balance >= $cost) {

                            $trip->update();
                            $this->prepaidAccountDeduction($trip,$account);
                            $account->balance = $account->balance - $cost;
                            $account->save();
                        } else {

                            \App\Services\TwilioService::voicecall($account->phone,'refill-need');
//                            CubeContact::deleteAccount($account->account_id);
                            CubeContact::updateCubeAccount($account->account_id,"Your Account Balance Is zero","Inactive");

                            // todo add balance if auto refill is on then again
                            return redirect()->back()->with('error', 'Prepaid Account:Low Balance');


                        }
                    }

                    if($account->account_type == 'postpaid'){
                        if($account->balance >= $cost) {

                            $paymentDataBulk[] = [
                                'driver_id' => $trip->driver_id,
                                'trip_id' => $trip->trip_id,
                                'payment_date' => now()->toDateString(),
                                'amount' => $cost,
                                'user_id' => auth()->user()->id,
                                'user_type' => 'customer',
                                'type' => 'debit',
                                'description' => 'Paying from PostPaid balance:customer_pay_to_account' . $account->account_id,
                                'account_id' => $trip->account_number,
                            ];
                            $paymentDataSend = [
                                'payments' => $paymentDataBulk,
                            ];
                            PaymentSaveService::save($paymentDataSend);

                            $account->balance = $account->balance - $cost;
                            $account->save();
                        }

                    }


                    $trip->update();
                    $pay_data = $this->addpay($trip, $request);
                    $tripId = $trip->trip_id;
                    $driverId = $trip->driver_id;
                    $extra_message = null;


                    if (isset($request->stop_amount)) {
                        $extraStopCharges = '$' . $request->stop_amount;
                        $stoplocation = $request->stop_location;
                        $extra_message .= "Extra Stop Charges: {$extraStopCharges}\nStop Location: {$stoplocation}. ";

                    }

                    if (isset($request->wait_amount)) {
                        $extraWaitCharges = '$' . $request->wait_amount;
                        $extra_message .= "Extra Wait Charges: {$extraWaitCharges}. ";

                    }
                    if (isset($request->round_trip)) {
                        $extraRoundCharges = '$' . $request->round_trip;
                        $extra_message .= "Extra Round Trip: {$extraRoundCharges}. ";
                    }


                    if (isset($request->is_driver)) {


                        if ($account->notification_setting == null) {
                            $phone = preg_replace('/[^0-9]/', '', $trip->passenger_phone);
                            $this->sendNotif($phone, $cost, $extraCharges, $tripId, $driverId, $extra_message);

                        } elseif ($account->notification_setting == 'account_email') {

                            EmailService::send($account->email, $cost, $extraCharges, $extra_message);

                        } elseif ($account->notification_setting == 'passenger_phone') {

                            $phone = preg_replace('/[^0-9]/', '', $trip->passenger_phone);
                            $this->sendNotif($phone, $cost, $extraCharges, $tripId, $driverId, $extra_message);


                        } elseif ($account->notification_setting == 'account_phone') {
                            $phone = preg_replace('/[^0-9]/', '', $account->phone);
                            $this->sendNotif($phone, $cost, $extraCharges, $tripId, $driverId, $extra_message);

                        } else {

                            $phone = preg_replace('/[^0-9]/', '', $account->phone);
                            $this->sendNotif($phone, $cost, $extraCharges, $trip->trip_id, $trip->driver_id, $extra_message);
                            $phone = preg_replace('/[^0-9]/', '', $trip->passenger_phone);
                            $this->sendNotif($phone, $cost, $extraCharges, $trip->trip_id, $trip->driver_id, $extra_message);

                        }


                    }

                    $logdata = array();
                    $logdata['from'] = 'driver';
                    $logdata['payment'] = $pay_data;
                    $logdata['trip'] = $trip;

                    if (isset($request->is_admin)) {
                        $logdata['message'] = 'Admin:Trip Payment Added By Driver Using Method Account#' . $request->account . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                    } elseif (isset($request->is_driver)) {
                        $logdata['message'] = 'Trip Payment Added By Driver Using Method Account#' . $request->account . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                    } else {
                        $logdata['message'] = 'Trip Payment Added By Driver Using Method Account#' . $request->account . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                    }

                    LogService::saveLog($logdata);
                    $this->ifBalanceMinusAutoPaidAsAdmin($trip);

                    DB::commit();
                    if (isset($request->is_admin)) {
                        $id = $trip->driver->id;
                        $trip_id = $trip->trip_id;
                        $paid_cost = $trip->trip_cost;
                        return view('admin.success', compact('id', 'trip_id', 'paid_cost'));

                    } else {
                        $trip_id = $trip->trip_id;
                        $paid_cost = $trip->trip_cost;
                        return view('driver.success', compact('trip_id', 'paid_cost'));

                    }

                } else {


                    DB::rollBack();
                    $logdata = array();
                    $logdata['from'] = 'driver';
                    if (isset($request->is_admin)) {
                        $logdata['message'] = 'Admin:Error : Inactive Account Entring  Account#' . $request->account . ' Trip#' . $trip->trip_id;

                    } elseif (isset($request->is_driver)) {
                        $logdata['message'] = 'Error : Inactive Account Entring  Account#' . $request->account . ' Trip#' . $trip->trip_id;

                    } else {
                        $logdata['message'] = 'Error : Inactive Account Entring  Account#' . $request->account . ' Trip#' . $trip->trip_id;

                    }

                    $logdata['trip'] = $trip;

                    LogService::saveLog($logdata);
                    return redirect()->back()->with('error', 'This account is inactive. Please try a different one');
                }
            } else {
                return redirect()->back()->with('error', 'Account Not Found');
            }


        }



        if ($request->payment_method == 'card') {


            try {

                if($request->has('trip') && isset($trip)){
                    $originalAmount = $request->amount;
                }else{

                    $originalAmount = $request->amount;
                    $trip = new Trip;
                    $trip->is_manuall = 1;
                    $trip->payment_method = 'card';
                    $trip->date = now()->toDateString();
                    $trip->time = now()->toTimeString();
                    $trip->driver_id = auth()->user()->driver_id;

                    do {
                        $randomTripId = random_int(1000000000, 9999999999);
                    } while (Trip::where('trip_id', $randomTripId)->exists());
                    $trip->trip_id = $randomTripId;
                    $trip->save();
                }


                $fee = ((float)$originalAmount * 0.03333333333) + .3;
                $cardknoxAmount = $originalAmount + $fee ;

                $cardknoxToken = $request->cardknoxToken;

                $desc = 'GoDrive Payment Trip#' . $trip->trip_id . ' driver#' . $trip->trip_id . ' Total Amount=' . $cardknoxAmount . ' , without Fee' . $originalAmount;
                $charge = CardKnoxService::processPayment($cardknoxToken, $cardknoxAmount, $desc);

                if ($charge['status'] == 'approved') {

                    $data['trip_cost'] = $originalAmount;
                    $data['gocab_paid'] = $originalAmount;
                    $data['payment_method'] = 'card';
                    $data['stripe_id'] = $charge['transaction_id'];


                    if (isset($request->complaint) && $request->complaint !== null) {
                        $data['complaint'] = $request->complaint;
                        $data['is_complaint'] = '1';
                    }

                    $trip->update($data);

                    $pay_data = $this->addpay($trip,$request);

                    $logdata = array();
                    $logdata['from'] = 'driver';
                    $logdata['payment'] = $pay_data;
                    $logdata['trip'] = $trip;
                    $logdata['strip'] = $charge;

                    if (isset($request->is_admin)) {
                        $logdata['message'] = 'Admin:Trip Payment Added By Driver Using Method Card#' . $charge['transaction_id'] . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                    } elseif (isset($request->is_driver)) {
                        $logdata['message'] = 'Trip Payment Added By Driver Using Method Card#' . $charge['transaction_id'] . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                    } else {
                        $logdata['message'] = 'Trip Payment Added By Driver Using Method Card#' . $charge['transaction_id'] . ' Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                    }
                    LogService::saveLog($logdata);
                    $this->ifBalanceMinusAutoPaidAsAdmin($trip);
                    DB::commit();
                    return response()->json(['status'=>true,'msg'=>'Payment Completed Thank you!']);

                } else {

                    DB::rollBack();
                    $logdata = array();
                    $logdata['from'] = 'driver';
                    $logdata['trip'] = $trip;
                    $logdata['strip'] = $charge;

                    if (isset($request->is_admin)) {
                        $logdata['message'] = 'Admin:Error :Card Payment Error By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount;

                    } elseif (isset($request->is_driver)) {
                        $logdata['message'] = 'Error :Card Payment Error By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount;

                    } else {
                        $logdata['message'] = 'Error :Card Payment Error By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount;

                    }

                    LogService::saveLog($logdata);

                    return response()->json(['status'=>false,'msg'=>'Payment Failed Please Try Again']);

                }
            } catch (\Exception $e) {

                DB::rollBack();
                $logdata = array();
                $logdata['from'] = 'driver';
                $logdata['trip'] = $trip;
                //  $logdata['strip'] = '';
                if (isset($request->is_admin)) {
                    $logdata['message'] = 'Admin:Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount . ' ' . $e->getMessage();

                } elseif (isset($request->is_driver)) {
                    $logdata['message'] = 'Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount . ' ' . $e->getMessage();

                } else {
                    $logdata['message'] = 'Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount . ' ' . $e->getMessage();

                }
                LogService::saveLog($logdata);

                return response()->json(['status'=>false,'msg'=>'Payment Failed Please Try Again']);
            }
        }


    }

    public function sendNotif($phone, $cost, $extraCharges, $tripid, $driverid, $extra_message = null)
    {

        if (!Str::startsWith($phone, '+1')) {
            $phone = '+1' . $phone;
        }
        // Log::info('SMS sending initiated for ' . $phone);

        TwilioService::sendSms($phone, $cost, $extraCharges, $tripid, $driverid, $extra_message);


    }

    public function ifBalanceMinusAutoPaidAsAdmin($trip)
    {

        $driver = Driver::where('driver_id', $trip->driver_id)->first();
        if ($driver) {

            $trip_cost = $trip->trip_cost;
            // Log::info($driver->balance());
            $balance = $driver->balance() - $trip_cost;
            // Log::info($balance);

            if ($balance < 0) {

                $rm = $balance + $trip->trip_cost;
                if ($rm <= 0) {

                    $paid = $trip->trip_cost;
                } else {

                    $paid = -$balance;

                }

                $new = new Adjustment();
                $new->driver_id = $trip->driver_id;
                $new->trip_id = $trip->trip_id;
                $new->date = now()->toDateString();
                $new->amount = $paid;
                $new->type = 'admin_paid_auto';
                $new->reason = 'Auto Adjustment Added When balance was ' . $balance;
                $new->save();

                $trip->is_auto_paid_as_adjustment = 1;
                $trip->save();

            }
        }
    }


    public function addpay($trip, $request)
    {

        $new = new Payment();
        $new->driver_id = $trip->driver_id;
        $new->trip_id = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float)$trip->gocab_paid;
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


    public function prepaidAccountDeduction($trip,$account)
    {
        $new = new Payment();
        $new->driver_id = $trip->driver_id;
        $new->trip_id = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float)$trip->trip_cost - $trip->discount_amount;
        $new->user_id = $account->id;
        $new->user_type = 'customer';
        $new->type = 'debit';
        $new->description = 'deduct_from_customer_prepaid_account_against_trip';
        $new->account_id = $account->account_id;
        $new->save();

        return $new;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getTripPayments(Request $request)
    {

        $trip = Trip::where('trip_id',$request->trip_id)->first();

        $modal = view('admin.partials.modals.trip_payments', compact('trip'));

        return $modal;
    }

    public function getSinglePayment(Request $request)
    {

        $data = Payment::where('id',$request->id)->first();

        $modal = view('admin.partials.modals.single_payment', compact('data'));

        return $modal;
    }

    public function updateSinglePayment(Request $request)
    {

        $payment = Payment::where('id',$request->id)->first();

        if($payment->amount == $request->amount){
            return redirect()->back();
        }elseif($request->amount > $payment->amount ){
            $type = 'credit';
            $diff = $request->amount - $payment->amount;

        }elseif($request->amount < $payment->amount ){
            $type = 'debit';
            $diff = $payment->amount - $request->amount;
        }



        $trip_id =  $payment->trip_id;
        $trip =  Trip::where('trip_id',$trip_id)->first();


        $add_histry = false;

        if($trip->paidAgianstTripByAdminQuery()->count() > 0){
            $add_histry = true;
        }
        if( $add_histry == true) {
            $data['payment'] = $payment;
            $data['trip'] = $trip;
            $data['diff'] = $diff;
            $data['type'] = $type;
            LogService::historyEditTripIfAdminPaymentExists($data);
        }
        $payment->amount = $request->amount;
        $payment->save();

        return redirect()->back()->with('success','Updated');
    }

    public function deleteSinglePayment(Request $request)
    {

        $data = Payment::where('id',$request->id)->first();
        $data->is_delete = 1;
        $data->save();

        return true;
    }


    public function getUpdatePricesModal(Request $request)
    {

        $trip = Trip::where('trip_id',$request->trip_id)->first();
        if($request->type == "cost") {
            $modal = view('admin.partials.modals.edit_cost', compact('trip'));
        }
        if($request->type == "extra") {
            $modal = view('admin.partials.modals.edit_extra', compact('trip'));
        }

        return $modal;
    }

    public function already_paid_and_updatig_cost($request,$trip){

        $fromcurrentweek = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of current week (Sunday)
        $tocurrentweek = Carbon::now()->toDateString();

        $triped = Trip::with('payments')
            ->where('driver_id', $trip->driver_id) // Prevent errors if $trip is null
            ->whereDoesntHave('payments', function ($query) {
                $query->where('type', 'debit')
                    ->where('user_type', 'admin');
            })
            ->whereIn('payment_method', ['account', 'card'])
            ->where('reason', '!=', '')
            ->orderBy('date', 'desc')
            ->first(); // Use get() if multiple results are expected



        if($triped) {
                return $triped;
            }

        return false;

    }

    public function update_cost(Request $request)
    {

        if($request->username == "" || empty($request->username)){
            return response()->json([
                'success' => false,
                'message' => 'Required username',
            ]);
        }

        $cost = $request->extra + $request->cost;
        $trip = Trip::where('trip_id',$request->trip_id)->first();
        $trip_old = Trip::where('trip_id',$request->trip_id)->first();;

        $add_histry = false;
        $comment = '';
        //check if any admin payment before
        if($trip->paidAgianstTripByAdminQuery()->count() > 0){
            $add_histry = true;
        }

        $prev_cost = $trip->trip_cost - $request->extra;
        if ($prev_cost == $request->cost) {

            return response()->json([
                'success' => false,
                'message' => 'Same Cost',
            ]);
        } elseif ($request->cost > $prev_cost) {


            $paid = $request->cost - $prev_cost;

            $newp = new Payment();
            $newp->driver_id = $trip->driver_id;
            $newp->trip_id = $trip->trip_id;
            $newp->user_id = $trip->driver_id;
            $newp->payment_date = now()->toDateString();
            $newp->user_type = 'driver';
            $newp->type = 'credit';
            $newp->amount = $paid;
            // todo please donot change descript
            $newp->description = 'cost_increas_edit_by_admin';
            $newp->edited_prices_by = $request->username;
            $newp->save();


        } elseif ($request->cost < $prev_cost) {

            $paid = $prev_cost - $request->cost;

//            if($add_histry) {
//                $comment = 'cost adjusted when trip='.$trip->trip_id.' had been edited';
//                $trip = $this->already_paid_and_updatig_cost($request,$trip);
//
//                if($trip == false){
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Unpaid Trip Not Found',
//                    ]);
//                }
//                $cost = $trip->trip_cost - $paid;
//
//                $comment .= 'was cost'.$trip->trip_cost;
//
//                Payment::where('trip_id', $trip->trip_id)->where('user_type', 'driver')->where('type', 'credit')->delete();
//
//                $newp = new Payment();
//                $newp->driver_id = $trip->driver_id;
//                $newp->trip_id = $trip->trip_id;
//                $newp->user_id = $trip->driver_id;
//                $newp->payment_date = now()->toDateString();
//                $newp->user_type = 'driver';
//                $newp->type = 'credit';
//                $newp->amount = $cost;
//                // todo please donot change descript
//                $newp->description = 'cost_edit_added_by_admin';
//                $newp->edited_prices_by = $request->username;
//                $newp->save();
//
//            }else {

                $check_before = Payment::where('trip_id', $trip->trip_id)->where('user_type', 'driver')->where('type', 'credit')->first();
                if ($check_before) {
                    if ($check_before->amount >= $paid) {
                        $bef_am = $check_before->amount;
                        $check_before->amount = $bef_am - $paid;
                        $check_before->description = 'cost_decrease_edit_by_admin_debit';
                        $check_before->edited_prices_by = $request->username;
                        $check_before->save();

                        $cost = Payment::where('trip_id', $trip->trip_id)->where('user_type', 'driver')->where('type', 'credit')->sum('amount');
                    }
//                }
            }

        }

        $trip->trip_cost = $cost;
//        $trip->gocab_paid = $cost;

        if($trip->discount_perc > 0){

            $disc_amount = $cost * (floatval($trip->discount_perc) / 100);
            $trip->discount_amount = $disc_amount;

        }

        $trip->reason = $request->reason.' '.$request->username.' '.$comment;


        if(isset($check_before) && $add_histry == true) {

            $data['payment'] = $check_before;
            $data['trip'] = $trip_old;
            $data['amount'] = $paid;
            $data['diff'] = "";
            $data['type'] = "";
            $data['reason'] = $trip->reason;
            LogService::historyEditTripIfAdminPaymentExists($data);
        }
        $updated_cost = $cost - $request->extra;
        $trip->save();
        return response()->json([
            'success' => true,
            'message' => 'Trip cost has been updated successfully',
            'updated_cost' => $updated_cost,
            'cost' => $cost,
            'trip_id' => $trip->trip_id,

        ]);
        //return redirect()->back()->with('success', 'Trip cost has updated successfully');
    }

    public function update_account(Request $request)
    {


        $account = Account::where('account_id', $request->account)->first();
        $trip_id = $request->trip_id;
        $trip = Trip::where('trip_id', $trip_id)->first();

        $trip->reason = $request->reason . ' Updated by /' . Auth::guard('admin')->user()->name;
        $trip->payment_method = $request->payment_method;
        $trip->account_number = $request->account;
        if ($account->account_type == 'prepaid') {
            if ($account->balance >= $trip->trip_cost) {

                $trip->update();
                $from_prepaid_deduction = $this->prepaidAccountDeduction($trip,$account);
                $account->balance = $account->balance - $trip->trip_cost;
                $account->save();
            } else {

                \App\Services\TwilioService::voicecall($account->phone,'refill-need');
//                CubeContact::deleteAccount($account->account_id);
                CubeContact::updateCubeAccount($account->account_id,"Your Account Balance Is zero","Inactive");

                return redirect()->back()->with('error', 'Prepaid Account:Low Balance');


            }
        }
        $trip->save();

        return response()->json([
            'success' => true,
            'message' => 'Trip account has been updated successfully',
            'account' => $trip->account_number,
            'method' => $trip->payment_method,
            'trip_id' => $trip_id,

        ]);
        //return redirect()->back()->with('success', 'Trip cost has updated successfully');
    }

    public function update_charges(Request $request)
    {

        if($request->username == "" || empty($request->username)){
            return response()->json([
                'success' => false,
                'message' => 'Required username',
            ]);
        }
        if (isset($request->stop) && isset($request->wait) && isset($request->round)) {
            $request_extra = $request->stop + $request->wait + $request->round;

            $cost = $request_extra + $request->cost;

            $trip = Trip::where('trip_id',$request->trip_id)->first();
            $trip->trip_cost = $cost;
            $trip->gocab_paid = $cost;
            $trip->extra_stop_amount = $request->stop;
            $trip->extra_wait_amount = $request->wait;
            $trip->extra_round_trip = $request->round;
            $prev_extra = $trip->extra_charges;

            $add_histry = false;
            $comment = '';

            //check if any admin payment before
            if($trip->paidAgianstTripByAdminQuery()->count() > 0){
                $add_histry = true;
//                $comment = 'cost adjusted when trip='.$trip->trip_id.' had been edited by '.$request->username;
                $tripp =  $this->already_paid_and_updatig_cost($request,$trip);
                if($tripp){
                    $trip = $tripp;
                }
            }


            if ($prev_extra == $request_extra) {
                $trip->extra_charges = $request_extra;
                $trip->reason = $request->reason;
                $trip->save();

                return response()->json([
                    'success' => 'false_true',
                    'message' => '!',


                ]);

            } elseif ($request_extra > $prev_extra) {

                $paid = $request_extra - $prev_extra;

                $newp = new Payment();
                $newp->driver_id = $trip->driver_id;
                $newp->trip_id = $trip->trip_id;
                $newp->user_id = $trip->driver_id;
                $newp->payment_date = now()->toDateString();
                $newp->user_type = 'driver';
                $newp->type = 'credit';
                $newp->amount = $paid;
                // todo please donot change descript
                $newp->description = 'charges_increase_edit_by_admin';
                $newp->edited_prices_by = $request->username;
                $newp->save();

            } elseif ($request_extra < $prev_extra) {

                $paid = $prev_extra - $request_extra;


                $check_before = Payment::where('trip_id',$trip->trip_id)->where('user_type','driver')->where('type','credit')->first();
                if($check_before){
                    if($check_before->amount >= $paid){
                        $bef_am = $check_before->amount;
                        $check_before->amount = $bef_am - $paid;
                        $check_before->description = 'charges_decrease_edit_by_admin_debit';
                        $check_before->edited_prices_by = $request->username;
                        $check_before->save();
                    }
                }

    //                $newp = new Payment();
    //                $newp->driver_id = $trip->driver_id;
    //                $newp->trip_id = $trip_id;
    //                $newp->user_id = $trip->driver_id;
    //                $newp->user_type = 'admin';
    //                $newp->payment_date = now()->toDateString();
    //                $newp->type = 'debit';
    //                $newp->amount = $paid;
    //                // todo please donot change descript
    //                $newp->description = 'charges_decrease_edit_by_admin_debit';
    //                $newp->edited_prices_by = $request->username;
    //                $newp->save();


            }

            if($trip->discount_perc > 0){

                $disc_amount = $cost * (floatval($trip->discount_perc) / 100);
                $trip->discount_amount = $disc_amount;

            }

            if(isset($newp) && $add_histry == true) {
                $data['payment'] = $newp;
                $data['trip'] = $trip;
                $data['diff'] = "";
                $data['type'] = "";
                LogService::historyEditTripIfAdminPaymentExists($data);
            }

            $trip->extra_charges = $request_extra;
            $trip->reason = $request->reason.'  '.$comment;
            $trip->save();
            $updated_cost = $cost - $request_extra;
            $description = 'Stop =$' . $trip->extra_stop_amount . ',Stop Location =' . $trip->stop_location . ',Wait =$' . $trip->extra_wait_amount . ',Round Trip = $' . $trip->extra_round_trip;
            // todo if adjust then ?
            return response()->json([
                'success' => true,
                'message' => 'Trip extra charges has been updated successfully',
                'extra' => $request_extra,
                'cost' => $cost,
                'trip_id' => $trip->trip_id,
                'updated_cost' => $updated_cost,
                'description' => $description,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',


            ]);
        }

        //return redirect()->back()->with('success', 'Trip cost has updated successfully');
    }



    public function deductionRemove(Request $request)
    {

        $history = TripEditHistory::find($request->id);
        $trip = Trip::where('trip_id',$history->trip_id)->first();
        $paid = $trip->trip_cost + $history->amount;

        $newp = new Payment();
        $newp->driver_id = $trip->driver_id;
        $newp->trip_id = $trip->trip_id;
        $newp->user_id = $trip->driver_id;
        $newp->payment_date = now()->toDateString();
        $newp->user_type = 'driver';
        $newp->type = 'credit';
        $newp->amount = $history->amount;
        $newp->description = 'cost_increase_del_history';
        $newp->save();

        $trip->trip_cost = $paid;
        $trip->save();

        $history->is_return = 1;
        $history->description = $request->reason;
        $history->save();


    }
    public function ajaxTrips(Request $request)
    {


        $trips = Trip::leftJoin('payments', 'trips.trip_id', '=', 'payments.trip_id')
            ->where('trips.is_delete', 0);

        $trips = $trips->where(function ($query) {
            $query->where('trips.payment_method', '!=', 'card')
                ->orWhere('trips.trip_cost', '>', 0); // Include trips with card only if trip_cost > 0
        });

        if ($request->type != 'all' && $request->type != 'extacost') {
            $trips = $trips->where('trips.payment_method', '!=', 'cash');
        }

        $trips = $trips->select(
            'trips.extra_charges',
            'trips.extra_stop_amount',
            'trips.stop_location',
            'trips.extra_wait_amount',
            'trips.extra_round_trip',
            'trips.location_from',
            'trips.location_to',
            'trips.account_number',
            'trips.payment_method',
            'trips.driver_id',
            'trips.trip_id',
            'trips.date',
            'trips.trip_cost',
            'trips.time',
            'trips.status',
            'trips.is_complaint',
            'trips.complaint',
            'trips.reason',
            'trips.accepted_by',
            'trips.cube_pin',
            'trips.cube_pin_status',
            'trips.is_auto_paid_as_adjustment',
            \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid"),
            \DB::raw("(SELECT COALESCE(SUM(CASE WHEN adjustments.type = 'admin_paid_auto' AND adjustments.trip_id = trips.trip_id AND adjustments.driver_id = trips.driver_id THEN adjustments.amount ELSE 0 END), 0)
               FROM adjustments
               WHERE adjustments.trip_id = trips.trip_id
                 AND adjustments.driver_id = trips.driver_id
                 AND adjustments.type = 'admin_paid_auto') as total_paid_adjust")
        );


        if ($request->type == 'all' || $request->type == 'extacost') {


            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {

                    $trips = $trips->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);

                }
            }
        } else {

            $trips = $trips->whereDate('trips.date', '>', '2024-09-14');

            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {

                    $trips = $trips->whereDate('trips.date', '<=', $request->to_date);

                }
            }
        }

        if (isset($request->driver)) {
            if ($request->driver != '') {

                $trips = $trips->where('trips.driver_id', $request->driver);

            }
        }

        $trips = $trips->groupBy(
            'trips.trip_id',
            'trips.extra_charges',

            'trips.extra_stop_amount',
            'trips.extra_wait_amount',
            'trips.extra_round_trip',
            'trips.date',

            'trips.trip_cost',
            'trips.location_from',
            'trips.location_to',
            'trips.account_number',
            'trips.payment_method',
            'trips.driver_id',
            'trips.date',
            'trips.time',
            'trips.status',
            'trips.is_complaint',
            'trips.complaint',
            'trips.reason',
            'trips.accepted_by',
            'trips.is_auto_paid_as_adjustment',
            'trips.cube_pin',
            'trips.cube_pin_status'

        );

        if ($request->type == 'partial') {
            $trips = $trips->havingRaw('total_paid + total_paid_adjust < trips.trip_cost');

        }
        if ($request->type == 'paid') {
            $trips = $trips->havingRaw('total_paid >= trips.trip_cost AND trips.trip_cost > 0 OR trips.is_auto_paid_as_adjustment = 1');

        }

        if ($request->type == 'extacost') {
            $trips = $trips->where('trips.extra_charges', '>', 0);

        }

        $trips = $trips = $trips->orderBy('trips.date', 'desc')
            ->orderBy('trips.time', 'desc')
            ->get();

        $view = view('admin.partials.table_trips', compact('trips', 'request'));

        return $view;
    }

    public function ajaxTripsAccount(Request $request)
    {

        $data = AccountService::GetTrips($request);
        $trips = $data['trips'];
        $view = view('admin.partials.table_trips2', compact('trips', 'request'));

        return $view;
    }

    public function register_complaint(Request $request)
    {
        $trip = Trip::where('trip_id', $request->trip_id)->first();
        $trip->complaint = $request->complaint;
        $trip->is_complaint = '1';
        $trip->save();
        if (isset($request->is_admin)) {
            return redirect()->to(url('admin/driver/' . $request->driver_id))->with('success', 'Your Complaint is Registerd');
        } else {
            return redirect('dashboard')->with('success', 'Your Complaint is Registerd');
        }


    }

    public function updateNew(Request $request)
    {


        $driver = Driver::where('driver_id', auth()->user()->driver_id)->first();
        if (empty($driver->cube_id)) {
            return redirect()->back()->with('error', 'Contact is not available in cube.');
        }


        DB::beginTransaction();

        $tempData = json_encode($request->all());

        try {
            if ($request->has('trip')) {


                $trip = Trip::find($request->trip);
                $existingPayment = Payment::where('trip_id', $trip->trip_id)->where('user_type', 'driver')
                    ->where('type', 'credit')->where('is_delete', 0)->first();

                if ($existingPayment) {
                    return redirect()->back()->with('error', 'Payment already exists for this trip.');
                }
                $trip->temp_data = $tempData;
                $trip->save();
                $location_to = $trip->location_to;
                // if ($request->payment_method == 'account') {

                    if ($trip->trip_cost == 0) {
                        $cost = (float)$request->amount;
                    } else {
                        $cost = (float)$trip->trip_cost;
                    }

                    $trip_cost = $cost + (float)$request->extra_charges;

                    $originalAmount = $trip_cost;
                    $fee = ((float)$originalAmount * 0.03333333333) + .3;
                    $totalAmount = $originalAmount + $fee;


            } else {


                $originalAmount = $request->amount;
                $fee = ((float)$originalAmount * 0.03333333333) + .3;
                $totalAmount = $originalAmount + $fee;


                $originalAmount = "";

                $trip = new Trip;
                $trip->payment_method = $request->payment_method;
                $trip->driver_id = auth()->user()->driver_id;
                $trip->date = now()->toDateString();
                $trip->time = now()->toTimeString();
                $trip->temp_data = $tempData;
                do {
                    $randomTripId = random_int(1000000000, 9999999999);
                } while (Trip::where('trip_id', $randomTripId)->exists());

                $trip->trip_id = $randomTripId;

                $trip->save();

                $location_to = null;
            }

            $base_fare = null;
            $extra_charges = 0;
            $extra_description = [];
            if(isset($request->extra_charges)){
            if($request->extra_charges > 0){

                $base_fare = $trip->trip_cost + $request->extra_charges ;
                //$extra_charges = floatval($request->extra_charges);
                $extra_charges = 0;
                // if ($request->stop_amount > 0){

                //     $extra_description[] = "stop ".$request->stop_location;
                // }

                // if ($request->wait_amount > 0) {

                //     $extra_description[] = " Wait";

                // }
                // if ($request->round_trip > 0) {

                //     $extra_description[] = "roundtrip";

                // }

            }
            }

            $extra_description = implode(',',$extra_description);

            $cube_req = CubeContact::updateDriverReturnCheck(auth()->user()->driver_id, $totalAmount, $originalAmount, $trip->trip_id, $location_to,$base_fare,$extra_charges,$extra_description != "" ? $extra_description : null,null);

            if (isset($cube_req['error'])) {
                DB::rollBack();
                return redirect()->back()->with(['error' => 'Please Retry Again']);
            }


            $logdata = array();
            $logdata['from'] = 'driver';
            $logdata['trip'] = $trip;

            if (isset($request->is_admin)) {
                $logdata['message'] = 'Admin:Cube Contact Updated By Driver #' . ($request->account ?? '') .
                ' Trip#' . ($trip->trip_id ?? '') .
                ' Amount ' . ($trip_cost ?? '');

            } elseif (isset($request->is_driver)) {

                $logdata['message'] = 'Cube Contact Updated By Driver #' . ($request->account ?? '') .
                ' Trip#' . ($trip->trip_id ?? '') .
                ' Amount ' . ($trip_cost ?? '');


            } else {

                $logdata['message'] = 'Cube Contact Updated By Driver #' . ($request->account ?? '') .
                ' Trip#' . ($trip->trip_id ?? '') .
                ' Amount ' . ($trip_cost ?? '');

            }

            LogService::saveLog($logdata);
            DB::commit();

            $trip_id = $trip->trip_id;

            return view('driver.wait', compact('trip_id'));

        } catch (\Exception $e) {

            DB::rollBack();
            $logdata = array();
            $logdata['from'] = 'driver';
            $logdata['trip'] = $trip;
                //  $logdata['strip'] = '';
            if (isset($request->is_admin)) {
            $logdata['message'] = 'Admin:Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount . ' ' . $e->getMessage();

            } elseif (isset($request->is_driver)) {
            $logdata['message'] = 'Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount . ' ' . $e->getMessage();

            } else {
                $logdata['message'] = 'Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $request->amount . ' ' . $e->getMessage();

            }
            LogService::saveLog($logdata);

            return Redirect::back()->withErrors(['error' => 'Payment failed: ' . $e->getMessage()]);
}
    }


    public function cubePayment(Request $request)
    {

        Log::info('webhook------------------------------------');
        Log::info($request->all());
        $add_account_complaint = false;
// add logs for webhook in db with time stamp json format
        $is_paid = false;
        if(isset($request->transaction)){

            $transaction = $request->transaction;

            if(isset($transaction['xResult']) && isset($transaction['xStatus'])){
                if($transaction['xStatus'] == 'Approved') {
                    $is_paid = true;
                }

            }
            if(isset($transaction['Status'])){
                if($transaction['Status'] == 'Approved') {

                    $is_paid = true;
                }

            }

        }

        DB::beginTransaction();

        $trip_id = $request->comments;

        $trip = Trip::where('trip_id',$trip_id)->first();

        if($trip) {
        if($trip->payment_method == 'cash') {

            $tempData = json_decode($trip->temp_data, true);
            Log::info($tempData);

            if ($is_paid == true) {
                if (isset($trip->trip_id) && $trip->trip_id != null) {

                    try {

                        $originalAmount = $request->amount;

                        if (isset($transaction['xStatus']) && $transaction['xStatus'] == 'Approved') {
                            $payment_method = 'card';
                            $account_id = "";

                            $finalAmount = $originalAmount; // Example final amount
                            $originalAmount = ($finalAmount - 0.3) / 1.03;

                            Log::info(": payment method is card =" . $originalAmount);

                        } elseif (isset($transaction['Status']) && $transaction['Status'] == 'Approved') {
                            $payment_method = 'account';
                            $account_number = explode("x", $request->phone);
                            $account_number = array_map(fn($val) => rtrim($val, "*"), $account_number);
                            $account_id = $account_number[0] ?? null;

                            if (strpos($request->phone, "*") !== false) {
                                $add_account_complaint = true;
                            }

                            $account = Account::where('account_id', $account_id)->first();
                            $account_id = $account ? $account->account_id : null;
                            if (isset($account_number[1])) {

                                $pinsArray = explode(',', $account->pins);
                                if (in_array($account_number[1], $pinsArray)) {
                                    $trip->cube_pin_status = $account_number[1];

                                } else {
                                    $trip->cube_pin_status = 'Wrong';
                                }

                            }
                            if ($account) {
                                if ($account->account_type == "prepaid") {
                                    if ($account->balance < 20) {
                                        if ($account->autofill == "on") {

                                            $account_responce = PaymentSaveService::prepPaidRefill($account, "single");

                                            if ($account_responce) {
                                                $account = Account::find($account->id);
                                                Log::info("prepaid refill suddenly");


                                            } else {
                                                Log::warning("Prepaid refill failed or returned false.");
                                            }

                                        }
                                        CubeContact::updateCubeAccount($account->account_id, "Your balance is low", "active");
                                        $account->reason = null;
                                    }
                                    if ($account->balance <= 0) {

//                                            CubeContact::deleteAccount($account->account_id);
                                        CubeContact::updateCubeAccount($account->account_id, "Your Account Balance Is zero", "Inactive");
                                            $account->reason = 'Low Balance';
//                                            $account->status = 0;
                                        \App\Services\TwilioService::voicecall($account->phone, 'refill-need');

                                    }
                                    $this->prepaidAccountDeduction($trip, $account);
                                    $account->balance = $account->balance - $originalAmount;
                                    $account->save();


                                }
                            }
                            Log::info(": payment method is account");
                        }

                        $extra_charges = $tempData['extra_charges'] ?? 0;

                        $extra_stop_amount = $tempData['stop_amount'] ?? 0;
                        $stop_location = $tempData['stop_location'] ?? null;

                        $extra_wait_amount = $tempData['wait_amount'] ?? 0;
                        $extra_round_trip = $tempData['round_trip'] ?? 0;

                        $data = [
                            'trip_cost' => $originalAmount,
                            'gocab_paid' => $originalAmount,
                            'payment_method' => $payment_method,
                            'account_number' => $account_id,
                            'extra_charges' => $extra_charges,
                            'extra_round_trip' => $extra_round_trip,
                            'extra_wait_amount' => $extra_wait_amount,
                            'extra_stop_amount' => $extra_stop_amount,
                            'stop_location' => $stop_location,
                        ];


                        if (isset($tempData['complaint']) && $tempData['complaint'] !== null) {
                            $data['complaint'] = $tempData['complaint'];
                            $data['is_complaint'] = '1';
                        }

                        $trip->update($data);

                        CubeContact::updateDriver($trip->driver_id);

                        $pay_data = new Payment();
                        $pay_data->driver_id = $trip->driver_id;
                        $pay_data->trip_id = $trip->trip_id;
                        $pay_data->payment_date = now()->toDateString();
                        $pay_data->amount = (float)$trip->gocab_paid;
                        $pay_data->user_id = $trip->driver_id;
                        $pay_data->user_type = 'driver';
                        $pay_data->type = 'credit';
                        $pay_data->description = 'webhook';
                        $pay_data->save();

                        $logdata = array();
                        $logdata['from'] = 'driver';
                        $logdata['payment'] = $pay_data;
                        $logdata['trip'] = $trip;
                        $logdata['payment'] = $tempData;
                        $logdata['message'] = 'Trip Payment Added By Driver Using Method Card# Trip#' . $trip->trip_id . ' Amount ' . $pay_data->amount;

                        LogService::saveLog($logdata);
                        $this->ifBalanceMinusAutoPaidAsAdmin($trip);
                        $trip->temp_data = null;
                        $trip->save();


                        if ($add_account_complaint) {

                            if ($trip->payment_method == 'account') {
                                $complaint = new Account_Complaint();
                                $complaint->account_id = $trip->account_number;
                                $complaint->trip_id = $trip->trip_id;
                                $complaint->complaint = 'Complaint from device';
                                $complaint->hash_id = 'webhook';
                                $complaint->save();
                            }

                        }


                        if (isset($account)) {


                            $extra_message = null;


                            if (isset($extra_stop_amount)) {

                                $extra_message .= "Extra Stop Charges: {$extra_stop_amount}\nStop Location: {$stop_location}. ";

                            }

                            if (isset($extra_wait_amount)) {

                                $extra_message .= "Extra Wait Charges: {$extra_wait_amount}. ";

                            }
                            if (isset($extra_round_trip)) {

                                $extra_message .= "Extra Round Trip: {$extra_round_trip}. ";
                            }

                            if ($account->notification_setting == null) {
                                $phone = preg_replace('/[^0-9]/', '', $trip->passenger_phone);
                                $this->sendNotif($phone, $trip->trip_cost, $trip->extra_charges, $trip->trip_id, $trip->driver_id, $extra_message);

                            } elseif ($account->notification_setting == 'account_email') {

                                EmailService::send($account->email, $trip->trip_cost, $trip->extra_charges, $extra_message);

                            } elseif ($account->notification_setting == 'passenger_phone') {

                                $phone = preg_replace('/[^0-9]/', '', $trip->passenger_phone);
                                $this->sendNotif($phone, $trip->trip_cost, $trip->extra_charges, $trip->trip_id, $trip->driver_id, $extra_message);


                            } elseif ($account->notification_setting == 'account_phone') {
                                $phone = preg_replace('/[^0-9]/', '', $account->phone);
                                $this->sendNotif($phone, $trip->trip_cost, $trip->extra_charges, $trip->trip_id, $trip->driver_id, $extra_message);

                            } else {

                                $phone = preg_replace('/[^0-9]/', '', $account->phone);
                                $this->sendNotif($phone, $trip->trip_cost, $trip->extra_charges, $trip->trip_id, $trip->driver_id, $extra_message);
                                $phone = preg_replace('/[^0-9]/', '', $trip->passenger_phone);
                                $this->sendNotif($phone, $trip->trip_cost, $trip->extra_charges, $trip->trip_id, $trip->driver_id, $extra_message);

                            }


                        }

                        DB::commit();
                        return true;


                    } catch (\Exception $e) {

                        DB::rollBack();
                        $logdata = array();
                        $logdata['from'] = 'driver';
                        $logdata['trip'] = $trip;
                        $logdata['message'] = 'webhook:Error :Card Payment Failed By Driver Trip#' . $trip->trip_id . ' Amount ' . $tempData['amount'] . ' ' . $e->getMessage();

                        LogService::saveLog($logdata);

                        return true;
                    }
                    // }

                }

            } else {
                return response()->json(['status' => false, 'data' => $request->transaction]);
            }
        }else{
            return response()->json(['status' => false, 'data' => 'Payment Already Done']);

        }
        }else {
            return response()->json(['status' => false, 'data' => 'Invalid trip']);
        }
    }

    public function start_payment_again($id){

        $trip = Trip::where('trip_id',$id)->first();

        Log::info('cancel-button-'.$id);

//        if(json_decode($trip->temp_data)->payment_method == 'card'){
//
//            CubeContact::updateDriver($trip->driver_id);
//
//        }else{

            //CubeContact::updateAccount($trip->account_number);

//        }
        CubeContact::updateDriver($trip->driver_id);

        $trip->save();
        return redirect()->to(url('/payment-new'));
    }

    public function checkwebhook($trip_id){

        $paid = 0;
        $trip = Trip::where('trip_id',$trip_id)->first();
        if(isset($trip)){

            $paid = $trip->payments->where('type','credit')->where('user_type','driver')->sum('amount');
        }

        if(empty($trip->temp_data)){

            if($paid > 0){
                return response()->json(['status'=>true,'msg'=>'Payment Received '.$paid]);
            }

        }

        return response()->json(['status'=>false,'msg'=>'Not Received']);

    }

        public function processPrepaidAccountDeductions()
        {
            // Step 1: Fetch account numbers from trips
            $accountNumbers = DB::table('trips')
                ->where('payment_method', 'account')
                ->whereRaw('LENGTH(trips.trip_id) = 10') // Explicitly qualify the table
                ->whereRaw('trips.trip_id REGEXP "^[0-9]+$"') // Explicitly qualify the table
                ->whereNotNull('trips.account_number')
                ->where('trips.account_number', '!=', '')
                ->pluck('trips.account_number'); // Explicitly qualify the table
              //  dd($accountNumbers);
            // Step 2: Get prepaid accounts that match the account numbers
            $prepaidAccounts = DB::table('accounts')
                ->whereIn('account_id', $accountNumbers)
                ->where('account_type', 'prepaid')
                ->get()
                ->keyBy('account_id');
              // dd($prepaidAccounts);
            // Step 3: Create the trips query
            $tripsQuery = DB::table('trips')
                ->where('trips.payment_method', 'account') // Explicitly qualify the table
                ->whereRaw('LENGTH(trips.trip_id) = 10') // Explicitly qualify the table
                ->whereRaw('trips.trip_id REGEXP "^[0-9]+$"') // Explicitly qualify the table
                ->whereIn('trips.account_number', $prepaidAccounts->keys()); // Explicitly qualify the table
                $trips = $tripsQuery->select('trips.*')->get(); // Explicitly select trips to avoid ambiguity

                $tripIds = $tripsQuery->select('trips.trip_id')->pluck('trip_id');
                dd($tripIds);
                // Step 4: Count trips in payments table with type 'credit'
            $tripsInPaymentsCount = $tripsQuery
                ->leftJoin('payments', 'trips.trip_id', '=', 'payments.trip_id')
                ->where('payments.type', 'credit') // Explicitly qualify the table
                ->count();

            // Debug the count
            // dd("Number of trips in payments table: $tripsInPaymentsCount");

            // Fetch the trips for further processing


            // Step 5: Process each trip
            foreach ($trips as $trip) {
                // Check if there's a payment record with type 'credit' for this trip
                $paymentExists = DB::table('payments')
                    ->where('payments.trip_id', $trip->trip_id) // Explicitly qualify the table
                    ->where('payments.type', 'credit') // Explicitly qualify the table
                    ->exists();

                // If a credit payment exists, skip this trip
                if ($paymentExists) {
                    continue;
                }

                // Process the trip if no credit payment exists
                if (isset($prepaidAccounts[$trip->account_number])) {
                    $account = $prepaidAccounts[$trip->account_number];

                    // Run the prepaidAccountDeduction function
                    $this->prepaidAccountDeduction($trip, $account);

                    // Deduct trip cost from account balance separately
                    DB::table('accounts')
                        ->where('accounts.account_id', $account->account_id) // Explicitly qualify the table
                        ->update(['accounts.balance' => $account->balance - $trip->trip_cost]); // Explicitly qualify the table
                }
            }

            dd("All accounts are updated");
        }



}

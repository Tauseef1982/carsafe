<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\CreditCard;
use App\Models\Driver;
use App\Models\Account_Complaint;
use App\Models\Payment;
use App\Services\AccountService;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
use App\Services\LogService;
use App\Services\PaymentSaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use function Yajra\DataTables\Html\Editor\ajax;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Str;

class UserPortalController extends Controller
{

    public function login(){

        return view('customer.login');

    }
    public function reset_password(){
        return view('customer.reset-password');
    }

    public function reset_password_email(Request $request){
        $user = Account::where('email', $request->email)->first();
        if($user){
            $token = Str::random(64);

            // Store the token
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => hash('sha256', $token),
                    'created_at' => Carbon::now()
                ]
            );
                $email_id = $request->email;
                $data = [
                    'title' => 'Welcome!',
                    'body' => 'Please click the link below to change your password, Thank you!',
                    'url' => url('/customer/change_password/'. $token)  ,
                ];

                Mail::to($email_id)->send(new ResetPasswordMail($data));

             return redirect()->back()->with('success', 'An Email with reset password link is sent, please check you inbox!');
        }else{
            return redirect()->back()->with('error', 'This email is not found in our system, please try with regesterd email !');
        }

    }
    public function change_password($token){
       
        return view('customer.change_password', compact('token'));
    }

    public function update_password(Request $request){
        $record = DB::table('password_reset_tokens')
    ->where('token', hash('sha256', $request->token))
    ->where('created_at', '>=', now()->subMinutes(60))
    ->first();

if (!$record) {
    return redirect()->back()->withErrors(['token' => 'Invalid or expired token.']);
}
        $password = $request->password;
        $con_password = $request->confirm_password;
        if($password === $con_password){
            $user = Account::where('email', $record->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();
          return redirect(url('customer/login'))->with('success', 'Password is reset, please login to your portal');
        }else{
            return redirect()->back()->with('error', 'Password is not match please try again');
        }
    }

    public function loginAttemp(Request $request){

        $user = Account::where('account_id',$request->username)->first();
        if($user->status == 0){
           return redirect()->back()->with('error', 'Account inactive. Please contact support.');  
        }
        if ($user) {

            if(!empty($user->password)) {
                if (Hash::check($request->password, $user->password)) {
                    Auth::guard('customer')->login($user);


                    if (Auth::guard('customer')->check()) {

                        return redirect()->route('customer.dashboard');

                    }

                }
            }
        }

        return redirect()->back()->with('error', 'Wrong Login Details');


    }

    public function logout()
    {

        Auth::guard('customer')->logout();
        return redirect()->to('customer/login');


    }

    public function index(Request $request)
    {


        $account = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account)->first();

            if ($request->ajax()) {
                $request->validate([
                    'from_date' => 'nullable|date',
                    'to_date' => 'nullable|date',
                ]);

                $data = AccountService::AccountSummary($request);
                return response()->json([
                    'total_trips' => $data['total_trips'],
                    'total_payments' => $data['total_payments'],
                ]);
            }

        return view('customer.index',compact('account'));

    }

    public function trips(Request $request)
    {

        if($request->ajax()) {
            $data = AccountService::GetTrips($request);

            return DataTables::of($data['trips'])
                ->addIndexColumn()
                ->addColumn('cube_status', function ($row) {
                    if (stripos($row->cube_pin_status, 'worng') !== false || stripos($row->cube_pin_status, 'wrong') !== false) {
                        return '';
                    }
                    return $row->cube_pin_status;
                }) ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary openTripModal" data-trip="' . $row->trip_id . '">Add Complaint</button>';
                })
                ->rawColumns(['action'])
                ->make(true);

        }
        $account_id = Auth::guard('customer')->user()->account_id;
        return view('customer.trips',compact('account_id'));
    }

    public function creditCards(Request $request)
    {
        $account_id = Auth::guard('customer')->user()->account_id;
        $creditcards = CreditCard::where('account_id',$account_id)->where('is_deleted' , 0);

        if(isset($request->tab)){
            if($request->tab == 'from_driver'){

                $creditcards = $creditcards->where('type','ach');

            }else{
                $creditcards = $creditcards->where('type','!=','ach');

            }
        }else{
            $creditcards = $creditcards->where('type','!=','ach');


        }
        $creditcards = $creditcards->get();

        return view('customer.cards',compact('creditcards','account_id'));
    }

    public function editCreditCard($id)
    {
        $creditcard = CreditCard::find($id);
        return view('customer.edit-card',compact('creditcard'));
    }

    public function updateCreditCard($id,Request $request)
    {
        $creditcard = CreditCard::find($id);

        $creditcard->account_id = $request->account_id;
        $creditcard->account_number = $request->account_number;
        $creditcard->account_name = $request->account_name;


        $expiry = $request->input('expiry');
        list($month, $year) = explode('/', $expiry);
        $fullYear = '20' . $year;
        $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();

        $creditcard->expiry = $expiryDate;
        $expiryWithoutSlash = str_replace('/', '', $expiry);

        $cardResponse = CardKnoxService::saveCard(
            $request->account_id,
            'credit',
            $request->card_number,
            $expiryWithoutSlash,
            $request->card_zip
        );

        if ($cardResponse['status']) {

            $creditcard->card_number = $request->card_number;
            $creditcard->expiry = $expiryDate;
            $creditcard->cvc = $request->cvc;
            $creditcard->cardnox_token = $cardResponse['data']['xToken'];
            $creditcard->charge_priority = $request->charge_priority;
            $creditcard->save();

            if($request->charge_priority == 1){
                CreditCard::where('account_id',$creditcard->account_id)->where('id','!=',$creditcard->id)->update(['charge_priority'=>0]);
            }

            return redirect()->back()->with('success','Updated Successfully');
        }
        return redirect()->back()->with('error','Please Enter Correct Details');

    }

    public function deleteCard($id)
    {

        CreditCard::find($id)->delete();
        return redirect()->back();
    }

    public function payments(Request $request)
    {

        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();
        if ($request->ajax()) {

            $account =  Account::where('account_id', $request->account_id)->first();
            if($account->account_type == 'postpaid'){
                $batchPayments = BatchPayment::where('account_id', $request->account_id)
                    ->where('from', 'customer_by_admin')
                    ->where('amount', '>', 0);

                return DataTables::of($batchPayments)
                    ->addIndexColumn()
                    ->addColumn('payment_type',function ($row) {

                        return $row->accountPay ? ($row->accountPay->payment_type == 'card' ? 'card-Ref#'.$row->accountPay->transaction_id.'' : 'cash') : '' ;
                    })
                    ->editColumn('created_at', function ($row) {
                        return date_time_formate($row->created_at);
                    })
                    ->make(true);
            }else{

                $batchPayments = AccountPayment::where('account_id',$account->account_id)->get();
                return DataTables::of($batchPayments)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        return date_time_formate($row->created_at);
                    })
                    ->make(true);
            }

        }
        return view('customer.payments',compact('account'));
    }

    public function invoices(Request $request)
    {
        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();
        if ($request->ajax()) {

            $from = Carbon::createFromDate($request->from)->format('Y-m-d');
            $to = Carbon::createFromDate($request->to)->format('Y-m-d');

            $accounts = AccountPayment::where('account_id',$account->account_id)->where('status','!=',null)->whereNotNull('hash_id');
//                ->whereDate('payment_date','>=',"$from")->whereDate('payment_date','<=',$to);

            return Datatables::of($accounts)

                ->addColumn('action', function ($row) {


                    // Action buttons
                    $html = '
                    <a href="' . url('account-invoice/' . $row->hash_id) . '" target="_blank" class="btn-sm btn-primary mt-2">
                        <i class="fa fa-eye"></i>
                    </a>';


                    return $html;
                })->addColumn('billing_email', function ($row) {
                    return $row->account ? $row->account->billing_email : '';
                })
                ->addColumn('account_id', function ($row) {
                    return '<a href="' . url('admin/show/account/' . $row->account->id) . '">'
                        . $row->account->account_id . '</a>';

                })
                ->rawColumns(['action','account_id'])
                ->make(true);


        }
        return view('customer.invoices');
    }

    public function settings()
    {

        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();

        return view('customer.setting',compact('account'));
    }

    public function updateSettings(Request $request)
    {
        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();

        DB::beginTransaction();

        $account->recharge = $request->recharge;
        $account->autofill = $request->autofill;
        $account->first_refill = $request->first_refill;
        $account->f_name = $request->f_name;
        $account->phone = $request->phone;
        $account->email = $request->email;
        $account->address = $request->address;
        $account->company_name = $request->company_name;
        $account->billing_email = $request->billing_email;
        $account->notes = $request->notes;

        $account->notification_setting = $request->notification_setting;
        $account->pins = $request->pins ? $request->pins : null;
        $account->save();

        DB::commit();

        return redirect()->back();
    }

    public function pins(){
        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();
        $pins = $account->pins;
       return  view('customer.pins', compact('pins'));
    }

    public function update_pins(Request $request){
        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();
        $account->pins = $request->pins;
        $account->save();
        return redirect()->back()->with('success', 'Your Account Pins Updated');
    }

    public function complaints(){
        $account_id = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$account_id)->first();
         $complaints = Account_Complaint::where('account_id', $account_id)->get();
         return view('customer.complaints', compact('complaints'));
    }

    public function paymentToRefill(Request $request)
    {

        $account_id = $request->account_id;
        $to_refill = $request->to_refill;
        $uaccount = Account::where('account_id', $account_id)->first();
         // Retrieve the account
        if($request->refill_method == 'card'){

          
            $cardDetails = CreditCard::where('account_id',$account_id)->where('charge_priority',1)->where('is_deleted', 0)->first();
         
            if (empty($cardDetails)) {
                // if no primary then secondary
                $cardDetails = CreditCard::where('account_id',$account_id)->where('charge_priority',0)->where('is_deleted', 0)->first();
                if (empty($cardDetails)) {
                    return redirect()->back()->with('error', 'No credit card details found for Account: ' . $account_id);
                }
            }
            $msg = '';
            $cardknoxToken = $cardDetails->cardnox_token;
           
            //trying to pay invoice if there is any before
            if($uaccount->account_type == 'postpaid') {

                $pending_inv_charged =  $this->invoiceIfAny($account_id,$to_refill,$cardknoxToken);
                $to_refill = $to_refill - $pending_inv_charged;
                $msg .= 'Pending Invoice Paid '.$pending_inv_charged;

            }

            //if not then moving to pay trip

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

                    $from_date = 2024-10-15;
                    $to_date = Carbon::now();

                    $trips_to_be_paid = $uaccount->trips->filter(function ($trip) use ($from_date, $to_date) {
                        return $trip->payment_method === 'account' &&
                            strpos($trip->status, 'Cancelled') === false &&
                            strpos($trip->status, 'canceled') === false &&
                            $trip->is_delete == 0 &&
                            $trip->date >= $from_date &&
                            $trip->date <= $to_date;
                    });
                    $total_payments = 0;
                    $batch_p = new BatchPayment();
                    $batch_p->account_id = $uaccount->account_id;
                    $batch_p->from = 'trips_paid_with_upfront_credit';
                    $batch_p->amount = $total_payments;
                    $batch_p->save();

                    foreach ($trips_to_be_paid as $paytrip) {

                        $already_paid = $paytrip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                        $unpaid_amount = $paytrip->trip_cost - $already_paid;

                        // Only pay if unpaid amount is <= available to_refill
                        if ($unpaid_amount > 0 && $unpaid_amount <= $to_refill) {


                            $pay_data = $this->addpay_customer($paytrip, $request,  $batch_p->id);

                            $total_payments += $unpaid_amount;
                            $to_refill -= $unpaid_amount; // update to_refill after payment
                        }

                    }

                    $batch_p->amount = $total_payments;
                    $batch_p->save();
                    $account_payment->batch_id = $batch_p->id;
                    $account_payment->save();


                }
           
                //$to_refill = $to_refill - $total_payments;
                if ($uaccount) {
                    $uaccount->balance += $to_refill;
                    $uaccount->save();


                    if($uaccount->account_type == 'prepaid') {
                        if ($uaccount->balance > 0) {
                            $uaccount->status = 1;
                            // if ($uaccount->cube_id == null || $uaccount->cube_id == '') {
                            //     CubeContact::createAccount($uaccount->account_id);
                            // }
                            // $cube_resp = CubeContact::updateCubeAccount($uaccount->account_id,null,'active');

                            $uaccount->save();
                        }
                    }

                } else {
                    return redirect()->back()->with(['status' => 'error', 'message' => 'Account not found.']);

                }


                $logdata = [
                    'from' => 'customer',
                    'payment' => $to_refill,
                    'cardknox_response' => $cardknoxResponse,
                    'message' => 'Refill Payment added using Cardknox for Account#' . $account_id . ' Amount: ' . $to_refill
                ];
                LogService::saveLog($logdata);


                DB::commit();
            } elseif ($cardknoxResponse['status'] == 'declined') {
                return redirect()->back()->with(['status' => 'error', 'message' => 'Cardknox Payment declined: ' . $cardknoxResponse['message']]);

            } else {

                return redirect()->back()->with(['status' => 'error', 'message' => 'Cardknox Payment failed: ' . $cardknoxResponse['message']]);

            }

        }
        //check first primary

        Session::flash('success',''.$to_refill.' Refill added successfully.'.$msg.'');
        return redirect()->back();


    }

    public function invoiceIfAny($account_id,$to_refill,$cardknoxToken){

        $unpaid_postpaid_accounts = AccountPayment::where('account_type', 'postpaid')
            ->where('status','!=','paid')->whereNotNull('hash_id')->where('account_id',$account_id)->first();

        if($unpaid_postpaid_accounts){

            $from_datee = $unpaid_postpaid_accounts->invoice_from_date;
            $to_datee = $unpaid_postpaid_accounts->invoice_to_date;

            if($unpaid_postpaid_accounts->trip_ids == null){

                $trips_to_be_paid = Account::where('account_id',$account_id)->first()->trips->filter(function ($trip) use ($from_datee,$to_datee) {
                    return $trip->payment_method === 'account' &&
                        strpos($trip->status, 'Cancelled') === false &&
                        strpos($trip->status, 'canceled') === false &&
                        $trip->is_delete == 0 &&
                        $trip->date >= $from_datee &&
                        $trip->date <= $to_datee;
                });

            }else{

                $trip_ids = json_decode($unpaid_postpaid_accounts->trip_ids);
                $trips_to_be_paid = Account::where('account_id',$account_id)->first()->trips->filter(function ($trip) use ($from_datee,$to_datee,$trip_ids) {

                    return $trip->payment_method === 'account' &&
                        strpos($trip->status, 'Cancelled') === false &&
                        strpos($trip->status, 'canceled') === false &&
                        $trip->is_delete == 0 &&
                        $trip->date >= $from_datee &&
                        $trip->date <= $to_datee && in_array($trip->trip_id,$trip_ids);

                });

            }


            $paymentDataBulk = [];
            $Trip_ids = [];
            $total_payments = 0;

            if (count($trips_to_be_paid) > 0) {
                foreach ($trips_to_be_paid as $trip) {


                    $alreadyPaid = $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                    if ($alreadyPaid < $trip->TotalCostDiscounted) {

                        $paying = (float)$trip->TotalCostDiscounted - $alreadyPaid;

                        if ($paying > 0 && $paying <= $to_refill) {

                            $paymentDataBulk[] = [
                                'driver_id' => $trip->driver_id,
                                'trip_id' => $trip->trip_id,
                                'payment_date' => now()->toDateString(),
                                'amount' => $paying,
                                'user_id' => 0,
                                'user_type' => 'customer',
                                'type' => 'debit',
                                'description' => 'Customer_portal:customer_pay_to_account' . $account_id,
                                'account_id' => $trip->account_number,
                            ];

                            $total_payments += $paying;
                            $to_refill -= $paying;

                            $Trip_ids[] = $trip->trip_id;
                        }
                    }
                }
            }

            if($total_payments > 0) {

                $fee = number_format($total_payments * 0.0375, 2, '.', '');
                $amuntWithfee = $total_payments + $fee;
                $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                    $cardknoxToken,
                    $amuntWithfee,
                    $account_id . '-amountActual' . $total_payments
                );

                $deduct_status = $fillAndDeduct['status'] === 'approved';
                $transaction_id = $deduct_status ? $fillAndDeduct['transaction_id'] : null;

                if ($deduct_status) {

                    $unpaid_postpaid_accounts->payment_date = now()->toDateString();
                    $unpaid_postpaid_accounts->payment_type = 'card';
                    $unpaid_postpaid_accounts->transaction_id = $transaction_id;
                    $unpaid_postpaid_accounts->note = 'When Customer Paying Balance Amount from portal,paying amount = '.$total_payments;

                    // Save batch payment
                    $batchPayment = BatchPayment::create([
                        'account_id' => $account_id,
                        'from' => 'customer_by_customer_portal',
                        'amount' => $total_payments,
                        'invoice_id' => $unpaid_postpaid_accounts->id,
                    ]);

                    $paymentDataSend = [
                        'batch_id' => $batchPayment->id,
                        'payments' => $paymentDataBulk,
                    ];
                    PaymentSaveService::save($paymentDataSend);

                    if($unpaid_postpaid_accounts->status == 'unpaid' && $unpaid_postpaid_accounts->amount < $total_payments){
                        $unpaid_postpaid_accounts->status = 'partial';
                    }else{
                        $unpaid_postpaid_accounts->status = 'paid';
                    }

                    $unpaid_postpaid_accounts->batch_id = $unpaid_postpaid_accounts->batch_id == null ? $batchPayment->id : $unpaid_postpaid_accounts->batch_id;
                    $unpaid_postpaid_accounts->save();


//                                        LogService::saveLog([
//                                            'from' => 'customer',
//                                            'payment' => $accountPayment,
//                                            'cardknox_response' => $fillAndDeduct,
//                                            'message' => 'Account: Payment deducted by Cron using Cardknox BatchPayment-ID#' . $batchPayment->id,
//                                        ]);

                    return $total_payments;
                }

            }

        }
        return 0;

    }
    public function addpay_customer($trip, $old_account, $batch_id = null)
    {
        $new = new Payment();
        $new->driver_id = $trip->driver_id;
        $new->trip_id = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float)$trip->trip_cost;
        $new->user_id = 1;
        $new->user_type = 'customer';
        $new->type = 'debit';
        $new->batch_id = $batch_id;
        $new->description = 'trip account id is  changed and this is to maintain balnce ' ;
        $new->account_id = $old_account;
        $new->save();

        return $new;
    }



}

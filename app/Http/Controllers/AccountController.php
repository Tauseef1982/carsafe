<?php

namespace App\Http\Controllers;

use App\Mail\CustomerLogins;
use App\Models\Account;
use App\Models\BatchPayment;
use App\Models\AccountPayment;
use App\Models\CreditCard;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\Discount;
use App\Services\AccountService;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
use App\Services\CustomPagination;
use App\Services\EmailService;
use App\Services\LogService;
use App\Services\PaymentSaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;



class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        if ($request->ajax()) {


            $accounts = Account::where('is_deleted', 0)->with(['trips']);

           if(isset($request->unpaid_postpaid) && $request->unpaid_postpaid == 1) {
               $unpaid_postpaid_accounts = AccountPayment::where('account_type', 'postpaid')
                   ->where('status', 'unpaid')
                   ->whereNotNull('hash_id')->pluck('account_id');

               $accounts = $accounts->whereIn('account_id', $unpaid_postpaid_accounts);
           }

            if(isset($request->status)){
            if($request->status != 'all'){
                $accounts = $accounts->where('status',$request->status);
            }
            }
            if(isset($request->have_card)){
              if($request->have_card == 'yes'){
                $accounts = Account::whereHas('cards', function ($query) {
                    $query->where('is_deleted', 0);
                })->get();

              }elseif($request->have_card == 'no'){
                $accounts = Account::whereDoesntHave('cards') // No cards at all
    ->orWhereHas('cards', function ($query) {
        $query->where('is_deleted', 1);
    })->whereDoesntHave('cards', function ($query) {
        $query->where('is_deleted', 0);
    })->get();


              }else{
                $accounts = Account::where('is_deleted', 0)->with(['trips']);
              }


                }
            return Datatables::of($accounts)
            ->addColumn('full_name', function ($row) {
                $fName = ucfirst(strtolower($row->f_name));
                $lName = ucfirst(strtolower($row->lname));
                return $fName . ' ' . $lName;
            })
                ->addColumn('totalPaidAmount', function ($row) {
//                    $totalPaid = $row->totalPaidAmountByCustomerFromAccount();

                    return number_format(0, 2, '.', ',');
                })

                ->addColumn('totalCost', function ($row) {
//                    return number_format($row->totalcost(), 2, '.', ',');
                })
                ->addColumn('actions', function ($row) {

                    // Action buttons
                    $html = '';
                    $html .= '<a href="' . url('admin/edit/account/' . $row->id) . '" class="btn-sm btn-primary">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <a href="' . url('admin/show/account/' . $row->id) . '" class="btn-sm btn-primary">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a class="btn-sm btn-danger" data-bs-toggle="modal"
                            data-original-title="test" data-bs-target="#exampleModal' . $row->id . '">
                        <i class="fa fa-trash"></i>
                    </a>';

                        $html .= '<a class="btn-sm btn-success" onclick="download_invoice_link(' . $row->id . ')"
                                data-modalcontent="">
                            <i class="fa fa-download"></i>
                        </a>';



                   return $html;
                })
                ->editColumn('status', function ($row) {
                    $status = $row->status == 1 ? 'Active' : 'Inactive';
                    return $status;
                })
                ->addColumn('cards', function ($row) {
                    return $row->cards()->where('is_deleted', 0)->exists() ? 'Yes' : 'No';
                })
                ->rawColumns(['total_cost', 'actions','cards'])
                ->make();


        }
        return view('admin.account.accounts');
    }

    public function invoices(Request $request){

        if ($request->ajax()) {

            $from = Carbon::createFromDate($request->from)->format('Y-m-d');
            $to = Carbon::createFromDate($request->to)->format('Y-m-d');

            $accounts = AccountPayment::where('status','!=',null)->whereNotNull('hash_id')
                ->whereDate('payment_date','>=',"$from")->whereDate('payment_date','<=',$to);

            return Datatables::of($accounts)

                ->addColumn('action', function ($row) {


                    // Action buttons
                    $html = '
                    <a href="' . url('account-invoice/' . $row->hash_id) . '" target="_blank" class="btn-sm btn-primary mt-2">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="' . url('admin/account-invoice/retry/' . $row->id) . '"  class="btn-sm btn-primary ms-2 mt-2">
                        <i class="fa fa-retweet"></i>
                    </a>';


                    return $html;
                })
                ->addColumn('deldel', function ($row) {


                    // Action buttons
                    $html = '
                    <a href="' . url('delete-dub/' . $row->id) . '" target="_blank" class="btn-sm btn-primary">
                        <i class="fa fa-trash"></i>
                    </a>';


                    return $html;
                })
                ->addColumn('charge10', function ($row) {


                    // Action buttons
                    $html = '
                    <a href="' . url('run-cron-tauseef/' . $row->hash_id) . '" target="_blank" class="btn-sm btn-primary">
                        charge10%
                    </a>';


                    return $html;
                })
                ->addColumn('email_sends', function ($row) {
                    $html = $row->email_sends;
                    $html .= '
                    <a href="' . url('admin/account-invoice/send-email/' . $row->hash_id) . '"  class="btn-sm btn-danger">
                        Send Again
                    </a>';

                    return $html;

                })->addColumn('billing_email', function ($row) {
                    return $row->account ? $row->account->billing_email : '';
                })
                ->addColumn('account_id', function ($row) {
                    return '<a href="' . url('admin/show/account/' . $row->account->id) . '">'
                   . $row->account->account_id . '</a>';

                })
                ->rawColumns(['action','email_sends','deldel','charge10','account_id'])
                ->filterColumn('account_id', function($query, $keyword) {
                    $query->whereRaw("account_id LIKE ?", ["%{$keyword}%"]);
                })
                ->make(true);


        }


        $unpaid_sum = AccountPayment::where('account_type', 'postpaid')
            ->where('status', 'unpaid')
            ->whereNotNull('hash_id')->sum('amount');

        return view('admin.account.invoices',compact('unpaid_sum'));

    }

    public function invoiceSendEmail($id){

        $invoice = AccountPayment::where('hash_id',$id)->first();
        $account = Account::where('account_id',$invoice->account_id)->first();
        EmailService::AccountInvoice($invoice,$account);

        return redirect()->back();
    }

    /**
     * Show the form for creating a new resource.
     */
    private function generateUniqueAccountId()
    {
        do {

            $randomNumber = mt_rand(1000, 9999);


            $exists = Account::where('account_id', $randomNumber)->exists();

        } while ($exists);

        return $randomNumber;
    }


    public function create(Request $request)
    {


        $expiry = $request->input('expiry');
        if(count(explode('/', $expiry)) != 2){
            return redirect()->back()->with('error','Incorrect Expiry Format');
        }

        if(Account::where('account_id',$request->account_id)->exists()){
            return redirect()->back()->with("error",'Already Exists With Same Account Number');

        }

        list($month, $year) = explode('/', $expiry);
        $fullYear = '20' . $year;
        $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();
        $expiryWithoutSlash = str_replace('/', '', $expiry);

        // Call CardKnoxService to save the card
        $cardResponse = CardKnoxService::saveCard(
            $request->account_id,
            'credit',
            $request->card_number,
            $expiryWithoutSlash,
            $request->card_zip
        );


        if ($cardResponse['status']) {

            $creditCard = new CreditCard;
            $creditCard->card_number = $request->card_number;
            $creditCard->cvc = $request->cvc;
            $creditCard->expiry = $expiryDate;
            $creditCard->cardnox_token = $cardResponse['data']['xToken'];

        } else {

            return redirect()->back()->with("error", $cardResponse['msg']);

        }


        $account = new Account();
        $account->account_type = $request->account_type;
        $account->recharge = $request->recharge;
        $account->autofill = $request->autofill;
        $account->account_id = $request->account_id;
        $account->f_name = $request->f_name;
        $account->phone = $request->phone;
        $account->email = $request->email;
        $account->address = $request->address;
        $account->company_name = $request->company_name;
        $account->billing_email = $request->billing_email;
        $account->notes = $request->notes;
        $account->status = 1;
        $account->first_refill = $request->first_refill;
        $account->notification_setting = $request->notification_setting;
        $account->pins = $request->pins ? $request->pins : null;
        $account->password = Hash::make($request->password);
        $account->save();
        $data['username'] = $request->account_id;
        $data['password'] = $request->password;
        Mail::to($request->email)->send(new CustomerLogins($data));

        $cardknoxToken = $creditCard->cardnox_token;
           $fee = $account->first_refill * 0.03;
           $amount = $fee + $account->first_refill;

        $cardknoxResponse = CardKnoxService::processCardknoxPaymentRefill($cardknoxToken,  $amount, $account->account_id);
        if ($cardknoxResponse['status'] == 'approved') {

            $account_payment = new AccountPayment();
            $account_payment->account_id = $account->account_id;
            $account_payment->account_type = $account->account_type;
            $account_payment->amount = $account->first_refill;
            $account_payment->transaction_id = $cardknoxResponse['transaction_id'];
            $account_payment->payment_date = Carbon::today();
            $account_payment->payment_type = 'card';
            $account_payment->save();

                $account->balance += $account->first_refill;
                $account->save();


            } else {
                return redirect()->back()->with(['status' => 'error', 'message' => 'Account not found.']);

            }
        $discount = new Discount;
        $discount->percentage = 10;
        $discount->start_date = Carbon::now();
        $discount->end_date = Carbon::now()->addDays(30);
        $discount->save();

        $discount->accounts()->attach($account->id);

        $creditCard->account_id = $account->account_id;
        $creditCard->save();
        return redirect()->back()->with('success', 'Account is addedd successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function status(Request $request, $id)
    {
        $account = Account::find($id);

        $account->status = $request->status;
        $account->save();
        return redirect()->route('admin.show_account')->with('success', 'Account status is changed successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $account = Account::find($id);
        $invoices = AccountPayment::where('status','!=',null)
        ->where('account_id', $account->account_id)->whereNotNull('hash_id')->get();

        return view('admin.account-show', compact('account','invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $account = Account::with('trips')->find($id);
        return view('admin/edit-account', compact('account'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $account = Account::find($id);

        if($account->account_type == 'postpaid') {
        if($request->account_type == 'prepaid') {

            $balnce = $account->totalcost() - $account->totalPaidAmountByCustomerFromAccount();
            if($balnce <= 0){

                $account->balance = 0;
//                $account->balance = 0-$balnce;
            }else{
                Session::flash('error','Cannot be convert to prepaid balance not 0');
                return  redirect()->back();

            }
        }
        }
        if($account->account_type == 'prepaid') {
            if($request->account_type == 'postpaid') {
                $balnce = $account->balance;
                if($balnce > 0){
                    Session::flash('error','Cannot be convert to prepaid balance not 0');
                    return redirect()->back();
                }
            }
        }

        DB::beginTransaction();

        $account->account_type = $request->account_type;
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
        $account->status = $request->status;
        $account->username = $request->username ? $request->username: null;
        $account->reason = $request->reason ? $request->reason : null;
        $account->notification_setting = $request->notification_setting;
        $account->pins = $request->pins ? $request->pins : null;
        if(isset($request->change_pass)) {
            $account->password = Hash::make($request->password);
        }
        $account->save();


            if($request->status == 0){

    //            CubeContact::deleteAccount($account->account_id);
                $cube_resp =  CubeContact::updateCubeAccount($account->account_id,"Your Account Is Closed","Inactive",true);

            }else{
                $cube_resp  = CubeContact::updateCubeAccount($account->account_id,null,"active",true);

            }

                if($cube_resp != 1){

                    DB::rollBack();
                    Session::flash('error',$cube_resp);
                    return redirect()->back();
                }

            if($account->cube_id == null || $account->cube_id == '') {
                CubeContact::createAccount($account->account_id);
            }

        DB::commit();

        return redirect()->route('admin.show_account')->with('success', 'Account is updated successfully');

    }


    public function paymentToRefill(Request $request)
    {

//        dd($request->all());
        $account_id = $request->account_id;
        $to_refill = $request->to_refill;
        $uaccount = Account::where('account_id', $account_id)->first(); // Retrieve the account
        if($request->refill_method == 'cash'){

            $account_payment = new AccountPayment();
            $account_payment->account_id = $uaccount->account_id;
            $account_payment->account_type = $uaccount->account_type;
            $account_payment->amount = $to_refill;
            $account_payment->payment_date = Carbon::today();
            $account_payment->payment_type = 'cash';
            $account_payment->save();

            if ($uaccount) {
                $uaccount->balance += $to_refill;
                $uaccount->save();


                if($uaccount->account_type == 'prepaid') {
                    if ($uaccount->balance > 0) {
                        $uaccount->status = 1;
                        if ($uaccount->cube_id == null || $uaccount->cube_id == '') {
                            CubeContact::createAccount($uaccount->account_id);
                        }
                        CubeContact::updateCubeAccount($uaccount->account_id,null,'active');

                        $uaccount->save();
                    }
                }

            } else {
                return redirect()->back()->with(['status' => 'error', 'message' => 'Account not found.']);

            }
            $logdata = [
                'from' => 'customer',
                'payment' => $to_refill,
                'message' => 'Refill Payment added using Cash for Account#' . $account_id . ' Amount: ' . $to_refill
            ];
            LogService::saveLog($logdata);


            DB::commit();

        }else{

            $cardDetails = CreditCard::where('account_id',$account_id)->where('charge_priority',1)->where('is_deleted', 0)->first();

            if (empty($cardDetails)) {
                // if no primary then secondary
                $cardDetails = CreditCard::where('account_id',$account_id)->where('charge_priority',0)->where('is_deleted', 0)->first();
                if (empty($cardDetails)) {
                    return redirect()->back()->with('error', 'No credit card details found for Account: ' . $account_id);
                }
            }

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

                if ($uaccount) {
                    $uaccount->balance += $to_refill;
                    $uaccount->save();


                    if($uaccount->account_type == 'prepaid') {
                        if ($uaccount->balance > 0) {
                            $uaccount->status = 1;
                            if ($uaccount->cube_id == null || $uaccount->cube_id == '') {
                                CubeContact::createAccount($uaccount->account_id);
                            }
                           $cube_resp = CubeContact::updateCubeAccount($uaccount->account_id,null,'active');

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



        return redirect()->back()->with(['status' => 'success', 'message' => 'Refill added successfully.']);


    }



    public function paymentToGocab(Request $request)
    {


        $account_id = $request->account_id;
        $id_of_account = Account::where('account_id', $account_id)->value('id');
        $to_gocab = $request->to_gocab;
        $trips = Trip::leftJoin('payments', 'trips.trip_id', '=', 'payments.trip_id')
            ->where('trips.account_number', $account_id)
            ->where('trips.is_delete', 0)->where('payment_method', 'account')
            ->whereDate('trips.date', '>', '2024-09-05');


        $trips = $trips->select(
            'trips.extra_charges',
            'trips.extra_stop_amount',
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
            'trips.is_auto_paid_as_adjustment',
            \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'debit' AND payments.user_type = 'customer' THEN payments.amount ELSE 0 END), 0) as total_paid")

        );

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
            'trips.is_auto_paid_as_adjustment'

        );
        // dd($trips->get());

        $trips = $trips->havingRaw('total_paid < trips.trip_cost');
        $trips = $trips = $trips->orderBy('trips.date', 'asc')
            ->orderBy('trips.time', 'asc')
            ->get();

        return view('admin.account.paytogocab', compact('trips', 'account_id', 'to_gocab', 'id_of_account'));

    }


    public function payFromAccountToCab(Request $request)
    {

        dd('ddd');
        $toGoCab = $request->input('to_gocab');
        $live_amount = $toGoCab;
        $tripsData = $request->input('trips');
        $account_id = $request->input('account_id');
        $payment_type = $request->payment_type ? $request->payment_type : 'card';
        $transaction_id = null;

        if (is_array($tripsData) && !empty($tripsData)) {

            $account = Account::find($request->id);
            $totalTripsAmount = array_sum(array_column($tripsData, 'amount'));

            if ($payment_type == 'card') {

                if (!$account->card) {

                    return response()->json(['status' => 'card', 'message' => 'No credit card details found for account_id: ' . $account->account_id]);
                }


                $cardknoxToken = $account->card->cardnox_token;
                $cardknoxResponse = CardKnoxService::processCardknoxPaymentRefill($cardknoxToken, $totalTripsAmount, $account->id);

                if ($cardknoxResponse['status'] == 'approved') {

                    $transaction_id = $cardknoxResponse['transaction_id'];
                } elseif ($cardknoxResponse['status'] == 'declined') {


                    return response()->json(['status' => 'error', 'message' => 'Cardknox Payment declined: ' . $cardknoxResponse['message']]);

                } else {
                    return response()->json(['status' => 'error', 'message' => 'Cardknox Payment failed: ' . $cardknoxResponse['message']]);

                }

            }

            $batch_new = new BatchPayment();
            $batch_new->account_id = $account_id;
            $batch_new->from = 'customer_by_admin';


            $batch_id = $batch_new->id;
            foreach ($tripsData as $trip) {

//                    $gocabPaid = Trip::where('id', $trip['trip_id'])->value('trip_cost');

//                    $trip['gocab_paid'] = $gocabPaid ?? 0;
                    if ($live_amount < 0) {
                        return response()->json(['status' => 'error', 'message' => 'Amount should not be negative']);

                    }

                    $originalAmount = $trip['amount'];
                    $paymentSum = Payment::where('trip_id', $trip['trip_id'])->where('user_type', 'customer')->where('type', 'debit')->sum('amount') ?? 0;

                    $remainingAmount = $originalAmount - $paymentSum;
                    if ($remainingAmount > $originalAmount) {
                        return response()->json(['status' => 'error', 'message' => 'Remaining trip amount should less than Trip total amount']);

                    }
                    if ($live_amount < $remainingAmount) {
                        $remainingAmount = $live_amount;
                    }

                    $totalAmount = $remainingAmount;

                    if ($totalAmount > 0) {

                        $pay_data = $this->addpay($trip, $request, $remainingAmount, $batch_id);

                        $live_amount -= $remainingAmount;



                    } else {
                        return response()->json(['status' => 'success', 'message' => 'Payments processed successfully.']);

                    }


            }

            $batch_p->amount = $total_payments;
            $batch_p->save();

            $account_payment = new AccountPayment();
            $account_payment->account_id = $account->account_id;
            $account_payment->account_type = $account->account_type;
            $account_payment->batch_id = $batch_p->id;
            $account_payment->amount = $total_payments;
            $account_payment->transaction_id = $transaction_id;
            $account_payment->payment_date = Carbon::today();
            $account_payment->payment_type = $payment_type;
            $account_payment->save();

            if ($payment_type == 'card') {
                $logdata = [
                    'from' => 'customer',
                    'payment' => $account_payment,
                    'cardknox_response' => $cardknoxResponse,
                    'message' => 'Account:Payment deducted by Admin using Cardknox BatchPayment-ID#' . $batch_p->id . ' Amount: ' . $batch_p->amount
                ];
            } else {
                $logdata = [
                    'from' => 'customer',
                    'payment' => $account_payment,
                    'message' => 'Account:Payment deducted by Admin using Cash BatchPayment-ID#' . $batch_p->id . ' Amount: ' . $batch_p->amount
                ];

            }
            LogService::saveLog($logdata);

        } else {
            return response()->json(['status' => 'error', 'message' => 'No Trips found ']);

        }
        return response()->json(['status' => 'success', 'message' => 'Payments processed successfully.']);

        // return redirect('/admin/accounts')->with('success', 'Payments processed successfully.');
    }


    public function addpay_customer($trip, $request)
    {
        $new = new Payment();
        $new->driver_id = $trip->driver_id;
        $new->trip_id = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float)$trip->trip_cost;
        $new->user_id = $request['id'];
        $new->user_type = 'customer';
        $new->type = 'debit';
        $new->batch_id = $request['batch_id'];
        $new->description = 'customer_pay_to_account' . $request['account_id'];
        $new->account_id = $trip->account_number;
        $new->save();

        return $new;
    }


//    public function addpay($trip, $request, $total_amount_paid, $batch_id)
//    {
//
//        $new = new Payment();
//        $new->driver_id = $trip['driver_id'];
//        $new->trip_id = $trip['trip_id'];
//        $new->batch_id = $batch_id;
//        $new->payment_date = now()->toDateString();
//        $new->amount = (float)$total_amount_paid;
//        $new->user_id = $trip['driver_id'];
//        $new->user_type = 'customer';
//        $new->type = 'debit';
//        $new->description = 'customer_pay_to_account' . $request->id;
//
//
//        $new->save();
//
//        $batch = BatchPayment::find($batch_id);
//        if ($batch) {
//            $batch->amount += $total_amount_paid;
//            $batch->save();
//        }
//
//        return $new;
//    }


    public function show_invoice(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);


        $account = Account::find($request->id);

        $from_date = $validated['from_date'];
        $to_date = $validated['to_date'];
        $account_number = $validated['id'];

        $total_number_of_trips = $account->trips->count();

        if (isset($request->payment_type) && isset($request->unpaid)) {

            $token = null;
            $payment_type = $request->payment_type;

            // if not cash then its card or ach
            if ($payment_type != 'cash') {

                if ($request->is_card == 0) {


                    if ($payment_type == 'card') {

                        $new = new CreditCard;
                        $new->account_id = $account->account_id;
                        $new->card_number = $request->card_number;
                        $new->cvc = $request->cvc;
                        $expiry = $request->input('expiry');

                        list($month, $year) = explode('/', $expiry);
                        $fullYear = '20' . $year;
                        $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();
                        $new->expiry = $expiryDate;

                        $expiryWithoutSlash = str_replace('/', '', $expiry);

                        $cardResponse = CardKnoxService::saveCard(
                            $request->account_id,
                            'credit',
                            $request->card_number,
                            $expiryWithoutSlash,
                            $request->card_zip
                        );

                    }elseif($payment_type == 'ach'){


                        $cardResponse = CardKnoxService::saveAch(
                            $request->account_id,
                            $request->ach_account_name,
                            $request->ach_account_number,
                            $request->routing_number

                        );


                    }else{


                        return redirect()->back()->with(['errors' => 'Nothing Happened']);


                    }

                    if ($cardResponse['status']) {

                        $new = new CreditCard;
                        $new->account_id = $account->account_id;
                        $new->account_number = $request->ach_account_number;
                        $new->routing_number = $request->routing_number;
                        $new->type = 'ach';
                        $new->cardnox_token = $cardResponse['data']['xToken'];

                        $new->save();

                        $token = $cardResponse['data']['xToken'];


                    }

                } elseif ($request->is_card == 1) {

                    $token = $account->card->cardnox_token;


                } else {

                    return redirect()->back()->with(['errors' => 'Nothing Happened']);
                }

            }

            if (isset($request->unpaid)) {

                $total_cost = $request->unpaid;
                $deduct_status = false;
                $transaction_id = null;


                if ($payment_type != 'cash') {

                    if ($token != null) {

                        if ($payment_type == 'card') {
                            $fill_and_deduct = CardKnoxService::processCardknoxPaymentRefill($token, $total_cost, $account->id);
                        }
                        if ($payment_type == 'ach') {

                            $fill_and_deduct = CardKnoxService::cardknoxAchPayment($token, $total_cost, $account->id);
                        }

                        if ($fill_and_deduct['status'] == 'approved') {
                            $deduct_status = true;
                            $transaction_id = $fill_and_deduct['transaction_id'];
                        } else {

                            return redirect()->back()->with(['errors' => 'Invalid Details,' . $fill_and_deduct['message']]);
                        }


                    }

                } elseif ($payment_type == 'cash') {

                    $deduct_status = true;

                }

                if ($deduct_status == true) {

                    $already_acc_payment = AccountPayment::where('account_id', $account->account_id)
                        ->where('invoice_from_date', $from_date)
                        ->where('invoice_to_date', $to_date)
                        ->whereNotNull('hash_id')
                        ->first();

                    if ($already_acc_payment) {


                        if($total_cost >= $already_acc_payment->amount){

                            $already_acc_payment->status = 'paid';
                            $already_acc_payment->save();



                        }else{
                            $already_acc_payment = false;
                        }


                    }


                        $batch_p = new BatchPayment();
                        $batch_p->account_id = $account->account_id;
                        $batch_p->from = 'customer_by_admin';
                        $batch_p->amount = $total_cost;
                        $batch_p->save();

                    $request->merge(['batch_id' => $batch_p->id]);
                    $request->merge(['account_id' => $account->account_id]);
                    $total_payments = 0;
//                    $trips_to_be_paid = $account->trips->where('payment_method', '=', 'account')->where('date', '>=', $from_date)->where('date', '<=', $to_date);

                    $trips_to_be_paid = $account->trips->filter(function ($trip) use ($from_date, $to_date) {
                        return $trip->payment_method === 'account' &&
                            strpos($trip->status, 'Cancelled') === false &&
                            strpos($trip->status, 'canceled') === false &&
                            $trip->is_delete == 0 &&
                                $trip->date >= $from_date &&
                                $trip->date <= $to_date;
                    });
                    foreach ($trips_to_be_paid as $paytrip) {

                        if ($paytrip->totalPaidAmountByCustomerFromAccountCard()->sum('amount') < $paytrip->trip_cost) {
                            $pay_data = $this->addpay_customer($paytrip, $request);
                            // dd($pay_data);

                            $total_payments = $total_payments + $paytrip->trip_cost;

                        }

                    }


                    $batch_p->amount = $total_payments;
                    $batch_p->save();

                    if(!$already_acc_payment) {

                        $account_payment = new AccountPayment();
                        $account_payment->account_id = $account->account_id;
                        $account_payment->account_type = $account->account_type;
                        $account_payment->batch_id = $batch_p->id;
                        $account_payment->amount = $total_payments;
                        $account_payment->transaction_id = $transaction_id;
                        $account_payment->payment_date = Carbon::today();
                        $account_payment->payment_type = $payment_type;
                        $account_payment->save();

                    }else{


                        $already_acc_payment->batch_id = $batch_p->id;
                        $already_acc_payment->payment_type = 'cash';
                        $already_acc_payment->save();
                        $account_payment = $already_acc_payment;

                    }
                    if ($payment_type != 'cash') {
                        $logdata = [
                            'from' => 'customer',
                            'payment' => $account_payment,
                            'cardknox_response' => $fill_and_deduct,
                            'message' => 'Account:Payment deducted by Admin using Cardknox BatchPayment-ID#' . $batch_p->id . ' Amount: ' . $batch_p->amount
                        ];
                    } else {
                        $logdata = [
                            'from' => 'customer',
                            'payment' => $account_payment,
                            'message' => 'Account:Payment deducted by Admin using Cash BatchPayment-ID#' . $batch_p->id . ' Amount: ' . $batch_p->amount
                        ];

                    }
                    LogService::saveLog($logdata);

                    return redirect()->back()->with(['success' => 'Paid Amount $' . $total_payments . ' Against This Invoice']);

                }


            }

            return redirect()->back()->with(['errors' => 'Nothing Happened']);

        }


        return view('invoices.invoice', compact('account_number', 'total_number_of_trips', 'account', 'from_date', 'to_date'));

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        $account = Account::find($id);
        $account->is_deleted = 1;
        $account->cube_id = null;
//        CubeContact::deleteAccount($account->account_id);
        CubeContact::updateCubeAccount($account->account_id,"Your Account Is Closed","Inactive");

        $account->save();
        return redirect()->back()->with('success', 'Account is deleted successfully');
    }


    public function getPaymentsAccount(Request $request)
    {

        if ($request->ajax()) {
            $payments = DB::table('payments')
                ->join('trips', 'payments.trip_id', '=', 'trips.trip_id')
                ->join('accounts', 'trips.account_number', '=', 'accounts.account_id')
                ->select(
                    'payments.batch_id',
                    'payments.payment_date',
                    DB::raw('SUM(payments.amount) as total_amount')
                )
                ->groupBy('payments.batch_id', 'payments.payment_date')
                ->where('accounts.account_id', $request->account_id)
                ->where('payments.user_type', 'customer')
                ->where('payments.type', 'debit')
                ->where('payments.is_delete', 0)
                ->get();
            // dd($payments);
            return DataTables::of($payments)
                ->addIndexColumn()
                ->editColumn('total_amount', function ($payment) {
                    return '$' . number_format($payment->total_amount, 2);
                })
                ->make(true);
        }

    }

    public function getBatchPayments(Request $request)
    {
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
            ->make(true);
           }else{

            $batchPayments = AccountPayment::where('account_id',$account->account_id)->get();
            return DataTables::of($batchPayments)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)
                    ->setTimezone('America/New_York')
                    ->format('Y-m-d h:i A');
            })
            ->make(true);
        }

        }

    }

    public function getPaymentsForBatch(Request $request)
    {
        if ($request->ajax()) {

            $payments = DB::table('payments')
                ->where('batch_id', $request->batch_id)
                ->where('user_type', 'customer')
                ->where('type', 'debit')
                ->where('is_delete', 0)
                ->get();

            return response()->json($payments);
        }
    }


    public function ajaxGetTotals(Request $request)
    {
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

    }
    public function accountPayments(){

        $payments = AccountPayment::all();
        return view('admin.account-payments', compact('payments'));
    }



    public function invoiceViewOrPay($id){

        $invoice = AccountPayment::where('hash_id',$id)->first();

        $trip_ids = $invoice->trip_ids != null ? json_decode($invoice->trip_ids) : null;
        if($invoice){
            $account = Account::where('account_id',$invoice->account_id)->first();

            if($trip_ids == null) {
                $trips = $account->trips->where('payment_method', '=', 'account')->where('date', '>=', $invoice->invoice_from_date)->where('date', '<=', $invoice->invoice_to_date)->where('is_delete', 0);
            }else {
                $trips = $account->trips->whereIn('trip_id', $trip_ids)->filter(function ($trip) use ($invoice) {
                    return $trip->payment_method === 'account' &&
                        strpos($trip->status, 'Cancelled') === false &&
                        strpos($trip->status, 'canceled') === false &&
                        $trip->is_delete == 0 &&
                        $trip->date >= $invoice->invoice_from_date &&
                        $trip->date <= $invoice->invoice_to_date;
                });
            }
            if($account->account_type == 'prepaid'){
                return view('invoices.prepaid_unauth_invoice_view',compact('account','trips','invoice'));

            }else{
                return view('invoices.unauth_invoice_view',compact('account','trips','invoice'));

            }

        }



    }
    public function invoicePay(Request $request,$id){



        // auto save as a secondary card
        $request->save_card = 1;
        $year = $request->input('expiry-year');
        $month = $request->input('expiry-month');

        $fullYear = '20' . $year;
        $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();
        $expiryWithoutSlash = $month.$year;

        $account_payment = AccountPayment::where('hash_id',$id)->first();
        if(!$account_payment){
            return redirect()->back()->with('error','Incorrect Invoice Submitting');

        }

        //validation end
        $token = null;

        if(isset($request->payment_type) && $request->payment_type == 'ach'){

            $cardResponse = CardKnoxService::saveAch(
                'ach-account-from-outside',
                $request->ach_account_name,
                $request->ach_account_number,
                $request->routing_number

            );

        }else{


            $cardResponse = CardKnoxService::saveCard(
                $request->account_id,
                'credit',
                $request->card_number,
                $expiryWithoutSlash,
                $request->card_zip
            );


        }


        $account = Account::where('account_id',$account_payment->account_id)->first();
        if ($cardResponse['status']) {


            if(isset($request->save_card) && $request->save_card == 1){

//                if($account->card){
//
//                    $new = CreditCard::find($account->card->id);
//                    $new->card_number = $request->card_number;
//                    $new->cvc = $request->cvc;
//                    $new->cardnox_token = $cardResponse['data']['xToken'];
//                    $new->expiry = $expiryDate;
//                    $new->save();
//
//                }else{

                    $new = new CreditCard;
                    $new->account_id = $account->account_id;
                    $new->card_number = $request->card_number;
                    $new->cvc = $request->cvc;
                    $new->cardnox_token = $cardResponse['data']['xToken'];
                    $new->expiry = $expiryDate;
                    $new->charge_priority = 0;
                    $new->save();
//                }

            }

            $token = $cardResponse['data']['xToken'];


        }else{

            return redirect()->back()->with('error',$cardResponse['msg']);

        }


        $total_payments = 0;
        $deduct_status = false;

//        $trips_to_be_paid = $account->trips->where('payment_method', '=', 'account')->where('date', '>=', $account_payment->invoice_from_date)->where('date', '<=', $account_payment->invoice_to_date);

        $trips_to_be_paid = $account->trips->filter(function ($trip) use ($account_payment) {
            return $trip->payment_method === 'account' &&
                strpos($trip->status, 'Cancelled') === false &&
                strpos($trip->status, 'canceled') === false &&
                $trip->is_delete == 0 &&
                    $trip->date >= $account_payment->invoice_from_date &&
                    $trip->date <= $account_payment->invoice_to_date;
        });

        $paymeny_data_Bulk = array();
        foreach ($trips_to_be_paid as $paytrip) {

            $AlreadyPaid = $paytrip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
            if ($AlreadyPaid < $paytrip->TotalCostDiscounted) {

                $paying = (float)$paytrip->TotalCostDiscounted - $AlreadyPaid;

                $payment_data['driver_id'] = $paytrip->driver_id;
                $payment_data['trip_id'] = $paytrip->trip_id;
                $payment_data['payment_date'] = now()->toDateString();
                $payment_data['amount'] = $paying;
                $payment_data['user_id'] = 0;
                $payment_data['user_type'] = 'customer';
                $payment_data['type'] = 'debit';
                $payment_data['description'] = 'LinkInvoice:customer_pay_to_account' . $account->account_id;
                $payment_data['account_id'] = $paytrip->account_number;

                $paymeny_data_Bulk[] = $payment_data;
                $total_payments = $total_payments + $paying;

            }

        }
        if(isset($request->payment_type) && $request->payment_type == 'ach') {

            $fee = 0;

        }else{

            $fee = number_format($total_payments * 0.0375, 2, '.', '');


        }
            $amuntWithfee = $total_payments + $fee;
        $fill_and_deduct = CardKnoxService::processCardknoxPaymentRefill($token, $amuntWithfee, $account->account_id);



        if ($fill_and_deduct['status'] == 'approved') {

            $deduct_status = true;
            $transaction_id = $fill_and_deduct['transaction_id'];
            $account_payment->transaction_id = $transaction_id;


        }
        $try = $account_payment->try + 1;

        if ($deduct_status == true) {


            $batch_p = new BatchPayment();
            $batch_p->account_id = $account->account_id;
            $batch_p->from = 'customer_by_admin';
            $batch_p->amount = $total_payments;
            $batch_p->save();

            $paymeny_data_send['batch_id'] = $batch_p->id;
            $paymeny_data_send['payments'] = $paymeny_data_Bulk;

            $account_payment->batch_id = $batch_p->id;

            PaymentSaveService::save($paymeny_data_send);
            $message ='Account:Payment deducted by Cron using Cardknox BatchPayment-ID#' . $batch_p->id . ' Amount: ' . $batch_p->amount;

            CubeContact::updateCubeAccount($account->account_id,null,"active");


        }else{

            Log::info('no'.$total_payments);
            $message = 'Account:Payment Failed by Cron using Cardknox';
            if($try > 3){
                CubeContact::updateCubeAccount($account->account_id,"Account is inactive due to unpaid invoices","Inactive");
                EmailService::AccountInActive($account);

            }
        }


        $account_payment->status = $deduct_status == true ? 'paid' : 'unpaid';
        $account_payment->try = $try;
        $account_payment->save();

        $logdata = [
            'from' => 'customer',
            'payment' => $amuntWithfee,
            'cardknox_response' => $fill_and_deduct,
            'message'=>$message
        ];

        LogService::saveLog($logdata);

        EmailService::AccountInvoice($account_payment,$account);

        return redirect()->back()->with(['success' => 'Paid Amount ' . $total_payments . ' Against This Invoice']);

    }



    public function sendBulkInvoiceEmail(Request $request){

        $request->validate([
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $accounts = Account::where('account_type','!=','prepaid')->where('is_deleted',0)->get();
        foreach ($accounts as $account) {

                    $data['from_date'] = $request->from_date;
                    $data['to_date'] = $request->to_date;
                    $data['account'] = $account;

                        EmailService::sendBulkInvoices($data);


        }
    }


    public function deleteDInvcoie($id){

        // dd('restrict');
        $data = AccountPayment::where('id',$id)
            // ->where('invoice_from_date','2025-01-01')
            // ->where('invoice_to_date','2024-01-15')
            ->where('account_type','postpaid')->first();
        if($data){

                DB::beginTransaction();

                $batch_id = $data->batch_id;
                if($batch_id != null) {


                    $countt = Payment::where('batch_id',$batch_id)->where('account_id','!=',null)->count();
                    if($countt > 0) {
                        Payment::where('batch_id',$batch_id)->where('account_id','!=',null)->delete();
                    }

                    BatchPayment::where('id',$batch_id)->delete();

                }

                $data->delete();

                DB::commit();

                dd('deleted');


            }

            dd('notfount');


    }
    public function invoices_retry($id){


        $accountPayment = AccountPayment::find($id);
        $insertData = [];
        $emailData = [];
        $today = Carbon::today();
        $pay_date = now()->toDateString();

        if($accountPayment->status == 'unpaid'){

            $from_date = $accountPayment->invoice_from_date;
            $to_date = $accountPayment->invoice_to_date;

            $account = Account::with(['trips' => function ($query) use ($from_date, $to_date) {
                $query->where('payment_method', 'account')
                    ->where('is_delete',0)
                    ->whereBetween('date', [$from_date, $to_date]);
            }])->where('account_type', 'postpaid')
                ->where('account_id', $accountPayment->account_id)->first();


            // Step 1: Arrange data
//            $token = $account->card ? $account->card->cardnox_token : null;
            $tokens = $account->cards ? $account->cards : [];
            if($tokens) {
                $tokens = $tokens->where('is_deleted',0)  // Filter out deleted records
                ->sortByDesc('charge_priority')  // Sort by priority (1 first)
                ->values();
            }
            $total_payments = 0;
            $deduct_status = false;

            $trips = $account->trips->filter(function ($trip) {
                return $trip->payment_method === 'account' &&
                    strpos($trip->status, 'Cancelled') === false &&
                    strpos($trip->status, 'canceled') === false;
            });

            $paymentDataBulk = [];
            if (count($trips)) {
                foreach ($trips as $trip) {


                    $alreadyPaid = $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                    if ($alreadyPaid < $trip->TotalCostDiscounted) {
                        $paying = (float)$trip->TotalCostDiscounted - $alreadyPaid;

                        $paymentDataBulk[] = [
                            'driver_id' => $trip->driver_id,
                            'trip_id' => $trip->trip_id,
                            'payment_date' => $pay_date,
                            'amount' => $paying,
                            'user_id' => 0,
                            'user_type' => 'customer',
                            'type' => 'debit',
                            'description' => 'cron:customer_pay_to_account' . $account->account_id,
                            'account_id' => $trip->account_number,
                        ];

                        $total_payments += $paying;
                    }
                }
            }
            if ($total_payments > 0) {
                $fee = number_format($total_payments * 0.0375, 2, '.', '');
                $amuntWithfee = $total_payments + $fee;

                // Add to bulk insert data
                $insertData[] = [
                    'account' => $account,
                    'tokens' => $tokens,
                    'total_payments' => $total_payments,
                    'amuntWithfee' => $amuntWithfee,
                    'payment_data_bulk' => $paymentDataBulk,
                ];


            }

        }

        // Step 2: Insert data and perform transactions
        DB::beginTransaction();
        try {
            foreach ($insertData as $data) {

                $account = $data['account'];
                $tokens = $data['tokens'];
                $total_payments = $data['total_payments'];
                $amuntWithfee = $data['amuntWithfee'];
                $paymentDataBulk = $data['payment_data_bulk'];



//                $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
//                    $token,
//                    $amuntWithfee,
//                    $account->account_id . '-amountActual' . $total_payments
//                );
                if(count($tokens) > 0) {
                    foreach ($tokens as $token) {
                        if ($token->type == 'credit') {
                            $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                                $token->cardnox_token,
                                $amuntWithfee,
                                $account->account_id . '-amountActual' . $total_payments
                            );
                            Log::info('retry token tryingg = ' . $token->cardnox_token . ' charged ' . $fillAndDeduct['status']);

                            if ($fillAndDeduct['status'] === 'approved') {
                                break;
                            }
                        }

                    }
                }else{
                    $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                        null,
                        $amuntWithfee,
                        $account->account_id . '-amountActual' . $total_payments
                    );
                }



                Log::Info('retryResp:'.json_encode($fillAndDeduct));
                $deduct_status = $fillAndDeduct['status'] === 'approved';
                $transaction_id = $deduct_status ? $fillAndDeduct['transaction_id'] : null;
                //dd($fillAndDeduct);
                // Save account payment
                $try = $accountPayment->try+1;

                if ($deduct_status) {
                        Log::info('single retry payment done');

                    // Save batch payment
                    $batchPayment = BatchPayment::create([
                        'account_id' => $account->account_id,
                        'from' => 'customer_by_admin',
                        'amount' => $total_payments,
                    ]);

                    $paymentDataSend = [
                        'batch_id' => $batchPayment->id,
                        'payments' => $paymentDataBulk,
                    ];
                    PaymentSaveService::save($paymentDataSend);
                    $accountPayment->update([
                        'amount' => $total_payments,
                        'payment_date' => $today,
                        'payment_type' => 'card',
                        'transaction_id' => $transaction_id,
                        'status' => $deduct_status ? 'paid' : 'unpaid',
                        'try' => $try,
                        'batch_id' => $batchPayment->id
                    ]);

                    LogService::saveLog([
                        'from' => 'customer',
                        'payment' => $accountPayment,
                        'cardknox_response' => $fillAndDeduct,
                        'message' => 'Account: Payment deducted by admin retry',
                    ]);


                    $emailData[] = $accountPayment;
                    CubeContact::updateCubeAccount($account->account_id,null,"active");

                }else{

                   $msg = json_encode($fillAndDeduct);
                    if($try > 3){
                        CubeContact::updateCubeAccount($account->account_id,"Account is inactive due to unpaid invoices","Inactive");
                        EmailService::AccountInActive($account);

                    }
                 return redirect()->back()->with('error', ''.$msg);

                }


            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing account payments: ' . $e->getMessage());
        }

// Step 3: Send emails
        foreach ($emailData as $accountPayment) {
            EmailService::AccountInvoice($accountPayment, $accountPayment->account);
            Log::info('single retry send email');

        }



        return redirect()->back()->with('success', 'Payment Done');



 }

    public function saveAch(){
    {
        $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([

                    "xKey" => env('CARDKNOX_XKEY'),
                    "xVersion" => "4.5.9",
                    "xSoftwareName" => env('APP_NAME'),
                    "xSoftwareVersion" => "1.0.0",
                    "xCommand"=>"check:Save",
                    "xCustom01" => "gocab-account_id",
                    "xRouting" => "021000021",
                    "xAccount" => "1234567890",
                    "xName" => "John Doe",
                    "xIP" => "1.2.3.4",
                    "xMICR" => "t021000021t 123456789o _2542",
                    "xAllowDuplicate" => "FALSE"
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);
            if ($response->xResult == "A") {
                $data = [
                    "xToken" => $response->xToken,
                    "xDate" => $response->xDate,
                    "xMaskedAccountNumber" => $response->xMaskedAccountNumber,
                   "xName" => $response->xName ?? null,

                ];


                return ['msg' => 'Success', 'status' => true, 'data' => $data];
            } else {
                return ['msg' => $response->xError, 'status' => false];
            }
    }
   }

    public function saleAch(){

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
                          "xToken": "9h5q8qqq2214h07nmph792m37q7p99n1",
                          "xKey": "' . env('CARDKNOX_XKEY') . '",
                          "xVersion": "4.5.9",
                          "xSoftwareName": "' . env('APP_NAME') . '",
                          "xSoftwareVersion": "1.0.0",
                          "xCommand": "check:Sale",
                          "xAllowDuplicate": "false",
                          "xIP": "127.0.0.1",
                          "xAmount": "9",
                          "xCustom01": "Account-0089",
                      }',

        CURLOPT_HTTPHEADER => array(
            'Content-Type: text/plain'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response);
    return $response ;

}

    public function uploadExcel(Request $request)
{
    // Validate the file
    $request->validate([
        'file' => 'required|mimes:csv,txt,xlsx,xls',
    ]);

    // Check if the file exists and is valid
    if ($request->hasFile('file') && $request->file('file')->isValid()) {
        // Get the uploaded file
        $file = $request->file('file');

        $targetPath = storage_path('app/uploads/' . $file->getClientOriginalName());


        $file->move(storage_path('app/uploads'), $file->getClientOriginalName());


        $fullFilePath = $targetPath;



        $extension = $file->getClientOriginalExtension();
        $type = \Maatwebsite\Excel\Excel::CSV;

        if (in_array($extension, ['xlsx', 'xls'])) {
            $type = \Maatwebsite\Excel\Excel::XLSX;
        }


        $data = Excel::toArray(new UsersImport, $fullFilePath, null, $type);

        $passengerPhones = collect($data[0])->pluck(0)->toArray();

        $this->account_ids($passengerPhones);

        return redirect()->back()->with('success', 'File uploaded and processed successfully.');
    } else {
        return back()->withErrors(['file' => 'File is required or invalid.']);
    }
}

    private function account_ids($passengerPhones)
{
    foreach ($passengerPhones as $passengerPhone) {

        echo "Processing phone: $passengerPhone<br>\n";


        $accountNumber = DB::table('trips')
            ->where('passenger_phone', $passengerPhone)
            ->where(function ($query) {
                $query->whereNotNull('account_number')
                      ->where('account_number', '!=', '');
            })
            ->value('account_number');


        if ($accountNumber) {
            echo "Account number found: $accountNumber for phone: $passengerPhone<br>\n";
        } else {
            echo "No valid account number found for phone: $passengerPhone<br>\n";
            continue;
        }


        $trip = DB::table('trips')
            ->where('passenger_phone', $passengerPhone)
            ->where('payment_method', 'account')
            ->where(function($query) {
                $query->where('account_number', '')
                      ->orWhereNull('account_number');
            })
            ->update(['account_number' => $accountNumber]);


        if ($trip) {
            echo "Updated trips for passenger phone: $passengerPhone with account number: $accountNumber.<br>\n";
        } else {
            echo "No trips found to update for passenger phone: $passengerPhone.<br>\n";
        }
    }

    dd("Trips updated successfully.");
}

    public function  account_complaint(Request $request,$id){
    $account_payment = AccountPayment::where('hash_id',$id)->first();
    return view('account_complaint', compact('account_payment'));
    }







}

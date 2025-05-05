<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\CreditCard;
use App\Models\Driver;
use App\Models\Account_Complaint;
use App\Services\AccountService;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
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
            
                $email_id = $request->email;
                $data = [
                    'title' => 'Welcome!',
                    'body' => 'Please click the link below to change your password, Thank you!',
                    'url' => url('/customer/change_password').'?email=' . urlencode($user->email)
                ]; 
                
                Mail::to($email_id)->send(new ResetPasswordMail($data));
               
             return redirect()->back()->with('success', 'An Email with reset password link is sent, please check you inbox!');
        }else{
            return redirect()->back()->with('error', 'This email is not found in our system, please try with regesterd email !');
        }
       
    }
    public function change_password(Request $request){
        $email = $request->query('email');
        return view('customer.change_password', compact('email'));
    }

    public function update_password(Request $request){
        $password = $request->password;
        $con_password = $request->confirm_password;
        if($password === $con_password){
            $user = Account::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();
          return redirect(url('customer/login'))->with('success', 'Password is reset, please login to your portal');
        }else{
            return redirect()->back()->with('error', 'Password is not match please try again');
        }
    }

    public function loginAttemp(Request $request){

        $user = Account::where('account_id',$request->username)->first();

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


}

<?php

namespace App\Http\Controllers;

use App\Models\TripEditHistory;
use App\Models\User;
use App\Models\Adjustment;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Services\TokenService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function PHPUnit\Framework\stringContains;
use function Symfony\Component\Mime\to;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DriverController extends Controller
{

    public function index(Request $request)
    {

        sleep(1);
        $from = Carbon::now()->toDateString();
        $to = Carbon::now()->toDateString();

        if (isset($request->driver_id)) {
            $driver_id = $request->driver_id;
        } else {
            $driver_id = Auth::guard('driver')->user()->driver_id;

        }



        if ($request->ajax()) {
        //todo account na ho
        $tripsidnotinc = Trip::where('driver_id', $driver_id)->where(function ($query) {
            $query->where('status', 'like', '%Cancelled%')
                  ->orWhere('status', 'like', '%canceled%');
        })->where('payment_method', 'account')->pluck('id');

        $query = Trip::where('driver_id', $driver_id)->where('is_delete', 0);
//        $query = Trip::where('driver_id',$driver_id)->whereNotIn('id',$tripsidnotinc);

        $apply = true;

        if ($request->has('from_date') && $request->has('to_date')) {

            if ($request->from_date != '' && $request->to_date != '') {
                $from = $request->from_date;
                $to = $request->to_date;

            } else {

                $apply = false;
            }
        }

        if ($apply == true) {
            $query->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->orderBy('date', 'desc')->orderBy('time', 'desc');
        } else {

            $query->whereDate('date', '>=', '2024-09-15')->orderBy('date', 'desc')->orderBy('time', 'desc');
        }
        $trips = $query->get();
        $total_trip = count($trips);

//        $total_earnings = collect($trips)->sum('trip_cost');

//        $cash_rec = collect($trips)->where('payment_method', 'cash')->sum('trip_cost');

        $driver = Driver::where('driver_id',$driver_id)->first();
        $balance = $driver->balance_details($from,$to,$apply);

//        $balance_debit = 0;
//        $total_recived = (float)$balance_debit + (float)$cash_rec;

//        if ($total_recived < 0) {
//            $total_recived = 0;
//        }

        if (isset($request->driver_id)) {

        } else {
            if ($balance < 0) {
                $balance = 0;
            }

        }


        //  last week
        $fromlastweek = Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of last week (Sunday)
        $tolastweek = Carbon::now()->subWeek()->endOfWeek(Carbon::SATURDAY)->toDateString();

        $lastweekbalance = Trip::where('driver_id', $driver_id)->where('is_delete',0)
            ->whereBetween('date', [$fromlastweek, $tolastweek])
            ->whereIn('payment_method', ['account', 'card'])
            ->whereNotIn('id',$tripsidnotinc)
            ->get(['trip_id', 'trip_cost']); // Get only trip_id and trip_cost
           
//        $totaltriplastWeek = count($lastweekbalance);
        $totalCost = $lastweekbalance->sum('trip_cost'); // Sum the trip costs
        $tripIdss = $lastweekbalance->pluck('trip_id')->toArray(); // Get trip_id array

//        $lastweekbalacne = $totalCost;

        $last_week_adjust_debit = Adjustment::where('driver_id', $driver_id)->where('type','admin_paid_auto')->whereIn('trip_id',$tripIdss)->sum('amount');

        $paid_last_week = Payment::where('is_delete',0)->whereIn('trip_id',$tripIdss)->where('type','debit')->where('user_type','admin')->where('driver_id',$driver_id)->sum('amount');
        $paid_last_week = $paid_last_week + $last_week_adjust_debit;

        $unpaid_last_week = $totalCost - $paid_last_week;
        $unpaid_last_week2 = $driver->balance_details($fromlastweek,$tolastweek,true);


        //  current week
        $fromcurrentweek = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of current week (Sunday)
        $tocurrentweek = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();


       $currentweekbalacne = Trip::where('driver_id', $driver_id)->where('is_delete',0)
            ->where('date','>=',$fromcurrentweek)
            ->where('date','<=',$tocurrentweek)
           ->whereNotIn('id',$tripsidnotinc)
            ->whereIn('payment_method',['account','card'])
            ->get(['trip_id', 'trip_cost']); // Get only trip_id and trip_cost


//        $totaltripCurrentWeek = count($currentweekbalacne);
        $totalCostCurrent = $currentweekbalacne->sum('trip_cost'); // Sum the trip costs
        $tripIdssCurent = $currentweekbalacne->pluck('trip_id')->toArray(); // Get trip_id array

        $Currentweekbalacne = $totalCostCurrent;

        $paid_current_week = Payment::where('is_delete',0)->whereIn('trip_id',$tripIdssCurent)->where('type','debit')->where('user_type','admin')->where('driver_id',$driver_id)->sum('amount');

        $current_week_adjust_debit = Adjustment::where('driver_id',$driver_id)->where('type','admin_paid_auto')->whereIn('trip_id',$tripIdssCurent)->whereBetween('date', [$fromcurrentweek, $tocurrentweek])->sum('amount');
        $paid_current_week = $paid_current_week + $current_week_adjust_debit;
        $unpaid_current_week = $Currentweekbalacne - $paid_current_week;

        $unpaid_current_week2 = $driver->balance_details($fromcurrentweek,$tocurrentweek,true);


        $edit_history_increase = TripEditHistory::where('driver_id',$driver_id)->where('date','>=',$fromcurrentweek)
            ->where('date','<=',$tocurrentweek)->where('type','credit')->sum('amount');

        $edit_history_decrease = TripEditHistory::where('driver_id',$driver_id)->where('date','>=',$fromcurrentweek)
                ->where('date','<=',$tocurrentweek)->where('type','debit')->sum('amount');


        $deduction = $edit_history_increase - $edit_history_decrease;
            $gocab_total = $driver->balance();
            return response()->json([
                'trips' => $trips,
                'total_trip' => $total_trip,
                'total_earnings' => 0,
                'gocab_paid' => $balance,
                'gocab_total' => $gocab_total,

                'total_last_week_trip' => 0,
                'lastw' => $unpaid_last_week,
                'paidlastweek' => 0,
                'unpaidlastweek' => 0,

                'total_Current_week_trip' => 0,
                'paidCurrentweek' => 0,
                'currentw' =>  $unpaid_current_week,
                'unpaidCurrentweek' => 0,

//                'total_recived' => $total_recived,
                'total_recived' => 0,
                'description' => '',
                'deductions'=>$deduction
            ]);
        }
//        $gocab_paid = 0;
//        $gocab_paid = $balance;

//        $gocab_total = Driver::where('driver_id', $driver_id)->first()->balance();


        return view('driver.index');
    }


    public function login()
    {
        if (Auth::guard('driver')->check()) {
            return view('driver.index');
        }
        return view('driver.login');
    }

    public function driver($id)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.taxicaller.net/api/v1/company/57068/user/ids/'.$id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . TokenService::token()
            ),
        ));
        $originalObject = curl_exec($curl);
        curl_close($curl);
        dd($originalObject);

    }
    public function taxidrivers(){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');


        $token = TokenService::token();
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
            CURLOPT_POSTFIELDS =>'{"template_id":12531,"company_id":57068,"output_format":"json","report_type":"USER","report_id":null,"search_query":{"filters":{},"results":{}}}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.TokenService::token(),
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        $drivers = $response->rows;
        return $drivers;

    }


    public function sendOtp(Request $request)
    {
        $username = $request->username;
        $user_phone = $request->phone;

        $driver = Driver::where('username', $username)->first();
        if ($driver) {
            $user_phone = $driver->phone;
        }else{
            return redirect()->back()->with('error', "Account Invalid/Inactive.");

        }

        if($driver->status == 0){

            return redirect()->back()->with('error', "Account Is Inactive.");

        }

        if ($user_phone != '') {
            if (!Str::startsWith($user_phone, '+1')) {
                $user_phone = '+1' . $user_phone;
            }
            
            $verify = $sendotp = TwilioService::sendotp($user_phone, $username);
            $response = $verify->getData();
            
            if ($response->success == true) {
                $user_phone = $driver->phone;
                session(['user_phone' => $user_phone , 'username' => $username]);
                return redirect()->route('send-otp-form');
            } else {

                return redirect()->back()->with('error', $response->errormessage);

            }

        } else {
            return redirect()->back()->with('error', "Invalid username or phone number.");
        }


    }

    public function showOtpForm()
    {
        $user_phone = session('user_phone');
        $username = session('username');

        if (!$user_phone) {
            return redirect()->route('login')->with('error', 'Please enter your username first.');
        }

        return view('driver.code', compact('user_phone', 'username'));
    }

    public function verifyOtp(Request $request)
    {

        $user_phone = $request->phone;
        if (!Str::startsWith($user_phone, '+1')) {
            $user_phone = '+1' . $user_phone;
        }
        $otp = $request->otp;
        $verify = $sendotp = TwilioService::verifyOtp($user_phone, $otp);
        $response = $verify->getData();

        if ($response->success == true) {
            $user_phone = $request->phone;
            $driver = Driver::where('phone', $user_phone)->where('username',$request->username)->first();

            if ($driver) {
                // Log in the driver using the custom guard
                Auth::guard('driver')->login($driver, true);

                // Verify if the driver is authenticated
                if (Auth::guard('driver')->check()) {
                    return redirect()->route('driver.dashboard');
                }

                return redirect()->back();
            }

            return redirect()->back();
        } else if ($response->success == false) {

            return redirect()->route('send-otp-form')->with('error', 'OTP is not matched');

        }

    }

    public function logout()
    {

        Auth::guard('driver')->logout();
        return redirect()->to('/');


    }

    public function addDispatchersToUsers()
    {

        $commonPassword = 'password123';

     $dispatchers = Driver::where('role','LIKE', '%"DISPATCHER"%' )->get();

        foreach ($dispatchers as $dispatcher) {

            $existingUser = User::where('username', $dispatcher->username)->first();

            if (!$existingUser) {

                User::create([
                    'name' => $dispatcher->first_name. ' ' .$dispatcher->first_name,
                     'username' => $dispatcher->username,
                    'password' => Hash::make($commonPassword),
                    'role' => 'dispatcher',
                ]);
            }
        }

        return response()->json(['message' => 'Dispatchers have been added to the users table successfully.']);
    }




}

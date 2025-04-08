<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Adjustment;
use App\Models\BatchPayment;
use App\Models\Driver;
use App\Models\DriverFee;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\TripEditHistory;
use App\Models\User;
use App\Services\CustomPagination;
use App\Services\LogService;
use App\Services\TaxiCallerApi;
use App\Services\TwilioService;
use App\Models\Log;
use App\Utils\dateUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DriversEarningsExport;

class AdminController extends Controller
{


    public function login(Request $request)
    {

        return view('admin.login');

    }

    public function attemptLogin(Request $request)
    {

        $admin = User::where('username', $request->username)->first();

        if ($admin) {

            if (Hash::check($request->password, $admin->password)) {
                Auth::guard('admin')->login($admin);


                if (Auth::guard('admin')->check()) {

                    if (Auth::guard('admin')->user()->role == 'admin') {
                        return redirect()->route('admin.dashboard');
                    } else {
                        return redirect()->route('admin.dataTabletrips', ['tab' => 'all']);

                    }
                }

                return redirect()->back()->with('error', 'Failed to login.');
            } else {
                return redirect()->back()->with('error', 'Invalid Username/Password');
            }
        } else {

            return redirect()->back()->with('error', 'Invalid Username');
        }

    }


    public function logout(Request $request)
    {


        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');

    }

    public function dispatchers()
    {
        $dispatchers = User::where('role', 'dispatcher')->get();
        return view('admin.dispatchers', compact('dispatchers'));
    }

    public function index(Request $request)
    {

        if (Auth::guard('admin')->user()->role != 'admin') {

            return redirect()->route('admin.trips');

        }

        $fromlastweek = Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of last week (Sunday)
        $tolastweek = Carbon::now()->subWeek()->endOfWeek(Carbon::SATURDAY)->toDateString();

        //        $total_payments = Payment::where('is_delete',0)->where('type', 'credit')
//            ->where('payment_date','>=',$fromlastweek)->where('payment_date','<=',$tolastweek)
//            ->sum('amount');
//
//        $total_recieved = Payment::where('is_delete',0)->where('type', 'debit')->where('user_type','admin')
//            ->where('payment_date','>=',$fromlastweek)->where('payment_date','<=',$tolastweek)
//            ->sum('amount');
//
//        $adjust = Adjustment::where('type','debit_driver_balance')
//            ->where('date','>=',$fromlastweek)->where('date','<=',$tolastweek)
//            ->sum('amount');

        // $lastWeekOwed = $total_payments - $total_recieved;
        // $lastWeekOwed = $lastWeekOwed - $adjust;

        $lastWeekTrips = Trip::where('is_delete', 0)
            ->where('date', '>=', $fromlastweek)
            ->where('date', '<=', $tolastweek)
            ->get(['trip_id', 'trip_cost']);


        // trip cost + extra in trips where payment method is not cash
        $lastWeekcost = Trip::where('is_delete', 0)
            ->where('date', '>=', $fromlastweek)
            ->where('date', '<=', $tolastweek)
            ->where('payment_method', '!=', 'cash')
            ->sum('trip_cost');

        $lastWeekextra = Trip::where('is_delete', 0)
            ->where('date', '>=', $fromlastweek)
            ->where('date', '<=', $tolastweek)
            ->where('payment_method', '!=', 'cash')
            ->sum('extra_charges');

        // trips unpaid

        //    $tripsUnpaid = Trip::whereBetween('trips.date', [$fromlastweek, $tolastweek])
//    ->where('trips.payment_method', '!=', 'cash')
//   ->sum('trip_cost');

        $tripsidnotinc = Trip::where(function ($query) {
            $query->where('status', 'like', '%Cancelled%')
                ->orWhere('status', 'like', '%canceled%');
        })->where('payment_method', 'account')->pluck('id');
        $tripsUnpaid = Trip::where('is_delete', 0)
            ->whereBetween('date', [$fromlastweek, $tolastweek])
            ->whereIn('payment_method', ['account', 'card'])
            ->whereNotIn('id', $tripsidnotinc)
            ->get(['trip_id', 'trip_cost']); // Get only trip_id and trip_cost

        $totalCost = $tripsUnpaid->sum('trip_cost'); // Sum the trip costs
        $tripIdss = $tripsUnpaid->pluck('trip_id')->toArray(); // Get trip_id array

        $last_week_adjust_debit = Adjustment::where('type', 'admin_paid_auto')->whereIn('trip_id', $tripIdss)->sum('amount');
        $paid_last_week = Payment::where('is_delete', 0)->whereIn('trip_id', $tripIdss)->where('type', 'debit')->where('user_type', 'admin')->sum('amount');
        $credit_last_week = Payment::where('is_delete', 0)->whereIn('trip_id', $tripIdss)->where('type', 'credit')->where('user_type', 'driver')->sum('amount');
        $tripsCount = $credit_last_week - ($paid_last_week + $last_week_adjust_debit);



        // trips unpaid



        $lastWeekRearn = $lastWeekTrips->sum('trip_cost');
        $lastWeekTrips = count($lastWeekTrips);
        $lastWeekOwed =  $lastWeekcost;
//        $lastWeekRearn = $lastWeekRearn;

        //  current week
        $fromcurrentweek = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of current week (Sunday)
        $tocurrentweek = Carbon::now()->toDateString();


        $currentWeekTrips = Trip::where('is_delete', 0)
            ->where('date', '>=', $fromcurrentweek)
            ->where('date', '<=', $tocurrentweek)
            ->get(['trip_id', 'trip_cost']);

        $currentWeekRearn = $currentWeekTrips->sum('trip_cost');
        $currentWeekTrips = count($currentWeekTrips);

        //        $Currenttotal_payments = Payment::where('is_delete',0)->where('type', 'credit')
//            ->where('payment_date','>=',$fromcurrentweek)->where('payment_date','<=',$tocurrentweek)
//            ->sum('amount');
//
//        $Currenttotal_recieved = Payment::where('is_delete',0)->where('type', 'debit')->where('user_type','admin')
//            ->where('payment_date','>=',$fromcurrentweek)->where('payment_date','<=',$tocurrentweek)
//            ->sum('amount');
//
//        $Currentadjust = Adjustment::where('type','debit_driver_balance')
//            ->where('date','>=',$fromcurrentweek)->where('date','<=',$tocurrentweek)
//            ->sum('amount');

        // $CurrentWeekOwed = $Currenttotal_payments - $Currenttotal_recieved;
        // $CurrentWeekOwed = $CurrentWeekOwed - $Currentadjust;
        $CurrentWeekcost = Trip::where('is_delete', 0)
            ->where('date', '>=', $fromcurrentweek)
            ->where('date', '<=', $tocurrentweek)
            ->where('payment_method', '!=', 'cash')
            ->sum('trip_cost');
        $CurrentWeekextra = Trip::where('is_delete', 0)
            ->where('date', '>=', $fromcurrentweek)
            ->where('date', '<=', $tocurrentweek)
            ->where('payment_method', '!=', 'cash')
            ->sum('extra_charges');





        $CurrentWeekOwed =  $CurrentWeekcost;
        $currentWeekRearn = $currentWeekRearn;
        $data['current_trips'] = $currentWeekTrips;
        $data['current_earning'] = $currentWeekRearn;
        $data['current_owed_driver'] = $CurrentWeekOwed;
        $data['current_weekly'] = 0;
        $data['current_start'] = $fromcurrentweek;
        $data['current_end'] = $tocurrentweek;

        $data['last_trips'] = $lastWeekTrips;
        $data['last_earning'] = $lastWeekRearn;
        $data['last_owed_driver'] = $lastWeekOwed;
        $data['last_weekly'] = 0;
        $data['last_start'] = $fromlastweek;
        $data['last_end'] = $tolastweek;
        $data['tripsCount'] = $tripsCount;

//        dd($lastWeekRearn);
        $util = new dateUtil();
        return view('admin.index', compact('data', 'util'));

    }

    public function inactivedrivers(Request $request)
    {
        if ($request->ajax()) {
            $data = Driver::where('role', 'NOT LIKE', '%"DISPATCHER"%')
                ->where('status', 0);
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<a href="' . url('admin/driver') . '/' . $row->id . '" class="btn btn-primary">View</a>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 1 || $row->status === null) {
                        return 'Active';
                    } else {
                        return 'Inactive';
                    }
                })
                ->editColumn('beta', function ($row) {
                    $isChecked = ($row->beta == 1) ? 'checked' : '';
                    return '
                            <label class="switch">
                            <input  type="checkbox" data-id="' . $row->id . '" ' . $isChecked . '>
                            <span class="switch-state"></span>
                            </label>
                        ';
                })

                ->addColumn('balance', function ($row) {
                    return $row->balance();
                })

                ->rawColumns(['action', 'beta'])
                ->make(true);
        }
        return view('admin.inactive-drivers');


    }
    public function updateBeta(Request $request)
    {

        $record = Driver::find($request->id);

        if (empty($record->cube_id)) {
            return response()->json(['success' => false, 'msg' => 'Contact is not available in cube.']);

        }
        $record->beta = $request->beta;
        $record->save();

        return response()->json(['success' => true, 'msg' => 'Updated Successfully']);
    }

    public function drivers(Request $request)
    {

        if ($request->ajax()) {


            if (isset($request->show_negative)) {
                $data = Driver::where('role', 'LIKE', '%"DRIVER"%');
                $data = $data->filter(function ($driver) {
                    return $driver->balance() < 0;
                });
            } else {
                $data = Driver::where('role', 'LIKE', '%"DRIVER"%')->where('status','=',1);
            }
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<a href="' . url('admin/driver') . '/' . $row->id . '" class="btn btn-primary">View</a>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 1 || $row->status === null) {
                        return 'Active';
                    } else {
                        return 'Inactive';
                    }
                })
                ->editColumn('beta', function ($row) {
                    $isChecked = ($row->beta == 1) ? 'checked' : '';
                    return '
                        <label class="switch">
                            <input  type="checkbox" data-id="' . $row->id . '" ' . $isChecked . '>
                            <span class="switch-state"></span>
                            </label>';
                })


                ->addColumn('balance', function ($row) {
                    return $row->balance();
                })->addColumn('last_trip_date', function ($row) {
                    $lastTrip = $row->trips()->latest()->first();
                    return $lastTrip
                        ? Carbon::parse($lastTrip->created_at)
                            ->setTimezone('America/New_York')
                            ->format('m-d-y')
                        : 'N/A';
                })

                ->rawColumns(['action', 'beta', 'last_trip_date'])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)
                        ->setTimezone('America/New_York')
                        ->format('m-d-y');
                })
                ->make();
        }
         $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY); // Sunday 00:00 AM
         $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);

         $driversWithoutTrips = Driver::where('status',1)->where('role','LIKE','%DRIVER%')
         ->whereDoesntHave('trips', function ($query) use ($startOfWeek, $endOfWeek) {
             $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
         })
         ->count();

        $this_week_drivers = Driver::where('status',1)->where('role','LIKE','%DRIVER%')->whereDate('created_at','>=',$startOfWeek)->whereDate('created_at','<=',$endOfWeek)->count();

//        $total_drivers = Driver::where('role', 'NOT LIKE', '%"DISPATCHER"%')->count();
//        $inactive_drivers = Driver::where('role', 'NOT LIKE', '%"DISPATCHER"%')->where('status', 0)->count();
        $active_drivers = Driver::where('role', 'LIKE', '%"DRIVER"%')->where('status', 1)->count();
        // dd("Active: ".$active_drivers."inactive: ". $inactive_drivers . "total :" .$total_drivers);
        return view('admin.drivers', compact('this_week_drivers', 'driversWithoutTrips', 'active_drivers'));

    }

    public function driver($id, Request $request)
    {

        $data = Driver::find($id);

        $util = new \App\Utils\dateUtil();

        if (request()->ajax()) {

            if ($request->tab == 'from') {
                $payments = Payment::where('is_delete', 0)->where('user_type', 'admin')->where('driver_id', $data->driver_id)->where('type', 'credit')->get();
                return view('admin.driver.payments_trips', compact('payments', 'util'));

            } elseif ($request->tab == 'to') {
                $to = Payment::where('is_delete', 0)->where('user_type', 'admin')->where('driver_id', $data->driver_id)->where('type', 'debit')->get();

            } elseif ($request->tab == 'from_customer') {
                $payments = Payment::where('is_delete', 0)->where('user_type', 'driver')->where('driver_id', $data->driver_id)->where('type', 'credit')->get();
                return view('admin.driver.from_customer', compact('payments', 'util'));

            } elseif ($request->tab == 'weekly') {
                $payments = Payment::where('is_delete', 0)->where('type', 'debit')->where('user_type', 'driver')->where('driver_id', $data->driver_id)->get();
                return view('admin.driver.weekly', compact('payments', 'util'));

            } elseif ($request->tab == 'batch') {



                $batchs = BatchPayment::where('driver_id', $data->driver_id)->orderByDesc('created_at');

                return Datatables::of($batchs)
                    ->addColumn('total_cost', function ($row) {
                        return number_format($row->trip_cost, 2, '.', ',');
                    })
                    ->editColumn('created_at', function ($row) use ($util) {
                        return date_time_formate($row->created_at);
                    })
                    ->make();
                //                return view('admin.driver.batch_trips',compact('batchs','util'));



            }
        }

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY); // Sunday 00:00 AM
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);

        $latestTrip = $data->trips()->latest('created_at')->first();
        $historyOfThisWeek = TripEditHistory::where('driver_id',$data->driver_id)->where('date','>=',$startOfWeek)->where('date','<=',$endOfWeek)->get();

        return view('admin.single-driver', compact('util', 'data', 'latestTrip','historyOfThisWeek'));

    }
    public function driver_inactive($id)
    {

        $driver = Driver::find($id);

        $username = TaxiCallerApi::ActiveDeactiveDriver($driver, false);

        $driver->username = $username;
        $driver->status = 0;
        $driver->save();
        return redirect()->back()->with('success', 'Driver is Inactive Successfully');
    }
    public function driver_active($id)
    {


        $driver = Driver::find($id);

        $username = TaxiCallerApi::ActiveDeactiveDriver($driver, true);
        $driver->username = $username;
        $driver->status = 1;
        $driver->save();

        return redirect()->back()->with('success', 'Driver is Active Successfully');


    }

    public function changeDriverFee(Request $request)
    {


        $data = DriverFee::where('driver_id', $request->driver_id)->first();
        $data->fee = $request->fee;
        $data->save();

        return redirect()->back();
    }

    public function changeDriverplate(Request $request)
    {


        $data = Driver::where('driver_id', $request->driver_id)->first();
        $data->plate = $request->plate;
        $data->save();

        return redirect()->back();
    }


    public function payments(Request $request)
    {

        $path = '/admin/payments';
        $payments = Payment::where('is_delete', 0)->get();
        $from = Payment::where('is_delete', 0)->where('user_type', 'admin')->where('type', 'credit')->get();
        $to = Payment::where('is_delete', 0)->where('user_type', 'admin')->where('type', 'debit')->get();
        $weekly = Payment::where('is_delete', 0)->where('user_type', 'driver')->whereNull('trip_id')->where('type', 'debit')->get();


        $payments = CustomPagination::pagination($payments, 30, $path);
        $from = CustomPagination::pagination($from, 30, $path);
        $to = CustomPagination::pagination($to, 30, $path);
        $weekly = CustomPagination::pagination($weekly, 30, $path);

        return view('admin.payment', compact('payments', 'from', 'to', 'weekly'));

    }

    public function paymentFromDriver(Request $request)
    {


        $new = new Payment();
        $new->driver_id = $request->driver_id;
        $new->payment_date = now()->toDateString();
        $new->amount = (float) $request->from_amount;
        $new->user_id = Auth::guard('admin')->user()->id;
        $new->user_type = 'admin';
        $new->type = 'credit';
        $new->save();

        //todo activate again if balance 0 or above call api
//        $driver = Driver::where('driver_id', $request->driver_id)->first();

        //        if ($driver->balance() >= 0) {
//            $driver->status = 1;
//            $driver->save();
//        }
        return redirect()->back();

    }

    public function paymentToDriver(Request $request)
    {


        //        $balance = Driver::where('driver_id',$request->driver_id)->first()->balance();
//        if($request->to_amount > $balance){
//
//            return redirect()->back()->with('error', 'Balance Low Than Paying.');
//
//        }
        $submit_amount = (float) $request->to_amount;
        $total_amount = 0;
        //        $tripsidnotinc = Trip::where('driver_id',$request->driver_id)->where(function ($query) {
//            $query->where('status', 'like', '%Cancelled%')
//                  ->orWhere('status', 'like', '%canceled%');
//        })->where('payment_method', 'account')->pluck('id');

        $trips = Trip::leftJoin('payments', 'trips.trip_id', '=', 'payments.trip_id')->where('trips.is_delete', 0)->where('trips.driver_id', $request->driver_id);
        if (isset($request->trip)) {

            $trips = $trips->whereIn('trips.trip_id', $request->trip);
            $batch = new BatchPayment();
            $batch->from = 'admin';
            $batch->amount = 0;
            $batch->driver_id = $request->driver_id;
            $batch->save();
        }

        $trips = $trips->where('trips.payment_method', '!=', 'cash');

        if (isset($request->priority)) {

            if ($request->priority == 'last_week') {

                $fromlastweek = Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of last week (Sunday)
                $tolastweek = Carbon::now()->subWeek()->endOfWeek(Carbon::SATURDAY)->toDateString();
                $trips->where('trips.date', '>=', $fromlastweek)->where('trips.date', '<=', $tolastweek);

            } elseif ($request->priority == 'current_week') {

                $fromcurrentweek = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();  // Start of current week (Sunday)
                $tocurrentweek = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                $trips->where('trips.date', '>=', $fromcurrentweek)->where('trips.date', '<=', $tocurrentweek);

            } else {
                $trips->where('trips.date', '>', '2024-09-14');
            }
        } else {
            $trips->where('trips.date', '>', '2024-09-14');
        }

        //            ->where('trips.status', 'NOT LIKE', '%Cancelled%')
//            ->where('trips.status','NOT LIKE', '%canceled%')
//            ->whereNotIn('trips.id',$tripsidnotinc)
        $trips = $trips->select(
            'trips.location_from',
            'trips.location_to',
            'trips.driver_id',
            'trips.trip_id',
            'trips.date',
            'trips.time',
            'trips.trip_cost',
            'trips.status',
            \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'credit' AND payments.user_type = 'driver' THEN payments.amount ELSE 0 END), 0) as total_paid_from_customer"),
            \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid"),
            \DB::raw("(SELECT COALESCE(SUM(CASE WHEN adjustments.type = 'admin_paid_auto' AND adjustments.trip_id = trips.trip_id AND adjustments.driver_id = trips.driver_id THEN adjustments.amount ELSE 0 END), 0)
               FROM adjustments
               WHERE adjustments.trip_id = trips.trip_id
                 AND adjustments.driver_id = trips.driver_id
                 AND adjustments.type = 'admin_paid_auto') as total_paid_adjust")
        )
            ->groupBy('trips.driver_id', 'trips.location_from', 'trips.location_to', 'trips.trip_id', 'trips.date', 'trips.time', 'trips.trip_cost')
            ->havingRaw('total_paid_adjust + total_paid < total_paid_from_customer')
            ->orderBy('trips.date', 'desc')
            ->orderBy('trips.time', 'desc')
            ->get();

        $trips = $trips->filter(function ($trip) {
            return strpos($trip->status, 'Cancelled') === false &&
                strpos($trip->status, 'canceled') === false;
        });

        if (isset($request->trip)) {


            foreach ($request->trip as $key => $trip) {


                $new = new Payment();
                $new->driver_id = $request->driver_id;
                $new->trip_id = $trip;
                $new->payment_date = now()->toDateString();
                $new->amount = (float) $request->amount[$key];
                $new->user_id = Auth::guard('admin')->user()->id;
                $new->user_type = 'admin';
                $new->type = 'debit';
                $new->batch_id = $batch->id;
                $new->description = '24-9';
                $new->save();

                $total_amount = $total_amount + $new->amount;

            }
        }


        if (isset($request->trip)) {

            $batch->amount = $total_amount;
            $batch->save();
            $id = Driver::where('driver_id', $request->driver_id)->first()->id;
            return view('admin.success', compact('id'));

        } else {

            return view('admin.paytodriver', compact('trips', 'submit_amount'));
        }

    }

    // function for weekly fee from balance manually

    public function payWeeklyFromBalance(Request $request)
    {

        $driver = Driver::where('driver_id', $request->driver_id)->first();

        $weeklybalance = $driver->weeklyFeeBalance();

        if ($weeklybalance > 0) {


            $tripsidnotinc = Trip::where('driver_id', $driver->driver_id)->where(function ($query) {
                $query->where('status', 'like', '%Cancelled%')
                    ->orWhere('status', 'like', '%canceled%');
            })->where('payment_method', 'account')->pluck('id');

            $trips = Trip::leftJoin('payments', 'trips.trip_id', '=', 'payments.trip_id')
                ->where('trips.driver_id', $driver->driver_id)
                ->where('trips.date', '>', '2024-09-14')->where('trips.payment_method', '!=', 'cash')->where('trips.is_auto_paid_as_adjustment', 0)
                ->whereNotIn('trips.id', $tripsidnotinc)->where('trips.is_delete', 0)
                ->select(
                    'trips.driver_id',
                    'trips.trip_id',
                    'trips.date',
                    'trips.time',
                    'trips.trip_cost',
                    \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'credit' AND payments.user_type = 'driver' THEN payments.amount ELSE 0 END), 0) as total_paid_from_customer"),
                    \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid"),
                    \DB::raw("(SELECT COALESCE(SUM(CASE WHEN adjustments.type = 'admin_paid_auto' AND adjustments.trip_id = trips.trip_id AND adjustments.driver_id = trips.driver_id THEN adjustments.amount ELSE 0 END), 0)
                      FROM adjustments
                     WHERE adjustments.trip_id = trips.trip_id
                   AND adjustments.driver_id = trips.driver_id
                 AND adjustments.type = 'admin_paid_auto') as total_paid_adjust")
                )
                ->groupBy('trips.driver_id', 'trips.trip_id', 'trips.date', 'trips.time', 'trips.trip_cost')
                ->havingRaw('total_paid_adjust + total_paid < total_paid_from_customer')
                ->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc')
                ->get();

            $balance = $driver->balance();



            if ($balance > $weeklybalance) {

                $split_total = $weeklybalance;
            } else {

                $split_total = $balance;
            }

            $new = new Payment();
            $new->driver_id = $driver->driver_id;
            $new->trip_id = null;
            $new->payment_date = now()->toDateString();
            $new->amount = (float) $driver->weeklyFeeBalance();
            $new->user_id = $driver->driver_id;
            $new->user_type = 'driver';
            $new->type = 'debit';
            $new->description = 'weekly_fee_paid_from_balance';
            $new->save();


            $parent_weekly_id = $new->id;
            foreach ($trips as $tr) {

                $left = $tr->total_paid_from_customer - ($tr->total_paid_adjust + $tr->total_paid);

                $paying = 0;
                if ($left > ($tr->total_paid_adjust + $tr->total_paid)) {

                    if ($split_total <= 0) {
                        $paying = 0;
                    } elseif ($split_total >= $left) {

                        $split_total = $split_total - $left;
                        $paying = $left;

                    } elseif ($split_total < $left && $split_total > 0) {
                        $paying = $split_total;

                        $split_total = $split_total - $paying;
                    }
                }

                if ($paying > 0) {

                    $new = new Adjustment();
                    $new->driver_id = $driver->driver_id;
                    $new->trip_id = $tr->trip_id;
                    $new->date = now()->toDateString();
                    $new->is_weekly = 1;
                    $new->weekly_payment_id = $parent_weekly_id;
                    $new->amount = $paying;
                    $new->type = 'admin_paid_auto';
                    $new->reason = 'Auto Adjustment From Weekly Added When balance was ' . $balance;
                    $new->save();

                    Trip::where('trip_id', $tr->trip_id)->update(['is_auto_paid_as_adjustment' => 1]);
                }

            }





        }

        return redirect()->back()->with('success', 'Weekly Fee is Paid From Balance');

    }


    // function for weekly fee from balance manually

    public function adjustments()
    {

        $data = Adjustment::all();
        return view('admin.adjustments', compact('data'));


    }


    public function adjustment(Request $request)
    {

        $trip = Trip::where('trip_id', $request->trip_id)->first();
        if ($trip) {

            $new = new Adjustment();
            $new->driver_id = $trip->driver_id;
            $new->trip_id = $request->trip_id;
            $new->date = now()->toDateString();
            $new->amount = $request->amount;
            $new->reason = $request->reason;
            $new->save();
        }
        return redirect()->back();

    }


    public function dataTabletrips(Request $request)
    {


        $util = new dateUtil();

        if ($request->ajax()) {

            // Eager load payments to calculate total_paid
            $trips = Trip::with(['payments'])
                ->where('trips.is_delete', 0)
                ->where(function ($query) {
                    $query->where('trips.payment_method', '!=', 'card')
                        ->orWhere('trips.trip_cost', '>', 0);
                })
                ->select(
                    'trips.extra_charges',
                    'trips.extra_stop_amount',
                    'trips.extra_wait_amount',
                    'trips.location_from',
                    'trips.location_to',
                    'trips.account_number',
                    'trips.payment_method',
                    'trips.driver_id',
                    'trips.trip_id',
                    'trips.passenger_phone',
                    'trips.date',
                    'trips.trip_cost',
                    'trips.time',
                    'trips.reason',
                    'trips.is_complaint',
                    'trips.complaint',
                    'trips.is_auto_paid_as_adjustment',
                    'trips.status',
                    'trips.accepted_by',
                    'trips.cube_pin',
                    'trips.cube_pin_status'
                );

            // Apply date filter
            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {
                    $trips = $trips->whereDate('trips.date', '>=', $request->from_date)
                        ->whereDate('trips.date', '<=', $request->to_date);
                }
            }

            // Filter by payment method
            if (isset($request->account)) {
                if ($request->account != '') {
                    $trips = $trips->where('trips.account_number', $request->account);
                }
            }


            if (isset($request->type)) {
                if ($request->type != '') {
                    $trips = $trips->where('trips.payment_method', $request->type);
                }
            }

            // Filter by driver
            if (isset($request->driver)) {
                if ($request->driver != '') {
                    $trips = $trips->where('trips.driver_id', $request->driver);
                }
            }

            // Handling paid and half-paid tabs
            if ($request->tab == 'paid') {
                //                $trips = $trips->havingRaw('total_paid >= trips.trip_cost AND trips.trip_cost > 0 OR trips.is_auto_paid_as_adjustment = 1');
            }
            if ($request->tab == 'half') {
                //                $trips = $trips->havingRaw('total_paid < trips.trip_cost AND total_paid > 0');
            }

            // Group by trip_id and paginate
            $trips = $trips->groupBy('trips.trip_id')
                ->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc');  // Pagination for faster loading

            return Datatables::of($trips)
                ->addColumn('total_cost', function ($row) {
                    return number_format($row->trip_cost, 2, '.', ',');
                })
                ->addColumn('cost', function ($row) {


                    $html = number_format($row->trip_cost - $row->extra_charges, 2, '.', ',');
                    if (Auth::guard('admin')->user()->role == 'admin') {
                        if($row->payment_method != "cash"){
                        $html .= '<button class="btn" onclick="edit_trip_prices(this)" data-type="cost" data-trip_id="' . $row->trip_id . '" data-bs-toggle="modal" data-original-title="test"
                        data-bs-target="#extraModaledit"  ><i class="fa fa-pencil"></i></button>';
                    }
                }

                    return $html;

                })
                ->addColumn('paid', function ($row) {
                    $paid = 0;
                    if ($row->is_auto_paid_as_adjustment == 1) {
                        //                        $paid .= '<p class="text-success">PAID AUTO <a href="' . url('admin/adjustments') . '" target="_blank">view</a></p>';
                    }
                    $paid = number_format($row->payments->where('user_admin', 'admin')->sum('amount'), 2, '.', ',');

                    foreach ($row->payments as $pp) {
                        //                         $paid = $paid + number_format($pp->amount, 2, '.', ',');

                    }

                    return $paid;
                })
                ->editColumn('payment_method', function ($row) {
                    $return = $row->payment_method;
                    if ($row->payment_method == 'cash') {
                        $return .= '<a target="_blank"
                            href="' . url('admin/trip/pay') . '/' . $row->trip_id . '"
                            class="btn-sm btn-primary w-100">Accept Customer Payment</a>';
                    } else {
                        $return .= 'Acc #:' . $row->account_number;
                        $return .= '<button class="btn" onclick="show_extra_model(this)"
                        data-type="account"
                        data-trip_id="' . $row->trip_id . '"
                        data-bs-toggle="modal"
                        data-original-title="test"
                        data-bs-target="#extraModaledit"
                        data-modelcontent="' . htmlspecialchars('
                        <div class=\'modal-content\'>
                           <div class=\'modal-header\'>
                               <h5 class=\'modal-title\' id=\'exampleModalLabel\'>Update Account and Payment Method</h5>
                               <button class=\'btn-close\' type=\'button\' data-bs-dismiss=\'modal\' aria-label=\'Close\'></button>
                           </div>
                           <form method=\'post\' class=\'account_update_form\' data-trip-id=\'' . $row->trip_id . '\'>
                               <div class=\'modal-body\'>
                                   ' . csrf_field() . '
                                   <input hidden class=\'form-control mb-3\' value=\'' . $row->trip_id . '\' name=\'trip_id\'/>

                                   <label for=\'\'>Please Enter Account Number</label>
                                   <input type=\'number\' name=\'account\' required
                                       value=\'' . $row->account_number . '\'
                                       class=\'form-control mb-3\'
                                       placeholder=\'Please Add Account\' />

                                   <label for=\'\'>Please Select Payment Method</label>
                                   <select name=\'payment_method\' class=\'form-select\'>
                                       <option value=\'' . $row->payment_method . '\' selected>' . $row->payment_method . '</option>
                                       <option value=\'account\'>Account</option>
                                       <option value=\'cash\'>Cash</option>
                                       <option value=\'card\'>Card</option>
                                   </select>

                                   <label for=\'\'>Please Enter Reason</label>
                                   <textarea name=\'reason\' id=\'\' class=\'form-control\' required placeholder=\'Enter Here\'>' . $row->reason . '</textarea>
                               </div>
                               <div class=\'modal-footer\'>
                                   <button class=\'btn btn-dark\' type=\'button\' data-bs-dismiss=\'modal\'>Close</button>
                                   <button class=\'btn btn-primary account_form_submit_btn\' type=\'button\' data-trip-id=\'' . $row->trip_id . '\'>Save</button>
                               </div>
                           </form>
                       </div>', ENT_QUOTES, 'UTF-8') . '">
                            <i class="fa fa-pencil"></i>
                        </button>';

                    }

                    return $return;
                })
                ->editColumn('extra_charges', function ($row) {
//                    return number_format($row->extra_charges, 2, '.', ',');

                    $html = number_format($row->extra_charges, 2, '.', ',');
                    if($row->payment_method != "cash"){
                    $html .= '<button class="btn" onclick="edit_trip_prices(this)" data-type="extra" data-trip_id="' . $row->trip_id . '" data-bs-toggle="modal" data-original-title="test"
                    data-bs-target="#extraModaledit"  ><i class="fa fa-pencil"></i></button>';
                    }
                    return $html;
                })
                ->editColumn('driver_id', function ($row) {
                    $driver = Driver::where('driver_id', $row->driver_id)->first();
                    if($driver) {
                        $driverLink = '<a href="' . url('admin/driver/' . $driver->id) . '" target="_blank">'
                            . $row->driver_id . '</a>';
                        return $driverLink;
                    }else{
                        return 'Not Found';
                    }
                })
                ->addColumn('extra_description', function ($row) {

                    return $row->ExtraDescription;
                })
                ->editColumn('date', function ($row) use ($util) {
                    // Format date using utility class
                    return $util->format_date($row->date);
                })
                ->editColumn('time', function ($row) use ($util) {
                    // Format time using utility class
                    return $util->time_format($row->time);
                })
                ->editColumn('cube_pin_status', function ($row) use ($util) {
                    // Format time using utility class
                    return $row->cube_pin.' '.$row->cube_pin_status;
                })
                ->rawColumns(['paid', 'payment_method', 'cost','extra_charges','driver_id'])
                ->make();
        }

        $drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();
        $accounts = Account::where('is_deleted', 0)->get();
        if ($request->tab == 'all') {
            return view('admin.trips.trips', compact('drivers', 'accounts'));
        }
        if ($request->tab == 'paid') {

            return view('admin.trips.paids', compact('drivers', 'accounts'));
        }
        if ($request->tab == 'half') {
            return view('admin.trips.half', compact('drivers', 'accounts'));
        }


    }

    public function dataTabletripsExtra(Request $request)
    {


        $util = new dateUtil();

        if ($request->ajax()) {


            $trips = Trip::where(function ($query) {
                $query->where('trips.payment_method', '!=', 'card')
                    ->orWhere('trips.trip_cost', '>', 0); // Include trips with card only if trip_cost > 0
            })->select(
                    'trips.extra_charges',
                    'trips.extra_stop_amount',
                    'trips.extra_wait_amount',
                    'trips.location_from',
                    'trips.location_to',
                    'trips.account_number',
                    'trips.payment_method',
                    'trips.driver_id',
                    'trips.trip_id',
                    'trips.passenger_phone',
                    'trips.date',
                    'trips.trip_cost',
                    'trips.time',
                    'trips.reason',
                    'trips.is_complaint',
                    'trips.complaint',
                    'trips.cube_pin',
                    'trips.cube_pin_status',
                    'trips.stop_location',
                    'trips.extra_round_trip',
                    'trips.is_auto_paid_as_adjustment'
                    // \DB::raw("COALESCE(SUM(CASE WHEN payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid")
                )->where('trips.extra_charges', '>', 0);



            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {

                    $trips = $trips->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);

                }
            }


            if (isset($request->type)) {
                if ($request->type != '') {

                    $trips = $trips->where('trips.payment_method', $request->type);

                }
            }

            if (isset($request->driver)) {
                if ($request->driver != '') {

                    $trips = $trips->where('trips.driver_id', $request->driver);

                }
            }

            $trips = $trips->groupBy('trips.trip_id');

            $trips = $trips->orderBy('trips.date', 'desc')->orderBy('trips.time', 'desc');

            return Datatables::of($trips)

                ->addColumn('total_cost', function ($row) {
                    return number_format($row->trip_cost, 2, '.', ',');
                })
                ->addColumn('cost', function ($row) {


                    $html = number_format($row->trip_cost - $row->extra_charges, 2, '.', ',');
                    $html .= '<button class="btn" onclick="edit_trip_prices(this)" data-type="cost" data-trip_id="' . $row->trip_id . '" data-bs-toggle="modal" data-original-title="test"
                    data-bs-target="#extraModaledit"  ><i class="fa fa-pencil"></i></button>';
                    return $html;

                })
                ->addColumn('paid', function ($row) {
                    $paid = '';
                    $total_paid = $row->paidAgianstTripByAdmin();
                    if ($row->is_auto_paid_as_adjustment == 1) {
                        $paid .= '<p class="text-success">PAID AUTO <a href="' . url('admin / adjustments') . '" target="_blank">view</a></p>';
                    } else {

                        $paid .= '' . number_format($total_paid, 2, '.', ',');

                    }
                    return $paid;
                })
                ->editColumn('payment_method', function ($row) {
                    $return = $row->payment_method;
                    if ($row->payment_method == 'cash') {
                        $return .= '<a target="_blank"
                            href="' . url('admin/trip/pay') . '/' . $row->trip_id . '"
                            class="btn-sm btn-primary w-100">Accept Customer Payment</a>';
                    } else {

                        $return .= 'Acc #:' . $row->account_number;

                    }

                    return $return;
                })
                ->editColumn('extra_charges', function ($row) {

                    //                    $trip = $row;
//                    $modal = view('admin.partials.modals.edit_extra', compact('trip'));
                    $html = number_format($row->extra_charges, 2, '.', ',');
                    $html .= '<button class="btn" onclick="edit_trip_prices(this)" data-type="extra" data-trip_id="' . $row->trip_id . '" data-bs-toggle="modal" data-original-title="test"
                    data-bs-target="#extraModaledit"  ><i class="fa fa-pencil"></i></button>';
                    return $html;
                })
                ->editColumn('driver_id', function ($row) {
                    $driver = Driver::where('driver_id', $row->driver_id)->first();

                    $driverLink = '<a href="' . url('admin/driver/' . $driver->id) . '" target="_blank">'
                        . $row->driver_id . '</a>';
                    return $driverLink;
                })

                ->editColumn('date', function ($row) use ($util) {
                    return $util->format_date($row->date);
                })
                ->editColumn('time', function ($row) use ($util) {
                    return $util->time_format($row->time);
                })

                //
                ->editColumn('cube_pin_status', function ($row) use ($util) {
                    // Format time using utility class
                    return $row->cube_pin.' '.$row->cube_pin_status;
                })
                ->addColumn('actions', function ($row) {

                    $html = '<button class="btn btn-primary" onclick="edit_payments(this)" data-type="cost" data-trip_id="' . $row->trip_id . '" data-bs-toggle="modal" data-original-title="test"
                    data-bs-target="#TripPaymentsModal"  >payments</button>';
                    return $html;

                })
                ->rawColumns(['paid', 'payment_method', 'extra_charges', 'cost', 'actions', 'driver_id'])
                ->make(true);
        }

        $drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();

        return view('admin.trips.trips_with_extra', compact('drivers'));



    }


    public function dataTableManuallyTripstrips(Request $request)
    {


        $util = new dateUtil();

        if ($request->ajax()) {

            $trips = Trip::leftjoin('payments', 'trips.trip_id', '=', 'payments.trip_id')
                ->select(
                    'trips.extra_charges',
                    'trips.location_from',
                    'trips.location_to',
                    'trips.account_number',
                    'trips.payment_method',
                    'trips.driver_id',
                    'trips.trip_id',
                    'trips.passenger_phone',
                    'trips.date',
                    'trips.trip_cost',
                    'trips.time',
                    'trips.reason',
                    'trips.is_complaint',
                    'trips.complaint',
                    'trips.cube_pin',
                    'trips.cube_pin_status',
                    'trips.extra_stop_amount',
                    'trips.stop_location',
                    'trips.extra_wait_amount',
                    'trips.extra_round_trip',
                    'trips.is_auto_paid_as_adjustment',
                    \DB::raw("COALESCE(SUM(CASE WHEN payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid")
                )->where('trips.trip_id', '>', 999999999);


            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {

                    $trips = $trips->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);

                }
            }


            if (isset($request->type)) {
                if ($request->type != '') {

                    $trips = $trips->where('trips.payment_method', $request->type);

                }
            }

            if (isset($request->driver)) {
                if ($request->driver != '') {

                    $trips = $trips->where('trips.driver_id', $request->driver);

                }
            }

            $trips = $trips->groupBy('trips.trip_id');

            $trips = $trips->orderBy('trips.date', 'desc')->orderBy('trips.time', 'desc');

            return Datatables::of($trips)
                ->addColumn('total_cost', function ($row) {
                    return number_format($row->trip_cost - $row->extra_charges, 2, '.', ',');
                })
                ->addColumn('paid', function ($row) {
                    $paid = '';
                    if ($row->is_auto_paid_as_adjustment == 1) {
                        $paid .= '<p class="text-success">PAID AUTO <a href="' . url('admin / adjustments') . '" target="_blank">view</a></p>';
                    } else {

                        $paid .= '' . number_format($row->total_paid, 2, '.', ',');

                    }
                    return $paid;
                })
                ->editColumn('payment_method', function ($row) {
                    $return = $row->payment_method;
                    if ($row->payment_method == 'cash') {
                        $return .= '<a target="_blank"
                            href="' . url('admin/trip/pay') . '/' . $row->trip_id . '"
                            class="btn-sm btn-primary w-100">Accept Customer Payment</a>';
                    } else {

                        $return .= 'Acc #:' . $row->account_number;

                    }

                    return $return;
                })
                ->editColumn('extra_charges', function ($row) {
                    return number_format($row->extra_charges, 2, '.', ',');
                })
                ->editColumn('date', function ($row) use ($util) {
                    return $util->format_date($row->date);
                })
                ->editColumn('time', function ($row) use ($util) {
                    return $util->time_format($row->time);
                })
                ->editColumn('driver_id', function ($row) {
                    $driver = Driver::where('driver_id', $row->driver_id)->first();

                    $driverLink = '<a href="' . url('admin/driver/' . $driver->id) . '" target="_blank">'
                        . $row->driver_id . '</a>';
                    return $driverLink;
                })
                ->editColumn('cube_pin_status', function ($row) use ($util) {
                    // Format time using utility class
                    return $row->cube_pin.' '.$row->cube_pin_status;
                })

                //
                ->rawColumns(['paid', 'payment_method', 'driver_id'])
                ->make(true);
        }

        $drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();

        return view('admin.trips.trips_manually', compact('drivers'));


    }

    public function dataTabletripsComplaint(Request $request)
    {

        $util = new dateUtil();

        if ($request->ajax()) {

            $trips = Trip::query();


            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {

                    $trips = $trips->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);

                }
            }
            $trips = $trips->where('trips.is_complaint', '1');

            if (isset($request->type)) {
                if ($request->type != '') {

                    $trips = $trips->where('trips.payment_method', $request->type);

                }
            }

            if (isset($request->driver)) {
                if ($request->driver != '') {

                    $trips = $trips->where('trips.driver_id', $request->driver);

                }
            }

            $trips = $trips->groupBy('trips.trip_id');

            $trips = $trips->orderBy('trips.date', 'desc')->orderBy('trips.time', 'desc');

            return Datatables::of($trips)
                ->addColumn('total_cost', function ($row) {
                    return number_format($row->trip_cost - $row->extra_charges, 2, '.', ',');
                })
                ->addColumn('paid', function ($row) {
                    $paid = '';
                    $total_paid = $row->paidAgianstTripByAdmin();
                    if ($row->is_auto_paid_as_adjustment == 1) {
                        $paid .= '<p class="text-success">PAID AUTO <a href="' . url('admin / adjustments') . '" target="_blank">view</a></p>';
                    } else {

                        $paid .= '' . number_format($total_paid, 2, '.', ',');

                    }
                    return $paid;
                })
                ->editColumn('payment_method', function ($row) {
                    $return = $row->payment_method;
                    if ($row->payment_method == 'cash') {
                        $return .= '<a target="_blank"
                            href="' . url('admin/trip/pay') . '/' . $row->trip_id . '"
                            class="btn-sm btn-primary w-100">Accept Customer Payment</a>';
                    } else {

                        $return .= 'Acc #:' . $row->account_number;

                    }

                    return $return;
                })
                ->editColumn('extra_charges', function ($row) {
                    return number_format($row->extra_charges, 2, '.', ',');
                })
                ->editColumn('driver_id', function ($row) {
                    $driver = Driver::where('driver_id', $row->driver_id)->first();

                    $driverLink = '<a href="' . url('admin/driver/' . $driver->id) . '" target="_blank">'
                        . $row->driver_id . '</a>';
                    return $driverLink;
                })
                ->editColumn('date', function ($row) use ($util) {
                    return $util->format_date($row->date);
                })
                ->editColumn('time', function ($row) use ($util) {
                    return $util->time_format($row->time);
                })

                ->rawColumns(['paid', 'payment_method', 'driver_id'])
                ->make(true);
        }

        $drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();

        return view('admin.trips.trips_with_complaint', compact('drivers'));



    }

    public function tripswithoutestimatedcost(Request $request)
    {
        $util = new dateUtil();

        if ($request->ajax()) {

            $trips = Trip::with(['payments'])
                ->where('trips.is_delete', 0)
                ->where('trips.estimated_cost', 0)
                ->where(function ($query) {
                    $query->where('trips.status', 'NOT LIKE', '%Cancelled%')
                        ->orWhere('trips.status', 'NOT LIKE', '%Client canceled%');
                })
                ->whereIn('trips.payment_method', ['card', 'account'])
                ->select(
                    'trips.extra_charges',
                    'trips.extra_stop_amount',
                    'trips.extra_wait_amount',
                    'trips.location_from',
                    'trips.location_to',
                    'trips.account_number',
                    'trips.payment_method',
                    'trips.driver_id',
                    'trips.trip_id',
                    'trips.passenger_phone',
                    'trips.date',
                    'trips.trip_cost',
                    'trips.time',
                    'trips.reason',
                    'trips.is_complaint',
                    'trips.complaint',
                    'trips.cube_pin',
                    'trips.cube_pin_status',
                    'trips.is_auto_paid_as_adjustment'
                );

            // Apply date filter from request
            if (isset($request->from_date) && isset($request->to_date)) {
                if ($request->from_date != '' && $request->to_date != '') {
                    $trips = $trips->whereDate('trips.date', '>=', $request->from_date)
                        ->whereDate('trips.date', '<=', $request->to_date);
                }
            }

            // Filter by account number
            if (isset($request->account) && $request->account != '') {
                $trips = $trips->where('trips.account_number', $request->account);
            }

            // Filter by payment type
            if (isset($request->type) && $request->type != '') {
                $trips = $trips->where('trips.payment_method', $request->type);
            }

            // Filter by driver ID
            if (isset($request->driver) && $request->driver != '') {
                $trips = $trips->where('trips.driver_id', $request->driver);
            }

            // Handling paid and half-paid tabs
            if ($request->tab == 'paid') {
                // Uncomment and adjust your logic here for "paid" tab filter
                // $trips = $trips->havingRaw('total_paid >= trips.trip_cost AND trips.trip_cost > 0 OR trips.is_auto_paid_as_adjustment = 1');
            }
            if ($request->tab == 'half') {
                // Uncomment and adjust your logic here for "half-paid" tab filter
                // $trips = $trips->havingRaw('total_paid < trips.trip_cost AND total_paid > 0');
            }

            // Group by trip_id and order by date and time
            $trips = $trips->groupBy('trips.trip_id')
                ->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc');

            return Datatables::of($trips)
                ->addColumn('total_cost', function ($row) {
                    return number_format($row->trip_cost + $row->extra_charges, 2, '.', ',');
                })
                ->addColumn('paid', function ($row) {
                    $paid = $row->payments->where('user_admin', 'admin')->sum('amount');
                    return number_format($paid, 2, '.', ',');
                })
                ->editColumn('payment_method', function ($row) {
                    $return = $row->payment_method;
                    if ($row->payment_method == 'cash') {
                        $return .= '<a target="_blank"
                        href="' . url('admin/trip/pay') . '/' . $row->trip_id . '"
                        class="btn-sm btn-primary w-100">Accept Customer Payment</a>';
                    } else {
                        $return .= 'Acc #:' . $row->account_number;
                    }
                    return $return;
                })
                ->editColumn('extra_charges', function ($row) {
                    return number_format($row->extra_charges, 2, '.', ',');
                })
                ->editColumn('driver_id', function ($row) {
                    $driver = Driver::where('driver_id', $row->driver_id)->first();

                    $driverLink = '<a href="' . url('admin/driver/' . $driver->id) . '" target="_blank">'
                        . $row->driver_id . '</a>';
                    return $driverLink;
                })
                ->addColumn('extra_description', function ($row) {
                    $extraStop = number_format($row->extra_stop_amount, 2, '.', ',');
                    $extraWait = number_format($row->extra_wait_amount, 2, '.', ',');
                    return 'Stop = $' . $extraStop . ', Wait = $' . $extraWait;
                })
                ->editColumn('date', function ($row) use ($util) {
                    return $util->format_date($row->date);
                })
                ->editColumn('time', function ($row) use ($util) {
                    return $util->time_format($row->time);
                })
                ->editColumn('cube_pin_status', function ($row) use ($util) {
                    // Format time using utility class
                    return $row->cube_pin.' '.$row->cube_pin_status;
                })
                ->rawColumns(['paid', 'payment_method', 'driver_id'])
                ->make();
        }

        // Fetch drivers and accounts
        $drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();
        $accounts = Account::where('is_deleted', 0)->get();

        // Render views based on tab

        return view('admin.trips.trips_without_estimated_cost', compact('drivers', 'accounts'));


    }


    public function trips(Request $request)
    {


        $path = '/admin/trips';
        $trips = Trip::leftjoin('payments', 'trips.trip_id', '=', 'payments.trip_id')->where('trips.is_delete', 0)
            ->select(
                'trips.extra_charges',
                'trips.location_from',
                'trips.location_to',
                'trips.account_number',
                'trips.payment_method',
                'trips.driver_id',
                'trips.trip_id',
                'trips.date',
                'trips.trip_cost',
                'trips.time',
                'trips.reason',
                'trips.is_auto_paid_as_adjustment',
                \DB::raw("COALESCE(SUM(CASE WHEN payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid")
            );


        $paid = Trip::leftjoin('payments', 'trips.trip_id', '=', 'payments.trip_id')->where('trips.is_delete', 0)
            ->where('trips.payment_method', '!=', 'cash')
            ->select(
                'trips.extra_charges',
                'trips.location_from',
                'trips.location_to',
                'trips.account_number',
                'trips.payment_method',
                'trips.driver_id',
                'trips.trip_id',
                'trips.date',
                'trips.trip_cost',
                'trips.time',
                'trips.reason',
                'trips.is_auto_paid_as_adjustment',
                \DB::raw("COALESCE(SUM(CASE WHEN payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid")

            );
        //
        $half = Trip::leftjoin('payments', 'trips.trip_id', '=', 'payments.trip_id')->where('trips.is_delete', 0)
            ->where('trips.payment_method', '!=', 'cash')
            ->select(
                'trips.extra_charges',
                'trips.location_from',
                'trips.location_to',
                'trips.account_number',
                'trips.payment_method',
                'trips.driver_id',
                'trips.trip_id',
                'trips.date',
                'trips.trip_cost',
                'trips.time',
                'trips.reason',
                'trips.is_auto_paid_as_adjustment',
                \DB::raw("COALESCE(SUM(CASE WHEN payments.type = 'debit' AND payments.user_type = 'admin' THEN payments.amount ELSE 0 END), 0) as total_paid")

            );


        if ($request->query('search', '') != '') {
            $searchTerm = $request->query('search');
            $trips = $trips->where(function ($query) use ($searchTerm) {
                return $query->where('trips.location_from', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.location_to', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.account_number', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.trip_cost', 'LIKE', '%' . $searchTerm . '%');
            });
            $half = $half->where(function ($query) use ($searchTerm) {
                return $query->where('trips.location_from', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.location_to', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.account_number', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.trip_cost', 'LIKE', '%' . $searchTerm . '%');
            });
            $half = $half->where(function ($query) use ($searchTerm) {
                return $query->where('trips.location_from', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.location_to', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.account_number', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('trips.trip_cost', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        if (isset($request->from_date) && isset($request->to_date)) {
            if ($request->from_date != '' && $request->to_date != '') {

                $trips = $trips->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);
                $half = $half->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);
                $paid = $paid->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);

            }
        }

        if (isset($request->type)) {
            if ($request->type != '') {

                $trips = $trips->where('trips.payment_method', $request->type);
                $half = $half->where('trips.payment_method', $request->type);
                $paid = $paid->where('trips.payment_method', $request->type);

            }
        }

        if (isset($request->driver)) {
            if ($request->driver != '') {

                $trips = $trips->where('trips.driver_id', $request->driver);
                $half = $half->where('trips.driver_id', $request->driver);
                $paid = $paid->where('trips.driver_id', $request->driver);

            }
        }


        if (isset($request->tab)) {
            $tab = $request->tab;
        } else {
            $tab = 'all';
        }


        if ($tab == 'all') {


            $trips = $trips->groupBy(
                'trips.trip_id',
                'trips.extra_charges',
                'trips.date',
                'trips.trip_cost',
                'trips.location_from',
                'trips.location_to',
                'trips.account_number',
                'trips.payment_method',
                'trips.driver_id',
                'trips.time',
                'trips.reason',
                'trips.is_auto_paid_as_adjustment'

            )->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc')
                ->get();
            $trips = CustomPagination::pagination($trips, 10, $path);

        } else {
            $trips = CustomPagination::pagination([], 20, $path);

        }


        if ($tab == 'paid') {

            $paid = $paid->groupBy(
                'trips.trip_id',
                'trips.extra_charges',
                'trips.date',
                'trips.trip_cost',
                'trips.location_from',
                'trips.location_to',
                'trips.account_number',
                'trips.payment_method',
                'trips.driver_id',
                'trips.time',
                'trips.is_auto_paid_as_adjustment',
                'trips.reason'
            )->havingRaw('total_paid >= trips.trip_cost AND trips.trip_cost > 0 OR trips.is_auto_paid_as_adjustment = 1')
                ->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc')
                ->get();

            $paid = CustomPagination::pagination($paid, 20, $path);
        } else {
            $paid = CustomPagination::pagination([], 20, $path);

        }


        if ($tab == 'half') {
            $half = $half->groupBy(
                'trips.trip_id',
                'trips.extra_charges',
                'trips.date',
                'trips.trip_cost',
                'trips.location_from',
                'trips.location_to',
                'trips.account_number',
                'trips.payment_method',
                'trips.driver_id',
                'trips.time',
                'trips.reason',
                'trips.is_auto_paid_as_adjustment'
            )->havingRaw('total_paid < trips.trip_cost AND total_paid > 0')
                ->orderBy('trips.date', 'desc')
                ->orderBy('trips.time', 'desc')
                ->get();
            $half = CustomPagination::pagination($half, 20, $path);
        } else {
            $half = CustomPagination::pagination([], 20, $path);

        }


        $drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();

        return view('admin.trips', compact('trips', 'paid', 'half', 'drivers'));

    }


    public function payingTripAccountMethod($id)
    {
        $amount = 0;
        $msg = '';
        $trips = Trip::where('trip_id', $id)->where('trips.is_delete', 0)->get();
        $tripSum = Trip::where('trip_id', $id)->where('trips.is_delete', 0)->sum('trip_cost');
        if (count($trips) > 0) {
            $sum = Payment::where('is_delete', 0)->where('trip_id', $id)->where('type', 'credit')->sum('amount');
            if ($sum > $tripSum) {
                $msg = 'Already Paid ' . $sum;
            }
            $amount = $tripSum - $sum;
            $drver_mainid = $trips[0]->driver->id;

            return view('admin.paytrips', compact('trips', 'msg', 'amount', 'drver_mainid'));
        }
    }



    public function logs()
    {

        $logs = Log::query()->first();


    }

    public function delete_weekfee(Request $request)
    {


        $id = $request->id;
        $fee = Payment::find($id);
        $fee->delete();

        $adjustment = Adjustment::where('weekly_payment_id', $id)->delete();
        return redirect()->back();
    }

    public function dublicateDrivers(Request $request)
    {
        $duplicateDrivers = Driver::whereIn('username', function ($query) {
            $query->select('username')
                ->from('drivers')
                ->groupBy('username')
                ->havingRaw('COUNT(*) > 1');
        })->get(['id', 'username', 'phone'])->toArray();


        dd($duplicateDrivers);

    }

    public function export()
    {
        return Excel::download(new DriversEarningsExport, 'drivers_earnings_last_week.xlsx');
    }

}

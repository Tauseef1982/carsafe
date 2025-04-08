<?php

namespace App\Services;
use App\Models\Account;
use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;


class AccountService
{


    public static function AccountSummary($request)
    {


            $account = Account::where('account_id',$request->account_id)->first();
            $totalTripsQuery = DB::table('trips')->where('payment_method', 'account')
                ->where('account_number', $request->account_id)
                ->where('is_delete', 0);

            // todo from 6th
            // if (isset($request->from_date)) {
            //         $totalTripsQuery->whereDate('date', '>=', $request->from_date);
            // }
            if (!isset($request->from_date) || empty($request->from_date)) {
                $totalTripsQuery->whereDate('date', '>', '2024-09-05');
            } else {
                $totalTripsQuery->whereDate('date', '>=', $request->from_date);
            }
            if (isset($request->to_date)) {
                $totalTripsQuery->whereDate('date', '<=', $request->to_date);
            }

            $totalTrips = $totalTripsQuery->count();
            $total_cost = $totalTripsQuery->sum('trip_cost');
            $discount_amount = $totalTripsQuery->sum('discount_amount');
            $total_cost = $total_cost - $discount_amount;
            $totalPaymentsQuery = DB::table('payments')
                ->join('trips', 'payments.trip_id', '=', 'trips.trip_id')
                ->where('trips.account_number', $request->account_id)
                ->where('payments.is_delete', 0)
                ->where('payments.type', 'debit')
                ->where('payments.user_type', 'customer');


            if (!isset($request->from_date) || empty($request->from_date)) {
                $totalPaymentsQuery->whereDate('payments.payment_date', '>', '2024-09-05');
            } else {
                $totalPaymentsQuery->whereDate('payments.payment_date', '>=', $request->from_date);
            }
            if (isset($request->to_date)) {
                $totalPaymentsQuery->whereDate('payments.payment_date', '<=', $request->to_date);
            }

            $totalPayments = $totalPaymentsQuery->sum('payments.amount');
            // dd($total_cost);
            $balance = $total_cost - $totalPayments;
            if($account->account_type == 'prepaid'){
                $balance = $account->balance;
            }
            return [
                'total_trips' => $totalTrips,
                'total_payments' => number_format($balance,2),
            ];

    }

    public static function GetTrips($request){

        $trips = Trip::leftJoin('payments', 'trips.trip_id', '=', 'payments.trip_id')
            ->where('trips.is_delete', 0);
        if (isset($request->account_id)) {
            if ($request->account_id != '') {

                $trips = $trips->where('trips.account_number',$request->account_id);

            }
        }
        // if ($request->type != 'all' && $request->type != 'extacost') {
        $trips = $trips->where('trips.payment_method', '=', 'account');
        // }
        // dd($trips->get());
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
            'trips.discount_amount',
            'trips.cube_pin',
            'trips.cube_pin_status',
            'trips.accepted_by',
            'trips.is_auto_paid_as_adjustment',
            \DB::raw("COALESCE(SUM(CASE WHEN payments.is_delete = 0 AND payments.type = 'debit' AND payments.user_type = 'customer' THEN payments.amount ELSE 0 END), 0) as total_paid")
        );


        if (!isset($request->from_date) || empty($request->from_date)) {

            $trips = $trips->whereDate('trips.date', '>', '2024-09-05');
        }
        if (isset($request->from_date) && isset($request->to_date) && !empty($request->from_date) && !empty($request->to_date)) {
            if ($request->from_date != '' && $request->to_date != '') {

                $trips = $trips->whereDate('trips.date', '>=', $request->from_date)->whereDate('trips.date', '<=', $request->to_date);

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
            'trips.discount_amount',
            'trips.cube_pin',
            'trips.cube_pin_status',
            'trips.accepted_by',
            'trips.is_auto_paid_as_adjustment'

        );

        if ($request->type == 'partial') {
            $trips = $trips->havingRaw('total_paid < trips.trip_cost');

        }
        if ($request->type == 'paid') {
            $trips = $trips->havingRaw('total_paid >= trips.trip_cost AND trips.trip_cost > 0');

        }

        if ($request->type == 'extacost') {
            $trips = $trips->where('trips.extra_charges', '>', 0);

        }

        $trips = $trips = $trips->orderBy('trips.date', 'desc')
            ->orderBy('trips.time', 'desc')
            ->get();

        return ['trips'=>$trips];
    }



}

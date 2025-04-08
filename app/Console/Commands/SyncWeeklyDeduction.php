<?php

namespace App\Console\Commands;

use App\Models\Adjustment;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWeeklyDeduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weeklyfees:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $Drivers = Driver::where('role', 'like', '%"DRIVER"%')->get();
        DB::beginTransaction();
        // Log::info(count($trips));


        foreach ($Drivers as $driver) {

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
                    ->orderBy('trips.date', 'asc')
                    ->orderBy('trips.time', 'asc')
                    ->get();

                $balance = $driver->balance();

                // balance greater than week total deduct
                // if balance less than week only balance

                if ($balance > $weeklybalance) {

                    $split_total = $weeklybalance;
                } else {

                    $split_total = $balance;
                }

                $new = new Payment();
                $new->driver_id = $driver->driver_id;
                $new->trip_id = null;
                $new->payment_date = now()->toDateString();
                $new->amount = (float)$driver->weeklyFeeBalance();
                $new->user_id = $driver->driver_id;
                $new->user_type = 'driver';
                $new->type = 'debit';
                $new->description = 'cron_weekly_debit';
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

                        Trip::where('trip_id',$tr->trip_id)->update(['is_auto_paid_as_adjustment'=>1]);
                    }

                }





            }


//                //todo call api
////                $driver->status = 0;
////                $driver->save();


        }

        DB::commit();
        Log::info('Weekly Executed');
    }

}




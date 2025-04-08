<?php

namespace App\Console\Commands;


use App\Models\Trip;
use App\Services\CubeContact;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateHoldTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:updateholdtoneutral';

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


        //  $current_server_time = Carbon::now()->subMinutes(3)->toDateTimeString();
       
        // $driverIds = Trip::where('updated_at',  $current_server_time)
        //     ->whereNotNull('temp_data')
        //     ->pluck('driver_id')
        //     ->unique();

        // Log::info($current_server_time);

        $start_time = Carbon::now()->subMinutes(3)->toDateTimeString(); // 4 minutes ago
$end_time = Carbon::now()->subMinutes(2)->toDateTimeString();   // 3 minutes ago

// Query trips updated within the 3-4 minute range
$trips = Trip::whereBetween('updated_at', [$start_time, $end_time])
    ->whereNotNull('temp_data')
    ->get(); // Get all columns of the matching trips

// If you only need the `driver_id`, you can pluck them
$driverIds = $trips->pluck('driver_id')->unique();
        Log::info($driverIds);
        $count = 0;
        foreach($driverIds as $driverId) {

            CubeContact::updateDriver($driverId);
            // Trip::where('driver_id',$driverId)->update(['temp_data'=>null]);
            $count++;
        }

        Log::info('DriverCubeToNeutral-'.$count);
    }

}



//            location_from / route.pick_up_text
//            location_to / route.drop_off_text
//            cost /fx.grand_total
//            date  / start
//            time / start

//            payment_method / cash
//            driver_id /i
//            account_number / account.name
//            strip_id / mmmmm

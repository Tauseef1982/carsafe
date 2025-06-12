<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:sync';

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
            CURLOPT_POSTFIELDS => '{
                    "company_id": 48647,
                    "report_type": "jobs",
                    "output_format": "json",
                    "template_id": 14122,
                    "search_query": {"period":{"@type":"relative","unit":"day","offset":0,"count":1},
                    "results":{"offset":0,"limit":10000}}
                    }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . TokenService::token(),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $response = json_decode($response);
        $trips = $response->rows;


        foreach ($trips as $trip) {
          // dd($trip);

            // Check if 'start' is valid
            if ($trip->{'start'} != '-' && $trip->{'start'} != '') {
                if (isset($trip->{'driverId'})) {
                    if ($trip->{'driverId'} != '') {


                        $dateTime = Carbon::createFromFormat('m/d/Y h:i A', $trip->{'start'});
                        $date = $dateTime->format('Y-m-d'); // e.g., '2024-09-01'
                        $time = $dateTime->format('H:i:s'); // e.g., '12:25:00'

                        $existingTrip = Trip::where('trip_id', (int)$trip->{'id'})->first();

                        if($trip->{'stops'} != '' && $trip->{'stops'} != null){
                            $to_location = $trip->{'stops'};

                        }else{
                            $to_location = $trip->{'route.drop_off_text'};

                        }


                        //todo confirm
                        if ($existingTrip) {
                            // Check if the payment method is cash
                            if ($existingTrip->payment_method === 'cash') {
                            if ($existingTrip->temp_data == null || $existingTrip->temp_data == '') {
                                $tsDelivered = !empty($trip->{'ts.delivered'}) ? date("Y-m-d H:i:s", strtotime($trip->{'ts.delivered'})) : null;
                                $ickedup = !empty($trip->{'icked up'}) ? date("Y-m-d H:i:s", strtotime($trip->{'icked up'})) : null;
                                $existingTrip->update([
                                    'location_from' => $trip->{'route.pick_up_text'},
                                    'location_to' => $to_location,
                                    'date' => $date,
                                    'time' => $time,
                                    'trip_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                                    'driver_id' => $trip->{'driverId'},
                                    'account_number' => $trip->{'account.name'},
                                    'passenger_phone' => $trip->{'passenger.phone'},
                                    'estimated_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                                    'status' => $trip->{'job.state.status_localized'},
                                    'ts_delivered' => $tsDelivered,
                                    'icked_up' => $ickedup,
                                ]);
                            }
                            }
                        } else {
                            $ickedup = !empty($trip->{'icked up'}) ? date("Y-m-d H:i:s", strtotime($trip->{'icked up'})) : null;
                            $tsDelivered = !empty($trip->{'ts.delivered'}) ? date("Y-m-d H:i:s", strtotime($trip->{'ts.delivered'})) : null;
                            Trip::create([
                                'trip_id' => (int)$trip->{'id'},
                                'location_from' => $trip->{'route.pick_up_text'},
                                'location_to' => $to_location,
                                'date' => $date,
                                'time' => $time,
                                'trip_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                                'driver_id' => $trip->{'driverId'},
                                'account_number' => $trip->{'account.name'},
                                'passenger_phone' => $trip->{'passenger.phone'},
                                'estimated_cost' => !empty($trip->{'fx.trip_base'}) && $trip->{'fx.trip_base'} != 0 ? $trip->{'fx.trip_base'} : $trip->{'estimatedPrice'},
                                'status' => $trip->{'job.state.status_localized'},
                                'ts_delivered' => $tsDelivered,
                                'icked_up' => $ickedup,
                                'first_destination'=>$trip->{'route.drop_off_text'}
                            ]);
                        }



                    }
                }

            }
        }



        Log::info('sync-trips');
    }

}



//            location_from / route.pick_up_text
//            location_to / route.drop_off_text
//            cost /fx.grand_total
//            date  / start
//            time / start
//            gocab_paid / 0.0
//            payment_method / cash
//            driver_id /i
//            account_number / account.name
//            strip_id / mmmmm

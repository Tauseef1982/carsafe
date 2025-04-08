<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\DriverFee;
use App\Services\CubeContact;
use App\Services\TokenService;
use App\Services\TaxiCallerApi;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:sync';

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
        CURLOPT_POSTFIELDS => '{"template_id":12531,"company_id":57068,"output_format":"json","report_type":"USER","report_id":null,"search_query":{"filters":{},"results":{}}}',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($response);

    if (isset($response->rows)) {
        $drivers = $response->rows;

        DB::beginTransaction();

        try {
            foreach ($drivers as $driver) {
                $existingDriver = Driver::where('driver_id', $driver->ID)->first();

                if ($existingDriver) {
                    if($existingDriver->username == ""){

//                        $username = TaxiCallerApi::ActiveDeactiveDriver($existingDriver,false);
                        CubeContact::deleteDriver($existingDriver->driver_id);
//                        $existingDriver->username = $username;
                        $existingDriver->status = 0;
                        $existingDriver->phone = "";
                        $existingDriver->save();

                    }else{

                        $existingDriver->update([
                            'first_name' => $driver->{'irst name'} ?? '',
                            'last_name' => $driver->{'ast name'} ?? '',
                            'role' => $driver->{'oles'} ?? '',
                            'phone' => $driver->{'phoneNumber'} ?? '',
                            'username' => $driver->{'username'} ?? '',
                        ]);

                    }

                } else {
                    // Create a new driver if it doesn't exist and phone is unique

                        $newDriver = Driver::create([
                            'first_name' => $driver->{'irst name'} ?? '',
                            'last_name' => $driver->{'ast name'} ?? '',
                            'role' => $driver->{'oles'} ?? '',
                            'driver_id' => $driver->{'ID'},
                            'phone' => $driver->{'phoneNumber'} ?? '',
                            'username' => $driver->{'username'} ?? '',
                        ]);

                        if (!DriverFee::where('driver_id', $driver->{'ID'})->exists()) {
                                            $fee = new DriverFee();
                                            $fee->driver_id = $driver->{'ID'};
                                            $fee->fee = 80;
                                            $fee->save();
                                        }

                }
            }

            DB::commit();
            Log::info('Drivers synchronized successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Driver sync failed: ' . $e->getMessage());
        }
    } else {
        Log::warning('No driver data found in the API response.');
    }
}



    // public function handle()
    // {
    //     ini_set('max_execution_time', 0);
    //     ini_set('memory_limit', '512M');


    //     $token = TokenService::token();
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://api.taxicaller.net/api/v1/reports/typed/generate',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS =>'{"template_id":12531,"company_id":57068,"output_format":"json","report_type":"USER","report_id":null,"search_query":{"filters":{},"results":{}}}',
    //         CURLOPT_HTTPHEADER => array(
    //             'Authorization: Bearer '.TokenService::token(),
    //             'Content-Type: application/json'
    //         ),
    //     ));
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    //     $response = json_decode($response);
    //     $drivers = $response->rows;

    //     DB::beginTransaction();

    //     foreach ($drivers as $driver){

    //     $existingDriver = Driver::where('driver_id', $driver->ID)->first();

    //     if ($existingDriver) {
    //         // Update the existing driver
    //         $existingDriver->update([
    //             'first_name' => $driver->{'irst name'},
    //             'last_name' => $driver->{'ast name'},
    //             'role' => $driver->oles,
    //             'phone' => $driver->phoneNumber,
    //             'username' => $driver->username
    //         ]);
    //     } else {
    //         // Check if the phone number is unique before creating a new driver
    //         if (!Driver::where('phone', $driver->phoneNumber)->exists()) {
    //             // Create a new driver
    //             Driver::create([
    //                 'first_name' => $driver->{'irst name'},
    //                 'last_name' => $driver->{'ast name'},
    //                 'role' => $driver->oles,
    //                 'driver_id' => $driver->{'ID'},
    //                 'phone' => $driver->phoneNumber,
    //                 'username' => $driver->username,
    //             ]);

    //             // Create a driver fee if it doesn't exist
    //             if (!DriverFee::where('driver_id', $driver->{'ID'})->exists()) {
    //                 $fee = new DriverFee();
    //                 $fee->driver_id = $driver->{'ID'};
    //                 $fee->fee = 80;
    //                 $fee->save();
    //             }
    //         }
    //     }

    //     }

    //      DB::commit();
    //     Log::info('synced-drivers');
    // }
}

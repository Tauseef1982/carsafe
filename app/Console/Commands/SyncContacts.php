<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Models\Account;
use App\Services\TokenService;
use Illuminate\Support\Facades\Log;

class SyncContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $token = TokenService::cubeToken();

        // for driver
        $driversWithNullCubeId = Driver::where('role', 'like', '%"DRIVER"%')->where('status',1)->whereNull('cube_id')->get();
        $driversWithNotNullCubeId = Driver::where('role', 'like', '%"DRIVER"%')->whereNotNull('cube_id')->get();


        foreach ($driversWithNullCubeId as $driver) {
            $data = [
                "email" => $driver->phone . "@gocab.com",
                "first_name" => $driver->first_name,
                "last_name" => $driver->last_name,
                "phone" => $driver->phone,
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://tapcube.co/contacts',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$token,
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response, true);
            $driver->cube_id = $response[0]['id'];
            $driver->save();


        }

        // foreach ($driversWithNotNullCubeId as $driver) {

        //     $data = [
        //         "email" => $driver->phone . "@gocab.com",
        //         "first_name" => $driver->first_name,
        //         "last_name" => $driver->last_name,
        //         "phone" => $driver->phone,
        //     ];

        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => 'https://tapcube.co/contacts/'.$driver->cube_id,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'POST',
        //         CURLOPT_POSTFIELDS => json_encode($data),
        //         CURLOPT_HTTPHEADER => array(
        //             'Authorization: Bearer '.$token,
        //             'Content-Type: application/json'
        //         ),
        //     ));
        //     $response = curl_exec($curl);
        //     curl_close($curl);

        // }


        // for account

        $accountsWithNullCubeId = Account::where('status',1)->where('is_deleted',0)->whereNull('cube_id')->get();
        $accountsWithNotNullCubeId = Account::whereNotNull('cube_id')->get();

        foreach ($accountsWithNullCubeId as $account) {

            if($account->account_type == 'prepaid'){
                if($account->balance <= 0){
                    continue;
                }
            }
            $data = [
                "email" => $account->email,
                "first_name" => $account->f_name,
                "last_name" => $account->account_id.'.',
                "phone" => $account->account_id,
            ];
            $customData = [];

            if($account->pins != null) {
                $customData[] = [
                    "key" => "pins",
                    "value" => $account->pins
                ];
                $customData[] = [
                    "key" => "Account Message",
                    "value" => null
                ];
                $customData[] = [
                    "key" => "Account Status",
                    "value" => "Active"
                ];
                $data["custom_data"] = $customData;

            }

            $curl2 = curl_init();
            curl_setopt_array($curl2, array(
                CURLOPT_URL => 'https://tapcube.co/contacts',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$token,
                    'Content-Type: application/json'
                ),
            ));



            $response2 = curl_exec($curl2);
            curl_close($curl2);
            $response2 = json_decode($response2, true);
            if(isset($response2[0]['_id'])){
                $account->cube_id = $response2[0]['_id'];
                $account->save();

            }else{
                if(isset($response2[0]['id'])){
                    $account->cube_id = $response2[0]['id'];
                    $account->save();

                }
            }

        }

        // foreach ($accountsWithNotNullCubeId as $account) {

        //     $data = [
        //         "email" => $account->email,
        //         "first_name" => $account->f_name,
        //         "last_name" => $account->account_type.'.',
        //         "phone" => $account->account_id,
        //     ];

        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => 'https://tapcube.co/contacts/'.$account->cube_id,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'POST',
        //         CURLOPT_POSTFIELDS => json_encode($data),
        //         CURLOPT_HTTPHEADER => array(
        //             'Authorization: Bearer '.$token,
        //             'Content-Type: application/json'
        //         ),
        //     ));
        //     $response = curl_exec($curl);
        //     curl_close($curl);

        // }

        Log::info('synced-all-in-cube');
    }
}

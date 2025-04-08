<?php

namespace App\Services;
use App\Models\ApiToken;
use App\Services\TokenService;
use Illuminate\Support\Facades\DB;
use App\Models\Driver;
use App\Models\Account;
use Illuminate\Support\Facades\Log;


class CubeContact
{

    public static function deleteDriver($driver_id)
    {
        $token = TokenService::cubeToken();

        $driver = Driver::where('driver_id', $driver_id)->first();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tapcube.co/contacts/'.$driver->cube_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $driver->cube_id=null;
        $driver->save();

    }

    public static function deleteAccount($acccount_id)
    {
        $token = TokenService::cubeToken();

        $account = Account::where('account_id', $acccount_id)->first();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tapcube.co/contacts/'.$account->cube_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $account->cube_id=null;
        $account->save();

    }

    public static function updateDriver($driver_id, $price = null,$without_fee = null, $tripId = null, $pickup_address = null,$method = null)
    {
        $token = TokenService::cubeToken();

        $driver = Driver::where('driver_id',$driver_id)->where('status',1)->first();

        if($driver) {
            $data = [
                "email" => $driver->phone . "@gocab.com",
                "first_name" => $driver->first_name,
                "last_name" => $driver->last_name,
                "phone" => $driver->phone
            ];


            $customData = [];

            // Add price to custom_data if provided

            $customData[] = [
                "key" => "Total Price",
                "value" => $without_fee
            ];

            $customData[] = [
                "key" => "Card Price",
                "value" => $price
            ];


            // Add tripId to custom_data if provided

            $customData[] = [
                "key" => "trip_id",
                "value" => $tripId
            ];


            // Add paymentMethod to custom_data if provided

            $customData[] = [
                "key" => "Pickup Address",
                "value" => $pickup_address
            ];

            $customData[] = [
                "key" => "Method",
                "value" => $method
            ];

            $data["custom_data"] = $customData;

            $start_time = microtime(true);
//        \Log::info('Sending API request to Cube', [
//            'time' => date('Y-m-d H:i:s'),
//            'data' => $data
//        ]);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://tapcube.co/contacts/' . $driver->cube_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            $end_time = microtime(true);
            curl_close($curl);
            $elapsed_time = $end_time - $start_time;
            \Log::info('Received API response from Cube', [
                'time' => date('Y-m-d H:i:s'),
                'elapsed_time' => $elapsed_time . ' seconds',
                'response' => $response
            ]);

            if (!isset($driver->cube_id) && isset($response[0]['id'])) {
                $driver->cube_id = $response[0]['id'];
                $driver->save();
            }
            // else if(isset($response[0]['id']) && isset($driver->cube_id) && $driver->cube_id != $response[0]['id']){
            //     $old_cube_id = $driver->cube_id;
            //     $driver->cube_id = $response[0]['id'];
            //     $driver->save();
            //     //delete old_cube_id in cube contacts

            // }
        }
    }

    public static function updateDriverReturnCheck($driver_id, $price = null,$without_fee = null, $tripId = null, $pickup_address = null,$base_fare,$extra = null,$extra_description = null,$method = null)
    {

        $token = TokenService::cubeToken();

        $driver = Driver::where('driver_id',$driver_id)->where('status',1)->first();

        if($driver) {
            $data = [
                "email" => $driver->phone . "@gocab.com",
                "first_name" => $driver->first_name,
                "last_name" => $driver->last_name,
                "phone" => $driver->phone
            ];

            $customData = [];

            $customData[] = [
                "key" => "Total Price",
                "value" => $without_fee
            ];

            $customData[] = [
                "key" => "Card Price",
                "value" => $price
            ];

            $customData[] = [
                "key" => "trip_id",
                "value" => $tripId
            ];

            $customData[] = [
                "key" => "Pickup Address",
                "value" => $pickup_address
            ];

            $customData[] = [
                "key" => "Method",
                "value" => $method
            ];

            $customData[] = [
                "key" => "Base Fare",
                "value" => $base_fare
            ];

            $customData[] = [
                "key" => "Extra",
                "value" => $extra
            ];

            $customData[] = [
                "key" => "Extra Description",
                "value" => $extra_description
            ];

            $data["custom_data"] = $customData;


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://tapcube.co/contacts/' . $driver->cube_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            if (!isset($driver->cube_id) && isset($response[0]['id'])) {
                $driver->cube_id = $response[0]['id'];
                $driver->save();
            }
            $response = json_decode($response, true);

            return $response;

        }
    }

    public static function createAccount($acccount_id)
    {
        $token = TokenService::cubeToken();

        $account = Account::where('account_id', $acccount_id)->first();

        $data = [
            "email" => $account->email,
            "first_name" => $account->f_name,
            "last_name" => $account->account_id.'.',
            "phone" => $account->account_id,
        ];

        $customData[] = [
            "key" => "Account Status",
            "value" => "active"
        ];
//        }


        $data["custom_data"] = $customData;


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
        $account->cube_id = $response2[0]['id'];
        $account->save();

    }


//    public static function updateAccount($acccount_id,$price = null, $tripId = null, $pickup_address = null )
//    {
//        $token = TokenService::cubeToken();
//
//        $account = Account::where('account_id', $acccount_id)->first();
//        $customData = [];
//        $data = [
//            "email" => $account->email,
//            "first_name" => $account->f_name,
//            "last_name" => $account->account_id.'.',
//            "phone" => $account->account_id,
//        ];
//
////        if ($price !== null) {
//            $customData[] = [
//                "key" => "Total Price",
//                "value" => $price
//            ];
////        }
//
//        // Add tripId to custom_data if provided
////        if ($tripId !== null) {
//            $customData[] = [
//                "key" => "trip_id",
//                "value" => $tripId
//            ];
////        }
//
//        // Add paymentMethod to custom_data if provided
////        if ($pickup_address !== null) {
//            $customData[] = [
//                "key" => "Pickup Address",
//                "value" => $pickup_address
//            ];
////        }
//
//        // Only add custom_data if it has any entries
////        if (!empty($customData)) {
//            $data["custom_data"] = $customData;
////        }
//
//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://tapcube.co/contacts/'.$account->cube_id,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS => json_encode($data),
//            CURLOPT_HTTPHEADER => array(
//                'Authorization: Bearer '.$token,
//                'Content-Type: application/json'
//            ),
//        ));
//        $response = curl_exec($curl);
//        curl_close($curl);
//
//    }

    public static function getDriver($driver_id)
    {
        $token = TokenService::cubeToken();

        $driver = Driver::where('driver_id', $driver_id)->first();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tapcube.co/contacts/'.$driver->cube_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response,true);

    }


    public static function updateCubeAccount($acccount_id,$account_msg = null, $account_status = null,$send_error = null)
    {
        $token = TokenService::cubeToken();

        $account = Account::where('account_id',$acccount_id)->first();
        $customData = [];
        $data = [
            "email" => $account->email,
            "first_name" => $account->f_name,
            "last_name" => $account->account_id.'.',
            "phone" => $account->account_id,
        ];

//        if ($price !== null) {
        $customData[] = [
            "key" => "Account Message",
            "value" => $account_msg
        ];
//        }

        // Add tripId to custom_data if provided
//        if ($tripId !== null) {
        $customData[] = [
            "key" => "Account Status",
            "value" => $account_status
        ];
//        }

        if($account->pins != null) {
            $customData[] = [
                "key" => "pins",
                "value" => $account->pins
            ];


        }

        // Only add custom_data if it has any entries
//        if (!empty($customData)) {
        $data["custom_data"] = $customData;
//        }
        Log::info('Sending to Cube: ' . json_encode($customData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tapcube.co/contacts/'.$account->cube_id,
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

        if($send_error) {
            $response = json_decode($response);
            if (isset($response->error)) {
                return $response->error;
            }else{
                return true;
            }

        }
    }


}

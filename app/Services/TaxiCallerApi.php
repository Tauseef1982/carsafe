<?php

namespace App\Services;



class TaxiCallerApi
{


    public static function ActiveDeactiveDriver($driver,$status)
    {


        $id = $driver->driver_id;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.taxicaller.net/api/v1/company/48647/user/ids/'.$id,
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


        $modifiedObject = json_decode($originalObject);
        $username = $modifiedObject->user->username;

        if (strpos($username,'inactive') !== false) {
            if($status == true) {
                $username = str_replace('-inactive', '', $username);
                $username = str_replace('inactive', '', $username);
            }
        } else {
            if($status == false) {

                $username = str_replace('inactive', '', $username);
                $username = $username . '-inactive';
            }
        }


        if($username == "") {

            $username = $driver->username;

            if (strpos($username, 'inactive') !== false) {
                if ($status == true) {
                    $username = str_replace('-inactive', '', $username);
                    $username = str_replace('inactive', '', $username);
                }
            } else {
                if ($status == false) {

                    $username = str_replace('inactive', '', $username);
                    $username = $username . '-inactive';
                }
            }
        }

//        dd($username);

        $modifiedObject->user->username = $username;
        $modifiedObject = json_encode($modifiedObject);


        $curl2 = curl_init();
        curl_setopt_array($curl2, array(
            CURLOPT_URL => 'https://api.taxicaller.net/api/v1/company/48647/user/ids/'.$id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $modifiedObject,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.TokenService::token(),
                'Content-Type: application/json'
            ),
        ));
        $response2 = curl_exec($curl2);
        curl_close($curl2);

        return $username;
    }


}

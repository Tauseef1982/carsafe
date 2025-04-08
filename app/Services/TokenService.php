<?php

namespace App\Services;
use App\Models\ApiToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TokenService
{

    public static function token(){

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>
                'https://api.taxicaller.net/AdminService/v1/jwt/for-key?key=0369dc59c73522891e50facc5bc72850&sub=*&ttl=900',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);


        $token = ApiToken::query()->first();
        if($token){

            $token->token = $response->token;
            $token->save();
        }else {
            $token = new ApiToken();
            $token->name = 'Api';
            $token->token = $response->token;
            $token->save();
        }

        return $token->token;
    }


    public static function cubeToken(){

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tapcube.co/login/app',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"email": "meilech@wiederand.co","password" : "123456aA"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

//        $token = ApiToken::where('name','CubeApi')->first();
//        if($token){
//
//            $token->token = $response->token;
//            $token->save();
//        }else {
//            $token = new ApiToken();
//            $token->name = 'CubeApi';
//            $token->token = $response->token;
//            $token->save();
//        }
//        Log::Info('TokenCube:'.$response->token);
        return $response->token;
    }
}

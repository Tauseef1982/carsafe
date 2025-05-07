<?php

namespace App\Services;

use App\Models\CreditCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardKnoxService
{
    public static function saveCard($id, $type, $card_number, $card_expiry, $card_zip)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "xCardNum" => $card_number,
                    "xExp" => $card_expiry,
                    "xKey" => env('CARDKNOX_XKEY'),
                    "xVersion" => "4.5.9",
                    "xSoftwareName" => env('APP_NAME'),
                    "xSoftwareVersion" => "1.0.0",
                    "xCommand" => "cc:save",
                    "xCustom01" => "carsafe-{$id}",
                    "xZip" => $card_zip,
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);

            if ($response->xResult == "A") {
                $data = [
                    "xToken" => $response->xToken,
                    "xExp" => $response->xExp,
                    "xDate" => $response->xDate,
                    "xMaskedCardNumber" => $response->xMaskedCardNumber,
                    "xCardType" => $response->xCardType ?? null,
                    "xName" => $response->xName ?? null,
                    "expiration_at" => $response->xDate ? date("Y-m-d H:i:s", strtotime($response->xDate)) : null,
                ];


                return ['msg' => 'Success', 'status' => true, 'data' => $data];
            } else {

                return ['msg' => $response->xError, 'status' => false];
            }
        } catch (\Exception $e) {
            return ['msg' => $e->getMessage(), 'status' => false];
        }
    }

    public static function saveAch($id, $name, $account_number, $routing_number)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([

                    "xKey" => env('CARDKNOX_XKEY'),
                    "xVersion" => "4.5.9",
                    "xSoftwareName" => env('APP_NAME'),
                    "xSoftwareVersion" => "1.0.0",
                    "xCommand"=>"check:Save",
                    "xCustom01" => "carsafe-{$id}",
                    "xRouting" => $routing_number,
                    "xAccount" => $account_number,
                    "xName" => $name,
                    "xIP" => "108.61.94.102",
                    "xMICR" => "t021000021t 123456789o _2542",
                    "xAllowDuplicate" => "FALSE"
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);

            if ($response->xResult == "A") {
                $data = [
                    "xToken" => $response->xToken,
                    "xDate" => $response->xDate,
                    "xMaskedAccountNumber" => $response->xMaskedAccountNumber,
                   "xName" => $response->xName ?? null,
                ];


                return ['msg' => 'Success', 'status' => true, 'data' => $data];
            } else {
                return ['msg' => $response->xError, 'status' => false];
            }
        } catch (\Exception $e) {
            return ['msg' => $e->getMessage(), 'status' => false];
        }
    }

    // Other methods remain unchanged...


    public static function processCardknoxPaymentRefill($cardknoxToken, $amount, $accountId)
    {


        Log::info('amount-'.$amount.'-'.$accountId);

        if(empty($cardknoxToken) || $cardknoxToken == null){

            return [
                'status' => 'declined',
                'message' => 'Token Not Found',
                'error_code' => 404,
            ];
        }
        $xAllowDuplicate =  false;
        if(config('app.CARDKNOX_ENV_LIVE') == false){
               $amount = 10;
               $cardknoxToken = '94352hph47hm7p855m8107q6310h215m';
               $xAllowDuplicate = true;
        }
       
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                              "xToken": "' .$cardknoxToken. '",
                              "xKey": "' . env('CARDKNOX_XKEY') . '",
                              "xVersion": "4.5.9",
                              "xSoftwareName": "' . env('APP_NAME') . '",
                              "xSoftwareVersion": "1.0.0",
                              "xCommand": "cc:sale",
                              "xAllowDuplicate": "'.$xAllowDuplicate.'",
                              "xIP": "108.61.94.102",
                              "xAmount": "' .$amount. '",
                              "xCustom01": "Account-' . $accountId . '",
                          }',

            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);


        if (isset($response->xResult) && $response->xResult == 'A') {
            return [
                'status' => 'approved',
                'transaction_id' => $response->xRefNum,
                'auth_code' => $response->xAuthCode,
                'amount' => $response->xAuthAmount,
                'card_type' => $response->xCardType,
                'masked_card_number' => $response->xMaskedCardNumber,
                'avs_result_code' => $response->xAvsResultCode,
                'avs_result' => $response->xAvsResult,
                'cvv_result_code' => $response->xCvvResultCode,
                'cvv_result' => $response->xCvvResult,
                'date' => $response->xDate,
//                'currency' => $response->xCurrency,
//                'entry_method' => $response->xEntryMethod,
            ];
        } else {
            return [
                'status' => 'declined',
                'message' => $response->xError ?? 'Unknown error',
                'error_code' => $response->xErrorCode ?? null,
            ];
        }

    }

    public static function processPayment($cardknoxToken,$amount,$desc)
    {

        if(config('app.CARDKNOX_ENV_LIVE') == false){

            $amount = 10;

        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                              "xToken": "' .$cardknoxToken. '",
                              "xKey": "' . env('CARDKNOX_XKEY') . '",
                              "xVersion": "4.5.9",
                              "xSoftwareName": "' . env('APP_NAME') . '",
                              "xSoftwareVersion": "1.0.0",
                              "xCommand": "cc:sale",
                              "xAllowDuplicate": "false",
                              "xIP": "108.61.94.102",
                              "xAmount": "' .$amount. '",
                              "xCustom01": "'.$desc.'",
                          }',

            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);


        if (isset($response->xResult) && $response->xResult == 'A') {
            return [
                'status' => 'approved',
                'transaction_id' => $response->xRefNum,
                'auth_code' => $response->xAuthCode,
                'amount' => $response->xAuthAmount,
                'card_type' => $response->xCardType,
                'masked_card_number' => $response->xMaskedCardNumber,
                'avs_result_code' => $response->xAvsResultCode,
                'avs_result' => $response->xAvsResult,
                'cvv_result_code' => $response->xCvvResultCode,
                'cvv_result' => $response->xCvvResult,
                'date' => $response->xDate,
//                'currency' => $response->xCurrency,
//                'entry_method' => $response->xEntryMethod,
            ];
        } else {
            return [
                'status' => 'declined',
                'message' => $response->xError ?? 'Unknown error',
                'error_code' => $response->xErrorCode ?? null,
            ];
        }

    }

    public static function cardknoxAchPayment($cardknoxToken, $amount, $accountId)
    {



        if(empty($cardknoxToken) || $cardknoxToken == null){

            return [
                'status' => 'declined',
                'message' => 'Token Not Found',
                'error_code' => 404,
            ];
        }
        if(config('app.CARDKNOX_ENV_LIVE') == false){


            $amount = 10;
            $cardknoxToken = 'npph0g9m33677493mm84257mqq1m5q18';


        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CARDKNOX_ENDPOINT'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                              "xToken": "' .$cardknoxToken. '",
                              "xKey": "' . env('CARDKNOX_XKEY') . '",
                              "xVersion": "4.5.9",
                              "xSoftwareName": "' . env('APP_NAME') . '",
                              "xSoftwareVersion": "1.0.0",
                              "xCommand": "check:Sale",
                              "xAllowDuplicate": "true",
                              "xIP": "108.61.94.102",
                              "xAmount": "' .$amount. '",
                              "xCustom01": "Account-' . $accountId . '",
                          }',

            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);


        if (isset($response->xResult) && $response->xResult == 'A') {
            return [
                'status' => 'approved',
                'transaction_id' => $response->xRefNum,
                'amount' => $response->xAuthAmount,
                'masked_card_number' => $response->xMaskedAccountNumber,
                'avs_result_code' => $response->xAvsResultCode,
                'avs_result' => $response->xAvsResult,

                'date' => $response->xDate,

            ];
        } else {
            return [
                'status' => 'declined',
                'message' => $response->xError ?? 'Unknown error',
                'error_code' => $response->xErrorCode ?? null,
            ];
        }

    }


}

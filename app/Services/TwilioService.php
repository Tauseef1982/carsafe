<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\SmsLog;


class TwilioService
{


    public static function sendotp($user_phone, $username)
    {

        $sid = config("app.TWILIO_SID");
        $token = config("app.TWILIO_AUTH_TOKEN");

        // if ($username === '1218') {
        //     $otp = 12345;
        // } else {
        //     $otp = rand(11111, 99999);
        // }

        $otp = $username;
        $msg_content = 'Your OTP Code For Login Is ' . $otp;

        try {
            if (false) {
                $client = new Client($sid, $token);

                $client->messages->create(
                    $user_phone,
                    array(
                        'from' => '+18556999224',
                        'body' => $msg_content
                    )
                );
            }

        } catch (\Exception $th) {

            $err = $th->getMessage();
            return response()->json(['success' => false, 'data' => [], 'message' => 'Please enter a valid number', 'errormessage' => $err], 200);

        }
        DB::table('tw_verify')->insert(['phone' => $user_phone, 'otp' => $otp]);

        return response()->json(['success' => true, 'data' => [], 'otpsend' => true, 'message' => 'otp send successfully,please check'], 200);

    }

    public static function sendSms($phone, $cost, $extraCharges, $tripId, $driverId, $extra_message=null)
    {
        if ($extraCharges > 0) {
          //  Log::info('Extra charges (' . $extraCharges . ') are greater than the fare (' . $cost . ') for trip ID ' . $tripId . '. SMS will not be sent.');
            return;
        }

        $sid = config("app.TWILIO_SID");
        $token = config("app.TWILIO_AUTH_TOKEN");


        $message = "The fare for your recent trip was added to your account.\n" .
            "Fare: \${$cost}\n";

        if ($extraCharges > 0) {

            $message .= "{$extra_message}";


        }

        $message .= "If you have any concerns about your fare or experience, feel free to contact us!\n" .
            "Thank you for choosing GoCab!";

        // Check if an SMS has already been sent for this trip and driver
        $existingSmsLog = SmsLog::where('trip_id', $tripId)
            ->where('driver_id', $driverId)
            ->where('status', 'success')
            ->first();

        if ($existingSmsLog) {
          //  Log::info('SMS for trip ID ' . $tripId . ' and driver ID ' . $driverId . ' has already been sent.');
            return;
        }

        DB::beginTransaction();

        try {
            $twilio = new Client($sid, $token);


            $twilio->messages->create(
                $phone,
                array(
                    'from' => '+18455994444',
                    'body' => $message
                )
            );


            SmsLog::create([
                'to_phone' => $phone,
                'message' => $message,
                'status' => 'success',
                'response' => 'SMS sent successfully',
                'trip_id' => $tripId,
                'driver_id' => $driverId
            ]);

            DB::commit();

         //   Log::info('SMS sent successfully to ' . $phone);

        } catch (\Twilio\Exceptions\RestException $e) {
            DB::rollBack();


            SmsLog::create([
                'to_phone' => $phone,
                'message' => $message,
                'status' => 'failed',
                'response' => $e->getMessage(),
                'trip_id' => $tripId,
                'driver_id' => $driverId
            ]);

          //  Log::error('Failed to send SMS: ' . $e->getMessage());
          //  Log::error('Twilio response: ' . $e->getCode() . ' - ' . $e->getMessage());


            if (session()->has('error')) {
                session()->flash('error', 'Failed to send SMS to the given phone number.');
            }

        } catch (\Exception $e) {
            DB::rollBack();


            SmsLog::create([
                'to_phone' => $phone,
                'message' => $message,
                'status' => 'failed',
                'response' => $e->getMessage(),
                'trip_id' => $tripId,
                'driver_id' => $driverId
            ]);

            //Log::error('Failed to send SMS: ' . $e->getMessage());


            if (session()->has('error')) {
                session()->flash('error', 'Something went wrong.');
            }


        }
    }

    public static function verifyOtp($user_phone, $otp)
    {

        $data = DB::table('tw_verify')->where(['phone' => $user_phone])->orderBy('id', 'desc')->first();

        if ($data != null) {
            if ($data->otp == $otp) {
                DB::table('tw_verify')->where('phone', $user_phone)->delete();
                return response()->json(['success' => true, 'data' => [], 'message' => 'Matched'], 200);

            } else {

                return response()->json(['success' => false, 'data' => [], 'message' => 'Invalid OTP'], 200);

            }
        } else {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Invalid OTP'], 200);

        }


    }


    public static function voicecall($to,$type)
    {


        $sid = config("app.TWILIO_SID");
        $token = config("app.TWILIO_AUTH_TOKEN");
        $from = config("app.TWILIO_PHONE");
        $to = preg_replace('/[^0-9]/', '', $to);

        if (!Str::startsWith($to, '+1')) {
            $to = '+1' . $to;
        }
        try {

            $twilio = new Client($sid, $token);

            if($type == 'refill-need'){
                $voice = '<Response><Say voice="man">Hello, this is Go Cab.,,,,,Your account balance has reached (amount).,,,,, Please call customer service to add more money to your account.</Say></Response>';

            }elseif($type == 'refilled-approved'){
                $voice = '<Response><Say voice="man">Hello, this is Go Cab.,,,,We have refilled your prepaid account.,,, Thank you.</Say></Response>';

            }elseif($type == 'refilled-declined'){
                $voice = '<Response><Say voice="man">Hello, this is Go Cab.,,,Your credit card was declined and we were unable to refill your prepaid account.,,,,,Please call customer service to add money to your account. Thank you.</Say></Response>';

            }else{

                $voice = '<Response><Say voice="man">Hello, this is Go Cab.,,,,,Please call customer service to add money to your account.,,,,, Thank you.</Say></Response>';

            }
             $twilio->calls->create(
                $to,
                 $from,
                ["twiml" => $voice]
//                ["url" => "http://demo.twilio.com/docs/classic.mp3"]
            );

        } catch (\Exception $th) {

            $err = $th->getMessage();
            Log::info('voiceCall-Error:'.$err);
        }


    }


}

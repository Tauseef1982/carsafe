<?php

namespace App\Services;

use App\Mail\AccountInvoice;
use App\Mail\InactiveAccount;
use App\Mail\PrepaidAccountInvoice;
use App\Mail\SendAccountBulkEmail;
use App\Mail\TripEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;


class EmailService
{


    public static function send($email, $cost, $extraCharges, $extra_message = null)
    {

        $message = "The fare for your recent trip was added to your account.\n" .
            "Fare: \${$cost}\n";

        if ($extraCharges > 0) {
            $message .= "Extras: \${$extraCharges}\n";
            $message .= "{$extra_message}";

        }

        $message .= "If you have any concerns about your fare or experience, feel free to contact us!\n" .
            "Thank you for choosing GoCab!";
        Mail::to($email)->send(new TripEmail($message));


    }


    public static function sendBulkInvoices($data)
    {

        $path = storage_path('app/public/' . time() . '-' . $data['account']->account_id . '.pdf');

        // Generate the PDF and save it to the path
        PDF::loadView('email.sendBulkInvoice', ['data' => $data])->save($path);
        $to_email = !empty($data['account']->billing_email) ? $data['account']->billing_email : $data['account']->email;
        $data['path'] = $path;
        try {
            Mail::to($to_email)->send(new SendAccountBulkEmail($data));
            unlink($path);
        } catch (\Exception $e) {

            Log::Info('account-invoice-email-sending-fail');
        }

    }


    public static function AccountInvoice($data, $account,$custom_msg = null)
    {

        try {

            $send_to = !empty($account->billing_email) ? $account->billing_email : $account->email;
             $message = "<p>Dear Account Holder,</p>";

            if($custom_msg != null){
                $message .= $custom_msg;
            }
            if ($data->status == 'paid') {

                $message .= "<br>";
                $message .= "<p>Your recent payment has been processed.</p>
                <br><p>Please <a href='" . route('unauth.invoice.view', $data->hash_id) . "'>click here</a> to view and download the invoice.</p>";
                $message .= "<br>";
                $message .= "<p>Thank You For Choosing GoCab.</p>";

            } else {

                $message .= "<br>";
                $message .= "<p>Your recent account payment has failed.</p>";
                $message .= "<br>";
                $message .= "<p>We will retry your card within 3 days.</p><br>
                <p>You can use <a href='" . route('unauth.invoice.view', $data->hash_id) . "'>this link</a> to pay your current invoice or update your credit card details.</p>";
                $message .= "<br>";
                $message .= "<p>Thank You For Choosing GoCab.</p>";

            }
            $message .= "<br>";
            $message .= "<p>If you notice a 'flagdown' charge on your last invoice for a trip you didn’t take in this period , feel free to <a href='" . route('add_complaint',$data->hash_id) . "'>submit a complaint </a> and we will review it and respond via email.</p>";

            $complaint_url = route('add_complaint',$data->hash_id);
            $data2['from_date'] = $data->invoice_from_date;
            $data2['to_date'] = $data->invoice_to_date;
            $data2['account'] = $account;
            $data2['complaint_url'] = $complaint_url;
            if (!File::exists(public_path('invoice/pdf'))) {
                File::makeDirectory(public_path('invoice/pdf'), 0777, true, true); // Recursive, with full permissions
            }

            $path = public_path('invoice/pdf/' . time() . '-' . $data->account_id . '.pdf');

            if($account->account_type == 'prepaid') {
                PDF::loadView('email.prepaid_accountinvoice_pdf', ['data' => $data2])->save($path);
            }else {
                PDF::loadView('email.accountinvoice_pdf', ['data' => $data2])->save($path);
            }
//            Log::info($pathPdf);
            $data3['path'] = $path;
            $data3['message'] = $message;

      //     Mail::to($send_to)->send(new AccountInvoice($data3));
            if($account->account_type == 'prepaid'){
                Mail::to('farhanbashir06@gmail.com')->send(new PrepaidAccountInvoice($data3));

            }else{
                Mail::to('farhanbashir06@gmail.com')->send(new AccountInvoice($data3));

            }
            $data->email_sends = $data->email_sends + 1;
            if (method_exists($data, 'save')) {
                $data->save();
            }

        } catch (\Exception $e) {

            Log::info("email not sent"." ".$e->getMessage());

//
        }

    }

    public static function AccountInActive($account)
    {

        try {

            $send_to = !empty($account->email) ? $account->email : null;
            $message = "<p>Hi,</p>";
            $data3['message'] = $message;

            if(!empty($send_to)) {
                Mail::to($send_to)->send(new InactiveAccount($data3));
            }

        } catch (\Exception $e) {

            Log::info("email not sent"." ".$e->getMessage());

        }

    }

    public static function genPdf($html,$filePath)
    {
//        dd($html);

        try{

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://mw-jsreport.herokuapp.com/api/report',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
            "template": {
            "name": "PDF REPORT OF JOBS"
            },
            "data": {
            "html": "<p>Hi,</p><br><p>Your recent payment has been processed.</p><br><p>Please <a>click here</a> to view and download the invoice.</p><br><p>Thank You For Choosing GoCab.</p>"
            }
            }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic NTVhZG0xbjo/P2pzcmVwb3J0ISFwYXNzMQ=='
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);


            Storage::put('pdf/'.$filePath, $response);
            $absolutePath = storage_path("app/pdf/{$filePath}");

            return $absolutePath;

        } catch (\Exception $e) {

            return null;

        }


    }

}

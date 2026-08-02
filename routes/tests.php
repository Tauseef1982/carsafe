<?php


use App\Mail\CustomerLogins;
use App\Models\Account;
use App\Models\Trip;
use App\Models\Payment;
use App\Services\CubeContact;
use App\Services\PaymentSaveService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Services\TokenService;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

Route::get('/mg', function () {

    \Illuminate\Support\Facades\Artisan::call('migrate');
});
Route::get('/sendsms/{code}', function ($phone) {

    $sid = config("app.TWILIO_SID");
    $token = config("app.TWILIO_AUTH_TOKEN");
    $from = config("app.TWILIO_PHONE");
    $twilio = new Client($sid, $token);

    $twilio->messages->create(
        $phone,
        array(
            'from' => $from,
            'body' => 'hii testt'
        )
    );
});

// Route::get('/mg', function () {
//     Artisan::call('migrate', [
//         '--path' => 'database/migrations/2024_04_07_123456_create_custom_table.php',
//     ]);
// });


Route::get('/mg2', function () {

    \Illuminate\Support\Facades\Artisan::call('trips:sync');
    return 'Command executed successfully!';


});

Route::get('/cache', function () {

    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return "Cache cleared successfully!";
});
Route::get('/clear-sessions', function () {
    \File::cleanDirectory(storage_path('framework/sessions'));
    return "All sessions cleared successfully!";
});

Route::get('/logs', function () {

    $logFile = storage_path('logs/laravel.log');

    if (File::exists($logFile)) {
        $logContent = File::get($logFile);

        return response($logContent, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    echo 'Log file not found.';

});


Route::get('/correct/balanceprepaid', function () {



    $accounts = \App\Models\Account::where('account_type', 'prepaid')
        ->where('is_deleted', 0)
        ->where('paypertrip', 'off')->get();
    $totalAccounts = 0;
    $negativeBalanceAccounts = 0;
    $plusBalanceAccounts = 0;
    $eqBalanceAccounts = 0;
    $lessBalnceaccounts = 0;

    foreach ($accounts as $account){

    $account_total_inv = \App\Models\AccountPayment::where('account_type', 'prepaid')
        ->where('account_id',$account->account_id)
        ->whereNull('hash_id')
        ->sum('amount');

        $account_payments = \App\Models\Trip::where('account_number',$account->account_id)
        ->where('payment_method' ,'!=','cash')->where('payper_trip', 0)->sum('trip_cost');

    $oldbalance = $account->balance;

    $balance = $account_total_inv - $account_payments;

    $diff =  $oldbalance - $balance ;


    $totalAccounts++; // Count every account processed
     echo "Account ID: {$account->account_id}\n";
        echo "Total Account Payments: {$account_total_inv}\n";
        echo "Total trip cost: {$account_payments}";
        echo " = Balance:" . $balance . "<br>";
        echo " = old Balance :" . $oldbalance . "<br>";
        echo " = diffirence :" . $diff . "<br>";

        // if ($diff != 0) {

        //     $account_payment = new \App\Models\AccountPayment();
        //     $account_payment->account_id = $account->account_id;
        //     $account_payment->account_type = $account->account_type;
        //     $account_payment->amount = $diff;
        //     $account_payment->payment_date = '2025-05-08';
        //     $account_payment->payment_type = 'cash';
        //     $account_payment->note = 'balance_reconciliation';
        //     $account_payment->save();





        // }


    if ($balance < 0) {
        $negativeBalanceAccounts++;

    }
    if($balance < $account->balance){
        $lessBalnceaccounts++;
    }
    if ($balance > $account->balance) {
        $plusBalanceAccounts++;

    }

        $balance = $account_total_inv - $account_payments;
        if($balance == $account->balance) {

            $eqBalanceAccounts++;
            // $account->save();
        }

    }

echo "Total Accounts Processed: {$totalAccounts}<br>";
echo "Accounts with Negative Balance: {$negativeBalanceAccounts}<br>";
echo "Accounts where pabalnce is in plus: {$plusBalanceAccounts}<br>";
echo "Accounts with ok balance: {$eqBalanceAccounts}<br>";
echo "Accounts where  balance is less in actual: {$lessBalnceaccounts}<br>";



});




Route::get('/script/account_defaultPin', function () {



    $accounts = Account::all();

    foreach ($accounts as $account) {
        $account_id = $account->account_id;
        $account->pins = $account_id;
        $account->save();
    }

    return response()->json([
        'message' => "Account id set as Pin for all accounts  successfully",

    ]);
});

Route::get('/del-logs/{code}', function ($code) {
    if ($code == '112233log') {

        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::delete($logFile);
            echo 'Log file deleted successfully.';
        } else {
            echo 'Log file not found.';
        }


    }
});

Route::get('/excel/upload', function () {

    return view('excel');

});

Route::post('/excel/upload', function (Request $request) {

    Excel::import(new UsersImport, $request->file('file'));

    dd('done');

});


Route::get('/send-logins', function () {


    $successCount = 0;
    $accounts = Account::where('is_deleted', 0)->get();
   foreach ($accounts as $account){
    if (empty($account->email)) {
        continue;
    }
    $randomNumber = mt_rand(10000000, 99999999);

      // $account = Account::where('account_id',$account->account_id)->first();
       $account->password = Hash::make($randomNumber);
       $account->save();
       $data['username'] = $account->account_id;
        // generates an 8-digit random number
         $data['password'] = $randomNumber;
       try {
        Mail::to($account->email)->send(new CustomerLogins($data));
        $successCount++;
    } catch (\Exception $e) {
        Log::error('Failed to send email to ' . $account->email . ': ' . $e->getMessage());
        continue;
    }
   }
   Log::info("Total emails successfully sent: {$successCount}");

});







Route::get("customers", function(){

    $data = [
        "SoftwareName" => "ACME Inc.",
        "SoftwareVersion" => "1.0",
        "NextToken" => "",
        "PageSize" => 500,
//        "Filters" => [
//            "BillFirstName" => "John",
//            "BillState" => "NY"
//        ]
    ];

    $token = 'carsafecorp21d90b1cbc7b43ab91a0159f73892c39';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.cardknox.com/v2/ListCustomers',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "Authorization:$token",
            "xKey: $token",
            "Content-Type: application/json",
            "X-Recurring-Api-Version: 2.1"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $customers = json_decode($response)->Customers;

    foreach ($customers as $cust){


        if(isset($cust->DefaultPaymentMethodId)) {


            $data = [
                "SoftwareName" => "ACME Inc.",
                "SoftwareVersion" => "1.0",
                "PaymentMethodId" => $cust->DefaultPaymentMethodId,
                "ShowDeleted" => false,

            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.cardknox.com/v2/GetPaymentMethod',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    "Authorization:$token",
                    "xKey: $token",
                    "Content-Type: application/json",
                    "X-Recurring-Api-Version: 2.1"
                ],
            ]);

            $response2 = curl_exec($curl);
            curl_close($curl);
            $card_data = json_decode($response2);

            if(isset($cust->BillFirstName)) {

                $card = new \App\Models\CreditCard();
                $card->account_id = $cust->DefaultPaymentMethodId;
                $card->account_number = $cust->BillFirstName ?? '';
                $card->account_name = $cust->BillLastName ?? '';


                $card->card_number = $card_data->MaskedCardNumber;
                $card->expiry = $card_data->Exp;
                $card->card_zip = $card_data->Zip;
                $card->cardnox_token = $card_data->Token;
                $card->save();
            }
        }
    }
die();

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.taxicaller.net/api/v1/company/48647/customer/account/list?limit=400&offset=1',
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

    dd($originalObject);

});
Route::get("trips_tc/{from}", function($from){

    if($from == 1) {

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
            CURLOPT_POSTFIELDS => json_encode([
                "company_id" => 57068,
                "report_type" => "jobs",
                "output_format" => "json",
                "template_id" => 12528,
                "search_query" => [
                    "period" => [
                        "@type" => "custom",
                        "start" => "2025-01-01T00:00:00",
                        "end" => "2025-01-31T00:00:00",
//                    "end" => "".$from."T00:00:00",
//                    "start" => "".$to."T00:00:00"
                    ]
                ]
            ]),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . TokenService::token(),
                'Content-Type: application/json'
            ),
        ));

    }
    if($from == 2) {

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
            CURLOPT_POSTFIELDS => json_encode([
                "company_id" => 57068,
                "report_type" => "jobs",
                "output_format" => "json",
                "template_id" => 12528,
                "search_query" => [
                    "period" => [
                        "@type" => "custom",
                        "start" => "2025-02-01T00:00:00",
                        "end" => "2025-02-28T00:00:00",
//                    "end" => "".$from."T00:00:00",
//                    "start" => "".$to."T00:00:00"
                    ]
                ]
            ]),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . TokenService::token(),
                'Content-Type: application/json'
            ),
        ));

    }


    if($from == 3) {

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
            CURLOPT_POSTFIELDS => json_encode([
                "company_id" => 57068,
                "report_type" => "jobs",
                "output_format" => "json",
                "template_id" => 12528,
                "search_query" => [
                    "period" => [
                        "@type" => "custom",
                        "start" => "2025-03-01T00:00:00",
                        "end" => "2025-03-02T00:00:00",
//                    "end" => "".$from."T00:00:00",
//                    "start" => "".$to."T00:00:00"
                    ]
                ]
            ]),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . TokenService::token(),
                'Content-Type: application/json'
            ),
        ));

    }
    $response = curl_exec($curl);
    curl_close($curl);
   $response = json_decode($response);

    $trips = $response->rows;
    $trips = collect($trips);

    foreach ($trips as $trip){


        if($trip->{'stops'} != '' && $trip->{'stops'} != null){
            $to_location = $trip->{'stops'};

        }else{
            $to_location = $trip->{'route.drop_off_text'};

        }

        Trip::where('trip_id',(int)$trip->{'id'})->update(['location_to'=>$to_location]);


    }
    dd('almost updated wait a while'.count($trips));

});

Route::get('update_cards', function () {
    DB::statement("
        UPDATE credit_cards
        JOIN accounts
            ON accounts.f_name = credit_cards.account_number
            AND accounts.lname = credit_cards.account_name
        SET credit_cards.account_id = accounts.account_id
    ");
});
Route::get('account_balance', function(){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.taxicaller.net/api/v1/company/48647/bank/account/list',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => '{"method":"search-customer-account","data":{"query":{"filtered":{"filter":{"@type":"bool","must":[{"@type":"term","field":"meta_.retention.active","term":"true"}]}},"sort":[{"field":"name.raw","order":"asc"}],"page":{"offset":0,"limit":5000}}}}',
        CURLOPT_HTTPHEADER => array(
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9',
            'Authorization: Bearer '. TokenService::token(),
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'Content-Type: text/plain;charset=UTF-8',
            'Origin: https://app.taxicaller.net',
            'Pragma: no-cache',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-site',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'sec-ch-ua: "Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    $accounts = $data['accounts'] ?? [];


    foreach ($accounts as $record) {

        if (preg_match('/customer-(\d+)/', $record['name'], $matches)) {
            $cubeId = $matches[1];
            $balance = $record['balance'] / 1000;


            $account = Account::where('cube_id', $cubeId)->first();
            if ($account) {
                $account->balance = $balance;
                if ($balance <= 0) {
                    $account->status = 0;
                }
                $account->save();
            }
        }
    }

    return 'Account balances updated successfully.';


});

Route::get('update_trips',function(){
    $trips = [
    327872799,
    327865156,
    327860768,
    327860523,
    327860491,
    327858454,
    327858554,
    327858695,
    327858779,
    327858996,
    327859031,
    327859048,
    327859057,
    327859069,
    327859104,
    327859258,
    327859350,
    327859394,
    327859400,
    327859419,
    327859479,
    327859524,
    327859582,
    327859618,
    327859636,
    327859651,
    327859652,
    327859684,
    327859702,
    327859725,
    327859746,
    327859789,
    327859811,
    327859875,
    327860085,
    327860097,
    327860164,
    327860239,
    327860339,
    327860362,
    327860477,
    327873837,
    327878237,
    327885641
];

    foreach ($trips as $trip_id) {
    $trip = Trip::where('trip_id', $trip_id)->first();

    if ($trip) {
        $trip->payment_method = 'card';
        $trip->gocab_paid = (float) $trip->trip_cost;
        $trip->save();

        // Check if a payment already exists for this trip
        $existingPayment = Payment::where('trip_id', $trip->trip_id)
            ->where('user_type', 'driver')
            ->where('type', 'credit')
            ->where('is_delete', 0)
            ->exists();

        if ($existingPayment) {
             echo "Payment already exists for trip ID: {$trip_id}\n";
            continue;
             // Skip if payment record already exists
        }

        $new = new Payment();
        $new->driver_id    = $trip->driver_id;
        $new->trip_id      = $trip->trip_id;
        $new->payment_date = now()->toDateString();
        $new->amount       = (float) $trip->gocab_paid;
        $new->user_id      = $trip->driver_id;
        $new->user_type    = 'driver';
        $new->type         = 'credit';
        $new->description  = 'trips accepted which were paid on card';
        $new->save();
    }

    echo "Processed trip ID: {$trip_id}\n";
}
});

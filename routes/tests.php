<?php


use App\Mail\CustomerLogins;
use App\Models\Account;
use App\Models\Trip;
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


Route::get('/mg', function () {

    \Illuminate\Support\Facades\Artisan::call('migrate');
});

// Route::get('/mg', function () {
//     Artisan::call('migrate', [
//         '--path' => 'database/migrations/2024_04_07_123456_create_custom_table.php',
//     ]);
// });


Route::get('/mg2', function () {

    \Illuminate\Support\Facades\Artisan::call('accounts:sync');
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

    dd('not allow');

    $accounts = \App\Models\Account::where('account_type', 'prepaid')
        ->where('is_deleted', 0)->get();

    foreach ($accounts as $account){

    $account_total_inv = \App\Models\AccountPayment::where('account_type', 'prepaid')
        ->where('account_id',$account->account_id)
        ->sum('amount');

        $account_payments = \App\Models\Payment::where('user_type','customer')->where('type','debit')
            ->where('account_id',$account->account_id)->sum('amount');

        echo "Account ID: {$account->account_id}\n";
        echo "Total Account Payments: {$account_total_inv}\n";
        echo "Total Payments: {$account_payments}";
        $balance = $account_total_inv - $account_payments;
        if($balance != $account->balance) {
            echo " = Balance Payments:" . $balance . "<br>";
            $account->balance = $account_total_inv - $account_payments;
            $account->save();
        }

    }




});


// Route::get('/script/account_defaultPin', function () {

//     $accounts = \App\Models\Account::where('account_type' , 'postpaid')->get();


//     sleep(2);
//     foreach($accounts as $account){

//         if($account->cube_id != '') {
//             if ($account->status == 0) {
//                 CubeContact::updateCubeAccount($account->account_id,"Your Account Is Closed","Inactive");

//             } elseif ($account->status == 1) {


//                     CubeContact::updateCubeAccount($account->account_id, null, "active");



//             }
//         }
//         sleep(1);
//     }


// });

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



    $accounts = Account::where('email','farhanbashir06@gmail.com')->get();
   foreach ($accounts as $account){

       $account = Account::where('account_id',$account->account_id)->first();
       $account->password = Hash::make($account->account_id.'@gocab');
       $account->save();
       $data['username'] = $account->account_id;
       $data['password'] = $account->account_id.'@gocab';
       Mail::to($account->email)->send(new CustomerLogins($data));
   }


});


Route::get('/testcubec/{id}', function ($id) {


    dd('not allow');


    $token = \App\Services\TokenService::cubeToken();

    $account = \App\Models\Account::find($id);
    $data = [
        "email" => $account->email,
        "first_name" => $account->f_name,
        "last_name" => $account->account_type.'.',
        "phone" => $account->account_id,
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
    $account->cube_id = $response[0]['_id'];
    $account->save();

    return $response;

});


Route::get('/cube-contacts', function () {



    dd('not allow');
    $token = \App\Services\TokenService::cubeToken().'iii';


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://tapcube.co/contacts',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);

    dd($response);

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

    // Loop over each balance record
    foreach ($accounts as $record) {
        // Extract the cube_id from the 'name' field
        if (preg_match('/customer-(\d+)/', $record['name'], $matches)) {
            $cubeId = $matches[1];
            $balance = $record['balance'] / 1000;

            // Find and update the account
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

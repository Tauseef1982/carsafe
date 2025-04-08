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


Route::get('/mg', function () {

    \Illuminate\Support\Facades\Artisan::call('migrate');
});

// Route::get('/mg', function () {
//     Artisan::call('migrate', [
//         '--path' => 'database/migrations/2024_04_07_123456_create_custom_table.php',
//     ]);
// });


Route::get('/mg2', function () {

    \Illuminate\Support\Facades\Artisan::call('prePaidInvoices:accounts');
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
    $page = request()->get('page', 1); // Get current page from query string, default to 1
    $perPage = 25; // Process 25 accounts per request

    $accounts = Account::where('account_type', 'postpaid')
        ->skip(($page - 1) * $perPage) // Skip previous batches
        ->take($perPage) // Take the next 25
        ->get();

    if ($accounts->isEmpty()) {
        return response()->json([
            'message' => 'No more accounts to process',
        ]);
    }

    sleep(2);

    foreach ($accounts as $account) {
        if (!empty($account->cube_id)) {
            if ($account->status == 0) {
                CubeContact::updateCubeAccount($account->account_id, "Your Account Is Closed", "Inactive");
            } elseif ($account->status == 1) {

                CubeContact::updateCubeAccount($account->account_id, null, "active");
                Log::info("Account ID: {$account->account_id} updated to active.");
            }
        }
        sleep(1);
    }

    return response()->json([
        'message' => "Processed batch $page successfully",
        'processed_accounts' => $accounts->pluck('account_id'),
        'next_page' => url('/script/account_defaultPin?page=' . ($page + 1)), // Provide next batch link
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

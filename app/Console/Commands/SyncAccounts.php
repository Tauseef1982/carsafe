<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Account;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://dn14.taxicaller.net/DispatchApp/setup',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
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
                'Referer: https://app.taxicaller.net/account/customers/accounts',
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
        $response = json_decode($response);
      
        $accounts = $response->data->docs;

        DB::beginTransaction();
       // Log::info(count( $accounts));
        foreach ($accounts as $account) {
            if (!Account::where('account_id', $account->{'name'})->exists()) {
                $newAccount = new Account();
                $newAccount->account_id = $account->{'name'};
                $newAccount->f_name = $account->{'lname'};
                $newAccount->status = $account->{'active'};
                $newAccount->phone = $account->tags->{'info'};
                $newAccount->company_name = $account->{'cname'};
                $newAccount->address = $account->tags->{'address'};
                $newAccount->billing_email = $account->tags->{'billing_email'};
                $newAccount->email = $account->tags->{'contact_email'};
               
                
                $newAccount->save();
                
            }

         
        }

         DB::commit();
        Log::info('sync-accounts');
    }

}
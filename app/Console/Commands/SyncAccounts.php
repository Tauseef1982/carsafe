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
        //  if($account->{'name'} == '0085'){
        //     dd($account);
        //  }
        $existingAccount = Account::where('account_id', $account->{'name'})->first();

        if (!$existingAccount) {
            // Create new account
            $newAccount = new Account();
            $newAccount->account_id = $account->{'name'};
        } else {
            // Update existing account
            $newAccount = $existingAccount;
        }

                $newAccount->f_name = $account->{'fname'};
                $newAccount->lname = $account->{'lname'};
                $newAccount->status = $account->{'active'};
                $newAccount->account_id = !empty($account->tags->{'info'}) ? $account->tags->{'info'} : $account->{'phone'};
                $newAccount->phone = !empty($account->tags->{'info'}) ? $account->tags->{'info'} : $account->{'phone'};

                $newAccount->company_name = $account->{'cname'};
                $newAccount->address = $account->tags->{'address'};
                $contactEmail = $account->tags->{'contact_email'} ?? null;
                $billingEmail = $account->tags->{'billing_email'} ?? null;

                if (!empty($contactEmail) && !empty($billingEmail) && $contactEmail !== $billingEmail) {
                    // Both emails are present and different
                    $newAccount->email = $contactEmail;
                    $newAccount->billing_email = $billingEmail;
                } else {
                    // Use whichever is available (or same) for both
                    $emailToUse = !empty($contactEmail) ? $contactEmail : $billingEmail;
                    $newAccount->email = $emailToUse;
                    $newAccount->billing_email = $emailToUse;
                }

                $newAccount->account_type = 'prepaid';
                $newAccount->cube_id = $account->{'id'};
                $newAccount->notification_setting = 'account_phone';
                
                $newAccount->save();
                
            }

         
        

         DB::commit();
        Log::info('sync-accounts');
    }

}
<?php

namespace App\Console\Commands;

use App\Models\AccountPayment;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Account;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
use App\Services\LogService;
use App\Services\PaymentSaveService;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefillAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refill:accounts';

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

    // Fetch eligible accounts
    $accounts = Account::where('autofill', 'on')
        ->where('account_type', 'prepaid')
        ->where('balance', '<', 21)
        ->where('status', 1)
        ->where('retry','<',5)
        ->get();

    Log::info("Total accounts to process: " . $accounts->count());

    PaymentSaveService::prepPaidRefill($accounts,$type = "multi");


    Log::info('Refill accounts process completed.');
}


}

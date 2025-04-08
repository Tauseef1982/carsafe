<?php

namespace App\Console\Commands;

use App\Models\AccountPayment;
use App\Models\Account;
use App\Models\BatchPayment;
use App\Models\Payment;
use App\Services\CardKnoxService;
use App\Services\EmailService;
use App\Services\LogService;
use App\Services\PaymentSaveService;
use App\Services\TokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PostPaidAccountsDeduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postPaidDeduction:accounts';

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

//        dd('restrict');
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        if (Carbon::now()->day > 15) {
            // Set $from_date to the 1st of the current month
            $from_date = Carbon::now()->startOfMonth()->format('Y-m-d');
            // Set $to_date to the 15th of the current month
            $to_date = Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d');
        } else {
            // Set $from_date to the 15th of the previous month
            $from_date = Carbon::now()->subMonth()->startOfMonth()->addDays(15)->format('Y-m-d');
            // Set $to_date to the last day of the previous month
            $to_date = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        }


        $accounts = Account::with(['trips' => function ($query) use ($from_date, $to_date) {
            $query->where('payment_method', 'account')
                ->where('is_delete', 0)
                ->whereBetween('date', [$from_date, $to_date]);
            }])->where('account_type', 'postpaid')
            ->where('is_deleted', 0)
            ->get();

        PaymentSaveService::postPaidDeductTripsPayments($accounts,$from_date,$to_date);


        Log::info('allPostpaidCronDone');


    }

}

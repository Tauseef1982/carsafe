<?php

namespace App\Console\Commands;

use App\Models\AccountPayment;
use App\Models\Account;
use App\Models\BatchPayment;
use App\Models\Payment;
use App\Services\CardKnoxService;
use App\Services\CubeContact;
use App\Services\EmailService;
use App\Services\LogService;
use App\Services\PaymentSaveService;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DueInvoices:accounts';

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

        $due_date = now()->yesterday()->toDateString();

        $dueInvoices = AccountPayment::where('account_type','postpaid')->where('status','unpaid')->whereNotNull('hash_id')->where('due_date',$due_date)->get();

        foreach ($dueInvoices as $dueinvoice) {

            Log::info('due');

//                DB::beginTransaction();

                $account = Account::where('account_id',$dueinvoice->account_id)->first();
                $token = $account->card ? $account->card->cardnox_token : null;
                $total_payments = 0;
                $deduct_status = false;

                $trips_to_be_paid = $account->trips->where('payment_method', '=', 'account')->where('date', '>=',$dueinvoice->invoice_from_date)->where('date', '<=',$dueinvoice->invoice_to_date);
                $paymeny_data_Bulk = array();
                foreach ($trips_to_be_paid as $paytrip) {

                    $AlreadyPaid = $paytrip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                    if ($AlreadyPaid < $paytrip->trip_cost) {

                        $paying = (float)$paytrip->trip_cost - $AlreadyPaid;

                        $payment_data['driver_id'] = $paytrip->driver_id;
                        $payment_data['trip_id'] = $paytrip->trip_id;
                        $payment_data['payment_date'] = now()->toDateString();
                        $payment_data['amount'] = $paying;
                        $payment_data['user_id'] = 0;
                        $payment_data['user_type'] = 'customer';
                        $payment_data['type'] = 'debit';
                        $payment_data['description'] = 'cron:customer_pay_to_account' . $account->account_id;
                        $payment_data['account_id'] = $paytrip->account_number;

                        $paymeny_data_Bulk[] = $payment_data;
                        $total_payments = $total_payments + $paying;

                    }

                }

                $fill_and_deduct = CardKnoxService::processCardknoxPaymentRefill($token, $total_payments, $account->id);


                if ($fill_and_deduct['status'] == 'approved') {

                    $deduct_status = true;
                    $transaction_id = $fill_and_deduct['transaction_id'];
                    $dueinvoice->transaction_id = $transaction_id;

                }

                if ($deduct_status == true) {


                    $batch_p = new BatchPayment();
                    $batch_p->account_id = $account->account_id;
                    $batch_p->from = 'customer_by_admin';
                    $batch_p->amount = $total_payments;
                    $batch_p->save();

                    $paymeny_data_send['batch_id'] = $batch_p->id;
                    $paymeny_data_send['payments'] = $paymeny_data_Bulk;

                    $dueinvoice->batch_id = $batch_p->id;

                    PaymentSaveService::save($paymeny_data_send);
                    $message ='Account:Payment deducted by Cron using Cardknox BatchPayment-ID#' . $batch_p->id . ' Amount: ' . $batch_p->amount;


                }else{
                    $message = 'Account:Payment Failed by Cron using Cardknox';
                }

            $try = $dueinvoice->try + 1;

                if($deduct_status == true){
                    $dueinvoice->status = 'paid';
                    $account->status = 1;
                    CubeContact::updateCubeAccount($account->account_id,null,"active");

                }else{
                    $account->status = 0;
                    $account->save();
                    if($try > 3){
                        CubeContact::updateCubeAccount($account->account_id,"Account is inactive due to unpaid invoices","Inactive");
                        EmailService::AccountInActive($account);

                    }
                }

                $dueinvoice->try = $try;
                $dueinvoice->save();


                $logdata = [
                    'from' => 'customer',
                    'payment' => $dueinvoice,
                    'cardknox_response' => $fill_and_deduct,
                    'message'=>$message
                ];

                LogService::saveLog($logdata);

                EmailService::AccountInvoice($dueinvoice,$account);

//                DB::commit();


        }


    }

}

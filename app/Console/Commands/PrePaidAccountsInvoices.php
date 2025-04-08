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

class PrePaidAccountsInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prePaidInvoices:accounts';

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

        $today = Carbon::today();

        if ($today->day > 15) {
            $from_date = $today->copy()->startOfMonth()->format('Y-m-d');
            $to_date = $today->copy()->startOfMonth()->addDays(14)->format('Y-m-d');
        } else {
            $from_date = $today->copy()->subMonth()->startOfMonth()->addDays(15)->format('Y-m-d');
            $to_date = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');
        }

        $from_date = '2024-12-10';
        $accounts = Account::with(['trips' => function ($query) use ($from_date, $to_date) {
            $query->where('payment_method', 'account')
                ->where('is_delete', 0)
                ->whereBetween('date', [$from_date, $to_date])
                ->where(function ($q) {
                    $q->where('status', 'NOT LIKE', '%Cancelled%')
                        ->where('status', 'NOT LIKE', '%canceled%');
                });
        }])
            ->where('account_type', 'prepaid')
            ->where('is_deleted', 0)
            ->get();

        $insertData = [];
        $emailData = [];
        $loop_limit = 0;

        foreach ($accounts as $account) {

            // Double-check to avoid duplicates before processing
            $alreadyExists = AccountPayment::where('account_id', $account->account_id)
                ->where('invoice_from_date', $from_date)
                ->where('invoice_to_date', $to_date)
                ->whereNotNull('hash_id')
                ->where('account_type', 'prepaid')
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $total_payments = 0;

            foreach ($account->trips as $trip) {
                $total_payments += $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
            }

            if ($total_payments > 0) {
                $insertData[] = [
                    'account' => $account,
                    'total_payments' => $total_payments,
                ];

                $loop_limit++;
                if ($loop_limit >= 10) {
                    break;
                }
            }
        }

        DB::beginTransaction();

        try {
            foreach ($insertData as $data) {
                $account = $data['account'];
                $total_payments = $data['total_payments'];
                $refTimestamp = now()->format('mdHis');

                // Re-check here just in case loop delay caused data race
                $duplicate = AccountPayment::where('account_id', $account->account_id)
                    ->where('invoice_from_date', $from_date)
                    ->where('invoice_to_date', $to_date)
                    ->whereNotNull('hash_id')
                    ->where('account_type', 'prepaid')
                    ->exists();

                if ($duplicate) {
                    continue;
                }

                $accountPayment = AccountPayment::create([
                    'account_id' => $account->account_id,
                    'account_type' => $account->account_type,
                    'amount' => $total_payments,
                    'payment_date' => $today,
                    'ref_no' => $account->id . $refTimestamp,
                    'hash_id' => encrypt($account->id . $refTimestamp),
                    'invoice_from_date' => $from_date,
                    'invoice_to_date' => $to_date,
                    'status' => 'paid',
                    'try' => 1,
                ]);

                $batchPayment = BatchPayment::create([
                    'account_id' => $account->account_id,
                    'from' => 'customer_by_admin',
                    'amount' => $total_payments,
                ]);

                $accountPayment->update(['batch_id' => $batchPayment->id]);

                LogService::saveLog([
                    'from' => 'customer',
                    'payment' => $accountPayment,
                    'message' => 'BatchPayment-ID#' . $batchPayment->id,
                ]);

                $emailData[] = $accountPayment;
            }

            DB::commit();

            Log::info('Total Inserted: ' . count($insertData));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing Invoice Prepaid: ' . $e->getMessage());
        }

        foreach ($emailData as $accountPayment) {
            EmailService::AccountInvoice($accountPayment, $accountPayment->account);
        }

        Log::info('Prepaid invoices sent.');
    }

}

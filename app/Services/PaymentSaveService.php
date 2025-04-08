<?php

namespace App\Services;

use App\Models\AccountPayment;
use App\Models\BatchPayment;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PaymentSaveService
{

    public static function save($data)
    {

        foreach ($data['payments'] as $pay) {

            $new = new Payment();
            $new->driver_id = $pay['driver_id'];
            $new->trip_id = $pay['trip_id'];
            $new->payment_date = $pay['payment_date'];
            $new->amount = $pay['amount'];
            $new->user_id = $pay['user_id'];
            $new->user_type = $pay['user_type'];
            $new->type = $pay['type'];
            $new->batch_id = isset($data['batch_id']) ? $data['batch_id'] : null;
            $new->description = $pay['description'];
            $new->account_id = $pay['account_id'];
            $new->save();

        }
    }

    public static function prepPaidRefill($acc,$type = "multi")
    {

        if($type == 'single'){

            $run_from = "Imediately:Refill Payment added using CardKnox";
            $accounts[] = $acc;
        }else{

            $run_from = "Cron:Refill Payment added using CardKnox";
            $accounts = $acc;
        }


        foreach ($accounts as $account) {

//            DB::beginTransaction();

            try {
                $to_refill = $account->recharge;
                $cardknoxToken = $account->card ? $account->card->cardnox_token : null;
                if(!$cardknoxToken){

                    if($type == 'single') {
                        Log::error("Error processing Account ID: {$account->account_id}. Exception: ");
                        \App\Services\TwilioService::voicecall($account->phone,'refilled-declined');

                        return false;
                    }
                }


                $fee = $to_refill * 0.03;
                $amount = $to_refill + $fee;

                $cardknoxResponse = CardKnoxService::processCardknoxPaymentRefill($cardknoxToken, $amount, $account->account_id);

                if ($cardknoxResponse['status'] !== 'approved') {
                    Log::warning("Payment declined for Account ID: {$account->account_id}. Response: " . json_encode($cardknoxResponse));
                    CubeContact::updateCubeAccount($account->account_id,"Your balance is low","Inactive");

                    if($type == 'single') {
                        \App\Services\TwilioService::voicecall($account->phone,'refilled-declined');
                        return false;
                    }
                    Log::warning("Payment declined and no call was made ");
                    $account->save();
                    continue;
                }

                Log::info("Payment approved for Account ID: {$account->account_id}");

                // Save payment record
                $account_payment = new AccountPayment();
                $account_payment->account_id = $account->account_id;
                $account_payment->account_type = $account->account_type;
                $account_payment->amount = $to_refill;
                $account_payment->transaction_id = $cardknoxResponse['transaction_id'];
                $account_payment->payment_date = Carbon::today();
                $account_payment->payment_type = 'card';
                $account_payment->save();

                // Update account balance
                $account->balance += $to_refill;
                $account->save();

                if($account->cube_id == null || $account->cube_id == '') {
                    CubeContact::createAccount($account->account_id);
                }

                CubeContact::updateCubeAccount($account->account_id, null, "active");

                // Log the successful operation
                $logData = [
                    'from' => 'customer',
                    'payment' => $to_refill,
                    'cardknox_response' => $cardknoxResponse,
                    'message' => $run_from.' for Account#' . $account->account_id . ' Amount: ' . $to_refill,
                ];
                LogService::saveLog($logData);

                Log::info("Account balance updated and log saved for Account ID: {$account->account_id}");
//                \App\Services\TwilioService::voicecall($account->phone,'refilled-approved');
                $account->retry = 0;
                $account->save();
//                DB::commit();
                if($type == 'single') {
                    return $account;
                }
            } catch (\Exception $e) {
//                DB::rollBack();
                Log::error("Error processing Account ID: {$account->account_id}. Exception: " . $e->getMessage());
            }

        }
    }



    public static function postPaidDeductTripsPayments($accounts, $from_date, $to_date,$custom_msg = null)
    {

        ini_set('max_execution_time', 0);
//        ini_set('memory_limit', '1024M');
        // Step 1: Arrange data
        $insertData = [];
        $emailData = [];
        $today = Carbon::today();
        $loop_limit = 0;
        $pay_date = now()->toDateString();

        foreach ($accounts as $account) {

            if (!AccountPayment::where('account_id', $account->account_id)
                ->where('invoice_from_date', $from_date)
                ->where('invoice_to_date', $to_date)
                ->whereNotNull('hash_id')
                ->exists()) {

                $tokens = $account->cards ? $account->cards : [];
                if($tokens) {
                    $tokens = $tokens->where('is_deleted',0)  // Filter out deleted records
                    ->sortByDesc('charge_priority')  // Sort by priority (1 first)
                    ->values();
                }
                $total_payments = 0;
                $deduct_status = false;

                $trips = $account->trips->filter(function ($trip) {
                    return $trip->payment_method === 'account' &&
                        strpos($trip->status, 'Cancelled') === false &&
                        strpos($trip->status, 'canceled') === false;
                });

                $paymentDataBulk = [];
                $Trip_ids = [];
                if (count($trips) > 0) {
                    foreach ($trips as $trip) {


                        $alreadyPaid = $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                        if ($alreadyPaid < $trip->TotalCostDiscounted) {
                            $paying = (float)$trip->TotalCostDiscounted - $alreadyPaid;

                            $paymentDataBulk[] = [
                                'driver_id' => $trip->driver_id,
                                'trip_id' => $trip->trip_id,
                                'payment_date' => $pay_date,
                                'amount' => $paying,
                                'user_id' => 0,
                                'user_type' => 'customer',
                                'type' => 'debit',
                                'description' => 'cron:customer_pay_to_account' . $account->account_id,
                                'account_id' => $trip->account_number,
                            ];

                            $total_payments += $paying;

                            $Trip_ids[] = $trip->trip_id;
                        }
                    }
                }
                if ($total_payments > 0) {
                    $fee = number_format($total_payments * 0.0375, 2, '.', '');
                    $amuntWithfee = $total_payments + $fee;

                    // Add to bulk insert data
                    $insertData[] = [
                        'account' => $account,
                        'tokens' => $tokens,
                        'total_payments' => $total_payments,
                        'amuntWithfee' => $amuntWithfee,
                        'payment_data_bulk' => $paymentDataBulk,
                        'trip_ids'=>json_encode($Trip_ids),
                    ];

                    $loop_limit++;

                }
                $Trip_ids = [];

                if ($loop_limit > 10) {
                    break;
                }
            }


        }

        $accounts = [];
// Step 2: Insert data and perform transactions
        DB::beginTransaction();
        try {
            foreach ($insertData as $data) {


                $account = $data['account'];
                $tokens = $data['tokens'];
                $total_payments = $data['total_payments'];
                $amuntWithfee = $data['amuntWithfee'];
                $paymentDataBulk = $data['payment_data_bulk'];

                if(count($tokens) > 0) {
                    foreach ($tokens as $token) {
                        if ($token->type == 'credit') {
                            $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                                $token->cardnox_token,
                                $amuntWithfee,
                                $account->account_id . '-amountActual' . $total_payments
                            );
                            Log::info('token tryingg = ' . $token->cardnox_token . ' charged ' . $fillAndDeduct['status']);

                            if ($fillAndDeduct['status'] === 'approved') {
                                break;
                            }
                        }

                    }
                }else{
                    $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                        null,
                        $amuntWithfee,
                        $account->account_id . '-amountActual' . $total_payments
                    );
                }

                sleep(3);
                $deduct_status = $fillAndDeduct['status'] === 'approved';
                $transaction_id = $deduct_status ? $fillAndDeduct['transaction_id'] : null;
                //dd($fillAndDeduct);
                // Save account payment
                $accountPayment = AccountPayment::create([
                    'account_id' => $account->account_id,
                    'account_type' => $account->account_type,
                    'amount' => $total_payments,
                    'payment_date' => $today,
                    'payment_type' => 'card',
                    'transaction_id' => $transaction_id,
                    'ref_no' => $account->id . now()->format('md'),
                    'hash_id' => encrypt($account->id . now()->format('md')),
                    'invoice_from_date' => $from_date,
                    'invoice_to_date' => $to_date,
                    'status' => $deduct_status ? 'paid' : 'unpaid',
                    'due_date' => now()->addDays(3)->toDateString(),
                    'trip_ids'=> $data['trip_ids'],
                    'try' => 1,
                ]);

                if ($deduct_status) {
                    // Save batch payment
                    $batchPayment = BatchPayment::create([
                        'account_id' => $account->account_id,
                        'from' => 'customer_by_admin',
                        'amount' => $total_payments,
                    ]);

                    $paymentDataSend = [
                        'batch_id' => $batchPayment->id,
                        'payments' => $paymentDataBulk,
                    ];
                    PaymentSaveService::save($paymentDataSend);

                    $accountPayment->update(['batch_id' => $batchPayment->id]);

                    LogService::saveLog([
                        'from' => 'customer',
                        'payment' => $accountPayment,
                        'cardknox_response' => $fillAndDeduct,
                        'message' => 'Account: Payment deducted by Cron using Cardknox BatchPayment-ID#' . $batchPayment->id,
                    ]);
                }

                // Prepare email data
                if (isset($fillAndDeduct['message'])) {
                    if ($fillAndDeduct['message'] != 'Duplicate Transaction') {
                        $emailData[] = $accountPayment;
                    } else {

                        Log::info('Dublicate_amount-' . $account->account_id);

                    }
                } else {
                    $emailData[] = $accountPayment;

                }

                $accounts[] = $account->account_id;
            }

            DB::commit();
            Log::info('total-inserted-' . count($insertData));



        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing account payments: ' . $e->getMessage());
            return 'Error processing account payments: ' . $e->getMessage();
        }

// Step 3: Send emails
        foreach($emailData as $accountPayment) {
           EmailService::AccountInvoice($accountPayment, $accountPayment->account,$custom_msg);
        }

        return 'total-Submitted-' . implode(',',$accounts);
    }
    public static function postPaidDeductTripsPayments2($accounts, $from_date, $to_date, $custom_msg = null)
    {
        // Step 1: Arrange data
        $insertData = [];
        $emailData = [];
        $today = Carbon::today();
        $loop_limit = 0;
        $pay_date = now()->toDateString();

        // Fetch all existing payments at once to reduce queries
        $existingPayments = AccountPayment::whereIn('account_id', $accounts->pluck('account_id'))
            ->where('invoice_from_date', $from_date)
            ->where('invoice_to_date', $to_date)
            ->whereNotNull('hash_id')
            ->pluck('account_id')
            ->toArray();

        foreach ($accounts as $account) {
            if (in_array($account->account_id, $existingPayments)) {
                continue; // Skip if payment already exists
            }

            // Fetch valid tokens, sorting in memory (better if done in DB)
            $tokens = $account->cards ? $account->cards->where('is_deleted', 0)->sortByDesc('charge_priority')->values() : [];

            $total_payments = 0;
            $Trip_ids = [];
            $paymentDataBulk = [];

            // Filter trips only once outside the loop for efficiency
            $trips = $account->trips->filter(function ($trip) {
                return $trip->payment_method === 'account' &&
                    stripos($trip->status, 'cancelled') === false;
            });

            foreach ($trips as $trip) {
                $alreadyPaid = $trip->totalPaidAmountByCustomerFromAccountCard()->sum('amount');
                if ($alreadyPaid < $trip->TotalCostDiscounted) {
                    $paying = (float) $trip->TotalCostDiscounted - $alreadyPaid;

                    $paymentDataBulk[] = [
                        'driver_id' => $trip->driver_id,
                        'trip_id' => $trip->trip_id,
                        'payment_date' => $pay_date,
                        'amount' => $paying,
                        'user_id' => 0,
                        'user_type' => 'customer',
                        'type' => 'debit',
                        'description' => 'cron:customer_pay_to_account' . $account->account_id,
                        'account_id' => $trip->account_number,
                    ];

                    $total_payments += $paying;
                    $Trip_ids[] = $trip->trip_id;
                }
            }

            if ($total_payments > 0) {
                $fee = number_format($total_payments * 0.0375, 2, '.', '');
                $amountWithFee = $total_payments + $fee;

                $insertData[] = [
                    'account' => $account,
                    'tokens' => $tokens,
                    'total_payments' => $total_payments,
                    'amountWithFee' => $amountWithFee,
                    'payment_data_bulk' => $paymentDataBulk,
                    'trip_ids' => json_encode($Trip_ids),
                ];

                $loop_limit++;
            }

            if ($loop_limit > 10) {
                break; // Prevent excessive processing in one batch
            }
        }

        if (empty($insertData)) {
            return 'No payments to process';
        }

        // Step 2: Insert data and perform transactions
        DB::beginTransaction();
        try {
            foreach ($insertData as $data) {
                $account = $data['account'];
                $tokens = $data['tokens'];
                $total_payments = $data['total_payments'];
                $amountWithFee = $data['amountWithFee'];
                $paymentDataBulk = $data['payment_data_bulk'];

                $fillAndDeduct = ['status' => 'failed']; // Default status

                if (count($tokens) > 0) {
                    foreach ($tokens as $token) {
                        if ($token->type == 'credit') {
                            $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                                $token->cardnox_token,
                                $amountWithFee,
                                $account->account_id . '-amountActual' . $total_payments
                            );

                            Log::info('Token Attempt: ' . $token->cardnox_token . ' charged ' . $fillAndDeduct['status']);

                            if ($fillAndDeduct['status'] === 'approved') {
                                break;
                            }
                        }
                    }
                } else {
                    $fillAndDeduct = CardKnoxService::processCardknoxPaymentRefill(
                        null,
                        $amountWithFee,
                        $account->account_id . '-amountActual' . $total_payments
                    );
                }

                sleep(2); // Reduce wait time

                $deduct_status = $fillAndDeduct['status'] === 'approved';
                $transaction_id = $deduct_status ? $fillAndDeduct['transaction_id'] : null;

                // Save account payment
                $accountPayment = AccountPayment::create([
                    'account_id' => $account->account_id,
                    'account_type' => $account->account_type,
                    'amount' => $total_payments,
                    'payment_date' => $today,
                    'payment_type' => 'card',
                    'transaction_id' => $transaction_id,
                    'ref_no' => $account->id . now()->format('md'),
                    'hash_id' => encrypt($account->id . now()->format('md')),
                    'invoice_from_date' => $from_date,
                    'invoice_to_date' => $to_date,
                    'status' => $deduct_status ? 'paid' : 'unpaid',
                    'due_date' => now()->addDays(3)->toDateString(),
                    'trip_ids' => $data['trip_ids'],
                    'try' => 1,
                ]);

                if ($deduct_status) {
                    $batchPayment = BatchPayment::create([
                        'account_id' => $account->account_id,
                        'from' => 'customer_by_admin',
                        'amount' => $total_payments,
                    ]);

                    PaymentSaveService::save([
                        'batch_id' => $batchPayment->id,
                        'payments' => $paymentDataBulk,
                    ]);

                    $accountPayment->update(['batch_id' => $batchPayment->id]);

                    LogService::saveLog([
                        'from' => 'customer',
                        'payment' => $accountPayment,
                        'cardknox_response' => $fillAndDeduct,
                        'message' => 'Account: Payment deducted by Cron using Cardknox BatchPayment-ID#' . $batchPayment->id,
                    ]);
                }

                // Prepare email data
                if (!empty($fillAndDeduct['message']) && $fillAndDeduct['message'] !== 'Duplicate Transaction') {
                    $emailData[] = $accountPayment;
                } else {
                    Log::info('Duplicate Transaction: Account ' . $account->account_id);
                }
            }

            DB::commit();
            Log::info('Total Inserted: ' . count($insertData));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing account payments: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }

        // Step 3: Send emails
        foreach ($emailData as $accountPayment) {
            EmailService::AccountInvoice($accountPayment, $accountPayment->account, $custom_msg);
        }

        return 'Total Submitted: ' . implode(',', $accounts->pluck('account_id')->toArray());
    }

}

<?php

namespace App\Console\Commands;


use App\Models\CustomerBooking;
use App\Services\LogService;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddScheduledBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CustomerBooking:scheduled';

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

        $now = Carbon::now();
        $fiveMinutesLater = $now->copy()->addMinutes(5);

        $dueInvoices = CustomerBooking::where('status', 'prebook')
            ->where('order_id', 'pre')
            ->whereBetween('schedule_date_time', [$now, $fiveMinutesLater])
            ->get();

        $token = TokenService::token();

        foreach ($dueInvoices as $dueinvoice) {
            try {
                $bookingData = json_decode($dueinvoice->booking_data, true);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])->post('https://api.taxicaller.net/api/v1/booker/order', $bookingData);

                $message = "Trying Customer Scheduled Booking {$dueinvoice->id}";

                if ($response->successful()) {
                    $data = $response->json();
                    $orderToken = $data['order_token'] ?? null;
                    $orderId = $data['order']['order_id'] ?? null;

                    $dueinvoice->order_id = $orderId;
                    $dueinvoice->type = 'changed';
                    $dueinvoice->data = json_encode($data);
                    $dueinvoice->save();

                    Log::info("Booking successful! Invoice #{$dueinvoice->id}, Order ID: {$orderId}");

                    $message = "Customer Scheduled Booking successful! Token: {$orderToken}, Order: {$orderId}";
                }

                $logdata = [
                    'from'    => 'cron:customer_booking',
                    'data'    => $dueinvoice,
                    'message' => $message,
                    'api_resp'=> $response->json() ?? 'Error',
                ];

                LogService::saveLog($logdata);

            } catch (\Exception $e) {
                Log::error("Failed Customer Scheduled Booking for Invoice #{$dueinvoice->id}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }



    }

}

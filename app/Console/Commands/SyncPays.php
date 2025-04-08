<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\Trip;
use App\Services\TokenService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pays:sync';

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


        DB::beginTransaction();

        $tripsids = Trip::where(function ($query) {
            $query->where('status', 'like', '%Cancelled%')
                  ->orWhere('status', 'like', '%canceled%');
        })
        ->where('payment_method', 'account')
        ->pluck('trip_id');
    

            //todo if account then 0
            Payment::withoutTimestamps(function () use ($tripsids) {

                Payment::whereIn('trip_id',$tripsids)->where('prev_am',0)->update([
                    'prev_am' => DB::raw('amount')
                ]);

                Payment::whereIn('trip_id',$tripsids)->update([
                    'is_delete' => 1
                ]);
            });


        DB::commit();
        Log::info('sync-pays');
    }

}





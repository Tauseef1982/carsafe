<?php

namespace App\Console\Commands;


use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Services\CubeContact;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class dispatcherToSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:dispatcherUser';

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

        $commonPassword = 'password123';

        $dispatchers = Driver::where('role','LIKE', '%"DISPATCHER"%' )->get();

        foreach ($dispatchers as $dispatcher) {

            $existingUser = User::where('username', $dispatcher->username)->first();

            if (!$existingUser) {

                User::create([
                    'name' => $dispatcher->first_name. ' ' .$dispatcher->first_name,
                    'username' => $dispatcher->username,
                    'password' => Hash::make($commonPassword),
                    'role' => 'dispatcher',
                ]);
            }
        }
    }

}

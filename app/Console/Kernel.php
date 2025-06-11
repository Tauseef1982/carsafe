<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule592622.
     */
    protected function schedule(Schedule $schedule): void
    {
        //\Log::Info(now()->toDateTimeString().'-------serverTime');
        $schedule->command('trips:sync')->everyThirtySeconds();
        $schedule->command('drivers:sync')->everyFiveMinutes();
        $schedule->command('pays:sync')->everyTwoMinutes();
        $schedule->command('refill:accounts')->dailyAt('01:00');
 //       $schedule->command('contacts:sync')->everyFiveMinutes();
//        $schedule->command('postPaidDeduction:accounts')->everyThreeMinutes();
 //     $schedule->command('postPaidDeduction:accounts')->sundays()->timezone('America/New_York')->between('09:00', '23:55')->everyFiveMinutes();


//   $schedule->command('trips:updateholdtoneutral')->everyMinute();

 //      $schedule->command('accounts:sync')->everyTenMinutes();
//      $schedule->command('weeklyfees:sync')->sundays();

//      $schedule->command('DueInvoices:accounts');
             $schedule->command('cron:dispatcherUser')->dailyAt('02:00');
             $schedule->command('prePaidInvoices:accounts')
    ->timezone('America/New_York')
    ->between('09:00', '23:55')
    ->everyFiveMinutes()
    ->when(function () {
        return in_array(now()->day, [1]);
    });



    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

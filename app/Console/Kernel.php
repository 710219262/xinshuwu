<?php

namespace App\Console;

use App\Console\Commands\AutoReceive;
use App\Console\Commands\CancelTimeoutOrder;
use App\Console\Commands\EnableExpressCompany;
use App\Console\Commands\Stat;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Stat::class,
        CancelTimeoutOrder::class,
        EnableExpressCompany::class,
        AutoReceive::class
    ];
    
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('stat')->everyFiveMinutes();
        $schedule->command('stale_order:cancel')->everyFiveMinutes();
        $schedule->command('auto_receive')->everyFiveMinutes();
    }
}

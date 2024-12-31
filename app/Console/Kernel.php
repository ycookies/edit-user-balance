<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\MonitorBalanceQueue::class,  // 注册监控命令
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 如果你想要定时启动监控，可以添加以下调度
        // $schedule->command('queue:monitor-balance')->everyMinute();
        // 注意：通常不需要调度，因为这是一个持续运行的监控进程
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

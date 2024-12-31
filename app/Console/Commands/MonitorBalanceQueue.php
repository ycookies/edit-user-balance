<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MonitorBalanceQueue extends Command
{
    protected $signature = 'queue:monitor-balance';
    protected $description = 'Monitor balance update queue';

    public function handle()
    {
        $this->info('Balance queue monitor started...');

        while (true) {
            // 检查队列状态
            $queueSize = Redis::llen('queues:balance');
            $this->info("Current queue size: {$queueSize}");

            // 检查正在处理的任务
            $processing = Redis::keys('user_balance_*');
            $this->info("Processing tasks: " . count($processing));

            // 检查失败的任务
            $failed = \DB::table('failed_jobs')
                ->where('queue', 'balance')
                ->count();
            $this->info("Failed tasks: {$failed}");

            sleep(5); // 每5秒检查一次
        }
    }
}
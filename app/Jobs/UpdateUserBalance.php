<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UpdateUserBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $amount;
    public $tries = 3; // 失败重试次数
    public $timeout = 30; // 超时时间

    public function __construct($userId, $amount)
    {
        $this->userId = $userId;
        $this->amount = $amount;
    }

    public function handle()
    {
        // 使用Redis锁确保任务唯一性
        $lock = Redis::lock('user_balance_' . $this->userId, 10);

        try {
            if ($lock->get()) {
                DB::transaction(function () {
                    $user = User::lockForUpdate()->find($this->userId);

                    if (!$user) {
                        throw new \Exception('User not found');
                    }

                    // 更新余额
                    $user->balance += $this->amount;

                    // 确保余额不为负
                    if ($user->balance < 0) {
                        throw new \Exception('Insufficient balance');
                    }

                    $user->save();

                    // 记录余额变动日志
                    \App\Models\BalanceLog::create([
                        'user_id' => $this->userId,
                        'amount' => $this->amount,
                        'before_balance' => $user->balance - $this->amount,
                        'after_balance' => $user->balance,
                        'job_id' => $this->job->getJobId(),
                    ]);
                });
            }
        } finally {
            // 确保释放锁
            optional($lock)->release();
        }
    }
}
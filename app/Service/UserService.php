<?php

namespace App\Services;

use App\Jobs\UpdateUserBalance;
use Illuminate\Http\Request;
use App\Models\User;

class UserService
{
    public function updateBalance($userId,$amount)
    {
        // 检查用户是否存在
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '用户不存在'
            ], 404);
        }
        // 生成唯一的任务ID（用户ID + 时间戳 + 随机数）
        $jobId = sprintf('balance_%d_%s_%s',
            $userId,
            time(),
            uniqid()
        );

        // 分发任务到队列，使用 jobId 作为任务的唯一标识
        UpdateUserBalance::dispatch($userId, $amount)
            ->onQueue('balance')  // 使用专门的队列
            ->job($jobId);       // 设置任务唯一ID

        return response()->json([
            'success' => true,
            'message' => '余额更新任务已加入队列',
            'job_id' => $jobId
        ]);
    }
}
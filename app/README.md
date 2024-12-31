# 用laravel自带队列功能，更改用户余额，必须保证队列的唯一性，用守护进程保证队列的唯一性

### 前提：
> 在 Supervisor 配置文件中添加队列处理进程：laravel-worker.conf

### 使用方法：
1. 启动队列处理进程：
```bash
supervisorctl start laravel-balance-worker

```
2. 启动监控进程：
```php
php artisan queue:monitor-balance
```

3.调用用户服务类更新余额：
```php
$userService = new \App\Services\UserService();
$response = $userService->updateBalance(new Request([
    'user_id' => 1,
    'amount' => 100
]));
```

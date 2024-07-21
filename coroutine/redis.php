<?php
/**
 * 一键协程化
 * flags设置协程化的函数范围
 * SWOOLE_HOOK_TCP TCP Socket 类型的 stream，包括最常见的 Redis、PDO、Mysqli 以及用 PHP 的 streams 系列函数操作 TCP 连接的操作
 */
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
//是否启用异步风格服务器的协程支持,开启 enable_coroutine 后在指定回调函数会自动创建协程
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
    //获取redis里面的key内容，然后输出到浏览器
    $redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
    $value = $redis->get($request->get['test']);//此处产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
    echo $value;

    //mysql处理逻辑

    //最终执行时间 time=max(redis,mysql)。如果使用传统方式，则执行时间为sum(redis,mysql)
    $response->header("Content-Type", "text/plain");
    $response->end($value);
});

$http->start();

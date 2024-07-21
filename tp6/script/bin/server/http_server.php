<?php

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set([
    'enable_static_handler' => true,
    'document_root' => '/Users/wangyinghui/Desktop/study/swoole/demo/tp6/public/static',
    'worker_num' => 5
]);

//此事件在 Worker 进程 / Task 进程 启动时发生，这里创建的对象可以在进程生命周期内使用。
$http->on('WorkerStart', function () {
    //引入框架文件
    require __DIR__ . '/../../../vendor/autoload.php';

    /**
     * WorkerStart只加载框架代码，真正执行放到request请求中
     */
    // 执行HTTP应用并响应
    /*
    $http = (new think\App())->http;

    $response = $http->run();

    $response->send();

    $http->end($response);
    */
});

$http->on('request', function ($request, $response) use ($http) {
    //再次请求时手动注销$_SERVER等全局变量，因为该类全局变量在同个进程下不会被自动注销。
    $_SERVER = [];

    //解决tp6 debug模式下报错问题
    $_SERVER['argv'] = [];

    //替换Thinkphp中的$_GET等全局变量
    if (isset($request->server)) {
        foreach ($request->server as $k => $v) {
            $_SERVER[strtoupper($k)] = $v;
        }
    }
    if (isset($request->header)) {
        foreach ($request->header as $k => $v) {
            $_SERVER[strtoupper($k)] = $v;
        }
    }
    $_GET = [];
    if (isset($request->get)) {
        foreach ($request->get as $k => $v) {
            $_GET[$k] = $v;
        }
    }
    $_POST = [];
    if (isset($request->post)) {
        foreach ($request->post as $k => $v) {
            $_POST[$k] = $v;
        }
    }

    //开启缓冲区
    ob_start();

    try {
        // 执行HTTP应用并响应
        $httpTp = (new think\App())->http;
        $responseTp = $httpTp->run();

        $responseTp->send();

        $httpTp->end($responseTp);
    } catch (\Exception $e) {
        // todo
    }

    //获取缓冲区内容
    $res = ob_get_contents();
//    //关闭并清除缓冲区
    ob_end_clean();

    $response->end($res);
});

$http->start();
//除了手动引入swoole外，也可以直接tp框架安装 topthink/think-swoole 扩展方式来引入swoole

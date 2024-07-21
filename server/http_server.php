<?php
$http = new Swoole\Http\Server('127.0.0.1', 9501);

$http->set([
    'worker_num' => 4,//worker进程数，一般为cpu的1-4倍
    'max_request' => 10000,
]);

$http->set([
    'enable_static_handler' => true,//开启静态文件请求处理功能
    'document_root' => '/Users/wangyinghui/Desktop/study/swoole/demo/data', //配置静态文件根目录
]);

$http->on('request', function ($request, $response) {
    $response->cookie('singwa', time() + 1800);
    $response->end('sss' . json_encode($request->get));
});
$http->start();


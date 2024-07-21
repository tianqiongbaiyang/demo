<?php
/**
 * tcp服务端
 * 创建Server对象，监听127.0.0.1:9501端口
 */
$server=new Swoole\Server('127.0.0.1',9501);

//设置参数
$server->set([
    'worker_num'=>4,//worker进程数，一般为cpu的1-4倍
    'max_request'=>10000,
]);

/**
 * 监听连接进入事件
 * $fd客户端连接的唯一标识
 * $reactor_id线程id
 */
$server->on('Connect',function($server,$fd,$reactor_id){
    echo "Client: Conect-fd is: $fd,reactor_id is $reactor_id";
});

//监听数据接收事件
$server->on('Receive',function($server,$fd,$reactor_id,$data){
    $server->send($fd,"Server: {$data}-fd is $fd,reactor_id is $reactor_id");
});

//监听连接关闭事件
$server->on('Close',function($server,$fd){
    echo "Client: Close.\n";
});

//启动服务器
$server->start();
<?php

//创建WebSocket Server对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9502);

$ws->set([
    'enable_static_handler' => true,
    'document_root' => '/Users/wangyinghui/Desktop/study/swoole/demo/data'
]);

//监听WebSocket连接打开事件
//$ws->on('Open',function($ws,$request){
//    $ws->push($request->fd,"hello welcome\n");
//});
$ws->on('Open', 'onOpen');
function onOpen($ws, $request)
{
    $ws->push($request->fd, "hello welcome\n");
}

//监听WebSocket消息事件
$ws->on('Message', function ($ws, $frame) {
    echo "Message:{$frame->data}\n";
    echo "fd:{$frame->fd}-opcode:{$frame->opcode}-finish:{$frame->finish}";
    $ws->push($frame->fd, "server:{$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on("Close", function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
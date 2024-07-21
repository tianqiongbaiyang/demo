<?php

class Ws
{
    const HOST = "0.0.0.0";
    const PORT = 8812;

    public $ws = null;

    public function __construct()
    {
        $this->ws = new Swoole\WebSocket\Server("0.0.0.0", 9501);

        $this->ws->set([
            'task_worker_num' => 2 //设置异步任务的工作进程数量
        ]);

        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on("close", [$this, 'onClose']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->start();
    }

    //监听连接事件
    public function onOpen($ws, $request)
    {
        var_dump($request->fd);

        //毫秒定时器，每2秒执行
        Swoole\Timer::tick(2000, function ($timer_id) {
            echo "2s: timerId:$timer_id\n";
        });
    }

    //监听消息事件
    public function onMessage($ws, $frame)
    {
        echo "server-push-message:{$frame->data}\n";

        //投递耗时任务，todo 10s
        //任务参数
        /*        $data=[
                  'task'=>1,
                  'fd'=>$frame->fd
                ];
                $ws->task($data);*/

        //毫秒定时器，5秒后执行,异步的
        Swoole\Timer::after(5000, function () use ($ws, $frame) {
            echo "5s-after\n";
            $ws->push($frame->fd, "server-time_after:");
        });

        $ws->push($frame->fd, "server-push:" . date('Y-m-d H:i:s'));
    }

    //处理异步任务（此回调函数在task进程中执行）
    public function onTask($serv, $taskId, $reactor_id, $data)
    {
        print_r($data);
        sleep(10);
        return "on task finish";//返回任务执行的结果
    }

    //处理异步任务的结果（此回调函数在worker进程中执行）
    public function onFinish($serv, $taskId, $data)
    {
        echo "taskId:{$taskId}\n";
        echo "finish-data-success:{$data}\n";
    }

    public function onClose($ws, $fd)
    {
        echo "clientId:$fd";
    }
}

$obj = new \bin\server\Ws();
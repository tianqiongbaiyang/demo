<?php

use Swoole\Process;

//true 重定向子进程的标准输入和输出。不会打印屏幕而是写入到主进程管道。
$process = new Process(function (Process $pro) {
    echo 222;
    //执行外部程序,php http_server
    $pro->exec("/Users/wangyinghui/Desktop/study/php/bin/php", [__DIR__ . '/../server/http_server.php']);
}, false);
//开启一个子进程
$pid = $process->start();
echo $pid . PHP_EOL;

//回收结束运行的子进程。
Swoole\Process::wait();
<?php
$filename = __DIR__ . '/1.txt';
//协程方式读取文件
Swoole\Coroutine\run(function () use ($filename) {
    $r = Swoole\Coroutine\System::readFile($filename);
    var_dump($r);
    sleep(10);
    echo $filename . PHP_EOL;
});
echo 'start' . PHP_EOL;
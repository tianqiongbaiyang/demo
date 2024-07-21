<?php
//协程方式写入文件
$filename = __DIR__ . '/1.log';
Swoole\Coroutine\run(function () use ($filename) {
    $w = Swoole\Coroutine\System::writeFile($filename, "hello world", FILE_APPEND);
    var_dump($w);
    sleep(5);
    echo $filename . PHP_EOL;
});
echo 'start';
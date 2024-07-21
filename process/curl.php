<?php
//多进程应用场景
echo "process-start-time:" . date("Y-m-d H:i:s");
$workers = [];
$urls = [
    'http://baidu.com',
    'http://baidu.com?search=singwa',
    'http://baidu.com?search=singwa1',
    'http://baidu.com?search=singwa2',
    'http://baidu.com?search=singwa3',
    'http://baidu.com?search=singwa4',
];
for ($i = 0; $i < 6; $i++) {
    //子进程
    $process = new Swoole\Process(function (Swoole\Process $worker) use ($i, $urls) {
        //curl
        $content = curlData($urls[$i]);
        //写入消息到管道
//        echo $content.PHP_EOL;
        $worker->write($content . PHP_EOL);
    }, true);
    $pid = $process->start();
    $workers[$pid] = $process;
}
foreach ($workers as $process) {
    //读取管道消息
    echo $process->read();
}

//模拟请求url 1s
function curlData($url)
{
    //curl file_get_contents
    sleep(1);
    return $url . "success" . PHP_EOL;
}

echo "process-end-time:" . date("Y-m-d H:i:s");

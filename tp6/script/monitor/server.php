<?php

/**
 * 通过监听端口号方式监听服务是否正常
 * linux命令行下执行
 * nohup /Users/wangyinghui/Desktop/study/php/bin/php /Users/wangyinghui/Desktop/study/swoole/demo/tp6/script/monitor/server.php > /Users/wangyinghui/Desktop/study/swoole/demo/tp6/script/monitor/a.txt &
 * 后台运行server.php文件并重定向输出到a.txt文件中。
 * 通过 ps aux |grep monitor/server.php 检查监控文件是否后台正常运行
 * 或者通过 tail -f /Users/wangyinghui/Desktop/study/swoole/demo/tp6/script/monitor/a.txt 查看
 */
class Server
{
    const PORT = 9501;

    public function port()
    {
        /**
         * netstat -anp 2>/dev/null |grep 9501 |grep LISTEN |wc -l
         * 2>/dev/null 把标准输出（例如一些系统的提示信息之类的）重新向到/dev/null文件中
         * wc -l 统计行数
         */
        //linux下执行方式
//        $shell="netstat -anp 2>/dev/null |grep ".self::PORT." |grep LISTEN |wc -l";

        //mac下 netstat -p 参数已被更改为指定协议蔟了,所以需要再加上 tcp 参数才行
        $shell = "netstat -anp tcp 2>/dev/null |grep " . self::PORT . " |grep LISTEN |wc -l";

        $result = shell_exec($shell);
        if ($result != 1) {
            //todo 发送报警服务（邮件、短信）
            echo date("Ymd H:i:s") . " error" . PHP_EOL;
        } else {
            echo date("Ymd H:i:s") . ' success' . PHP_EOL;
        }
    }
}

//用swoole的毫秒级定时器来取代linux的分钟级定时器
Swoole\Timer::tick(2000, function () {
    (new Server())->port();
});



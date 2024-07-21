<?php

use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

class AsyMysql
{
    public $dbSource = "";

    public function __construct()
    {
        /*        run(function () {
                    $swoole_mysql = new MySQL();
                    $swoole_mysql->connect([
                        'host'     => '127.0.0.1',
                        'port'     => 3306,
                        'user'     => 'root',
                        'password' => '123456',
                        'database' => 'root',
                    ]);
                    $res = $swoole_mysql->query('select sleep(1)');
                    var_dump($res);
                });*/

        Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

        Co\run(function () {
            for ($c = 100; $c--;) {
                go(function () {//创建100个协程
                    $redis = new Redis();
                    $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程，不会阻塞进程
                    $redis->get('key');//此处产生协程调度，cpu切到下一个协程，不会阻塞进程
                });
            }
        });
    }
}

$obj = new AsyMysql();

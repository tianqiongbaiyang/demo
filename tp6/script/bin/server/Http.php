<?php

namespace bin\server;

use Swoole;
use think;

class Http
{
    const HOST = "0.0.0.0";
    const PORT = 9501;

    public $http = null;

    public function __construct()
    {
        $this->http = new Swoole\Http\Server(self::HOST, self::PORT);

        $this->http->set([
            'enable_static_handler' => true,
            'document_root' => '/Users/wangyinghui/Desktop/study/swoole/demo/tp6/public/static',
            'worker_num' => 4,
            'task_worker_num' => 4
        ]);

        $this->http->on("workerStart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
        $this->http->on("finish", [$this, 'onFinish']);
        $this->http->on("close", [$this, 'onClose']);

        $this->http->start();
    }

    public function onWorkerStart($server, $worker_id)
    {
        //引入框架文件
//        require __DIR__ . '/../vendor/autoload.php';
        //引入框架文件并先执行应用，保证在其他回调下可以使用tp的基础类库
        require __DIR__ . '/../../../public/index.php';
    }

    public function onRequest($request, $response)
    {
        //再次请求时手动注销$_SERVER等全局变量，因为该类全局变量在同个进程下不会被自动注销。
        $_SERVER = [];

        //解决tp6 debug模式下报错问题
        $_SERVER['argv'] = [];

        //替换Thinkphp中的$_GET等全局变量
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        //存储swoole http server对象，方便tp框架引用
        $_POST['http_server'] = $this->http;

        //开启缓冲区
        ob_start();

        try {
            // 执行HTTP应用并响应
            $httpTp = (new think\App())->http;
            $responseTp = $httpTp->run();

            $responseTp->send();

            $httpTp->end($responseTp);
        } catch (\Exception $e) {
            // todo
        }

        //获取缓冲区内容
        $res = ob_get_contents();
        //关闭并清除缓冲区
        ob_end_clean();

        $response->end($res);
    }

    public function onTask($serv, $taskId, $workerId, $data)
    {
        /*        try{
                    $response=\app\common\lib\ali\Sms::sendSms($data['phone'],$data['code']);
                }catch(\Exception $e){
                    //todo
                    echo $e->getMessage();
                }

                return "on task finish";*/

        //分发task任务，让不同的任务走不同的逻辑
        $obj = new \app\common\lib\task\Task;
        $method = $data['method'];
        $flag = $obj->$method($data['data']);

        return $flag;
    }

    public function onFinish($serv, $taskId, $data)
    {
        echo "taskId:{$taskId}\n";
        echo "finish-data-success:{$data}\n";
    }

    public function onClose($ws, $fd)
    {
        echo "clientId:{$fd}\n";
    }
}

new Http();
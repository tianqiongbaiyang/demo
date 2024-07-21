<?php

namespace bin\server;

use Swoole;
use think;
//命令行执行 nohup  /Users/wangyinghui/Desktop/study/php/bin/php /Users/wangyinghui/Desktop/study/swoole/demo/tp6/script/bin/server/Ws.php > /Users/wangyinghui/Desktop/study/swoole/demo/tp6/script/bin/server/ws.log & 后台挂起运行并重定向输出到ws.log文件
class Ws
{
    const HOST = "0.0.0.0";
    const PORT = 9501;    //赛况服务的监听端口
    const ChART_PORT = 9502;  //聊天室服务的监听端口

    public $ws = null;

    public function __construct()
    {
        /**
         * swoole5.0默认模式为SWOOLE_BASE，这里需要手动设置为SWOOLE_PROCESS，否则在task worker
         * 中进行websocket客户端消息推送时会提示Swoole\WebSocket\Server::push(): session#7 does not exists情况
         */
        $this->ws = new Swoole\Websocket\Server(self::HOST, self::PORT, SWOOLE_PROCESS);

        //增加监听的端口，用于聊天室服务使用
        $this->ws->listen(self::HOST, self::ChART_PORT, SWOOLE_SOCK_TCP);

        $this->ws->set([
            'enable_static_handler' => true,
            'document_root' => '/Users/wangyinghui/Desktop/study/swoole/demo/tp6/public/static',
            'worker_num' => 4,
            'task_worker_num' => 4
        ]);

        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on("workerStart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    /**
     * @return void 启动后在主进程（master）的主线程回调此函数
     */
    public function onStart()
    {
        //设置进程名称
        swoole_set_process_name("live_master");
    }

    public function onWorkerStart($server, $worker_id)
    {
        //引入框架文件
//        require __DIR__ . '/../vendor/autoload.php';
        //引入框架文件并先执行应用，保证在其他回调下可以使用tp的基础类库
        require __DIR__ . '/../../../public/index.php';

        //初始化时，如果redis集合中有fd，则清空掉
        $clients = \app\common\lib\redis\Predis::getInstance()->sMembers(config('redis.live_game_key'));
        foreach ($clients as $fd) {
            \app\common\lib\redis\Predis::getInstance()->sRem(config('redis.live_game_key'), $fd);
        }
    }

    public function onRequest($request, $response)
    {
        //过滤图标请求
        if ($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return;
        }

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
        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }

        $this->writeLog();

        //存储swoole ws server对象，方便tp框架引用
        $_POST['http_server'] = $this->ws;

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

    public function onTask(Swoole\Server $server, $taskId, $workerId, $data)
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
        $flag = $obj->$method($data['data'], $server);

        return $flag;
    }

    public function onFinish($serv, $taskId, $data)
    {
        echo "taskId:{$taskId}\n";
        echo "finish-data-success:{$data}\n";
    }

    public function onClose($ws, $fd)
    {
        //客户端关闭连接时，移除redis中的fd
        \app\common\lib\redis\Predis::getInstance()->sRem(config('redis.live_game_key'), $fd);

        echo "clientId close:{$fd}\n";
    }

    //监听ws消息事件
    public function onMessage($ws, $frame)
    {
        echo "ser-push-message:{$frame->data}\n";
        $ws->push($frame->fd, "server-push:" . date("Y-m-d H:i:s"));
    }

    public function onOpen($ws, $request)
    {
        //这里使用redis集合来存储fd。也可以通过$connections方式直接获取，更加方便。
        //fd redis [1]
        \app\common\lib\redis\Predis::getInstance()->sAdd(config('redis.live_game_key'), $request->fd);

        echo 'clientId opened:' . $request->fd;
    }

    /**
     * 记录日志
     */
    public function writeLog()
    {
        $datas = array_merge(['date' => date("Ymd H:i:s")], $_GET, $_POST, $_SERVER);

        $logs = "";
        foreach ($datas as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $logs .= $key . ":" . $value . " ";
        }

        $filename = __DIR__ . '/../../../runtime/log/' . date("Ym") . "/" . date("d") . "_access_log";
        \Swoole\Coroutine\System::writeFile($filename, $logs . PHP_EOL, FILE_APPEND);


    }
}

new Ws();


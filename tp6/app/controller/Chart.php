<?php

namespace app\controller;

use app\common\lib\Util;

class Chart
{
    public function index()
    {
        //todo 登陆逻辑

        if (empty($_POST['game_id'])) {
            return Util::show(config('code.error'), 'error');
        }
        if (empty($_POST['content'])) {
            return Util::show(config('code.error'), 'error');
        }

        //todo 聊天内容入库以及第一次加载时返回最后20条记录逻辑

        $data = [
            'user' => '用户' . rand(0, 2000), //模拟登陆的用户名
            'content' => $_POST['content']
        ];

        /**
         * 通过connections属性方式获取客户端连接
         * 获取连接9502端口服务的客户端
         */
        foreach ($_POST['http_server']->ports[1]->connections as $fd) {
            $_POST['http_server']->push($fd, json_encode($data));
        }

        return Util::show(config('code.success'), 'ok', $data);
    }
}
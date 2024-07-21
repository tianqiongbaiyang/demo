<?php

namespace app\controller;

use app\common\lib\redis\Predis;
use app\common\lib\Util;

class Live
{
    public function index()
    {
        //所有逻辑判断场景,包括token校验等
        if (empty($_GET)) {
            return Util::show(config('code.error'), 'error');
        }

        //模拟mysql查询场景
        $teams = [
            '1' => [
                'name' => '马刺',
                'logo' => '/live/imgs/team1.png'
            ],
            '4' => [
                'name' => '火箭',
                'logo' => '/live/imgs/team2.png'
            ]
        ];

        $data = [
            'type' => intval($_GET['type']),
            'title' => !empty($teams[$_GET['team_id']]['name']) ? $teams[$_GET['team_id']]['name'] : '直播员',
            'logo' => !empty($teams[$_GET['team_id']]['logo']) ? $teams[$_GET['team_id']]['logo'] : '',
            'content' => !empty($_GET['content']) ? $_GET['content'] : '',
            'image' => !empty($_GET['image']) ? $_GET['image'] : '',
        ];

        //todo 赛况的基本信息入库

        //数据组织好push到直播页面
        /*        $clients=Predis::getInstance()->sMembers(config('redis.live_game_key'));
                foreach($clients as $fd){
                    var_dump($fd);
                    $_POST['http_server']->push($fd,json_encode($data));
                }*/

        //通过task任务进行消息发送
        $taskData = [
            'method' => 'pushLive',
            'data' => $data
        ];
        $_POST['http_server']->task($taskData);

        return Util::show(config('code.success'), 'ok', $_GET);
    }
}
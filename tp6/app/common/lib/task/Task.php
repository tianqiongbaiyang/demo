<?php

namespace app\common\lib\task;

//放swoole所有task异步任务
use app\common\lib\Redis;
use app\common\lib\redis\Predis;

class Task
{
    //异步发送验证码
    public function sendSms($data, $serv)
    {
        try {
            $response = \app\common\lib\ali\Sms::sendSms($data['phone'], $data['code']);
        } catch (\Exception $e) {
            //todo
            return false;
        }

        //如果发送成功，把验证码记录到redis里面
        if ($response['Code'] === 'OK') {
            Predis::getInstance()->set(Redis::smsKey($data['phone']), $data['code'], config('redis.out_time'));
        } else {
            return false;
        }

        return true;
    }

    /**
     * 通过task机制发送赛况实时数据给客户端
     * @param $data
     * @param $serv
     * @return true
     */
    public function pushLive($data, $server)
    {
        $clients = Predis::getInstance()->sMembers(config('redis.live_game_key'));

        foreach ($clients as $fd) {
            $server->push($fd, json_encode($data));
        }

    }
}
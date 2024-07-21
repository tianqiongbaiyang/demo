<?php

namespace app\controller;

use app\common\lib\Redis;
use app\common\lib\redis\Predis;
use app\common\lib\Util;
use app\BaseController;

class Login extends BaseController
{
    public function index()
    {
        $phoneNum = intval($_GET['phone_num']);
        $code = intval($_GET['code']);
        if (empty($phoneNum) || empty($code)) {
            return Util::show(config('code.error'), 'phone or code is error');
        }

        //redis code，这里只能用同步redis，不能用异步方式，因为需要获取redis存储的验证码进行比较
        try {
            $redisCode = Predis::getInstance()->get(Redis::smsKey($phoneNum));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        //模拟mysql存储用户数据
        if ($redisCode == $code) {
            //todo 校验后验证码应该立即失效，避免多次验证情况。

            //写入redis
            $data = [
                'user' => $phoneNum,
                'srcKey' => md5($phoneNum),
                'time' => time(),
                'isLogin' => true
            ];
            Predis::getInstance()->set(Redis::userKey($phoneNum), $data);

            return Util::show(config('code.success'), 'ok', $data);
        } else {
            return Util::show(config('code.error'), 'login error');
        }
    }
}
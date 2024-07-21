<?php

namespace app\common\lib\ali;

class Sms
{
    public static function sendSms($phoneNum, $code)
    {
        //todo

        //模拟阿里大于成功发送验证码返回信息
        $data = [
            'Code' => 'OK'
        ];
        return $data;
    }
}
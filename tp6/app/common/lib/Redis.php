<?php

namespace app\common\lib;

class Redis
{
    //验证码 redis key的前缀
    public static $pre = "sms_";
    //用户 redis key的前缀
    public static $userpre = "user_";

    //存储验证码redis key
    public static function smsKey($phone)
    {
        return self::$pre . $phone;
    }

    //存储用户redis key
    public static function userKey($phone)
    {
        return self::$userpre . $phone;
    }

}
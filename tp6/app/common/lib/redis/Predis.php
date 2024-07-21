<?php

namespace app\common\lib\redis;

//同步redis
class Predis
{
    public $redis = "";
    //定义单例模式的变量
    private static $_instance = null;

    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->redis = new \Redis();
        $result = $this->redis->connect(config('redis.host'), config('redis.port'), config('redis.timeOut'));
        if ($result === false) {
            throw new \Exception('redis connect error');
        }
    }

    public function set($key, $value, $time = 0)
    {
        if (!$key) {
            return '';
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if (!$time) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
    }

    public function get($key)
    {
        if (!$key) {
            return '';
        }
        return $this->redis->get($key);
    }

    /*    public function sAdd($key,$value){
            return $this->redis->sAdd($key,$value);
        }

        public function sRem($key,$value){
            return $this->redis->sRem($key,$value);
        }*/

    public function __call($name, $arguments)
    {
        if (count($arguments) != 2) {
            return '';
        }
        $this->redis->$name($arguments[0], $arguments[1]);
    }

    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }
}
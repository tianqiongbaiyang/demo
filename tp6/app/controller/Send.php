<?php

namespace app\controller;

use app\common\lib\Redis;
use app\common\lib\Util;
use app\common\lib\ali\Sms;
use app\BaseController;

class Send extends BaseController
{
    //发送验证码
    public function index()
    {

        $phoneNum = request()->get('phone_num', 0, 'intval');
        if (empty($phoneNum)) {
            return Util::show(config('code.error'), 'error');
        }
        //生成一个随机数
        $code = rand(1000, 9999);

        /*        try{
                    $response=Sms::sendSms($phoneNum,$code);
                }catch(\Exception $e){
                    return Util::show(config('code.error'), '阿里大于内部异常');
                }

                if($response['Code']==='OK'){
                    //redis
                    try {
                        $redis = new \Redis();
                        $redis->connect(config('redis.host'), config('redis.port'), config('redis.out_time'));
                        $redis->set(Redis::smsKey($phoneNum), $code, config('redis.out_time'));

                    }catch(\Exception $e){
                        var_dump($e->getMessage());
                    }

                    return Util::show(config('code.success'), 'success');
                }else{
                    return Util::show(config('code.error'), 'error');
                }*/

        //使用swoole task异步任务进行验证码发送并存储到redis中
        /*        $taskData=[
                    'phone'=>$phoneNum,
                    'code'=>$code
                ];*/
        $taskData = [
            'method' => 'sendSms',
            'data' => [
                'phone' => $phoneNum,
                'code' => $code
            ]
        ];
        $_POST['http_server']->task($taskData);
        return Util::show(config('code.success'), 'ok');
    }
}
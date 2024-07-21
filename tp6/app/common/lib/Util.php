<?php

namespace app\common\lib;
class Util
{
    //API输出格式
    public static function show($status, $message = '', $data = [])
    {
        $result = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        return json_encode($result);
    }
}
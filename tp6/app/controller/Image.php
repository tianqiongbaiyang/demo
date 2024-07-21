<?php

namespace app\controller;

use app\BaseController;
use app\common\lib\Util;

class Image extends BaseController
{
    public function index()
    {
        $file = request()->file('file');
        // 上传到本地服务器
        $info = \think\facade\Filesystem::disk('upload')->putFile('upload', $file);

        if ($info) {
            $data = [
                'image' => config('live.host') . $info,
            ];
            return Util::show(config('code.success'), 'ok', $data);
        } else {
            return Util::show(config('code.error'), 'error');
        }
    }

}

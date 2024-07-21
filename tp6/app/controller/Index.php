<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return '';
    }

    public function singwa()
    {
        return time();
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello tp6,' . $name;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/9/17
 * Time: 10:27
 */

namespace app\api\controller;


class apiTest
{
    public function weixin()
    {
        $data = [
            'weixin'=>'xiaochengxv'
        ];

        return json($data);

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/9/17
 * Time: 10:48
 */

namespace app\api\controller;


class Test
{
    public function weixin()
    {
        $data = [
            'weixin'=>'xiaochengxv'
        ];

        return json($data);

    }
}
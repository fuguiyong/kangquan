<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/21
 * Time: 13:34
 */

namespace app\index\controller;

use think\Controller;

class Base extends Controller
{
    //返回信息函数
    public function return_msg($errcode, $errmsg = '', $data = [])
    {
        $backInfo = [
            'errcode' => $errcode,
            'errmsg' => $errmsg,
            'data' => $data
        ];

        echo json_encode($backInfo);
        die;
    }

}
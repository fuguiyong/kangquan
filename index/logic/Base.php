<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/21
 * Time: 13:40
 */

namespace app\index\logic;
use think\Model;


class Base extends Model
{
    //返回信息函数
    public function return_msg($errcode, $errmsg = '', $data=null)
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
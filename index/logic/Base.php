<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/21
 * Time: 13:40
 */

namespace app\index\logic;
use think\Model;
use think\Cache;

class Base extends Model
{

    //验证码判断函数
    public function check_valid_code($what,$tel,$valid_code)
    {
        $valid_msg = $tel.'_'.$what;
        //判断是否获取过验证码获取是否过期了
        if(!Cache::has($valid_msg)){
            $this->return_msg('6','验证码过期或者没有获取');
        }
        //验证正确性
        $server_valid_code = Cache::get($valid_msg);
        if($server_valid_code != $valid_code){
            $this->return_msg('7','验证码错误');
        }

    }

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
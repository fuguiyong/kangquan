<?php
namespace app\index\controller;

use aliyunSms\SendMsg;//阿里云
// use smsDemo\smsyzm;//云之讯

class Bind extends Base
{
    //绑定首页
    public function toBind()
    {
        //用户自动登录
        $url = 'http://www.kangquanpay.top/tobinding';
        $this->login($url);
        //返回页面
        return view('bind');
    }

    //验证码获取ajax
    public function getValid()
    {
        //----------阿里云---------
        $sms = new SendMsg();
        return $sms->sendValid();
        //----------云之讯--------
        // $sms = new smsyzm();
        // //执行验证码的发送，并且返回前端json数据
        // return $sms->sendValid();//返回给前端json数据
    }

    //绑定表单
    public function bindForm()
    {
        $userBind = \think\Loader::model('User', 'logic');
        $res = $userBind->binding();
        //返回给前端数据
        return json($res);
    }
}

?>

<?php
namespace app\index\controller;

use aliyunSms\SendMsg;//阿里云
use think\Cache;

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
        //获取用户的手机号,判断验证码还是否有效
        $mobile = input('mobile');
        if (Cache::has($mobile . '_bind')) {
            $this->return_msg('1', '刚刚的验证码还没有过期，还可以重复使用，请稍后再获取');
        } else {

            //产生6随机验证码的函数
            $validCode = $this->randValidCode();
            $sms = new SendMsg();
            $resArr = $sms->sendValid($mobile, $validCode);

            if (@$resArr['Message'] == 'OK' && @$resArr['Code'] = 'OK') {//发送成功
                //先缓存
                $cacheRes = Cache::set($mobile . '_bind', $validCode, 60 * 5);//缓存5分钟
                if (!$cacheRes) {
                    $this->return_msg('3', '服务器错误，请你重新获取验证码');
                }
                $this->return_msg('0', '验证码获取成功，有效期5分钟');
            } else {
                $this->return_msg('2', '验证码发送失败，请你重试');
            }

        }


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

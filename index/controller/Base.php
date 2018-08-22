<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/21
 * Time: 13:34
 */

namespace app\index\controller;

use think\Controller;
use think\Session;
use app\weixin\exClass\weixin\WeAuthorize;//授权服务类

class Base extends Controller
{

    //用户自动登录
    public function login($url)
    {
        //先判断是否登录
        //如果没登陆，判断是否带参数code，是=》获取信息并且登录 ，否=》授权回调
        if (!Session::has('user')) {

            //实例化授权类
            $urlAuth = new WeAuthorize(APPID, APPSECRET);
            if (input('get.code') != null) {//用户已经回调了，直接获取信息
                $userInfo = $urlAuth->get_user_info();
                $user = [
                    'openid' => $userInfo['openid'],
                    'nickname' => $userInfo['nickname'],
                    'sex' => $userInfo['sex'],
                    'headimgurl' => $userInfo['headimgurl']
                ];
                //设置用户登录信息
                Session::set('user', $user);
            } else {//授权回调
                //动态获取当前的url
                // $url = 'http'.$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                // $url = 'http://www.kangquanpay.top/pay';
                $redurl = $urlAuth->get_authorize_url($url);
                //构建跳转地址 跳转
                header("location:{$redurl}");
            }
        }
    }

    //返回信息函数
    public function return_msg($errcode, $errmsg = '', $data = null)
    {
        $backInfo = [
            'errcode' => $errcode,
            'errmsg' => $errmsg,
            'data' => $data
        ];

        echo json_encode($backInfo);
        die;
    }

    //产生6随机验证码的函数
    public function randValidCode()
    {
        $str = '0123456789';
        $validCode = '';
        for ($i = 0; $i < 6; $i++) {

            $validCode .= $str[mt_rand(0, 9)];
        }
        return $validCode;

    }

}